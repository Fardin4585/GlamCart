<?php
require('connection.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Users</title>
</head>
<body>

<?php
// Insert logic
if (isset($_GET['user_f_name']) ) {
    $user_f_name = $_GET['user_f_name'];
    $user_l_name = $_GET['user_l_name'];
    $user_email = $_GET['user_email'];
    $user_password=$_GET['user_password'];
	$user_address = $_GET['user_address'];
	$user_phone_no= $_GET['user_phone_no'];
	 
    $sql = "INSERT INTO users (user_f_name,user_l_name ,user_email,user_password,user_address,user_phone_no)
            VALUES (' $user_f_name', '$user_l_name', ' $user_email','$user_password','$user_address','$user_phone_no')";

    if ($conn->query($sql) == TRUE) {
        echo "Data Inserted!";
		
		} else {
        echo "Data not Inserted!";
    }
}
?>


<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
    User's First Name:<br>
    <input type="text" name="user_f_name"><br><br>

     User's Last Name:<br>
    <input type="text" name="user_l_name"><br><br>
	
	User's Email :<br>
    <input type="email" name="user_email"><br><br>
	
	 User's Password:<br>
   <input type="password" name="user_password"><br><br>

    User's Address:<br>
   <input type="text" name="user_address"><br><br>
   
   User's Phone:<br>
   <input type="text" name="user_phone_no"><br><br>
   
  

    <input type="submit" value="Submit">
</form>

</body>
</html>        