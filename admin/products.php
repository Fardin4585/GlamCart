<?php
/**
 * GlamCart - Product Management
 * Admin page to manage all products
 */

require_once '../connection.php';
session_start();

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

// Handle product status toggle
if (isset($_POST['toggle_status'])) {
    $product_id = (int)$_POST['product_id'];
    $new_status = $_POST['new_status'];
    
    $sql = "UPDATE product SET product_status = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $product_id);
    
    if ($stmt->execute()) {
        $success_message = "Product status updated successfully!";
        log_admin_action("Updated product status", "product", $product_id);
    } else {
        $error_message = "Failed to update product status.";
    }
    $stmt->close();
}

// Handle product deletion
if (isset($_POST['delete_product'])) {
    $product_id = (int)$_POST['product_id'];
    
    $sql = "DELETE FROM product WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        $success_message = "Product deleted successfully!";
        log_admin_action("Deleted product", "product", $product_id);
    } else {
        $error_message = "Failed to delete product.";
    }
    $stmt->close();
}

// Get products with pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM product";
$result = $conn->query($count_sql);
$total_products = $result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $per_page);

// Get products
$sql = "SELECT p.*, b.brand_name, c.Category_Name as category_name 
        FROM product p 
        LEFT JOIN brand b ON p.product_brand = b.brand_id 
        LEFT JOIN category c ON p.product_category = c.Category_id 
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - GlamCart Admin</title>
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
        
        .products-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .products-header h1 {
            margin: 0;
        }
        
        .btn-add {
            background: var(--success);
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
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
        
        .badge-secondary {
            background: var(--gray-medium);
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
                    <li><a href="products.php" class="active"><i class="fas fa-box"></i> Products</a></li>
                    <li><a href="add_product.php"><i class="fas fa-plus"></i> Add Product</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
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
                <div class="products-header">
                    <h1>Product Management</h1>
                    <a href="add_product.php" class="btn-add">
                        <i class="fas fa-plus"></i> Add New Product
                    </a>
                </div>
            </div>
            
            <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success_message) ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error_message) ?>
            </div>
            <?php endif; ?>
            
            <!-- Products Table -->
            <div class="card">
                <div class="card-header">
                    <h3>All Products (<?= $total_products ?> total)</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($products)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Brand</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <img src="<?= !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : '../assets/images/placeholder.jpg' ?>" 
                                             alt="<?= htmlspecialchars($product['product_name']) ?>" 
                                             class="product-image">
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($product['product_name']) ?></strong>
                                        <br>
                                        <small class="text-muted">Code: <?= htmlspecialchars($product['product_code']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($product['brand_name']) ?></td>
                                    <td><?= htmlspecialchars($product['category_name']) ?></td>
                                    <td>$<?= number_format($product['product_price'], 2) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $product['product_status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($product['product_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_product.php?id=<?= $product['product_id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to change the status?')">
                                                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                                <input type="hidden" name="new_status" value="<?= $product['product_status'] === 'active' ? 'inactive' : 'active' ?>">
                                                <button type="submit" name="toggle_status" class="btn btn-<?= $product['product_status'] === 'active' ? 'warning' : 'success' ?> btn-sm">
                                                    <i class="fas fa-<?= $product['product_status'] === 'active' ? 'pause' : 'play' ?>"></i>
                                                    <?= $product['product_status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                                                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                                <button type="submit" name="delete_product" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>" class="btn btn-sm">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="btn btn-sm">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <div class="text-center">
                        <i class="fas fa-box" style="font-size: 4rem; color: var(--gray-medium); margin-bottom: 1rem;"></i>
                        <h3>No products found</h3>
                        <p>Start by adding your first product.</p>
                        <a href="add_product.php" class="btn btn-primary">Add Product</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>
