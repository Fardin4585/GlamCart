<?php
require ('connection.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Brand</title>
</head>
<body> 
    <?php
    if (isset($_GET['brand_name']) && isset($_GET['brand_origin'])) {
        $brand_name   = $_GET['brand_name'];
        $brand_origin = $_GET['brand_origin'];

        $sql = "INSERT INTO brand (brand_name, brand_origin) 
                VALUES ('$brand_name', '$brand_origin')";

        if ($conn->query($sql) == TRUE) {
            echo 'Brand added!';
        } else {
            echo 'Brand not added!';
        }
    }
    ?>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
        Brand Name:<br>
        <input type="text" name="brand_name"><br><br>

        Brand Origin:<br>
        <input type="text" name="brand_origin"><br><br>

        <input type="submit" value="submit"> 
    </form>
</body>
</html>