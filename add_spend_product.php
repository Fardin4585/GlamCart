<?php
require('connection.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Spend Product</title>
</head>
<body>

<?php
// Insert logic
if (isset($_GET['spend_product_name']) ) {
    $spend_product_name = $_GET['spend_product_name'];
    $spend_product_quantity = $_GET['spend_product_quantity'];
    $spend_product_entry_date = $_GET['spend_product_entry_date'];

    $sql = "INSERT INTO spend_product (spend_product_name, spend_product_quantity, spend_product_entry_date)
            VALUES ('$spend_product_name', '$spend_product_quantity', '$spend_product_entry_date')";

    if ($conn->query($sql) == TRUE) {
        echo "Data Inserted!";
		
		
        $update_store = "UPDATE store_product 
                         SET store_product_quantity = store_product_quantity - $spend_product_quantity 
                         WHERE store_product_name = '$spend_product_name'";
        $conn->query($update_store);
		
		
		
		
		} else {
        echo "Data not Inserted!";
    }
}

// Fetch product names from product table
$product_sql = "SELECT product_id, product_name FROM product";
$product_query = $conn->query($product_sql);
?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
    Select Product:<br>
    <select name="spend_product_name">
        <?php
        while ($data = mysqli_fetch_assoc($product_query)) {
            $product_id = $data['product_id'];
            $product_name = $data['product_name'];
            echo "<option value='$product_id'>$product_name</option>";
        }
        ?>
    </select><br><br>

    Quantity:<br>
    <input type="text" name="spend_product_quantity"><br><br>

    Entry Date:<br>
    <input type="date" name="spend_product_entry_date"><br><br>

    <input type="submit" value="Submit">
</form>

</body>
</html>        