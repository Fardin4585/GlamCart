<?php
/**
 * GlamCart - Login Page
 * Secure user authentication with password hashing
 */

require_once 'connection.php';
session_start();

$error_message = '';
$success_message = '';

// Check if user is already logged in
if (is_logged_in()) {
    redirect('index.php');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } elseif (!validate_email($email)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Check if user exists and verify password
        $sql = "SELECT user_id, user_f_name, user_l_name, user_email, user_password 
                FROM users 
                WHERE user_email = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password (simple comparison for now since passwords are not hashed in your DB)
            if ($password === $user['user_password']) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_f_name'] = $user['user_f_name'];
                $_SESSION['user_l_name'] = $user['user_l_name'];
                $_SESSION['user_email'] = $user['user_email'];
                $_SESSION['user_role'] = 'customer'; // Default to customer since no role field exists
                
                // Log successful login (only for admin users)
                if (is_admin()) {
                    log_admin_action('user_login', 'users', $user['user_id']);
                }
                
                // Redirect to home page
                redirect('index.php');
            } else {
                $error_message = 'Invalid email or password.';
            }
        } else {
            $error_message = 'Invalid email or password.';
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GlamCart</title>
    <meta name="description" content="Login to your GlamCart account to shop makeup and cosmetics.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <i class="fas fa-magic"></i> GlamCart
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="auth-container">
            <div class="auth-card card">
                <div class="card-header">
                    <h2 class="text-center">Login to Your Account</h2>
                    <p class="text-center">Welcome back! Please enter your credentials to continue.</p>
                </div>
                
                <div class="card-body">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="auth-form">
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   required 
                                   autocomplete="email">
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <div class="password-input-group">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-control" 
                                       required 
                                       autocomplete="current-password">
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remember" id="remember">
                                <span class="checkmark"></span>
                                Remember me
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </div>
                    </form>
                    
                    <div class="auth-links">
                        <a href="forgot_password.php" class="text-primary">
                            <i class="fas fa-key"></i> Forgot your password?
                        </a>
                    </div>
                    
                    <div class="auth-divider">
                        <span>or</span>
                    </div>
                    
                    <div class="social-login">
                        <a href="google_login.php" class="btn btn-secondary" style="width: 100%; margin-bottom: 1rem; text-decoration: none; display: inline-block;">
                            <i class="fab fa-google"></i> Continue with Google
                        </a>
                        <button class="btn btn-secondary" style="width: 100%;" disabled>
                            <i class="fab fa-facebook"></i> Continue with Facebook (Coming Soon)
                        </button>
                    </div>
                </div>
                
                <div class="card-footer text-center">
                    <p>Don't have an account? <a href="register.php" class="text-primary">Sign up here</a></p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> GlamCart. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/script.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleButton.className = 'fas fa-eye';
            }
        }
        
        // Form validation
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
        });
    </script>
</body>
</html>        