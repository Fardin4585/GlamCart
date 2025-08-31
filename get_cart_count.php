<?php
/**
 * GlamCart - Get Cart Count
 * Returns the number of items in user's cart
 */

require_once 'connection.php';
session_start();

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['count' => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get cart count
$sql = "SELECT SUM(quantity) as count FROM cart WHERE user_id = ?";
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
