<?php
/**
 * GlamCart - Add to Wishlist Handler
 * Server-side wishlist management
 */

require_once 'connection.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to add items to wishlist']);
    exit;
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    
    if ($product_id <= 0) {
        $response['message'] = 'Invalid product ID';
    } else {
        // Check if product exists and is active
        $sql = "SELECT product_id, product_name FROM product WHERE product_id = ? AND product_status = 'active'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $response['message'] = 'Product not found or not available';
        } else {
            $product = $result->fetch_assoc();
            $stmt->close();
            
            // Check if item already exists in wishlist
            $sql = "SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $response['message'] = 'Product already in wishlist!';
            } else {
                // Add new item to wishlist
                $sql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $user_id, $product_id);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Item added to wishlist successfully!';
                } else {
                    $response['message'] = 'Failed to add item to wishlist';
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
