<?php
require ('connection.php');


$sql1="SELECT * FROM category";
$query1=$conn->query($sql1);

$data_list=array();

while($data1=mysqli_fetch_assoc($query1)){
$Category_id=$data1['Category_id'];

$Category_Name=$data1['Category_Name'];

$data_list[$Category_id]=$Category_Name;
}









$sql2 = "SELECT * FROM brand";
$query2 = $conn->query($sql2);

$brand_list = array();

while ($data2 = mysqli_fetch_assoc($query2)) {
    $brand_id = $data2['brand_id'];
    $brand_name = $data2['brand_name'];
    $brand_list[$brand_id] = $brand_name;
}
  


?>




  
  
<!DOCTYPE html>
<html>
     <head>
	 <title> List of Product</title>
     </head>
      <body> 
	       <?php
		   
		$sql="SELECT * FROM product";
		$query=$conn->query($sql);
		echo "<table border='1'><tr><th>Product</th><th>Category</th><th>Code</th><th>Price</th><th>Brand</th><th>Action</th></tr>";
		while($data=mysqli_fetch_assoc($query)){
			$product_id        =$data['product_id'];
			$product_name      =$data['product_name'];
			$product_category  =$data['product_category'];
			$product_code  =$data['product_code'];
			$product_price  =$data['product_price'];
			$product_brand  =$data['product_brand'];
			
         echo"<tr>
		 <td>$product_name  </td>
		 <td>$data_list[$product_category]</td>
		 <td>$product_code</td>
		 <td>$product_price</td>
		 <td>{$brand_list[$product_brand]}</td>
		 
		 <td><a href='edit_product.php?id=$product_id '>Edit</td></tr>";

		}
		echo "</table>";
        
		
		
		?>
	       

</body>


</html>       