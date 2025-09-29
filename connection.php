<?php
/**
 * Database Connection Configuration
 * GlamCart - Makeup and Cosmetics Shop Management System
 */

// Load session configuration for persistent login
require_once 'session_config.php';

// Database configuration
$hostname = 'localhost';
$username = 'root';
$password = '';
$dbname = 'glam_cart';

// Create connection with error handling
try {
    $conn = new mysqli($hostname, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4 for proper character encoding
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Log error and display user-friendly message
    error_log("Database connection error: " . $e->getMessage());
    die("Sorry, we're experiencing technical difficulties. Please try again later.");
}

// Helper function to sanitize input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Helper function to validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Helper function to generate secure password hash
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Helper function to verify password
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Helper function to generate random string
function generate_random_string($length = 10) {
    return bin2hex(random_bytes($length));
}

// Helper function to format price
function format_price($price) {
    return number_format($price, 2);
}

// Helper function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper function to get cart count from database
function getCartCountFromDB() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }
    
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT SUM(quantity) as count FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['count'] ?? 0;
}

// Helper function to get wishlist count from database
function getWishlistCountFromDB() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }
    
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['count'] ?? 0;
}

// Admin functions
function is_admin() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT au.admin_id, au.admin_role, au.is_active, u.user_f_name, u.user_l_name 
            FROM admin_users au 
            JOIN users u ON au.user_id = u.user_id 
            WHERE au.user_id = ? AND au.is_active = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin_data = $result->fetch_assoc();
    $stmt->close();
    
    if ($admin_data) {
        // Store admin info in session for easy access
        $_SESSION['admin_id'] = $admin_data['admin_id'];
        $_SESSION['admin_role'] = $admin_data['admin_role'];
        $_SESSION['admin_name'] = $admin_data['user_f_name'] . ' ' . $admin_data['user_l_name'];
        return true;
    }
    
    return false;
}

function get_admin_role() {
    return $_SESSION['admin_role'] ?? null;
}

function has_admin_permission($permission) {
    $role = get_admin_role();
    
    if ($role === 'super_admin') {
        return true; // Super admin has all permissions
    }
    
    // Define role-based permissions
    $role_permissions = [
        'admin' => [
            'manage_products',
            'manage_users', 
            'manage_orders',
            'view_reports',
            'manage_categories',
            'manage_brands'
        ],
        'moderator' => [
            'view_orders',
            'view_users',
            'view_products',
            'update_order_status'
        ]
    ];
    
    return in_array($permission, $role_permissions[$role] ?? []);
}

function log_admin_action($action, $table_name = null, $record_id = null, $details = null) {
    global $conn;
    
    // Only log if user is an admin
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }
    
    // Check if admin_logs table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'admin_logs'");
    if (!$table_check || $table_check->num_rows == 0) {
        return false; // Table doesn't exist, skip logging
    }
    
    $admin_id = $_SESSION['admin_id'];
    $admin_name = $_SESSION['admin_name'] ?? 'Unknown Admin';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    try {
        $sql = "INSERT INTO admin_logs (admin_id, admin_name, action, table_name, record_id, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssisss", $admin_id, $admin_name, $action, $table_name, $record_id, $details, $ip_address, $user_agent);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        // If there's any error, just return false instead of crashing
        return false;
    }
}
?>
  
  