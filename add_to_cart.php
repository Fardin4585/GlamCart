<?php
/**
 * GlamCart - Add to Cart Handler
 * Server-side cart management
 */

require_once 'connection.php';
session_start();

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit;
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if ($product_id <= 0) {
        $response['message'] = 'Invalid product ID';
    } elseif ($quantity <= 0) {
        $response['message'] = 'Invalid quantity';
    } else {
        // Check if product exists and is active
        $sql = "SELECT product_id, product_name, product_price FROM product WHERE product_id = ? AND product_status = 'active'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $response['message'] = 'Product not found or not available';
        } else {
            $product = $result->fetch_assoc();
            $stmt->close();
            
            // Check if item already exists in cart
            $sql = "SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing cart item
                $cart_item = $result->fetch_assoc();
                $new_quantity = $cart_item['quantity'] + $quantity;
                
                $sql = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $new_quantity, $cart_item['cart_id']);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Cart updated successfully!';
                    $response['quantity'] = $new_quantity;
                } else {
                    $response['message'] = 'Failed to update cart';
                }
            } else {
                // Add new item to cart
                $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iii", $user_id, $product_id, $quantity);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Item added to cart successfully!';
                    $response['quantity'] = $quantity;
                } else {
                    $response['message'] = 'Failed to add item to cart';
                }
            }
            $stmt->close();
        }
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
