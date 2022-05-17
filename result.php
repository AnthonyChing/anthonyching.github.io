<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Results</title>
	<!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
</head>
<body>
	<div class="container">
<?php
	session_start();
	require_once("connect.php");
	$sqla = "SELECT * FROM ingredients";
	$result=$db->prepare($sqla);
    $result->execute();
    $row=$result->fetchAll(PDO::FETCH_ASSOC);
    $ingredient_quan = 0;
    $ingredient_id_string = "";
    $food_prepared = [];
    $advanced_search = true;

    if(isset($_GET["advanced_search"])){
    	if($_GET["advanced_search"] == "false"){
    		$advanced_search = false;
    		foreach($_SESSION['ingredient_id_string'] as $i){
    			if($ingredient_quan > 0){
						$ingredient_id_string = $ingredient_id_string . ',';  
					}
					$ingredient_id_string = $ingredient_id_string . $i;
					$ingredient_quan += 1;
    		}
    	}
    	else{
		    $check_integer = true;
		    echo'
		    	<h3 class = "mt-2">您輸入的食材有：</h3>
		    ';
		    for($i = 0; $i < $result->rowcount(); $i++){
				if(isset($_POST[$row[$i]['Ingredient_id']])){

					if($ingredient_quan > 0){
						$ingredient_id_string = $ingredient_id_string . ',';  
					}
					$ingredient_id_string = $ingredient_id_string . $row[$i]['Ingredient_id'];
					$ingredient_quan += 1;
					if(trim($_POST[$row[$i]['Ingredient_id']]) == "" || trim($_POST[$row[$i]['Ingredient_id']]) == 0){
						$input = 0;
					}
					else{
						$input = (float) trim($_POST[$row[$i]['Ingredient_id']]);
						if($input == 0){
							$check_integer = false;
						}
					}
					$food_prepared[$i][0] = $row[$i]['Ingredient_name'];
					$food_prepared[$i][1] = $input;
					/* food_prepared -> $i = Ingredient_id
					               0 -> name
					               1 -> quantity/weight
					*/
					echo $food_prepared[$i][0] . ': ' . $food_prepared[$i][1] . $row[$i]['Measurement'];
					echo '<br>';
				}
			}

			if($check_integer == false){
				header('Location: quantity.php?check_integer=false');
			}
		}
    }



	$sql = "SELECT * FROM recipe as a
			INNER JOIN ri as b
			WHERE a.Recipe_id = b.Recipe_id
			and FIND_IN_SET(b.Ingredient_id,(?)) IS TRUE
			Group BY a.Recipe_id";
	$result = $db -> prepare($sql);
	$result -> execute(array($ingredient_id_string));
	$row = $result -> fetchAll(PDO::FETCH_ASSOC);
	//echo $result -> rowcount(). ' recipe(s) queried<p>';
	$ris = ""; // Recipe id list like 3,4,5
	$recipe = []; // A small recipe table
	for($i = 0; $i < $result->rowcount(); $i++){
		$recipe[$i][0] = $row[$i]['Dish_name']; //Dish name
		$recipe[$i][1] = $row[$i]['Recipe_id']; //Recipe id
	    if($i > 0){
	    	$ris = $ris .',';
	    }
	    $ris = $ris . $row[$i]['Recipe_id'];
	}
	$sqlb = "SELECT * FROM ri as a
			INNER JOIN ingredients as b
			WHERE a.Ingredient_id = b.Ingredient_id
			and FIND_IN_SET(Recipe_id, (?)) IS TRUE";
	$result = $db -> prepare($sqlb);
	$result -> execute(array($ris));
	$row = $result -> fetchAll(PDO::FETCH_ASSOC);
	/*
	foreach($row as $a){
		foreach($a as $b){
			echo $b . ' ';
		}
		echo '<br>';
	}
	*/

 ?>
		<div id="accordion">
		</div>
		<?php 
			if($advanced_search == true){
				echo'
					<h3 class = "mt-2 mb-0">推薦食譜：</h3>
					<div class="row">
			            <div class="col">
			                (
			                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 24 24">
			                <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
			                </svg>
			                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star" viewBox="0 0 24 24">
			                <path d="M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256 4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 3.522-3.356c.33-.314.16-.888-.282-.95l-4.898-.696L8.465.792a.513.513 0 0 0-.927 0L5.354 5.12l-4.898.696c-.441.062-.612.636-.283.95l3.523 3.356-.83 4.73zm4.905-2.767-3.686 1.894.694-3.957a.565.565 0 0 0-.163-.505L1.71 6.745l4.052-.576a.525.525 0 0 0 .393-.288L8 2.223l1.847 3.658a.525.525 0 0 0 .393.288l4.052.575-2.906 2.77a.565.565 0 0 0-.163.506l.694 3.957-3.686-1.894a.503.503 0 0 0-.461 0z"/>
			                </svg>代表適合做這道菜的程度)
			            </div>
			        </div>
				';
			}
			else{
				echo '<h3 class = "mt-2 mb-2">食譜查詢結果：</h3>';
			}
		 ?>
		<div id="accordion">
		<?php
			$recipes_shown = 0;
			$ok_recipes = [];
			$show_threshold =  0.3; //至少要食譜上的0.3倍量才會顯示
			$buffer = 1 - $show_threshold;
			if($advanced_search == false){
				//not advanced search
				/*
				for($i = 0; $i < $result->rowcount(); $i++){
					$ok_recipes[$i][0] = $row[$i]['Dish_name'];
					$ok_recipes[$i][1] = $row[$i]['Recipe_id'];
				}
				*/
				$ok_recipes = $recipe;
			}
			else{
				function cmp($a, $b){
					if($a[2] == true && $b[2] == false){
						return false;
					}
					else if($a[2] == true && $b[2] == true){
						if($a[3] > $b[3]){
							return false;
						}
						else{
							return true;
						}
					}
					else if($a[2] == false && $b[2] == false){
						if($a[4] < $b[4]){
							return false;
						}
						else if($a[4] == $b[4]){
							if($a[3] > $b[3]){
								return false;
							}
							else{
								return true;
							}
						}
						else{
							return true;
						}
					}
					else{
						return true;
					}
				}

				for($r = 0; $r < count($recipe); $r++){
					$relevance = 0;
					$difference_ratio = [];
					$show = true;
					$surplus = true;
					$ingredients_not_enough = 0;
					for($rows_of_the_big_ri_table = 0; $rows_of_the_big_ri_table < $result->rowcount(); $rows_of_the_big_ri_table++){
						if($recipe[$r][1] == $row[$rows_of_the_big_ri_table]['Recipe_id']){
							foreach($food_prepared as $f){
								if($f[0] == $row[$rows_of_the_big_ri_table]['Ingredient_name']){
									if($f[1] < $row[$rows_of_the_big_ri_table]['Quantity']){
										$surplus = false;
										$ingredients_not_enough += 1;
										if($f[1] < $row[$rows_of_the_big_ri_table]['Quantity'] * $show_threshold){
											$show = false;
											/*
											echo $recipe[$r][0] .  '<br>'. $row[$rows_of_the_big_ri_table]['Ingredient_name'] . '至少需要' . $row[$rows_of_the_big_ri_table]['Quantity'] * 0.9 . '但我只有' . $f[1] . '<br>';
											echo '跟食譜上差了' . $row[$rows_of_the_big_ri_table]['Quantity'] - $f[1] . $row[$rows_of_the_big_ri_table]['Measurement'] . '<br>';
											*/
										}
										//calculating difference ratio
										$difference_ratio[count($difference_ratio)] = ($f[1] - $row[$rows_of_the_big_ri_table]['Quantity'])/ $row[$rows_of_the_big_ri_table]['Quantity'] * $buffer;
									}
									//else if ($f[1] >= $row[$rows_of_the_big_ri_table]['Quantity'])
									else{
										$difference_ratio[count($difference_ratio)] = $row[$rows_of_the_big_ri_table]['Quantity'] / $f[1];
									}
								}
							}
						}
					}	
					if($show){
						//0,1
						$ok_recipes[count($ok_recipes)] = $recipe[$r];
						//2
						$ok_recipes[count($ok_recipes)-1][2] = $surplus;
						//3
						if($surplus){
							foreach ($difference_ratio as $value) {
								$relevance += $value;
							}
							$relevance /= count($difference_ratio);
							$ok_recipes[count($ok_recipes)-1][3] = $relevance;
						}
						else{
							$count = 0;
							foreach ($difference_ratio as $value) {
								if($value < 0){
									$relevance += $value;
									$count++;
								}
							}
							if($count == 0){
								//do something, or doesn't have to be here. Avoid 0 division error
							}
							else{
								$relevance = 1 + ($relevance/$count);
								$ok_recipes[count($ok_recipes)-1][3] = $relevance;
							}
						}
						//4
						$ok_recipes[count($ok_recipes)-1][4] = $ingredients_not_enough;
						if($surplus == true){
							$ok_recipes[count($ok_recipes)-1][6] = "text-primary";
						}
						else{
							$ok_recipes[count($ok_recipes)-1][6] = "text-info";
						}
					}
				}
				usort($ok_recipes, "cmp");
			}

			foreach($ok_recipes as $okr){
				if($advanced_search == true){
					print_stars($okr[3]);
				}
				else{
					$okr[6] = "";
				}
				echo'
					<div class="card">
						<div class="card-header" id="heading'.$okr[1].'">
							<div class = "row">
								<div class = "col-6">
									<h5 class="mb-0">
										<button class="btn btn-link '.$okr[6].'" data-toggle="collapse" data-target="#'.$okr[1].'" aria-expanded="false" aria-controls="'.$okr[1].'">
											'.$okr[0];
				if($advanced_search == true){
					if($okr[2]==false){
						echo '(缺)';
					}
				}
				echo'
										</button>
							      	</h5>	
								</div>
							</div>
					    </div>
						<div id="'.$okr[1].'" class="collapse" aria-labelledby="heading'.$okr[1].'" data-parent="#accordion">
						    <div class="card-body">';
						    	if($advanced_search == true){
									echo '相關分數: ' .$okr[3] . '分<br>'; 
						    	}
								for($j = 0; $j < $result->rowcount(); $j++){
									if($okr[1] == $row[$j]['Recipe_id']){
										echo $row[$j]['Ingredient_name'] . '需要 ' . $row[$j]['Quantity'] . $row[$j]['Measurement'];
										echo '<br>';
									}
								}
				echo'		</div>
						</div>
					</div>';
				$recipes_shown += 1;
		}

		function print_stars($score) {
			$star_fill = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
						  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
						</svg>';
			$star_half = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-half" viewBox="0 0 16 16">
						  <path d="M5.354 5.119 7.538.792A.516.516 0 0 1 8 .5c.183 0 .366.097.465.292l2.184 4.327 4.898.696A.537.537 0 0 1 16 6.32a.548.548 0 0 1-.17.445l-3.523 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256a.52.52 0 0 1-.146.05c-.342.06-.668-.254-.6-.642l.83-4.73L.173 6.765a.55.55 0 0 1-.172-.403.58.58 0 0 1 .085-.302.513.513 0 0 1 .37-.245l4.898-.696zM8 12.027a.5.5 0 0 1 .232.056l3.686 1.894-.694-3.957a.565.565 0 0 1 .162-.505l2.907-2.77-4.052-.576a.525.525 0 0 1-.393-.288L8.001 2.223 8 2.226v9.8z"/>
						</svg>';
			$star_empty = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star" viewBox="0 0 16 16">
						  <path d="M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256 4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 3.522-3.356c.33-.314.16-.888-.282-.95l-4.898-.696L8.465.792a.513.513 0 0 0-.927 0L5.354 5.12l-4.898.696c-.441.062-.612.636-.283.95l3.523 3.356-.83 4.73zm4.905-2.767-3.686 1.894.694-3.957a.565.565 0 0 0-.163-.505L1.71 6.745l4.052-.576a.525.525 0 0 0 .393-.288L8 2.223l1.847 3.658a.525.525 0 0 0 .393.288l4.052.575-2.906 2.77a.565.565 0 0 0-.163.506l.694 3.957-3.686-1.894a.503.503 0 0 0-.461 0z"/>
						</svg>';
			$stars = floor($score/0.1); // 1 point per half star
			$output = "";
			$star_count = 0;
			while($stars > 0){
				if($stars >= 2){
					$stars -= 2;
					$star_count++;
					$output .= $star_fill;
				}
				else{
					$stars -= 1;
					$star_count++;
					$output .= $star_half;
				}
			}
			while($star_count < 5){
				$output .= $star_empty;
				$star_count++;
			}
			echo $output;
		}
			
		 ?>
		</div>
		<div class="row mt-2">
			<a href="index.php"><button class="btn btn-primary">返回</button></a>
		</div>



		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</div>

</body>
</html>