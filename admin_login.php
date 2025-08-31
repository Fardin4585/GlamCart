<?php
/**
 * GlamCart - Admin Login
 * Separate login page for admin users only
 */

require_once 'connection.php';
session_start();

// If already logged in as admin, redirect to admin panel
if (is_logged_in() && is_admin()) {
    redirect('admin/');
}

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } else {
        // Check if user exists and is admin
        $sql = "SELECT u.*, au.admin_id, au.admin_role, au.is_active 
                FROM users u 
                JOIN admin_users au ON u.user_id = au.user_id 
                WHERE u.user_email = ? AND au.is_active = 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Check password (plain text for your existing DB)
            if ($password === $user['user_password']) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_f_name'] = $user['user_f_name'];
                $_SESSION['user_l_name'] = $user['user_l_name'];
                $_SESSION['user_email'] = $user['user_email'];
                $_SESSION['admin_id'] = $user['admin_id'];
                $_SESSION['admin_role'] = $user['admin_role'];
                $_SESSION['admin_name'] = $user['user_f_name'] . ' ' . $user['user_l_name'];
                $_SESSION['user_role'] = 'admin';
                
                // Log admin login
                log_admin_action('admin_login', 'users', $user['user_id']);
                
                // Redirect to admin panel
                redirect('admin/');
            } else {
                $error_message = 'Invalid email or password.';
            }
        } else {
            $error_message = 'Access denied. Admin privileges required.';
        }
        
        $stmt->close();
    }
}

// Check for success message from logout
if (isset($_GET['message']) && $_GET['message'] === 'logged_out') {
    $success_message = 'You have been logged out successfully.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GlamCart</title>
    <meta name="description" content="Admin login for GlamCart management system.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%);
            padding: 2rem;
        }
        
        .admin-login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 3rem;
            width: 100%;
            max-width: 400px;
        }
        
        .admin-login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .admin-login-header h1 {
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .admin-login-header .admin-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .back-to-site {
            text-align: center;
            margin-top: 2rem;
        }
        
        .back-to-site a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-to-site a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-card">
            <div class="admin-login-header">
                <div class="admin-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1>Admin Login</h1>
                <p>Access GlamCart Management System</p>
            </div>
            
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
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Admin Email
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
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <i class="fas fa-sign-in-alt"></i> Login to Admin Panel
                    </button>
                </div>
            </form>
            
            <div class="back-to-site">
                <a href="index.php">
                    <i class="fas fa-arrow-left"></i> Back to Main Site
                </a>
            </div>
        </div>
    </div>
    
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
    </script>
</body>
</html>
