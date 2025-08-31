
<?php
require ('connection.php');
?>





<!DOCTYPE html>
<html>
     <head>
	 <title> Add Product</title>
     </head>
      <body> 
	       <?php
		   if(isset($_GET['product_name']) && isset($_GET['product_entry_date'])){
			   $product_name      = $_GET['product_name'];
			   $product_category  = $_GET['product_category'];
			   $product_code      = $_GET['product_code'];
			   $product_entry_date= $_GET['product_entry_date'];
			   $product_price     = $_GET['product_price'];
			   $product_brand     = $_GET['product_brand'];
			  
		   
	$sql = "INSERT INTO product (product_name,product_category,product_code,product_entry_date,product_price,product_brand ) 
    VALUES ('$product_name', '$product_category',' $product_code','$product_entry_date','$product_price','$product_brand')";

if($conn->query($sql) == TRUE){
    echo 'Data Inserted!';
}else{
    echo 'Data not Inserted!';
    }
		   
}
		   
     ?>
	 
	 <?php
	 
	 $sql="SELECT * FROM category";
	 $query=$conn->query($sql);
	 
	 ?>
	 
	 <?php
        $sql_brand = "SELECT * FROM brand";
        $query_brand = $conn->query($sql_brand);
         ?>
          
	 
	 
	 
	       <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="GET">
		   Product:<br>
		   <input type="text" name="product_name"><br><br>
		   Product Category:<br>
		   <select name="product_category"><br><br>
		    <?php
		  while( $data=mysqli_fetch_array($query)){
	  $Category_id=$data['Category_id'];
	  $Category_Name=$data['Category_Name'];
	  
	  echo "<option value='$Category_id'>$Category_Name</option>";
	  
		  }
	    ?>
		   
		   </select>
		   <br><br>
		   Product Code:<br>
		   <input type="text" name="product_code"><br><br>
		   Product Entry Date:<br>
		   <input type="date" name="product_entry_date"><br><br>
		   Product Price:<br>
		   <input type="text" name="product_price"><br><br>
		   Product Brand:<br>
		   
		   <select name="product_brand">
            <?php
            while($data_brand = mysqli_fetch_array($query_brand)){
              $brand_id = $data_brand['brand_id'];
               $brand_name = $data_brand['brand_name'];
                echo "<option value='$brand_id'>$brand_name</option>";
                   }
                    ?>
               </select><br><br>  
		   
		   
		   
		   
		   
		   
		   
		   <input type="submit" value="submit"> 
		   </form>
      </body>


</html>