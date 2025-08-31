<?php
/**
 * GlamCart - Checkout Page
 * Order processing and payment integration
 */

require_once 'connection.php';
session_start();

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Get user information
$user_info = [];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $user_info = $result->fetch_assoc();
}
$stmt->close();

// Get cart items
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

// Redirect if cart is empty
if (empty($cart_items)) {
    redirect('cart.php');
}

// Calculate totals
$shipping = $cart_total >= 50 ? 0 : 5.99;
$tax = $cart_total * 0.08;
$total = $cart_total + $shipping + $tax;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = sanitize_input($_POST['shipping_address']);
    $shipping_city = sanitize_input($_POST['shipping_city']);
    $shipping_state = sanitize_input($_POST['shipping_state']);
    $shipping_zip = sanitize_input($_POST['shipping_zip']);
    $shipping_phone = sanitize_input($_POST['shipping_phone']);
    $payment_method = sanitize_input($_POST['payment_method']);
    $discount_code = sanitize_input($_POST['discount_code'] ?? '');
    $order_notes = sanitize_input($_POST['order_notes'] ?? '');
    
    // Validate required fields
    $errors = [];
    
    if (empty($shipping_address)) {
        $errors[] = 'Shipping address is required.';
    }
    
    if (empty($shipping_city)) {
        $errors[] = 'City is required.';
    }
    
    if (empty($shipping_state)) {
        $errors[] = 'State is required.';
    }
    
    if (empty($shipping_zip)) {
        $errors[] = 'ZIP code is required.';
    }
    
    if (empty($shipping_phone)) {
        $errors[] = 'Phone number is required.';
    }
    
    if (empty($payment_method)) {
        $errors[] = 'Payment method is required.';
    }
    
    // Discount code validation (simplified for current database structure)
    $discount_amount = 0;
    if (!empty($discount_code)) {
        // For now, we'll skip discount validation since the discounts table structure is different
        // You can implement custom discount logic here if needed
        $errors[] = 'Discount codes are not available at this time.';
    }
    
    if (empty($errors)) {
        // Generate order number
        $order_number = 'GC' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 6));
        
        // Calculate final total
        $final_total = $total - $discount_amount;
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order (using actual database structure)
            $sql = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("id", $user_id, $final_total);
            $stmt->execute();
            $order_id = $conn->insert_id;
            $stmt->close();
            
            // Add to spend_product table for each item
            foreach ($cart_items as $item) {
                $sql = "INSERT INTO spend_product (spend_product_name, spend_product_quantity, spend_product_entry_date) 
                        VALUES (?, ?, CURDATE())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $item['product_id'], $item['quantity']);
                $stmt->execute();
                $stmt->close();
            }
            
            // Note: Discount usage tracking not available in current database structure
            
            // Clear cart
            $sql = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Log admin action
            log_admin_action('order_created', 'orders', $order_id);
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to success page
            redirect("order_success.php?order_id=$order_id");
            
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            $error_message = 'An error occurred while processing your order. Please try again.';
        }
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
    <title>Checkout - GlamCart</title>
    <meta name="description" content="Complete your purchase securely.">
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
        <div class="checkout-container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Checkout</h1>
                <p>Complete your purchase securely</p>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $error_message ?>
                </div>
            <?php endif; ?>

            <div class="checkout-content">
                <!-- Checkout Form -->
                <div class="checkout-form-section">
                    <form method="POST" class="checkout-form">
                        <!-- Shipping Information -->
                        <div class="form-section">
                            <h2><i class="fas fa-shipping-fast"></i> Shipping Information</h2>
                            
                            <div class="form-group">
                                <label for="shipping_address" class="form-label">Shipping Address *</label>
                                <textarea id="shipping_address" name="shipping_address" class="form-control form-textarea" 
                                          required><?= htmlspecialchars($_POST['shipping_address'] ?? $user_info['user_address'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="shipping_city" class="form-label">City *</label>
                                    <input type="text" id="shipping_city" name="shipping_city" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['shipping_city'] ?? $user_info['user_city'] ?? '') ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="shipping_state" class="form-label">State *</label>
                                    <input type="text" id="shipping_state" name="shipping_state" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['shipping_state'] ?? $user_info['user_state'] ?? '') ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="shipping_zip" class="form-label">ZIP Code *</label>
                                    <input type="text" id="shipping_zip" name="shipping_zip" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['shipping_zip'] ?? $user_info['user_zip'] ?? '') ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="shipping_phone" class="form-label">Phone Number *</label>
                                <input type="tel" id="shipping_phone" name="shipping_phone" class="form-control" 
                                       value="<?= htmlspecialchars($_POST['shipping_phone'] ?? $user_info['user_phone'] ?? '') ?>" required>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="form-section">
                            <h2><i class="fas fa-credit-card"></i> Payment Method</h2>
                            
                            <div class="payment-methods">
                                <div class="payment-option">
                                    <input type="radio" id="credit_card" name="payment_method" value="credit_card" 
                                           <?= ($_POST['payment_method'] ?? '') === 'credit_card' ? 'checked' : '' ?> required>
                                    <label for="credit_card">
                                        <i class="fas fa-credit-card"></i>
                                        <span>Credit Card</span>
                                    </label>
                                </div>
                                
                                <div class="payment-option">
                                    <input type="radio" id="paypal" name="payment_method" value="paypal" 
                                           <?= ($_POST['payment_method'] ?? '') === 'paypal' ? 'checked' : '' ?>>
                                    <label for="paypal">
                                        <i class="fab fa-paypal"></i>
                                        <span>PayPal</span>
                                    </label>
                                </div>
                                
                                <div class="payment-option">
                                    <input type="radio" id="cash_on_delivery" name="payment_method" value="cash_on_delivery" 
                                           <?= ($_POST['payment_method'] ?? '') === 'cash_on_delivery' ? 'checked' : '' ?>>
                                    <label for="cash_on_delivery">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>Cash on Delivery</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Discount Code -->
                        <div class="form-section">
                            <h2><i class="fas fa-tag"></i> Discount Code (Optional)</h2>
                            
                            <div class="form-group">
                                <label for="discount_code" class="form-label">Discount Code</label>
                                <input type="text" id="discount_code" name="discount_code" class="form-control" 
                                       value="<?= htmlspecialchars($_POST['discount_code'] ?? '') ?>" 
                                       placeholder="Enter discount code">
                            </div>
                        </div>

                        <!-- Order Notes -->
                        <div class="form-section">
                            <h2><i class="fas fa-sticky-note"></i> Order Notes (Optional)</h2>
                            
                            <div class="form-group">
                                <label for="order_notes" class="form-label">Special Instructions</label>
                                <textarea id="order_notes" name="order_notes" class="form-control form-textarea" 
                                          placeholder="Any special instructions for your order..."><?= htmlspecialchars($_POST['order_notes'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-section">
                            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                                <i class="fas fa-lock"></i> Place Order Securely
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Order Summary -->
                <div class="order-summary-section">
                    <div class="order-summary card">
                        <div class="card-header">
                            <h3>Order Summary</h3>
                        </div>
                        
                        <div class="card-body">
                            <!-- Order Items -->
                            <div class="order-items">
                                <?php foreach ($cart_items as $item): ?>
                                <div class="order-item">
                                    <div class="item-image">
                                        <img src="<?= !empty($item['product_image']) ? htmlspecialchars($item['product_image']) : 'assets/images/placeholder.jpg' ?>" 
                                             alt="<?= htmlspecialchars($item['product_name']) ?>">
                                    </div>
                                    
                                    <div class="item-details">
                                        <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                                        <p class="item-brand"><?= htmlspecialchars($item['brand_name']) ?></p>
                                        <p class="item-quantity">Qty: <?= $item['quantity'] ?></p>
                                    </div>
                                    
                                    <div class="item-price">
                                        $<?= number_format($item['item_total'], 2) ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Order Totals -->
                            <div class="order-totals">
                                <div class="total-row">
                                    <span>Subtotal (<?= count($cart_items) ?> items)</span>
                                    <span>$<?= number_format($cart_total, 2) ?></span>
                                </div>
                                
                                <div class="total-row">
                                    <span>Shipping</span>
                                    <span><?= $shipping > 0 ? '$' . number_format($shipping, 2) : 'Free' ?></span>
                                </div>
                                
                                <div class="total-row">
                                    <span>Tax</span>
                                    <span>$<?= number_format($tax, 2) ?></span>
                                </div>
                                
                                <hr>
                                
                                <div class="total-row total">
                                    <span>Total</span>
                                    <span>$<?= number_format($total, 2) ?></span>
                                </div>
                            </div>
                            
                            <!-- Security Info -->
                            <div class="security-info">
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
        // Form validation
        document.querySelector('.checkout-form').addEventListener('submit', function(e) {
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
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
        
        // Payment method selection
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // You can add payment method specific logic here
                console.log('Selected payment method:', this.value);
            });
        });
    </script>
</body>
</html>
