<?php 
	require_once 'connect.php';
	$sql = "DELETE FROM action_log";
	$result=$db->prepare($sql);
    $result->execute();
    $sql = "ALTER TABLE action_log AUTO_INCREMENT=1";
	$result=$db->prepare($sql);
    $result->execute();
    header('Location: 1.php');
 ?>