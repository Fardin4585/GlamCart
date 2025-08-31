<?php
/**
 * GlamCart - Makeup and Cosmetics Shop Management System
 * Homepage
 */

require_once 'connection.php';
  session_start();

// Get featured products
$featured_products = [];
$sql = "SELECT p.*, b.brand_name, c.Category_Name AS category_name 
        FROM product p 
        LEFT JOIN brand b ON p.product_brand = b.brand_id 
        LEFT JOIN category c ON p.product_category = c.Category_id 
        WHERE p.product_status = 'active' 
          AND p.product_featured = 1 
        ORDER BY p.created_at DESC 
        LIMIT 8";


$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $featured_products[] = $row;
    }
}

// Get active discounts
$discounts = [];
$sql = "SELECT * FROM discounts 
        WHERE (start_date IS NULL OR start_date <= CURDATE())
          AND (end_date IS NULL OR end_date >= CURDATE())
        ORDER BY discount_value DESC 
        LIMIT 3";


$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $discounts[] = $row;
    }
}

// Get categories for navigation
$categories = [];
$sql = "SELECT Category_id AS category_id, Category_Name AS category_name 
        FROM category 
        ORDER BY Category_Name";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
 ?>
  
<!DOCTYPE html>
<html lang="en">
     <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlamCart - Makeup and Cosmetics Shop</title>
    <meta name="description" content="Discover the latest makeup and cosmetics from top brands. Shop foundation, lipstick, eyeshadow, mascara, and more at GlamCart.">
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
            
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="shop.php">Shop</a></li>
                    <?php foreach ($categories as $category): ?>
                        <li><a href="shop.php?category=<?= $category['category_id'] ?>"><?= htmlspecialchars($category['category_name']) ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <!-- Search Bar -->
                <form class="search-form" action="shop.php" method="GET">
                    <div class="search-container">
                        <input type="text" name="search" class="search-input" placeholder="Search products...">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- User Actions -->
                <div class="user-actions">
                    <?php if (is_logged_in()): ?>
                        <a href="wishlist.php" class="wishlist-icon">
                            <i class="fas fa-heart"></i>
                            <span class="wishlist-count"><?= isset($_SESSION['user_id']) ? getWishlistCountFromDB() : '0' ?></span>
                        </a>
                        <a href="cart.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count"><?= isset($_SESSION['user_id']) ? getCartCountFromDB() : '0' ?></span>
                        </a>
                        <div class="user-menu">
                            <a href="dashboard.php" class="username-link">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($_SESSION['user_f_name']) ?>
                            </a>
                            <a href="logout.php" class="logout-link">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-secondary btn-sm">Login</a>
                        <a href="register.php" class="btn btn-primary btn-sm">Register</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <button class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1>Discover Your Perfect Look</h1>
                <p>Shop the latest makeup and cosmetics from top brands. From foundation to lipstick, we have everything you need to create your signature style.</p>
                <div class="hero-actions">
                    <a href="shop.php" class="btn btn-primary btn-lg">Shop Now</a>
                    <a href="about.php" class="btn btn-secondary btn-lg">Learn More</a>
                </div>
            </div>
        </section>

        <!-- Discount Banners -->
        <?php if (!empty($discounts)): ?>
        <section class="discount-banners mb-4">
            <div class="grid grid-3">
                <?php foreach ($discounts as $discount): ?>
                <div class="card discount-card">
                    <div class="card-body text-center">
                        <h3 class="text-primary"><?= htmlspecialchars($discount['discount_name']) ?></h3>
                        <p class="discount-code">Use code: <strong><?= htmlspecialchars($discount['discount_code']) ?></strong></p>
                        <p class="discount-value">
                            <?php if ($discount['discount_type'] === 'percentage'): ?>
                                Save <?= $discount['discount_value'] ?>%
                            <?php else: ?>
                                Save $<?= number_format($discount['discount_value'], 2) ?>
                            <?php endif; ?>
                        </p>
                        <?php if ($discount['discount_min_amount'] > 0): ?>
                            <p class="discount-min">On orders over $<?= number_format($discount['discount_min_amount'], 2) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Featured Products -->
        <section class="featured-products mb-4">
            <div class="section-header">
                <h2>Featured Products</h2>
                <p>Discover our most popular makeup and cosmetics</p>
            </div>
            
            <?php if (!empty($featured_products)): ?>
            <div class="grid grid-4">
                <?php foreach ($featured_products as $product): ?>
                <div class="product-card fade-in">
                    <img src="<?= !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : 'assets/images/placeholder.jpg' ?>" 
                         alt="<?= htmlspecialchars($product['product_name']) ?>" 
                         class="product-image">
                    
                    <div class="product-info">
                        <h3 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h3>
                        <p class="product-brand"><?= htmlspecialchars($product['brand_name']) ?></p>
                        <p class="product-category"><?= htmlspecialchars($product['category_name']) ?></p>
                        
                        <div class="product-price">
                            $<?= number_format($product['product_price'], 2) ?>
                            <?php if (!empty($product['product_sale_price']) && $product['product_sale_price'] < $product['product_price']): ?>
                                <span class="sale-price">$<?= number_format($product['product_sale_price'], 2) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions">
                            <a href="product.php?id=<?= $product['product_id'] ?>" class="btn btn-secondary btn-sm">View Details</a>
                            <button class="btn btn-primary btn-sm add-to-cart" 
                                    data-product-id="<?= $product['product_id'] ?>"
                                    data-product-name="<?= htmlspecialchars($product['product_name']) ?>"
                                    data-product-price="<?= $product['product_price'] ?>">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                            <button class="btn btn-secondary btn-sm add-to-wishlist" 
                                    data-product-id="<?= $product['product_id'] ?>"
                                    data-product-name="<?= htmlspecialchars($product['product_name']) ?>">
                                <i class="fas fa-heart"></i> Wishlist
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="shop.php" class="btn btn-primary btn-lg">View All Products</a>
            </div>
            <?php else: ?>
            <div class="text-center">
                <p>No featured products available at the moment.</p>
            </div>
            <?php endif; ?>
        </section>

        <!-- Categories Section -->
        <section class="categories-section mb-4">
            <div class="section-header">
                <h2>Shop by Category</h2>
                <p>Find exactly what you're looking for</p>
            </div>
            
            <div class="grid grid-3">
                <?php foreach ($categories as $category): ?>
                <div class="category-card card">
                    <div class="card-body text-center">
                        <div class="category-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h3><?= htmlspecialchars($category['category_name']) ?></h3>
                        <p>Discover amazing products in this category</p>
                        <a href="shop.php?category=<?= $category['category_id'] ?>" class="btn btn-primary">Shop <?= htmlspecialchars($category['category_name']) ?></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Why Choose Us -->
        <section class="why-choose-us mb-4">
            <div class="section-header">
                <h2>Why Choose GlamCart?</h2>
                <p>We're committed to providing the best shopping experience</p>
            </div>
            
            <div class="grid grid-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3>Fast Shipping</h3>
                    <p>Free shipping on orders over $50</p>
                </div>
                
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Authentic Products</h3>
                    <p>100% genuine products from top brands</p>
                </div>
                
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <h3>Easy Returns</h3>
                    <p>30-day return policy for your peace of mind</p>
                </div>
                
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Our customer service team is always here to help</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>GlamCart</h3>
                <p>Your one-stop destination for makeup and cosmetics. Discover the latest trends and products from top brands.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="shop.php">Shop</a>
                <a href="about.php">About Us</a>
                <a href="contact.php">Contact</a>
                <a href="faq.php">FAQ</a>
                <a href="privacy.php">Privacy Policy</a>
                <a href="terms.php">Terms of Service</a>
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
                <p><i class="fas fa-clock"></i> Mon-Fri: 9AM-6PM</p>
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