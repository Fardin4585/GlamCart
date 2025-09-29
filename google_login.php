<?php
/**
 * GlamCart - Google OAuth Login
 * Initialize Google OAuth authentication
 */

require_once 'connection.php';
require_once 'google_config.php';

// Check if user is already logged in
if (is_logged_in()) {
    redirect('index.php');
}

// Check if Google OAuth is properly configured
if (GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID' || GOOGLE_CLIENT_SECRET === 'YOUR_GOOGLE_CLIENT_SECRET') {
    header('Location: login.php?error=' . urlencode('Google OAuth is not configured. Please contact the administrator.'));
    exit();
}

// Generate state parameter for security
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

// Build Google OAuth URL
$auth_url = GOOGLE_AUTH_URL . '?' . http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'scope' => GOOGLE_SCOPES,
    'response_type' => 'code',
    'state' => $state,
    'access_type' => 'offline',
    'prompt' => 'consent'
]);

// Redirect to Google OAuth
header('Location: ' . $auth_url);
exit();
?>
