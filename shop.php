<?php
/**
 * GlamCart - Shop Page
 * Product browsing with filters, search, and pagination
 */

require_once 'connection.php';
session_start();

// Get filter parameters
$search = sanitize_input($_GET['search'] ?? '');
$category_id = (int)($_GET['category'] ?? 0);
$brand_id = (int)($_GET['brand'] ?? 0);
$min_price = (float)($_GET['min_price'] ?? 0);
$max_price = (float)($_GET['max_price'] ?? 0);
$sort = sanitize_input($_GET['sort'] ?? 'name_asc');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;

// Build the SQL query
$where_conditions = ["p.product_status = 'active'"];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(p.product_name LIKE ? OR b.brand_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'ss';
}

if ($category_id > 0) {
    $where_conditions[] = "p.product_category = ?";
    $params[] = $category_id;
    $param_types .= 'i';
}

if ($brand_id > 0) {
    $where_conditions[] = "p.product_brand = ?";
    $params[] = $brand_id;
    $param_types .= 'i';
}

if ($min_price > 0) {
    $where_conditions[] = "p.product_price >= ?";
    $params[] = $min_price;
    $param_types .= 'd';
}

if ($max_price > 0) {
    $where_conditions[] = "p.product_price <= ?";
    $params[] = $max_price;
    $param_types .= 'd';
}

$where_clause = implode(' AND ', $where_conditions);

// Build ORDER BY clause
$order_clause = match($sort) {
    'name_desc' => 'p.product_name DESC',
    'price_asc' => 'p.product_price ASC',
    'price_desc' => 'p.product_price DESC',
    'newest' => 'p.created_at DESC',
    'oldest' => 'p.created_at ASC',
    default => 'p.product_name ASC'
};

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM product p 
              LEFT JOIN brand b ON p.product_brand = b.brand_id 
              WHERE $where_clause";

$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$total_result = $stmt->get_result();
$total_products = $total_result->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_products / $per_page);
$offset = ($page - 1) * $per_page;

// Get products
$sql = "SELECT p.*, b.brand_name, c.Category_Name AS category_name 
        FROM product p 
        LEFT JOIN brand b ON p.product_brand = b.brand_id 
        LEFT JOIN category c ON p.product_category = c.Category_id 
        WHERE $where_clause 
        ORDER BY $order_clause 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$params[] = $per_page;
$params[] = $offset;
$param_types .= 'ii';

if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$stmt->close();

// Get categories for filter
$categories = [];
$sql = "SELECT Category_id AS category_id, Category_Name AS category_name FROM category ORDER BY Category_Name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Get brands for filter
$brands = [];
$sql = "SELECT * FROM brand ORDER BY brand_name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $brands[] = $row;
    }
}

// Get price range
$price_range = [];
$sql = "SELECT MIN(product_price) as min_price, MAX(product_price) as max_price FROM product WHERE product_status = 'active'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $price_range = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - GlamCart</title>
    <meta name="description" content="Browse our collection of makeup and cosmetics. Filter by category, brand, and price.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="top-bar-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-magic"></i> GlamCart
                </a>
                
                <!-- Search Bar -->
                <form class="search-form" action="shop.php" method="GET">
                    <div class="search-container">
                        <input type="text" name="search" class="search-input" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- Top Bar Right Actions -->
                <div class="top-bar-actions">
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
        </div>
        
        <!-- Navigation Bar -->
        <nav class="nav-bar">
            <div class="nav-content">
                <button class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="shop.php" <?= ($category_id == 0 && empty($search)) ? 'class="active"' : '' ?>>Shop</a></li>
                    <?php foreach ($categories as $category): ?>
                        <li><a href="shop.php?category=<?= $category['category_id'] ?>" <?= ($category_id == $category['category_id']) ? 'class="active"' : '' ?>><?= htmlspecialchars($category['category_name']) ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Shop Products</h1>
            <p>Discover amazing makeup and cosmetics from top brands</p>
        </div>

        <!-- Filters and Products -->
        <div class="shop-container">
            <!-- Filters Sidebar -->
            <aside class="filters-sidebar">
                <div class="filters card">
                    <div class="card-header">
                        <h3><i class="fas fa-filter"></i> Filters</h3>
                        <button class="btn btn-sm btn-secondary" onclick="clearFilters()">Clear All</button>
                    </div>
                    
                    <div class="card-body">
                        <form class="filter-form" method="GET" action="shop.php">
                            <!-- Search -->
                            <div class="filter-group">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control filter-input" 
                                       value="<?= htmlspecialchars($search) ?>" placeholder="Search products...">
                            </div>
                            
                            <!-- Category Filter -->
                            <div class="filter-group">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select filter-input">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['category_id'] ?>" 
                                                <?= $category_id == $category['category_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['category_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Brand Filter -->
                            <div class="filter-group">
                                <label class="form-label">Brand</label>
                                <select name="brand" class="form-select filter-input">
                                    <option value="">All Brands</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?= $brand['brand_id'] ?>" 
                                                <?= $brand_id == $brand['brand_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($brand['brand_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Price Range -->
                            <div class="filter-group">
                                <label class="form-label">Price Range</label>
                                <div class="price-range">
                                    <input type="number" name="min_price" class="form-control filter-input" 
                                           placeholder="Min" value="<?= $min_price > 0 ? $min_price : '' ?>" step="0.01">
                                    <span>to</span>
                                    <input type="number" name="max_price" class="form-control filter-input" 
                                           placeholder="Max" value="<?= $max_price > 0 ? $max_price : '' ?>" step="0.01">
                                </div>
                            </div>
                            
                            <!-- Sort -->
                            <div class="filter-group">
                                <label class="form-label">Sort By</label>
                                <select name="sort" class="form-select filter-input">
                                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name A-Z</option>
                                    <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name Z-A</option>
                                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price Low to High</option>
                                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price High to Low</option>
                                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                                    <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Products Section -->
            <section class="products-section">
                <!-- Results Header -->
                <div class="results-header">
                    <div class="results-info">
                        <p>Showing <?= $total_products ?> products</p>
                        <?php if (!empty($search) || $category_id > 0 || $brand_id > 0): ?>
                            <p class="active-filters">
                                Active filters: 
                                <?php if (!empty($search)): ?>
                                    <span class="filter-tag">Search: "<?= htmlspecialchars($search) ?>"</span>
                                <?php endif; ?>
                                <?php if ($category_id > 0): ?>
                                    <?php 
                                    $cat_name = '';
                                    foreach ($categories as $cat) {
                                        if ($cat['category_id'] == $category_id) {
                                            $cat_name = $cat['category_name'];
                                            break;
                                        }
                                    }
                                    ?>
                                    <span class="filter-tag">Category: <?= htmlspecialchars($cat_name) ?></span>
                                <?php endif; ?>
                                <?php if ($brand_id > 0): ?>
                                    <?php 
                                    $brand_name = '';
                                    foreach ($brands as $brand) {
                                        if ($brand['brand_id'] == $brand_id) {
                                            $brand_name = $brand['brand_name'];
                                            break;
                                        }
                                    }
                                    ?>
                                    <span class="filter-tag">Brand: <?= htmlspecialchars($brand_name) ?></span>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="view-options">
                        <button class="btn btn-sm btn-secondary view-toggle" data-view="grid">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary view-toggle" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>

                <!-- Products Grid -->
                <?php if (!empty($products)): ?>
                <div class="products-grid grid grid-4" id="products-container">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card fade-in">
                        <div class="product-image-container">
                            <img src="<?= !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : 'assets/images/placeholder.jpg' ?>" 
                                 alt="<?= htmlspecialchars($product['product_name']) ?>" 
                                 class="product-image">
                            
                            <div class="product-overlay">
                                <button class="btn btn-sm btn-primary add-to-cart" 
                                        data-product-id="<?= $product['product_id'] ?>"
                                        data-product-name="<?= htmlspecialchars($product['product_name']) ?>"
                                        data-product-price="<?= $product['product_price'] ?>">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                                <button class="btn btn-sm btn-secondary add-to-wishlist" 
                                        data-product-id="<?= $product['product_id'] ?>"
                                        data-product-name="<?= htmlspecialchars($product['product_name']) ?>">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="product-info">
                            <h3 class="product-title">
                                <a href="product.php?id=<?= $product['product_id'] ?>">
                                    <?= htmlspecialchars($product['product_name']) ?>
                                </a>
                            </h3>
                            <p class="product-brand"><?= htmlspecialchars($product['brand_name']) ?></p>
                            <p class="product-category"><?= htmlspecialchars($product['category_name']) ?></p>
                            
                            <div class="product-price">
                                $<?= number_format($product['product_price'], 2) ?>
                                <?php if (!empty($product['product_sale_price']) && $product['product_sale_price'] < $product['product_price']): ?>
                                    <span class="sale-price">$<?= number_format($product['product_sale_price'], 2) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-stock">
                                <?php if (isset($product['product_stock']) && $product['product_stock'] > 0): ?>
                                    <span class="in-stock">In Stock (<?= $product['product_stock'] ?>)</span>
                                <?php else: ?>
                                    <span class="in-stock">Available</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-sm">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-sm">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="no-products">
                    <div class="text-center">
                        <i class="fas fa-search" style="font-size: 4rem; color: var(--gray-medium); margin-bottom: 1rem;"></i>
                        <h3>No products found</h3>
                        <p>Try adjusting your filters or search terms.</p>
                        <a href="shop.php" class="btn btn-primary">Clear Filters</a>
                    </div>
                </div>
                <?php endif; ?>
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
    <script>
        // Auto-apply filters on change
        document.querySelectorAll('.filter-input').forEach(input => {
            input.addEventListener('change', function() {
                document.querySelector('.filter-form').submit();
            });
        });
        
        // Clear filters function
        function clearFilters() {
            window.location.href = 'shop.php';
        }
        
        // View toggle functionality
        document.querySelectorAll('.view-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const view = this.dataset.view;
                const container = document.getElementById('products-container');
                
                // Remove existing view classes
                container.classList.remove('grid-view', 'list-view');
                
                // Add new view class
                if (view === 'list') {
                    container.classList.add('list-view');
                } else {
                    container.classList.add('grid-view');
                }
                
                // Update active button
                document.querySelectorAll('.view-toggle').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>
