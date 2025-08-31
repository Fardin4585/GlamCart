<?php
require('connection.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Store Product</title>
</head>
<body>

<?php
// Insert logic
if (isset($_GET['store_product_name']) && isset($_GET['store_product_quantity']) && isset($_GET['store_product_entry_date'])) {
    $store_product_name = $_GET['store_product_name'];
    $store_product_quantity = $_GET['store_product_quantity'];
    $store_product_entry_date = $_GET['store_product_entry_date'];

    $sql = "INSERT INTO store_product (store_product_name, store_product_quantity, store_product_entry_date)
            VALUES ('$store_product_name', '$store_product_quantity', '$store_product_entry_date')";

    if ($conn->query($sql) === TRUE) {
        echo "Data Inserted!";
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
    <select name="store_product_name">
        <?php
        while ($row = mysqli_fetch_assoc($product_query)) {
            $product_id = $row['product_id'];
            $product_name = $row['product_name'];
            echo "<option value='$product_id'>$product_name</option>";
        }
        ?>
    </select><br><br>

    Quantity:<br>
    <input type="text" name="store_product_quantity"><br><br>

    Entry Date:<br>
    <input type="date" name="store_product_entry_date"><br><br>

    <input type="submit" value="Submit">
</form>

</body>
</html>