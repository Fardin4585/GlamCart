<?php
function data_list($tablename,$column1,$column2){

require('connection.php');
$sql="SELECT * FROM $tablename";

$query=$conn->query($sql);


while ($data=mysqli_fetch_array($query)){

$data_id=$data[column1];
$data_name=$data[column2];




}


}

// Note: The following functions are already defined in connection.php:
// - is_logged_in()
// - is_admin() 
// - getCartCountFromDB()
// - getWishlistCountFromDB()
// 
// Please use the functions from connection.php instead to avoid conflicts.

?>