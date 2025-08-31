<?php
require ('connection.php');
?>
  
  
<!DOCTYPE html>
<html>
     <head>
	 <title> List of Category</title>
     </head>
      <body> 
	       <?php
		   
		$sql="SELECT * FROM category";
		$query=$conn->query($sql);
		echo "<table border='1'><tr><th>Category</th><th>Date</th><th>Action</th></tr>";
		while($data=mysqli_fetch_assoc($query)){
			$Category_id        =$data['Category_id'];
			$Category_Name      =$data['Category_Name'];
			$Category_entry_date=$data['Category_entry_date'];
			
         echo"<tr><td>$Category_Name</td><td>$Category_entry_date</td><td><a href='edit_category.php?id=$Category_id '>Edit</td></tr>";

		}
		echo "</table>";
        
		
		
		?>
	       

</body>


</html>