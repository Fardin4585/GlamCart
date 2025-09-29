<?php
/**
 * Session Configuration
 * Configure session settings for persistent login
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure session settings
    ini_set('session.cookie_lifetime', 86400 * 30); // 30 days
    ini_set('session.gc_maxlifetime', 86400 * 30);  // 30 days
    ini_set('session.cookie_httponly', 1);          // Security: prevent XSS
    ini_set('session.use_strict_mode', 1);          // Security: prevent session fixation
    ini_set('session.cookie_secure', 0);            // Set to 1 for HTTPS only
    
    // Set session name
    session_name('GLAMCART_SESSION');
    
    // Start the session
    session_start();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}
?>
