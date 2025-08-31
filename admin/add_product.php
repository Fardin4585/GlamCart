<?php
/**
 * GlamCart - Add Product
 * Admin page to add new products
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

$errors = [];
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $product_name = trim($_POST['product_name'] ?? '');
    $product_category = (int)($_POST['product_category'] ?? 0);
    $product_brand = (int)($_POST['product_brand'] ?? 0);
    $product_code = trim($_POST['product_code'] ?? '');
    $product_price = (float)($_POST['product_price'] ?? 0);
    $product_sale_price = !empty($_POST['product_sale_price']) ? (float)$_POST['product_sale_price'] : null;
    $product_description = trim($_POST['product_description'] ?? '');
    $product_status = $_POST['product_status'] ?? 'active';
    $product_featured = isset($_POST['product_featured']) ? 1 : 0;
    
    // Validation
    if (empty($product_name)) {
        $errors[] = "Product name is required";
    }
    
    if ($product_category <= 0) {
        $errors[] = "Please select a category";
    }
    
    if ($product_brand <= 0) {
        $errors[] = "Please select a brand";
    }
    
    if (empty($product_code)) {
        $errors[] = "Product code is required";
    }
    
    if ($product_price <= 0) {
        $errors[] = "Product price must be greater than 0";
    }
    
    if ($product_sale_price !== null && $product_sale_price >= $product_price) {
        $errors[] = "Sale price must be less than regular price";
    }
    
    // Handle image upload
    $product_image = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/products/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_info = pathinfo($_FILES['product_image']['name']);
        $file_extension = strtolower($file_info['extension']);
        
        // Check file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($file_extension, $allowed_types)) {
            $errors[] = "Only JPG, PNG, GIF, and WebP images are allowed";
        }
        
        // Check file size (max 5MB)
        if ($_FILES['product_image']['size'] > 5 * 1024 * 1024) {
            $errors[] = "Image file size must be less than 5MB";
        }
        
        if (empty($errors)) {
            $filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                $product_image = 'assets/images/products/' . $filename;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }
    
    // If no errors, insert product
    if (empty($errors)) {
        $sql = "INSERT INTO product (product_name, product_category, product_brand, product_code, 
                                   product_price, product_sale_price, product_description, 
                                   product_image, product_status, product_featured, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siissdsssi", $product_name, $product_category, $product_brand, 
                         $product_code, $product_price, $product_sale_price, $product_description, 
                         $product_image, $product_status, $product_featured);
        
        if ($stmt->execute()) {
            $product_id = $conn->insert_id;
            $success_message = "Product added successfully!";
            log_admin_action("Added new product", "product", $product_id);
            
            // Clear form data
            $_POST = [];
        } else {
            $errors[] = "Failed to add product: " . $conn->error;
        }
        $stmt->close();
    }
}

// Get categories for dropdown
$categories = [];
$sql = "SELECT Category_id, Category_Name FROM category ORDER BY Category_Name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Get brands for dropdown
$brands = [];
$sql = "SELECT brand_id, brand_name FROM brand ORDER BY brand_name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $brands[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - GlamCart Admin</title>
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
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1rem;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            border-radius: 4px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
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
                    <li><a href="add_product.php" class="active"><i class="fas fa-plus"></i> Add Product</a></li>
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
                <h1>Add New Product</h1>
                <p>Add a new product to your store</p>
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
            
            <form method="POST" enctype="multipart/form-data" class="form-grid">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3>Basic Information</h3>
                    
                    <div class="form-group">
                        <label for="product_name" class="form-label">Product Name *</label>
                        <input type="text" id="product_name" name="product_name" class="form-control" 
                               value="<?= htmlspecialchars($_POST['product_name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_code" class="form-label">Product Code *</label>
                        <input type="text" id="product_code" name="product_code" class="form-control" 
                               value="<?= htmlspecialchars($_POST['product_code'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_category" class="form-label">Category *</label>
                        <select id="product_category" name="product_category" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['Category_id'] ?>" 
                                    <?= (isset($_POST['product_category']) && $_POST['product_category'] == $category['Category_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['Category_Name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_brand" class="form-label">Brand *</label>
                        <select id="product_brand" name="product_brand" class="form-select" required>
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $brand): ?>
                            <option value="<?= $brand['brand_id'] ?>" 
                                    <?= (isset($_POST['product_brand']) && $_POST['product_brand'] == $brand['brand_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($brand['brand_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_description" class="form-label">Description</label>
                        <textarea id="product_description" name="product_description" class="form-control" rows="4"><?= htmlspecialchars($_POST['product_description'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <!-- Pricing & Status -->
                <div class="form-section">
                    <h3>Pricing & Status</h3>
                    
                    <div class="form-group">
                        <label for="product_price" class="form-label">Regular Price *</label>
                        <input type="number" id="product_price" name="product_price" class="form-control" 
                               step="0.01" min="0" value="<?= htmlspecialchars($_POST['product_price'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_sale_price" class="form-label">Sale Price</label>
                        <input type="number" id="product_sale_price" name="product_sale_price" class="form-control" 
                               step="0.01" min="0" value="<?= htmlspecialchars($_POST['product_sale_price'] ?? '') ?>">
                        <small class="form-text">Leave empty if no sale price</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_status" class="form-label">Status</label>
                        <select id="product_status" name="product_status" class="form-select">
                            <option value="active" <?= (isset($_POST['product_status']) && $_POST['product_status'] === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= (isset($_POST['product_status']) && $_POST['product_status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="product_featured" name="product_featured" 
                               <?= (isset($_POST['product_featured']) && $_POST['product_featured']) ? 'checked' : '' ?>>
                        <label for="product_featured">Featured Product</label>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_image" class="form-label">Product Image</label>
                        <input type="file" id="product_image" name="product_image" class="form-control" accept="image/*">
                        <small class="form-text">Max size: 5MB. Allowed formats: JPG, PNG, GIF, WebP</small>
                        
                        <div class="image-preview" id="image-preview">
                            <i class="fas fa-image" style="font-size: 3rem; color: #ddd;"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="form-section" style="grid-column: 1 / -1;">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus"></i> Add Product
                        </button>
                        <a href="products.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> Back to Products
                        </a>
                    </div>
                </div>
            </form>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script>
        // Image preview functionality
        document.getElementById('product_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('image-preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<i class="fas fa-image" style="font-size: 3rem; color: #ddd;"></i>';
            }
        });
    </script>
</body>
</html>
