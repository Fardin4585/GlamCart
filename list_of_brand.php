<?php
require ('connection.php');
?>
  
<!DOCTYPE html>
<html>
<head>
    <title>List of Brand</title>
</head>
<body> 
    <?php
    $sql = "SELECT * FROM brand";
    $query = $conn->query($sql);

    echo "<table border='1'><tr><th>Brand Name</th><th>Origin</th><th>Action</th></tr>";

    while($data = mysqli_fetch_assoc($query)) {
        $brand_id    = $data['brand_id'];
        $brand_name  = $data['brand_name'];
        $brand_origin= $data['brand_origin'];

        echo "<tr><td>$brand_name</td><td>$brand_origin</td><td><a href='edit_brand.php?id=$brand_id'>Edit</a></td></tr>";
    }

    echo "</table>";
    ?>
</body>
</html>