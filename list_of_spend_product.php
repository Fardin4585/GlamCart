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
	 <title> List of Spend Product</title>
     </head>
      <body> 
	       <?php
		   
		$sql="SELECT * FROM spend_product";
		$query=$conn->query($sql);
		echo "<table border='1'><tr><th>Product</th><th>Quantity</th><th>Entry Date</th><th>Action</th></tr>";
		while($data=mysqli_fetch_assoc($query)){
			$spend_product_id        =$data['spend_product_id'];
			$spend_product_name      =$data['spend_product_name'];
			$spend_product_quantity=$data['spend_product_quantity'];
			$spend_product_entry_date  =$data['spend_product_entry_date'];
			
			
         echo"<tr>
		 
		 <td>$data_list[$spend_product_name]</td>
		 <td>$spend_product_quantity</td>
		 <td>$spend_product_entry_date</td>
		
		 
		 <td><a href='edit_spend_product.php?id=$spend_product_id '>Edit</td></tr>";

		}
		echo "</table>";
        
		
		
		?>
	       

</body>


</html>       