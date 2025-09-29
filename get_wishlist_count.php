<?php
/**
 * GlamCart - Get Wishlist Count
 * Returns the number of items in user's wishlist
 */

require_once 'connection.php';

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['count' => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get wishlist count
$sql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

$count = $row['count'] ?? 0;

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['count' => (int)$count]);
?>
