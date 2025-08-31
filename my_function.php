<?php
function data_list($tablename,$column1,$column2){

require('connection.php');
$sql="SELECT * FROM $tablename";

$query=$conn->query($sql);


while ($data=mysqli_fetch_array($query)){

$data_id=$data[column1];
$data_name=$data[column2];




}


}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Get cart count from database
function getCartCountFromDB() {
    if (!isset($_SESSION['user_id'])) return 0;
    
    require('connection.php');
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}

// Get wishlist count from database
function getWishlistCountFromDB() {
    if (!isset($_SESSION['user_id'])) return 0;
    
    require('connection.php');
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}

?>