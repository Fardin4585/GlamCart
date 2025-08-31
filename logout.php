<?php
/**
 * GlamCart - Logout Page
 * Secure session destruction and logout
 */

require_once 'connection.php';
session_start();

// Log the logout action if user is logged in and is admin
if (is_logged_in() && is_admin()) {
    log_admin_action('user_logout', 'users', $_SESSION['user_id']);
}

// Destroy all session data
$_SESSION = array();

// If a session cookie is used, destroy it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to appropriate login page based on user type
if (isset($_SESSION['admin_id'])) {
    // Admin user - redirect to admin login
    redirect('admin_login.php?message=logged_out');
} else {
    // Regular user - redirect to regular login
    redirect('login.php?message=logged_out');
}
?>