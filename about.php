<?php
/**
 * GlamCart - About Page
 * Company information and story
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - GlamCart</title>
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
                    <li><a href="about.php" class="active">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>About GlamCart</h1>
            <p>Discover our story and mission in the world of beauty and cosmetics</p>
        </div>

        <!-- About Content -->
        <div class="about-container">
            <!-- Hero Section -->
            <section class="about-hero">
                <div class="about-hero-content">
                    <div class="about-hero-text">
                        <h2>Your Beauty Journey Starts Here</h2>
                        <p>At GlamCart, we believe that everyone deserves access to high-quality makeup and cosmetics that make them feel confident and beautiful. Since our founding, we've been dedicated to bringing you the latest trends, premium brands, and exceptional customer service.</p>
                    </div>
                    <div class="about-hero-image">
                        <i class="fas fa-spa" style="font-size: 8rem; color: var(--primary-color); opacity: 0.8;"></i>
                    </div>
                </div>
            </section>

            <!-- Mission & Vision -->
            <section class="mission-vision">
                <div class="mission-vision-grid">
                    <div class="mission-card">
                        <div class="card-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3>Our Mission</h3>
                        <p>To empower individuals to express their unique beauty through premium cosmetics, expert guidance, and an inclusive shopping experience that celebrates diversity and self-expression.</p>
                    </div>
                    
                    <div class="vision-card">
                        <div class="card-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3>Our Vision</h3>
                        <p>To become the leading destination for beauty enthusiasts, offering innovative products, educational content, and a community that inspires confidence and creativity.</p>
                    </div>
                </div>
            </section>

            <!-- Company Story -->
            <section class="company-story">
                <div class="story-content">
                    <h2>Our Story</h2>
                    <p>Founded in 2020, GlamCart began as a small passion project by beauty enthusiasts who wanted to create a better shopping experience for makeup lovers. What started as a local beauty store has grown into a trusted online destination for cosmetics and beauty products.</p>
                    
                    <p>We understand that beauty is deeply personal, which is why we've curated a collection that caters to all skin types, tones, and preferences. Our team of beauty experts is always available to help you find the perfect products for your unique needs.</p>
                    
                    <p>Today, GlamCart serves thousands of customers nationwide, offering everything from everyday essentials to luxury cosmetics, all while maintaining the personal touch and expert advice that made us successful from the start.</p>
                </div>
            </section>

            <!-- Values -->
            <section class="values-section">
                <h2>Our Core Values</h2>
                <div class="values-grid">
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4>Quality</h4>
                        <p>We only stock products from reputable brands that meet our high standards for quality and safety.</p>
                    </div>
                    
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Inclusivity</h4>
                        <p>Beauty comes in all forms, and we're committed to offering products for every skin tone and type.</p>
                    </div>
                    
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Trust</h4>
                        <p>We build lasting relationships with our customers through transparency, honesty, and exceptional service.</p>
                    </div>
                    
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h4>Innovation</h4>
                        <p>We stay ahead of beauty trends and continuously improve our offerings to meet evolving customer needs.</p>
                    </div>
                </div>
            </section>

            <!-- Team Section -->
            <section class="team-section">
                <h2>Meet Our Team</h2>
                <div class="team-grid">
                    <div class="team-member">
                        <div class="member-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h4>Fardin Khan</h4>
                        <p class="member-role">Founder & CEO</p>
                        <p>Full Stack Developer blending front-end creativity with back-end efficiency</p>
                    </div>
                    
                    <div class="team-member">
                        <div class="member-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h4>Sakia Ananna</h4>
                        <p class="member-role">Head of Operations</p>
                        <p>Expert in supply chain management and customer experience</p>
                    </div>
                    
                    <div class="team-member">
                        <div class="member-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h4>Emily Rodriguez</h4>
                        <p class="member-role">Beauty Consultant</p>
                        <p>Certified makeup artist and product specialist</p>
                    </div>
                </div>
            </section>

            <!-- Call to Action -->
            <section class="cta-section">
                <div class="cta-content">
                    <h2>Ready to Start Your Beauty Journey?</h2>
                    <p>Explore our extensive collection of premium cosmetics and discover products that make you feel confident and beautiful.</p>
                    <div class="cta-buttons">
                        <a href="shop.php" class="btn btn-primary">Shop Now</a>
                        <a href="contact.php" class="btn btn-secondary">Get in Touch</a>
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
