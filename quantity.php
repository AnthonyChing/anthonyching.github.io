<?php 
	session_start();
	require_once("connect.php");
	if(!isset($_POST["ing_query"])){
		//nothing selected
		header('Location: index.php?selected=false');
	}
	$ingredient_id = $_POST["ing_query"];
	if(!isset($_POST["advanced_search"])){
		$_SESSION['ingredient_id_string'] = $ingredient_id;
		header('Location: result.php?advanced_search=false');	
	}
	else{
		//advanced search on
		$ingredient_quan = 0;
		$ingredient_id_string = "";
		foreach($ingredient_id as $i){
			if($ingredient_quan > 0){
				$ingredient_id_string = $ingredient_id_string . ',';  
			}
			$ingredient_id_string = $ingredient_id_string . $i;
			$ingredient_quan += 1;
		}
		$sql = "SELECT * FROM ingredients WHERE FIND_IN_SET(ingredient_id, (?))";
		$result = $db -> prepare($sql);
		$result -> execute(array($ingredient_id_string));
		$row = $result -> fetchAll(PDO::FETCH_ASSOC);

		$incorrect_ingredients = "";
	}
 ?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Quantity</title>
	<!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
</head>
<body>
	<div class="container">
		<h3 class = "mt-2 mb-2">請輸入各食材的數量或重量:</h3>
		<form action="result.php?advanced_search=true" method="post">
			<?php 
				for($i = 0; $i < $result->rowcount(); $i++){
					echo '<div class="input-group ">
						  	<span class="input-group-text" id="basic-addon1">'.$row[$i]['Ingredient_name'].'</span>
						  	<input type="text" class="form-control" name = "'.$ingredient_id[$i].'"aria-label="Username" value = "'.$incorrect_ingredients.'"aria-describedby="basic-addon1">
						  	<span class="input-group-text">'.$row[$i]['Measurement'].'</span>
						  	</div>
					';
				}
			 ?>
			<button type="submit" class="btn btn-primary mt-2">確認</button>
		</form>
		<div class="row mt-2">
			<a href="index.php"><button class="btn btn-primary">返回</button></a>
		</div>
	</div>
</body>
</html>