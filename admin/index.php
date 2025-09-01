<?php
/**
 * GlamCart - Admin Dashboard
 * Main admin panel entry point
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

// Get quick statistics
$stats = [];

// Total products
$sql = "SELECT COUNT(*) as total FROM product";
$result = $conn->query($sql);
$stats['total_products'] = $result->fetch_assoc()['total'];

// Total users
$sql = "SELECT COUNT(*) as total FROM users";
$result = $conn->query($sql);
$stats['total_users'] = $result->fetch_assoc()['total'];

// Total orders
$sql = "SELECT COUNT(*) as total FROM orders";
$result = $conn->query($sql);
$stats['total_orders'] = $result->fetch_assoc()['total'];

// Recent orders
$recent_orders = [];
$sql = "SELECT o.*, u.user_f_name, u.user_l_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.user_id 
        ORDER BY o.order_date DESC 
        LIMIT 5";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recent_orders[] = $row;
    }
}

// Recent products
$recent_products = [];
$sql = "SELECT p.*, b.brand_name, c.Category_Name as category_name 
        FROM product p 
        LEFT JOIN brand b ON p.product_brand = b.brand_id 
        LEFT JOIN category c ON p.product_category = c.Category_id 
        ORDER BY p.created_at DESC 
        LIMIT 5";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recent_products[] = $row;
    }
}

// Total contact messages
$sql = "SELECT COUNT(*) as total FROM contact_messages";
$result = $conn->query($sql);
$stats['total_messages'] = $result ? $result->fetch_assoc()['total'] : 0;

// Unread contact messages
$sql = "SELECT COUNT(*) as total FROM contact_messages WHERE status = 'unread'";
$result = $conn->query($sql);
$stats['unread_messages'] = $result ? $result->fetch_assoc()['total'] : 0;

// Recent contact messages
$recent_messages = [];
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recent_messages[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GlamCart</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            margin: 0 0 0.5rem 0;
            color: var(--primary);
        }
        
        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--text-dark);
        }
        
        .recent-section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
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
                    <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                    <li><a href="add_product.php"><i class="fas fa-plus"></i> Add Product</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="brands.php"><i class="fas fa-copyright"></i> Brands</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="contact_messages.php"><i class="fas fa-envelope"></i> Contact Messages</a></li>
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
                <h1>Admin Dashboard</h1>
                <p>Overview of your GlamCart store</p>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions" style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem;">Quick Actions</h3>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="add_product.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Product
                    </a>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-box"></i> Manage Products
                    </a>
                    <a href="orders.php" class="btn btn-success">
                        <i class="fas fa-shopping-cart"></i> View Orders
                    </a>
                    <a href="users.php" class="btn btn-info">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="categories.php" class="btn btn-warning">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                    <a href="brands.php" class="btn btn-dark">
                        <i class="fas fa-copyright"></i> Brands
                    </a>
                    <a href="contact_messages.php" class="btn btn-info">
                        <i class="fas fa-envelope"></i> Contact Messages
                    </a>
                    <?php if (has_admin_permission('manage_admins')): ?>
                    <a href="manage_admins.php" class="btn btn-danger">
                        <i class="fas fa-user-shield"></i> Manage Admins
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><i class="fas fa-box"></i> Total Products</h3>
                    <div class="stat-number"><?= $stats['total_products'] ?></div>
                    <p>Products in store</p>
                </div>
                
                <div class="stat-card">
                    <h3><i class="fas fa-users"></i> Total Users</h3>
                    <div class="stat-number"><?= $stats['total_users'] ?></div>
                    <p>Registered users</p>
                </div>
                
                <div class="stat-card">
                    <h3><i class="fas fa-shopping-cart"></i> Total Orders</h3>
                    <div class="stat-number"><?= $stats['total_orders'] ?></div>
                    <p>Orders placed</p>
                </div>
                
                <div class="stat-card">
                    <h3><i class="fas fa-envelope"></i> Contact Messages</h3>
                    <div class="stat-number"><?= $stats['total_messages'] ?></div>
                    <p><?= $stats['unread_messages'] ?> unread</p>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="recent-section">
                <h3><i class="fas fa-clock"></i> Recent Orders</h3>
                <?php if (!empty($recent_orders)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?= $order['order_id'] ?></td>
                                <td><?= htmlspecialchars($order['user_f_name'] . ' ' . $order['user_l_name']) ?></td>
                                <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                <td><span class="badge badge-<?= $order['status'] === 'completed' ? 'success' : 'warning' ?>"><?= ucfirst($order['status']) ?></span></td>
                                <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p>No recent orders</p>
                <?php endif; ?>
            </div>
            
            <!-- Recent Products -->
            <div class="recent-section">
                <h3><i class="fas fa-star"></i> Recent Products</h3>
                <?php if (!empty($recent_products)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Brand</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['product_name']) ?></td>
                                <td><?= htmlspecialchars($product['brand_name']) ?></td>
                                <td><?= htmlspecialchars($product['category_name']) ?></td>
                                <td>$<?= number_format($product['product_price'], 2) ?></td>
                                <td><span class="badge badge-<?= $product['product_status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($product['product_status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p>No products added yet</p>
                <?php endif; ?>
            </div>
            
            <!-- Recent Contact Messages -->
            <div class="recent-section">
                <h3><i class="fas fa-envelope"></i> Recent Contact Messages</h3>
                <?php if (!empty($recent_messages)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_messages as $msg): ?>
                            <tr>
                                <td><?= htmlspecialchars($msg['name']) ?></td>
                                <td><?= htmlspecialchars($msg['email']) ?></td>
                                <td><?= htmlspecialchars($msg['subject']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $msg['status'] === 'unread' ? 'warning' : ($msg['status'] === 'read' ? 'info' : 'success') ?>">
                                        <?= ucfirst($msg['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($msg['created_at'])) ?></td>
                                <td>
                                    <a href="contact_messages.php?id=<?= $msg['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="contact_messages.php?action=mark_read&id=<?= $msg['id'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-check"></i> Mark Read
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p>No contact messages yet</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>
