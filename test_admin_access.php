<?php
/**
 * GlamCart - Admin Access Test
 * Simple page to test admin access and show available admin pages
 */

require_once 'connection.php';

echo "<h1>GlamCart Admin Access Test</h1>";

// Check if user is logged in
if (!is_logged_in()) {
    echo "<p style='color: red;'>❌ User is NOT logged in</p>";
    echo "<p><a href='login.php'>Login as Regular User</a> | <a href='admin_login.php'>Login as Admin</a></p>";
    exit;
}

echo "<p style='color: green;'>✅ User is logged in</p>";
echo "<p><strong>User:</strong> " . htmlspecialchars($_SESSION['user_f_name'] . ' ' . $_SESSION['user_l_name']) . "</p>";
echo "<p><strong>Email:</strong> " . htmlspecialchars($_SESSION['user_email']) . "</p>";

// Check if user is admin
if (is_admin()) {
    echo "<p style='color: green;'>✅ User is ADMIN</p>";
    echo "<p><strong>Admin Role:</strong> " . htmlspecialchars($_SESSION['admin_role'] ?? 'Unknown') . "</p>";
    
    echo "<h2>Available Admin Pages:</h2>";
    echo "<ul>";
    echo "<li><a href='admin/'>Admin Dashboard</a></li>";
    echo "<li><a href='admin/products.php'>Manage Products</a></li>";
    echo "<li><a href='admin/add_product.php'>Add Product</a></li>";
    echo "<li><a href='admin/categories.php'>Manage Categories</a></li>";
    echo "<li><a href='admin/brands.php'>Manage Brands</a></li>";
    echo "<li><a href='admin/users.php'>Manage Users</a></li>";
    echo "<li><a href='admin/orders.php'>Manage Orders</a></li>";
    if (has_admin_permission('manage_admins')) {
        echo "<li><a href='admin/manage_admins.php'>Manage Admins</a></li>";
    }
    echo "</ul>";
    
    echo "<h2>Admin Functions:</h2>";
    echo "<ul>";
    echo "<li>✅ Can access admin panel</li>";
    echo "<li>✅ Can manage products</li>";
    echo "<li>✅ Can manage users</li>";
    echo "<li>✅ Can manage orders</li>";
    echo "<li>✅ Can manage categories and brands</li>";
    if (has_admin_permission('manage_admins')) {
        echo "<li>✅ Can manage other admins</li>";
    } else {
        echo "<li>❌ Cannot manage other admins (insufficient permissions)</li>";
    }
    echo "</ul>";
    
} else {
    echo "<p style='color: red;'>❌ User is NOT an admin</p>";
    echo "<p>Regular users cannot access admin pages.</p>";
    echo "<p><a href='setup_admin.php'>Setup Admin Access</a></p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Back to Main Site</a> | <a href='logout.php'>Logout</a></p>";
?>
