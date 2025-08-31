<?php
require('connection.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Users</title>
</head>
<body>

<?php
if (isset($_GET['id'])) {
    $getid = $_GET['id'];
    $sql = "SELECT * FROM users WHERE user_id = $getid";
    $query = $conn->query($sql);
    $data = mysqli_fetch_assoc($query);

    $user_id        = $data['user_id'];
    $user_f_name      = $data['user_f_name'];
    $user_l_name  = $data['user_l_name'];
    $user_email= $data['user_email'];
	$user_password= $data['user_password'];
	$user_address= $data['user_address'];
	$user_phone_no= $data['user_phone_no'];
	
	
	
	
    
}

if (isset($_GET['user_f_name'])) {
    
         
    $new_user_f_name      = $_GET['user_f_name'];
    $new_user_l_name = $_GET['user_l_name'];
    $new_user_email  = $_GET['user_email'];
	$new_user_password  = $_GET['user_password'];
	$new_user_address  = $_GET['user_address'];
    $new_user_phone_no  = $_GET['user_phone_no'];
	$new_user_id  = $_GET['user_id'];
	
	$sql1 = "UPDATE users SET
                user_f_name= '$new_user_f_name',
				user_l_name= '$new_user_l_name',
                user_email=  '$new_user_email',
				user_password='$new_user_password', 
				user_address=  '$new_user_address',
				user_phone_no=  '$new_user_phone_no'
				
                
            WHERE user_id = $new_user_id";

    if ($conn->query($sql1) == TRUE) {
        echo " updated!";
    } else {
        echo "Failed to update!";
    }
}

?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
     User's First Name:<br>
    <input type="text" name="user_f_name" value="<?php echo $user_f_name ?>"> <br><br>
	
	
	User's Last Name:<br>
    <input type="text" name="user_l_name" value="<?php echo $user_l_name ?>"> <br><br>

     User's Email:<br>
    <input type="text" name="user_email" value="<?php echo $user_email ?>"> <br><br>
    
     User's Password:<br>
    <input type="text" name="user_password" value="<?php echo $user_password ?>"> <br><br>
	
	User's Address:<br>
    <input type="text" name="user_address" value="<?php echo $user_address ?>"> <br><br>
	
	User's Phone No.:<br>
    <input type="text" name="user_phone_no" value="<?php echo $user_phone_no ?>"> <br><br>
    
    
	 
 <input type="text" name="user_id" value="<?php echo $user_id ?>" hidden>  <br><br> 	 
	   
	   

      <input type="submit" value="Submit">
    </form>

   </body>
    </html>