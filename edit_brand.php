<?php
require ('connection.php');
?>
  
<!DOCTYPE html>
<html>
<head>
    <title>Edit Brand</title>
</head>
<body> 
<?php
if(isset($_GET['id'])){
    $getid = $_GET['id']; 
    
    $sql = "SELECT * FROM brand WHERE brand_id = $getid";
    $query = $conn->query($sql);
    
    $data = mysqli_fetch_assoc($query);
    $brand_id = $data['brand_id']; 
    $brand_name = $data['brand_name']; 
    $brand_origin = $data['brand_origin']; 
}

if(isset($_GET['brand_name'])){
    $new_brand_name = $_GET['brand_name'];
    $new_brand_origin = $_GET['brand_origin'];
    $new_brand_id = $_GET['brand_id'];
    
    $sql1 = "UPDATE brand SET
        brand_name = '$new_brand_name',
        brand_origin = '$new_brand_origin' 
        WHERE brand_id = $new_brand_id";
    
    if($conn->query($sql1) == TRUE){
        echo 'Updated!';
    } else {
        echo 'Failed to update!';
    }
}
?>

<form action="edit_brand.php" method="GET">
    Brand Name:<br>
    <input type="text" name="brand_name" value="<?php echo $brand_name ?>"><br><br>
    Brand Origin:<br>
    <input type="text" name="brand_origin" value="<?php echo $brand_origin ?>"><br><br>
    <input type="hidden" name="brand_id" value="<?php echo $brand_id ?>"> 
    <input type="submit" value="update"> 
</form>

</body>
</html>