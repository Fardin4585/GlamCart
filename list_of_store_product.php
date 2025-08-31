<?php
require ('connection.php');


$sql1="SELECT * FROM product";
$query1=$conn->query($sql1);

$data_list=array();

while($data1=mysqli_fetch_assoc($query1)){
$product_id=$data1['product_id'];

$product_name=$data1['product_name'];

$data_list[$product_id]=$product_name;
}






?>




  
  
<!DOCTYPE html>
<html>
     <head>
	 <title> List of Store Product</title>
     </head>
      <body> 
	       <?php
		   
		$sql="SELECT * FROM store_product";
		$query=$conn->query($sql);
		echo "<table border='1'><tr><th>Product</th><th>Quantity</th><th>Entry Date</th><th>Action</th></tr>";
		while($data=mysqli_fetch_assoc($query)){
			$store_product_id        =$data['store_product_id'];
			$store_product_name      =$data['store_product_name'];
			$store_product_quantity=$data['store_product_quantity'];
			$store_product_entry_date  =$data['store_product_entry_date'];
			
			
         echo"<tr>
		 
		 <td>$data_list[$store_product_name]</td>
		 <td>$store_product_quantity</td>
		 <td>$store_product_entry_date</td>
		
		 
		 <td><a href='edit_store_product.php?id=$store_product_id '>Edit</td></tr>";

		}
		echo "</table>";
        
		
		
		?>
	       

</body>


</html>       