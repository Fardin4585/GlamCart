<?php
require ('connection.php');
?>
  
<!DOCTYPE html>
<html>
     <head>
	 <title> Edit Category</title>
     </head>
      <body> 
	       <?php
		  if(isset($_GET['id'])){
			  $getid=$_GET['id']; 
			  
			  $sql="SELECT * FROM category WHERE Category_id=$getid";
		      
			  $query=$conn->query($sql);
			  
			  $data=mysqli_fetch_assoc($query);
			 $Category_id=$data['Category_id']; 
			 $Category_Name=$data['Category_Name']; 
			 $Category_entry_date=$data['Category_entry_date']; 
		  }

       if(isset( $_GET['Category_Name'])){
		   
		$new_Category_Name      =   $_GET['Category_Name'];
		$new_Category_entry_date=   $_GET['Category_entry_date'];
		$new_Category_id        =   $_GET['Category_id'];
		
		 
		
		$sql1="UPDATE category SET
		   Category_Name='$new_Category_Name',
		   Category_entry_date='$new_Category_entry_date' WHERE Category_id =$new_Category_id";
		  if( $conn->query($sql1)==TRUE){
		  echo 'Updated!';
		  }
		  else{
			  echo'Failed to update!';
		  
		  
		  }

		  
		   
	   }
   

   ?>
	      


		  <form action="edit_category.php" method="GET">
		   Category:<br>
		   <input type="text" name="Category_Name"  value="<?php echo $Category_Name ?>"><br><br>
		   Category Entry Date:<br>
		   <input type="date" name="Category_entry_date" value="<?php echo $Category_entry_date ?>"><br><br>
		   <input type="text" name="Category_id"  value="<?php echo $Category_id ?>"> 
		   <input type="submit" value="update"> 
		   </form>
      </body>


</html>