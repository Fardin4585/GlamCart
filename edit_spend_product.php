<?php
require('connection.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Spend Product</title>
</head>
<body>

<?php
if (isset($_GET['id'])) {
    $getid = $_GET['id'];
    $sql = "SELECT * FROM spend_product WHERE spend_product_id = $getid";
    $query = $conn->query($sql);
    $data = mysqli_fetch_assoc($query);

    $spend_product_id        = $data['spend_product_id'];
    $spend_product_name      = $data['spend_product_name'];
    $spend_product_quantity  = $data['spend_product_quantity'];
    $spend_product_entry_date= $data['spend_product_entry_date'];
    
}

if (isset($_GET['spend_product_name'])) {
    
    $new_spend_product_name       = $_GET['spend_product_name'];
    $new_spend_product_quantity       = $_GET['spend_product_quantity'];
    $new_spend_product_entry_date = $_GET['spend_product_entry_date'];
    $new_spend_product_id        = $_GET['spend_product_id'];
    
	$sql1 = "UPDATE spend_product SET
               spend_product_name = '$new_spend_product_name',
                spend_product_quantity = '$new_spend_product_quantity',
                spend_product_entry_date = '$new_spend_product_entry_date'
                
            WHERE spend_product_id = $new_spend_product_id";

    if ($conn->query($sql1) == TRUE) {
        echo " updated!";
    } else {
        echo "Failed to update!";
    }
}

?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
     Product:<br>
    <select name="spend_product_name">
        <?php
		$product_sql="SELECT * FROM product";
		$product_query=$conn->query($product_sql);
        while ($row = mysqli_fetch_array($product_query)) {
            $product_id = $row['product_id'];
            $product_name = $row['product_name'];
           
        ?>
		<option value='<?php echo $product_id?>'><?php if($spend_product_name==$product_id)echo'selected';?>>
		<?php echo $product_name?>
		</option>";
	    <?php } ?> 
		</select><br><br>

    Quantity:<br>
       <input type="text" name="spend_product_quantity" value="<?php echo $spend_product_quantity; ?>"> <br><br>

    Entry Date:<br>
       <input type="date" name="spend_product_entry_date" value="<?php echo $spend_product_entry_date; ?>"> <br><br>
	 
 <input type="text" name="spend_product_id" value="<?php echo $spend_product_id; ?>" hidden> 	 
	   
	   

      <input type="submit" value="Submit">
    </form>

   </body>
    </html>