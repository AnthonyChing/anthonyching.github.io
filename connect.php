<?php
$user = 'kirito';
$password = 'kirito1234';
$db = 'fridge';
$host = 'localhost';
$port = 3306;
$server  = "mysql:host=localhost;dbname=fridge";
/*$link = mysqli_init();
$success = mysqli_real_connect(
   $link,
   $host,
   $user,
   $password,
   $db,
   $port
);
*/
try{
   $db = new PDO($server, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
}
catch(PDOException $e){
   echo $e->getMessage();
}

?>
