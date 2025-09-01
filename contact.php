<?php
/**
 * GlamCart - Contact Page
 * Customer contact form and information
 */

require_once 'connection.php';
require_once 'my_function.php';
session_start();

// Get categories for navigation with proper aliases
$categories_query = "SELECT Category_ID AS category_id, Category_Name AS category_name FROM category ORDER BY Category_Name";
$categories_result = $conn->query($categories_query);
$categories = [];
if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Handle contact form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message_text = sanitize_input($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message_text)) {
        $message = 'Please fill in all required fields.';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $message_type = 'error';
    } else {
        // In a real application, you would send an email or save to database
        // For now, we'll just show a success message
        $message = 'Thank you for your message! We will get back to you soon.';
        $message_type = 'success';
        
        // Clear form data
        $name = $email = $subject = $message_text = '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - GlamCart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="top-bar-content">
                <a href="index.php" class="logo">GlamCart</a>
                
                <form class="search-form" method="GET" action="shop.php">
                    <input type="text" name="search" class="search-input" placeholder="Search for products..." value="">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                
                <div class="top-bar-actions">
                    <a href="wishlist.php" class="wishlist-icon">
                        <i class="fas fa-heart"></i>
                        <?php if (is_logged_in()): ?>
                            <span class="wishlist-count"><?= getWishlistCountFromDB() ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <a href="cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if (is_logged_in()): ?>
                            <span class="cart-count"><?= getCartCountFromDB() ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <?php if (is_logged_in()): ?>
                        <div class="user-menu">
                            <a href="profile.php" class="username-link"><?= htmlspecialchars($_SESSION['username'] ?? $_SESSION['user_f_name'] ?? 'User') ?></a>
                            <a href="logout.php" class="logout-link">Logout</a>
                        </div>
                    <?php else: ?>
                        <div class="user-menu">
                            <a href="login.php" class="username-link">Login</a>
                            <a href="register.php" class="logout-link">Register</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Navigation Bar -->
        <nav class="nav-bar">
            <div class="nav-content">
                <button class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="shop.php">Shop</a></li>
                    <?php foreach ($categories as $category): ?>
                        <li><a href="shop.php?category=<?= $category['category_id'] ?>"><?= htmlspecialchars($category['category_name']) ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php" class="active">Contact</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Contact Us</h1>
            <p>Get in touch with our team. We're here to help with any questions or concerns.</p>
        </div>

        <!-- Contact Content -->
        <div class="contact-container">
            <!-- Contact Information -->
            <section class="contact-info">
                <div class="contact-info-grid">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3>Visit Us</h3>
                        <p>123 Beauty Street<br>Makeup City, MC 12345<br>United States</p>
                    </div>
                    
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h3>Call Us</h3>
                        <p>Main: (555) 123-4567<br>Support: (555) 123-4568<br>Mon-Fri: 9AM-6PM EST</p>
                    </div>
                    
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Email Us</h3>
                        <p>General: info@glamcart.com<br>Support: support@glamcart.com<br>Sales: sales@glamcart.com</p>
                    </div>
                    
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Business Hours</h3>
                        <p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 10:00 AM - 4:00 PM<br>Sunday: Closed</p>
                    </div>
                </div>
            </section>

            <!-- Contact Form Section -->
            <section class="contact-form-section">
                <div class="contact-form-container">
                    <div class="form-header">
                        <h2>Send Us a Message</h2>
                        <p>Fill out the form below and we'll get back to you as soon as possible.</p>
                    </div>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'danger' ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form class="contact-form" method="POST" action="contact.php">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?= htmlspecialchars($name ?? '') ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?= htmlspecialchars($email ?? '') ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject" class="form-label">Subject *</label>
                            <select id="subject" name="subject" class="form-select" required>
                                <option value="">Select a subject</option>
                                <option value="General Inquiry" <?= ($subject ?? '') === 'General Inquiry' ? 'selected' : '' ?>>General Inquiry</option>
                                <option value="Product Information" <?= ($subject ?? '') === 'Product Information' ? 'selected' : '' ?>>Product Information</option>
                                <option value="Order Support" <?= ($subject ?? '') === 'Order Support' ? 'selected' : '' ?>>Order Support</option>
                                <option value="Returns & Exchanges" <?= ($subject ?? '') === 'Returns & Exchanges' ? 'selected' : '' ?>>Returns & Exchanges</option>
                                <option value="Technical Support" <?= ($subject ?? '') === 'Technical Support' ? 'selected' : '' ?>>Technical Support</option>
                                <option value="Partnership" <?= ($subject ?? '') === 'Partnership' ? 'selected' : '' ?>>Partnership</option>
                                <option value="Other" <?= ($subject ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message" class="form-label">Message *</label>
                            <textarea id="message" name="message" class="form-control form-textarea" 
                                      rows="6" placeholder="Please describe your inquiry in detail..." required><?= htmlspecialchars($message_text ?? '') ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- FAQ Section -->
            <section class="faq-section">
                <h2>Frequently Asked Questions</h2>
                <div class="faq-grid">
                    <div class="faq-item">
                        <h4>How can I track my order?</h4>
                        <p>Once your order ships, you'll receive a tracking number via email. You can also track your order by logging into your account and visiting the order history section.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>What is your return policy?</h4>
                        <p>We offer a 30-day return policy for most items. Products must be unused and in their original packaging. Some items may have different return policies due to hygiene reasons.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>Do you ship internationally?</h4>
                        <p>Currently, we only ship within the United States. We're working on expanding our shipping options to other countries in the near future.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>How can I contact customer support?</h4>
                        <p>You can reach our customer support team through the contact form above, by phone at (555) 123-4568, or by email at support@glamcart.com.</p>
                    </div>
                </div>
            </section>

            <!-- Map Section -->
            <section class="map-section">
                <h2>Find Us</h2>
                <div class="map-placeholder">
                    <div class="map-content">
                        <i class="fas fa-map" style="font-size: 4rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                        <h3>GlamCart Headquarters</h3>
                        <p>123 Beauty Street, Makeup City, MC 12345</p>
                        <p>Located in the heart of the beauty district, our flagship store offers an immersive shopping experience with expert beauty consultants and exclusive product launches.</p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>GlamCart</h3>
                <p>Your one-stop destination for makeup and cosmetics. Discover the latest trends and products from top brands.</p>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="shop.php">Shop</a>
                <a href="about.php">About Us</a>
                <a href="contact.php">Contact</a>
                <a href="faq.php">FAQ</a>
            </div>
            
            <div class="footer-section">
                <h3>Categories</h3>
                <?php foreach (array_slice($categories, 0, 6) as $category): ?>
                    <a href="shop.php?category=<?= $category['category_id'] ?>"><?= htmlspecialchars($category['category_name']) ?></a>
                <?php endforeach; ?>
            </div>
            
            <div class="footer-section">
                <h3>Contact Info</h3>
                <p><i class="fas fa-map-marker-alt"></i> 123 Beauty Street, Makeup City, MC 12345</p>
                <p><i class="fas fa-phone"></i> (555) 123-4567</p>
                <p><i class="fas fa-envelope"></i> info@glamcart.com</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> GlamCart. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/script.js"></script>
</body>
</html>
