<?php
require('connection.php');

// Fetch product details
if (isset($_GET['id'])) {
    $getid = $_GET['id'];
    $sql = "SELECT * FROM product WHERE product_id = $getid";
    $query = $conn->query($sql);
    $data = mysqli_fetch_assoc($query);

    $product_id         = $data['product_id'];
    $product_name       = $data['product_name'];
    $product_category   = $data['product_category'];
    $product_code       = $data['product_code'];
    $product_entry_date = $data['product_entry_date'];
    $product_price      = $data['product_price'];
    $product_brand      = $data['product_brand'];
}

// Update product
if (isset($_GET['product_name']) && isset($_GET['product_entry_date'])) {
    $new_product_id         = $_GET['product_id'];
    $new_product_name       = $_GET['product_name'];
    $new_product_category   = $_GET['product_category'];
    $new_product_code       = $_GET['product_code'];
    $new_product_entry_date = $_GET['product_entry_date'];
    $new_product_price      = $_GET['product_price'];
    $new_product_brand      = $_GET['product_brand'];

    $sql1 = "UPDATE product SET
                product_name = '$new_product_name',
                product_category = '$new_product_category',
                product_code = '$new_product_code',
                product_entry_date = '$new_product_entry_date',
                product_price = '$new_product_price',
                product_brand = '$new_product_brand'
            WHERE product_id = $new_product_id";

    if ($conn->query($sql1) === TRUE) {
        echo " updated!";
    } else {
        echo "Failed to update!";
    }
}

// Fetch categories for dropdown
$category_sql = "SELECT * FROM category";
$category_query = $conn->query($category_sql);



$brand_query = $conn->query("SELECT * FROM brand");




?>



<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
</head>
<body>
    <form action="edit_product.php" method="GET">
        Product:<br>
        <input type="text" name="product_name" value="<?php echo $product_name; ?>"><br><br>

        Product Category:<br>
        <select name="product_category">
            <?php
            while ($cat = mysqli_fetch_assoc($category_query)) {
                $selected = ($cat['Category_id'] == $product_category) ? "selected" : "";
                echo "<option value='{$cat['Category_id']}' $selected>{$cat['Category_Name']}</option>";
            }
            ?>
        </select><br><br>

        Product Code:<br>
        <input type="text" name="product_code" value="<?php echo $product_code; ?>"><br><br>

        Product Entry Date:<br>
        <input type="date" name="product_entry_date" value="<?php echo $product_entry_date; ?>"><br><br>

        Product Price:<br>
        <input type="text" name="product_price" value="<?php echo $product_price; ?>"><br><br>

        Product Brand:<br>
           <select name="product_brand">
              <?php
             while ($brand = mysqli_fetch_assoc($brand_query)) {
             $selected = ($brand['brand_id'] == $product_brand) ? "selected" : "";
                 echo "<option value='{$brand['brand_id']}' $selected>{$brand['brand_name']}</option>";
                 }
                  ?>
            </select><br><br>









        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <input type="submit" value="Update">
    </form>
</body>
</html>