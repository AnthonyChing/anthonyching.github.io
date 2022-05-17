<?php 
	session_start();
	require_once 'connect.php';

	$action_type = $_SESSION['action_type'];
	$ingredient_id = $_SESSION['fridge_ingredient_id'];
	$ingredient_name = "";
	$quantity = "";
	$measurement = "";
	date_default_timezone_set('Asia/Taipei');
	$date = date('Y/m/d/h:i:sa', time());

	$sql = "SELECT * FROM ingredients";
	$result=$db->prepare($sql);
    $result->execute();
    $row=$result->fetchAll(PDO::FETCH_ASSOC);

    //update the action log
    foreach ($row as $value) {
    	if($value['Ingredient_id'] == $_SESSION['fridge_ingredient_id']){
    		$ingredient_name =  $value['Ingredient_name'];
    		$quantity = $_POST['quantity'];
    		$measurement = $value['Measurement'];
    	}
    }
    if($_POST['timespan_value'] != null){
    	$timespan_calc = '+' . $_POST['timespan_value'] . ' ' .$_POST['timespan_measurement'];
    	$expiration_date = date('Y-m-d', strtotime($timespan_calc));
    }
    else{
    	$expiration_date = $_POST['expiration_date'];
    }

    if($action_type == 'put_in'){
	    $sql = "INSERT INTO action_log (Action_type, Ingredient_id, Ingredient_name, Quantity, Measurement, Datetime, Expiration_date)
				VALUES (?,?,?,?,?,?,?)";
		$result=$db->prepare($sql);
	    $result->execute(array($action_type,$ingredient_id,$ingredient_name,$quantity,$measurement,$date,$expiration_date));
    }
    else if($action_type == 'take_out'){
    	$sql = "INSERT INTO action_log (Action_type, Ingredient_id, Ingredient_name, Quantity, Measurement, Datetime)
				VALUES (?,?,?,?,?,?)";
		$result=$db->prepare($sql);
	    $result->execute(array($action_type,$ingredient_id,$ingredient_name,$quantity,$measurement,$date));
    }

    //update the fridge supply table
    if($action_type == 'put_in'){
    	$sql = "SELECT * FROM fridge_supply";
		$result=$db->prepare($sql);
    	$result->execute();
    	$row=$result->fetchAll(PDO::FETCH_ASSOC);

    	$already_in_stock = false;
    	foreach($row as $i){
    		if($i['Ingredient_id'] == $ingredient_id && $i['Expiration_date'] == $expiration_date){
    			$sql = "UPDATE fridge_supply 
    					SET Quantity = Quantity + ?
    					WHERE Ingredient_id = ? AND Expiration_date = ?";
    			$result=$db->prepare($sql);
    			$result->execute(array($quantity,$ingredient_id,$expiration_date));
    			$already_in_stock = true;
    		}
    	}
    	if($already_in_stock == false){
	    	$sql = "INSERT INTO fridge_supply (Ingredient_name, Ingredient_id, Quantity, Measurement, Expiration_date)
				VALUES (?,?,?,?,?)";
			$result=$db->prepare($sql);
	    	$result->execute(array($ingredient_name,$ingredient_id,$quantity,$measurement,$expiration_date));
    	}


    }
    else if($action_type == 'take_out'){
    	$sql = "SELECT * FROM fridge_supply
    			WHERE Ingredient_id = ?
    			ORDER BY Expiration_date ASC";
		$result=$db->prepare($sql);
    	$result->execute(array($ingredient_id));
    	$row=$result->fetchAll(PDO::FETCH_ASSOC);

    	$quantity_change = [];
    	$quantity_left = $quantity;
    	foreach($row as $value){
    		if($quantity_left != 0){
    			if($quantity_left >= $value['Quantity']){
    				$quantity_left -= $value['Quantity'];
    				$quantity_change[$value['Expiration_date']] = 0;
    			}
    			else{
    				$quantity_change[$value['Expiration_date']] = $value['Quantity'] - $quantity_left;
    				$quantity_left = 0;
    			}
    		}
    	}

    	foreach ($quantity_change as $key => $value) {
    		if($value == 0){
    			$sql = "DELETE FROM fridge_supply
						WHERE Ingredient_id = ?
						AND Expiration_date = ?";
		    	$result=$db->prepare($sql);
		    	$result->execute(array($ingredient_id, $key));
    		}
    		else{
		    	$sql = "UPDATE fridge_supply 
		    			SET Quantity = ?
		    			WHERE Ingredient_id = ?
		    			AND Expiration_date = ?";
		    	$result=$db->prepare($sql);
		    	$result->execute(array($value,$ingredient_id,$key));
    		}
    	}

    }
    else if($action_type == 'modify'){

    }
    else{
    	//error
    }
    header('Location: index.php?action=success');
    





 ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
	<a href="index.php">back</a>
</body>
</html>
