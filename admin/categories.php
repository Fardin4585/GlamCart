<?php
/**
 * GlamCart - Category Management
 * Admin page to manage product categories
 */

require_once '../connection.php';

// Check if user is logged in and is admin
if (!is_logged_in()) {
    header('Location: ../admin_login.php');
    exit;
}

// Check if user is admin
if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}

$errors = [];
$success_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $category_name = trim($_POST['category_name'] ?? '');
        
        if (empty($category_name)) {
            $errors[] = "Category name is required";
        } else {
            // Check if category already exists
            $check_sql = "SELECT Category_id FROM category WHERE Category_Name = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $category_name);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $errors[] = "Category already exists";
            } else {
                $sql = "INSERT INTO category (Category_Name) VALUES (?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $category_name);
                
                if ($stmt->execute()) {
                    $success_message = "Category added successfully!";
                    log_admin_action("Added category", "category", $conn->insert_id);
                } else {
                    $errors[] = "Failed to add category: " . $conn->error;
                }
                $stmt->close();
            }
            $check_stmt->close();
        }
    }
    
    if (isset($_POST['delete_category'])) {
        $category_id = (int)$_POST['category_id'];
        
        // Check if category is used by products
        $check_sql = "SELECT COUNT(*) as count FROM product WHERE product_category = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $category_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $product_count = $check_result->fetch_assoc()['count'];
        $check_stmt->close();
        
        if ($product_count > 0) {
            $errors[] = "Cannot delete category. It is used by " . $product_count . " product(s).";
        } else {
            $sql = "DELETE FROM category WHERE Category_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $category_id);
            
            if ($stmt->execute()) {
                $success_message = "Category deleted successfully!";
                log_admin_action("Deleted category", "category", $category_id);
            } else {
                $errors[] = "Failed to delete category: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Get all categories
$categories = [];
$sql = "SELECT c.*, COUNT(p.product_id) as product_count 
        FROM category c 
        LEFT JOIN product p ON c.Category_id = p.product_category 
        GROUP BY c.Category_id 
        ORDER BY c.Category_Name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - GlamCart Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 250px;
            background: var(--primary);
            color: white;
            padding: 1rem;
        }
        
        .admin-content {
            flex: 1;
            padding: 2rem;
            background: #f8f9fa;
        }
        
        .admin-header {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 2rem;
        }
        
        .admin-nav {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
        }
        
        .admin-nav li {
            margin-bottom: 0.5rem;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 0.75rem 1rem;
            display: block;
            border-radius: 4px;
            transition: background 0.3s;
        }
        
        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(255,255,255,0.1);
        }
        
        .admin-nav i {
            margin-right: 0.5rem;
            width: 20px;
        }
        
        .admin-header p, .admin-header small {
            color: rgba(255, 255, 255, 0.9);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }
        
        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .badge-success {
            background: var(--success);
            color: white;
        }
        
        .badge-warning {
            background: var(--warning);
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-header">
                <h2><i class="fas fa-magic"></i> GlamCart Admin</h2>
                <p>Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['user_f_name']) ?></p>
            </div>
            
            <nav>
                <ul class="admin-nav">
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                    <li><a href="add_product.php"><i class="fas fa-plus"></i> Add Product</a></li>
                    <li><a href="categories.php" class="active"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="brands.php"><i class="fas fa-copyright"></i> Brands</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <?php if (has_admin_permission('manage_admins')): ?>
                    <li><a href="manage_admins.php"><i class="fas fa-user-shield"></i> Manage Admins</a></li>
                    <?php endif; ?>
                    <li><a href="../index.php"><i class="fas fa-home"></i> Back to Site</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Admin Content -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>Category Management</h1>
                <p>Manage product categories</p>
            </div>
            
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success_message) ?>
            </div>
            <?php endif; ?>
            
            <div class="form-grid">
                <!-- Add Category -->
                <div class="form-section">
                    <h3><i class="fas fa-plus"></i> Add New Category</h3>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="category_name" class="form-label">Category Name *</label>
                            <input type="text" id="category_name" name="category_name" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['category_name'] ?? '') ?>" required>
                        </div>
                        
                        <button type="submit" name="add_category" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    </form>
                </div>
                
                <!-- Categories List -->
                <div class="form-section">
                    <h3><i class="fas fa-list"></i> All Categories</h3>
                    
                    <?php if (!empty($categories)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Category Name</th>
                                    <th>Products</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($category['Category_Name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $category['product_count'] > 0 ? 'success' : 'warning' ?>">
                                            <?= $category['product_count'] ?> product(s)
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($category['product_count'] == 0): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                                <input type="hidden" name="category_id" value="<?= $category['Category_id'] ?>">
                                                <button type="submit" name="delete_category" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <span class="text-muted">Cannot delete (has products)</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p>No categories found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>
