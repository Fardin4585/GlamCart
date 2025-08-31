<?php
/**
 * GlamCart - Check and Create Cart Table
 * This script checks if the cart table exists and creates it if it doesn't
 */

require_once 'connection.php';

// Check if cart table exists
$check_table = $conn->query("SHOW TABLES LIKE 'cart'");

if ($check_table->num_rows === 0) {
    // Create cart table
    $sql = "CREATE TABLE cart (
        cart_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_product (user_id, product_id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Cart table created successfully!";
    } else {
        echo "Error creating cart table: " . $conn->error;
    }
} else {
    echo "Cart table already exists!";
}

// Check if wishlist table exists
$check_wishlist = $conn->query("SHOW TABLES LIKE 'wishlist'");

if ($check_wishlist->num_rows === 0) {
    // Create wishlist table
    $sql = "CREATE TABLE wishlist (
        wishlist_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_product (user_id, product_id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<br>Wishlist table created successfully!";
    } else {
        echo "<br>Error creating wishlist table: " . $conn->error;
    }
} else {
    echo "<br>Wishlist table already exists!";
}

echo "<br><br>Database check complete!";
?>
