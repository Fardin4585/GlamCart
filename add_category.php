<?php
$hostname = 'localhost';
$username = 'root';
$password = '';
$dbname   = 'glam_cart'; // update this if your DB name is different

$conn = new mysqli($hostname, $username, $password, $dbname);

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}
?>
  
  
  
  
<!DOCTYPE html>
<html>
     <head>
	 <title> Add Category</title>
     </head>
      <body> 
	       <?php
		   if(isset($_GET['Category_Name']) && isset($_GET['Category_entry_date'])){
			   $Category_Name      = $_GET['Category_Name'];
			   $Category_entry_date= $_GET['Category_entry_date'];
		   
	$sql = "INSERT INTO category (Category_name, Category_entry_date) 
    VALUES ('$Category_Name', '$Category_entry_date')";

if($conn->query($sql) == TRUE){
    echo 'Data Inserted!';
}else{
    echo 'Data not Inserted!';
    }
		   
}
		   
     ?>
	       <form action="add_category.php" method="GET">
		   Category:<br>
		   <input type="text" name="Category_Name"><br><br>
		   Category Entry Date:<br>
		   <input type="date" name="Category_entry_date"><br><br>
		   <input type="submit" value="submit"> 
		   </form>
      </body>


</html>