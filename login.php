<?php
require $_SERVER['DOCUMENT_ROOT'].'/config/config.php';
function check_user($username,$email,$department,$sid){
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
// Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    else {
        //mysqli_query($conn,'SET MAMES utf8');
        $query="SELECT * FROM `users` WHERE `sid`='$sid'";
        if ($result=mysqli_query($conn,$query)) {
               if (mysqli_num_rows($result)== 0 ) {
                   $query="INSERT INTO `users` (`user_id`, `username`, `email`, `department`,`sid`) VALUES (NULL, '$username', '$email', '$department','$sid')";
                mysqli_query($conn,$query);
                $query="SELECT `user_id` FROM `users` WHERE`sid`='$sid'";

               if($result=mysqli_query($conn,$query)){
                   $userid=mysqli_fetch_array($result);
                return $userid['user_id'];
               }
            }
               else {
                   $userid=mysqli_fetch_array($result);
                  return $userid['user_id'];

               }
        }
          }
    $conn->close();
}
?>
