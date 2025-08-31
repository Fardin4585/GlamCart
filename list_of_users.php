<?php
require ('connection.php');

?>




  
  
<!DOCTYPE html>
<html>
     <head>
	 <title> List of Users</title>
     </head>
      <body> 
	       <?php
		   
		$sql="SELECT * FROM users";
		$query=$conn->query($sql);
		echo "<table border='1'><tr><th>First Name</th><th>Last Name</th><th>Email</th><th>Address</th><th>Phone</th></tr>";
		while($data=mysqli_fetch_assoc($query)){
			$user_id        =$data['user_id'];
			$user_f_name    =$data['user_f_name'];
			$user_l_name=$data['user_l_name'];
			$user_email  =$data['user_email'];
			$user_address=$data['user_address'];
			$user_phone_no=$data['user_phone_no'];
			
         echo"<tr>
		 
		 <td>$user_f_name</td>
		 <td>$user_l_name</td>
		 <td>$user_email</td>
		  <td>$user_address</td>
		   <td>$user_phone_no</td>
		 
		
		 
		 <td><a href='edit_users.php?id=$user_id '>Edit</td></tr>";

		}
		echo "</table>";
        
		
		
		?>
	       

</body>


</html>       