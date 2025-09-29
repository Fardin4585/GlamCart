<?php
/**
 * GlamCart - Registration Page
 * Secure user registration with validation and password hashing
 */

require_once 'connection.php';

$error_message = '';
$success_message = '';

// Check if user is already logged in
if (is_logged_in()) {
    redirect('index.php');
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = sanitize_input($_POST['address']);
    $city = sanitize_input($_POST['city']);
    $state = sanitize_input($_POST['state']);
    $zip = sanitize_input($_POST['zip']);
    
    // Validate input
    $errors = [];
    
    if (empty($first_name)) {
        $errors[] = 'First name is required.';
    }
    
    if (empty($last_name)) {
        $errors[] = 'Last name is required.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!validate_email($email)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $sql = "SELECT user_id FROM users WHERE user_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = 'An account with this email address already exists.';
        }
        $stmt->close();
    }
    
    if (empty($errors)) {
        // Insert new user (using existing database structure)
        $sql = "INSERT INTO users (user_f_name, user_l_name, user_email, user_password, user_address, user_phone_no) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $first_name, $last_name, $email, $password, $address, $phone);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
            // Log registration
            log_admin_action('user_registration', 'users', $user_id);
            
            $success_message = 'Registration successful! You can now login with your email and password.';
            
            // Clear form data
            $_POST = [];
        } else {
            $error_message = 'Registration failed. Please try again.';
        }
        
        $stmt->close();
    } else {
        $error_message = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - GlamCart</title>
    <meta name="description" content="Create your GlamCart account to shop makeup and cosmetics.">
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
                    <h2 class="text-center">Create Your Account</h2>
                    <p class="text-center">Join GlamCart and discover amazing makeup and cosmetics!</p>
                </div>
                
                <div class="card-body">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?= $error_message ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="auth-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name" class="form-label">
                                    <i class="fas fa-user"></i> First Name *
                                </label>
                                <input type="text" 
                                       id="first_name" 
                                       name="first_name" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name" class="form-label">
                                    <i class="fas fa-user"></i> Last Name *
                                </label>
                                <input type="text" 
                                       id="last_name" 
                                       name="last_name" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email Address *
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
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                   autocomplete="tel">
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Password *
                            </label>
                            <div class="password-input-group">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-control" 
                                       required 
                                       autocomplete="new-password"
                                       minlength="8">
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-text">Password must be at least 8 characters long.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock"></i> Confirm Password *
                            </label>
                            <div class="password-input-group">
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       class="form-control" 
                                       required 
                                       autocomplete="new-password"
                                       minlength="8">
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Address
                            </label>
                            <textarea id="address" 
                                      name="address" 
                                      class="form-control form-textarea" 
                                      rows="3"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city" class="form-label">
                                    <i class="fas fa-city"></i> City
                                </label>
                                <input type="text" 
                                       id="city" 
                                       name="city" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="state" class="form-label">
                                    <i class="fas fa-map"></i> State
                                </label>
                                <input type="text" 
                                       id="state" 
                                       name="state" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($_POST['state'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="zip" class="form-label">
                                    <i class="fas fa-mail-bulk"></i> ZIP Code
                                </label>
                                <input type="text" 
                                       id="zip" 
                                       name="zip" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($_POST['zip'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="terms" id="terms" required>
                                <span class="checkmark"></span>
                                I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a> *
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="newsletter" id="newsletter">
                                <span class="checkmark"></span>
                                Subscribe to our newsletter for updates and special offers
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>
                        </div>
                    </form>
                    
                    <div class="auth-divider">
                        <span>or</span>
                    </div>
                    
                    <div class="social-login">
                        <button class="btn btn-secondary" style="width: 100%; margin-bottom: 1rem;">
                            <i class="fab fa-google"></i> Continue with Google
                        </button>
                        <button class="btn btn-secondary" style="width: 100%;">
                            <i class="fab fa-facebook"></i> Continue with Facebook
                        </button>
                    </div>
                </div>
                
                <div class="card-footer text-center">
                    <p>Already have an account? <a href="login.php" class="text-primary">Login here</a></p>
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
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleButton = passwordInput.parentNode.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleButton.className = 'fas fa-eye';
            }
        }
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            updatePasswordStrengthIndicator(strength);
        });
        
        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        function checkPasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            return strength;
        }
        
        function updatePasswordStrengthIndicator(strength) {
            const strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            const strengthColors = ['#dc3545', '#fd7e14', '#ffc107', '#28a745', '#20c997'];
            
            // You can add a visual indicator here if needed
            console.log(`Password strength: ${strengthText[strength - 1] || 'Very Weak'}`);
        }
        
        // Form validation
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                isValid = false;
                alert('Passwords do not match.');
            }
            
            if (password.length < 8) {
                isValid = false;
                alert('Password must be at least 8 characters long.');
            }
            
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                isValid = false;
                alert('Please enter a valid email address.');
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
