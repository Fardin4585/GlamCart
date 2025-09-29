<?php
/**
 * GlamCart - Shopping Cart Page
 * Cart management with product updates and checkout
 */

require_once 'connection.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_quantity':
            $product_id = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            
            if ($quantity > 0) {
                $sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iii", $quantity, $user_id, $product_id);
                $stmt->execute();
                $stmt->close();
                $success_message = 'Cart updated successfully!';
            } else {
                // Remove item if quantity is 0
                $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $user_id, $product_id);
                $stmt->execute();
                $stmt->close();
                $success_message = 'Item removed from cart!';
            }
            break;
            
        case 'remove_item':
            $product_id = (int)$_POST['product_id'];
            
            $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $stmt->close();
            $success_message = 'Item removed from cart!';
            break;
            
        case 'clear_cart':
            $sql = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            $success_message = 'Cart cleared successfully!';
            break;
    }
}

// Get cart items with product details
$cart_items = [];
$cart_total = 0;

$sql = "SELECT c.*, p.product_name, p.product_price, p.product_image, 
               b.brand_name, p.product_sale_price
        FROM cart c 
        JOIN product p ON c.product_id = p.product_id 
        LEFT JOIN brand b ON p.product_brand = b.brand_id 
        WHERE c.user_id = ? AND p.product_status = 'active'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $price = !empty($row['product_sale_price']) && $row['product_sale_price'] < $row['product_price'] 
                 ? $row['product_sale_price'] 
                 : $row['product_price'];
        
        $row['final_price'] = $price;
        $row['item_total'] = $price * $row['quantity'];
        $cart_total += $row['item_total'];
        $cart_items[] = $row;
    }
}
$stmt->close();

// Get available discounts (simplified for current database structure)
$discounts = [];
// Note: Discount functionality is limited due to database structure differences
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - GlamCart</title>
    <meta name="description" content="Review and manage your shopping cart items.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="top-bar">
            <div class="top-bar-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-magic"></i> GlamCart
                </a>
                
                <!-- Search Bar -->
                <form class="search-form" action="shop.php" method="GET">
                    <div class="search-container">
                        <input type="text" name="search" class="search-input" placeholder="Search products...">
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
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="cart-container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Shopping Cart</h1>
                <p>Review your items and proceed to checkout</p>
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

            <?php if (empty($cart_items)): ?>
                <!-- Empty Cart -->
                <div class="empty-cart">
                    <div class="text-center">
                        <i class="fas fa-shopping-cart" style="font-size: 4rem; color: var(--gray-medium); margin-bottom: 1rem;"></i>
                        <h3>Your cart is empty</h3>
                        <p>Looks like you haven't added any products to your cart yet.</p>
                        <a href="shop.php" class="btn btn-primary btn-lg">Start Shopping</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Cart Items -->
                <div class="cart-content">
                    <div class="cart-items-section">
                        <div class="cart-header">
                            <h2>Cart Items (<?= count($cart_items) ?>)</h2>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to clear your cart?')">
                                <input type="hidden" name="action" value="clear_cart">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Clear Cart
                                </button>
                            </form>
                        </div>

                        <div class="cart-items">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item" data-product-id="<?= $item['product_id'] ?>">
                                <div class="cart-item-image">
                                    <img src="<?= !empty($item['product_image']) ? htmlspecialchars($item['product_image']) : 'assets/images/placeholder.jpg' ?>" 
                                         alt="<?= htmlspecialchars($item['product_name']) ?>">
                                </div>
                                
                                <div class="cart-item-details">
                                    <h3 class="cart-item-title">
                                        <a href="product.php?id=<?= $item['product_id'] ?>">
                                            <?= htmlspecialchars($item['product_name']) ?>
                                        </a>
                                    </h3>
                                    <p class="cart-item-brand"><?= htmlspecialchars($item['brand_name']) ?></p>
                                    
                                    <div class="cart-item-price">
                                        <?php if (!empty($item['product_sale_price']) && $item['product_sale_price'] < $item['product_price']): ?>
                                            <span class="original-price">$<?= number_format($item['product_price'], 2) ?></span>
                                        <?php endif; ?>
                                        <span class="current-price">$<?= number_format($item['final_price'], 2) ?></span>
                                    </div>
                                    
                                    <div class="cart-item-stock">
                                        <span class="in-stock">Available</span>
                                    </div>
                                </div>
                                
                                <div class="cart-item-quantity">
                                    <label class="form-label">Quantity</label>
                                    <div class="quantity-control">
                                        <button type="button" class="quantity-btn quantity-minus" onclick="updateQuantity(<?= $item['product_id'] ?>, -1)">-</button>
                                        <input type="number" class="quantity-input" value="<?= $item['quantity'] ?>" 
                                               min="1" max="99" 
                                               onchange="updateQuantity(<?= $item['product_id'] ?>, 0, this.value)">
                                        <button type="button" class="quantity-btn quantity-plus" onclick="updateQuantity(<?= $item['product_id'] ?>, 1)">+</button>
                                    </div>
                                </div>
                                
                                <div class="cart-item-total">
                                    <span class="total-label">Total</span>
                                    <span class="total-price">$<?= number_format($item['item_total'], 2) ?></span>
                                </div>
                                
                                <div class="cart-item-actions">
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Remove this item from cart?')">
                                        <input type="hidden" name="action" value="remove_item">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <div class="summary-card card">
                            <div class="card-header">
                                <h3>Order Summary</h3>
                            </div>
                            
                            <div class="card-body">
                                <div class="summary-row">
                                    <span>Subtotal (<?= count($cart_items) ?> items)</span>
                                    <span>$<?= number_format($cart_total, 2) ?></span>
                                </div>
                                
                                <div class="summary-row">
                                    <span>Shipping</span>
                                    <span><?= $cart_total >= 50 ? 'Free' : '$5.99' ?></span>
                                </div>
                                
                                <div class="summary-row">
                                    <span>Tax</span>
                                    <span>$<?= number_format($cart_total * 0.08, 2) ?></span>
                                </div>
                                
                                <hr>
                                
                                <div class="summary-row total">
                                    <span>Total</span>
                                    <span>$<?= number_format($cart_total + ($cart_total >= 50 ? 0 : 5.99) + ($cart_total * 0.08), 2) ?></span>
                                </div>
                                
                                <!-- Discount Code -->
                                <div class="discount-section">
                                    <h4>Have a discount code?</h4>
                                    <div class="discount-input">
                                        <input type="text" id="discount-code" class="form-control" placeholder="Enter discount code">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="applyDiscount()">Apply</button>
                                    </div>
                                    
                                    <?php if (!empty($discounts)): ?>
                                    <div class="available-discounts">
                                        <h5>Available Discounts:</h5>
                                        <ul>
                                            <?php foreach ($discounts as $discount): ?>
                                            <li>
                                                <strong><?= htmlspecialchars($discount['discount_code']) ?></strong> - 
                                                <?= htmlspecialchars($discount['discount_name']) ?>
                                                (<?= $discount['discount_type'] === 'percentage' ? $discount['discount_value'] . '%' : '$' . $discount['discount_value'] ?> off)
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Checkout Button -->
                                <div class="checkout-actions">
                                    <a href="checkout.php" class="btn btn-primary btn-lg" style="width: 100%;">
                                        <i class="fas fa-credit-card"></i> Proceed to Checkout
                                    </a>
                                    <a href="shop.php" class="btn btn-secondary" style="width: 100%; margin-top: 1rem;">
                                        <i class="fas fa-arrow-left"></i> Continue Shopping
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Security Info -->
                        <div class="security-info card">
                            <div class="card-body">
                                <h4><i class="fas fa-shield-alt"></i> Secure Checkout</h4>
                                <p>Your payment information is protected with bank-level security.</p>
                                <div class="security-badges">
                                    <i class="fas fa-lock"></i>
                                    <i class="fas fa-credit-card"></i>
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
        function updateQuantity(productId, change, newValue = null) {
            let quantity;
            
            if (newValue !== null) {
                quantity = parseInt(newValue);
            } else {
                const input = document.querySelector(`[data-product-id="${productId}"] .quantity-input`);
                quantity = parseInt(input.value) + change;
            }
            
            if (quantity < 1) {
                quantity = 1;
            }
            
            // Update the input value
            const input = document.querySelector(`[data-product-id="${productId}"] .quantity-input`);
            input.value = quantity;
            
            // Submit form to update cart
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_quantity">
                <input type="hidden" name="product_id" value="${productId}">
                <input type="hidden" name="quantity" value="${quantity}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        function applyDiscount() {
            const code = document.getElementById('discount-code').value.trim();
            if (!code) {
                alert('Please enter a discount code.');
                return;
            }
            
            // This would typically make an AJAX call to validate and apply the discount
            alert('Discount code functionality would be implemented here.');
        }
        
        // Auto-update cart count
        document.addEventListener('DOMContentLoaded', function() {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = '<?= count($cart_items) ?>';
            }
        });
    </script>
</body>
</html>
