<?php
/**
 * GlamCart - Google OAuth Callback
 * Handle Google OAuth callback and user authentication
 */

require_once 'connection.php';
require_once 'google_config.php';
session_start();

// Check if Google OAuth is properly configured
if (GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID' || GOOGLE_CLIENT_SECRET === 'YOUR_GOOGLE_CLIENT_SECRET') {
    header('Location: login.php?error=' . urlencode('Google OAuth is not configured. Please contact the administrator.'));
    exit();
}

// Check for errors from Google
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
    $error_description = isset($_GET['error_description']) ? htmlspecialchars($_GET['error_description']) : 'Unknown error';
    
    // Redirect to login page with error
    header('Location: login.php?error=' . urlencode($error_description));
    exit();
}

// Check for authorization code
if (!isset($_GET['code'])) {
    header('Location: login.php?error=' . urlencode('No authorization code received'));
    exit();
}

// Verify state parameter for security
if (!isset($_GET['state']) || !isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    header('Location: login.php?error=' . urlencode('Invalid state parameter'));
    exit();
}

$code = $_GET['code'];

// Exchange authorization code for access token
$token_data = [
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'code' => $code,
    'grant_type' => 'authorization_code',
    'redirect_uri' => GOOGLE_REDIRECT_URI
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, GOOGLE_TOKEN_URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$token_response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    header('Location: login.php?error=' . urlencode('Failed to exchange code for token'));
    exit();
}

$token_data = json_decode($token_response, true);

if (!isset($token_data['access_token'])) {
    header('Location: login.php?error=' . urlencode('No access token received'));
    exit();
}

$access_token = $token_data['access_token'];

// Get user information from Google
$user_info_url = GOOGLE_USER_INFO_URL . '?access_token=' . $access_token;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $user_info_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token
]);

$user_response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    header('Location: login.php?error=' . urlencode('Failed to get user information'));
    exit();
}

$user_data = json_decode($user_response, true);

if (!isset($user_data['email'])) {
    header('Location: login.php?error=' . urlencode('No email received from Google'));
    exit();
}

// Extract user information
$google_id = $user_data['id'];
$email = $user_data['email'];
$first_name = $user_data['given_name'] ?? '';
$last_name = $user_data['family_name'] ?? '';
$name = $user_data['name'] ?? '';

// If first_name or last_name is empty, try to split the name
if (empty($first_name) && empty($last_name) && !empty($name)) {
    $name_parts = explode(' ', $name, 2);
    $first_name = $name_parts[0];
    $last_name = isset($name_parts[1]) ? $name_parts[1] : '';
}

// Check if user already exists
$sql = "SELECT user_id, user_f_name, user_l_name, user_email, google_id FROM users WHERE user_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User exists, update Google ID if not set and log them in
    $user = $result->fetch_assoc();
    
    if (empty($user['google_id'])) {
        // Update user with Google ID
        $update_sql = "UPDATE users SET google_id = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $google_id, $user['user_id']);
        $update_stmt->execute();
        $update_stmt->close();
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_f_name'] = $user['user_f_name'];
    $_SESSION['user_l_name'] = $user['user_l_name'];
    $_SESSION['user_email'] = $user['user_email'];
    $_SESSION['user_role'] = 'customer';
    
    $stmt->close();
    
    // Log successful login if function exists
    if (function_exists('log_admin_action')) {
        log_admin_action('google_login', 'users', $user['user_id']);
    }
    
    // Redirect to home page
    redirect('index.php');
} else {
    // User doesn't exist, create new account
    $stmt->close();
    
    // Generate a random password for Google users (they won't use it)
    $random_password = bin2hex(random_bytes(8));
    $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
    
    // Insert new user
    $insert_sql = "INSERT INTO users (user_f_name, user_l_name, user_email, user_password, google_id) VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $google_id);
    
    if ($insert_stmt->execute()) {
        $user_id = $conn->insert_id;
        
        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_f_name'] = $first_name;
        $_SESSION['user_l_name'] = $last_name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'customer';
        
        // Log registration if function exists
        if (function_exists('log_admin_action')) {
            log_admin_action('google_registration', 'users', $user_id);
        }
        
        $insert_stmt->close();
        
        // Redirect to home page
        redirect('index.php');
    } else {
        $insert_stmt->close();
        header('Location: login.php?error=' . urlencode('Failed to create account'));
        exit();
    }
}

// Clear OAuth state
unset($_SESSION['oauth_state']);
?>
