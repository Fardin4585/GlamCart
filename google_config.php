<?php
/**
 * GlamCart - Google OAuth Configuration
 * Configuration file for Google OAuth settings using environment variables
 * 
 * IMPORTANT: Set your actual Google OAuth credentials in the .env file
 * Get them from: https://console.developers.google.com/
 */

// Load environment variables
require_once 'env_loader.php';

// Google OAuth Configuration from environment variables
define('GOOGLE_CLIENT_ID', EnvLoader::get('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID'));
define('GOOGLE_CLIENT_SECRET', EnvLoader::get('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET'));
define('GOOGLE_REDIRECT_URI', EnvLoader::get('GOOGLE_REDIRECT_URI', 'http://localhost/GlamCart/google_callback.php'));

// Google OAuth Scopes
define('GOOGLE_SCOPES', 'openid email profile');

// Google API URLs
define('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USER_INFO_URL', 'https://www.googleapis.com/oauth2/v2/userinfo');

/**
 * Instructions for setting up Google OAuth:
 * 
 * 1. Copy .env.example to .env
 * 2. Edit .env file and add your actual Google OAuth credentials:
 *    GOOGLE_CLIENT_ID=your_actual_client_id
 *    GOOGLE_CLIENT_SECRET=your_actual_client_secret
 * 3. Go to Google Cloud Console: https://console.developers.google.com/
 * 4. Create a new project or select an existing one
 * 5. Enable the Google+ API
 * 6. Go to "Credentials" and create "OAuth 2.0 Client IDs"
 * 7. Set the authorized redirect URI to: http://localhost/GlamCart/google_callback.php
 * 8. Copy the Client ID and Client Secret to your .env file
 * 9. Never commit the .env file to version control!
 */
?>
