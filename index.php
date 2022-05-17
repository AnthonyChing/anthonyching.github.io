<?php 
    session_start();
    require_once 'connect.php';
    //$sql = "SELECT * FROM ingredients ORDER BY Category DESC";
    if(isset($_GET['selected'])){
        if($_GET['selected'] == 'false'){
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                  No ingredients selected! Please select at least one ingredient.
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        }
    }
    else if(isset($_GET['action'])){
        if($_GET['action']=='success'){
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                  輸入成功!
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        }
    }

 ?>
<html>
<head>
    <title>Home</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
 </head>
<body>
    <div class="container">
        <div class="mt-2"><h3>紀錄食物：</h3></div>
        <form action = "index.php" method="post">
            <div class="row">
                <div class="col-6">
                    <select class="form-select form-select-m" aria-label=".form-select-lg example" name="method">
                        <?php 
                            if(isset($_POST['ingredient_id'])){
                                $selected = ['','',''];
                                $selected[$_POST['method']] = "selected";
                                echo '
                                    <option value '.$selected[0].'= "0">放入食物</option>
                                    <option value '.$selected[1].' ="1">拿出食物</option>
                                    <option value '.$selected[2].' ="2">修改數量/保存期限</option>
                                ';
                            }
                            else{
                                echo '
                                    <option selected value = "0">放入食物</option>
                                    <option value="1">拿出食物</option>
                                    <option value="2">修改數量/保存期限</option>
                                ';
                            }
                         ?>
                    </select>
                </div>
                <div class="col-6">
                    <select class="form-select form-select-m mb-1" aria-label=".form-select-lg example" name = "ingredient_id">
                        <?php 
                            $sql = "SELECT * FROM ingredients ORDER BY Category DESC";
                            $result=$db->prepare($sql);
                            $result->execute();
                            $row=$result->fetchAll(PDO::FETCH_ASSOC);
                            $category = "";
                            for($i = 0; $i < $result->rowcount(); $i++){
                                if($row[$i]['Seasoning'] != 1){
                                    if($row[$i]['Category'] != $category){
                                        echo '<optgroup label="'.$row[$i]['Category'].'">';
                                        $category = $row[$i]['Category'];
                                    }
                                    if(isset($_POST['ingredient_id'])){
                                        if($row[$i]['Ingredient_id'] == $_POST['ingredient_id']){
                                            echo '<option selected value = "'.$row[$i]['Ingredient_id'].'">'.$row[$i]['Ingredient_name'].'</option>';
                                        }
                                        else{
                                            echo '<option value = "'.$row[$i]['Ingredient_id'].'">'.$row[$i]['Ingredient_name'].'</option>';
                                        }
                                    }
                                    else{
                                        echo '<option value = "'.$row[$i]['Ingredient_id'].'">'.$row[$i]['Ingredient_name'].'</option>';
                                    }
                                    if($row[$i]['Category'] != $category){
                                        echo '</optgroup>';
                                    }
                                }
                            }
                         ?>
                    </select>
                </div>
            </div>
            <?php 
                if(isset($_POST['ingredient_id'])){
                    echo '<button type="submit" class="btn btn-primary">重新選擇</button>';
                }
                else{
                    echo '<button type="submit" class="btn btn-primary">輸入數量</button>';
                }
             ?>
        </form>
        <?php 
            date_default_timezone_set('Asia/Taipei');
            $date = date('Y-m-d', time());
            $time = date('h:i:s', time());
            $datetime = $date . 'T' . $time;
            if(isset($_POST['ingredient_id'])){
                //action_type
                if($_POST['method'] == 0){
                    $_SESSION['action_type'] = 'put_in';
                    $Measurement = "";
                    foreach ($row as $key => $value) {
                        if($_POST['ingredient_id'] == $value['Ingredient_id'])
                        $Measurement = $value['Measurement'];
                    }
                    echo '
                          <form action = "action.php" method="post">
                              <div class="input-group mb-3">
                                <input type = "input-group-text" class="form-control" name="quantity">
                                <span class="input-group-text">'.$Measurement.'</span>
                              </div>
                              <div class="input-group mb-1">
                                <span class="input-group-text">輸入食用期限到期日：</span>
                                <input type="date" class = "form-control" name="expiration_date" value="'.$date.'">
                              </div>
                              <div class="input-group mb-3">
                                <span class="input-group-text">或輸入多久後到期：</span>
                                <input type="text" class = "form-control" name="timespan_value" value="">
                                <select class="form-select" name = "timespan_measurement">
                                    <option selected value="day">日</option>
                                    <option value="week">周</option>
                                    <option value="month">月</option>
                                    <option value="year">年</option>
                                </select>
                              </div>

                              <button type="submit" class="btn btn-primary">確認</button>
                          </form>
                    ';
                }
                else if($_POST['method'] == 1){
                    $_SESSION['action_type'] = 'take_out';
                    $Measurement = "";
                    foreach ($row as $key => $value) {
                        if($_POST['ingredient_id'] == $value['Ingredient_id'])
                        $Measurement = $value['Measurement'];
                    }
                    echo '
                          <form action = "action.php" method="post">
                              <div class="input-group mb-3">
                                <input type = "input-group-text" class="form-control" name="quantity">
                                <span class="input-group-text">'.$Measurement.'</span>
                              </div>
                              <button type="submit" class="btn btn-primary">確認</button>
                          </form>
                        ';
                }
                else{
                    $_SESSION['action_type'] = 'modify';
                }
                //fridge_ingredient_id
                $_SESSION['fridge_ingredient_id'] = $_POST['ingredient_id'];
                
            }
            //echo 'Next Week: '. date('Y-m-d', strtotime('+10 week')) ."\n";
         ?>
        <h3>
            冰箱裡現在有：
        </h3>
        <?php 
            $remind_days_ahead = 2;
            $ingredients_expired = [];
            $sql = "SELECT * FROM fridge_supply ORDER BY Expiration_date ASC";
            $result=$db->prepare($sql);
            $result->execute();
            $row=$result->fetchAll(PDO::FETCH_ASSOC);
         ?>
        <div class="row">
            <div class="alert alert-danger" role="alert">
            已經過期了>_< 
            <br>
            <?php
                foreach ($row as $key => $value) {
                    $date_today = new DateTime($date);
                    $expiration_date = new DateTime($value['Expiration_date']);
                    $interval = date_diff($date_today, $expiration_date);
                    if($interval->format('%R%a') < 0){
                        echo $value['Ingredient_name'] . $value['Quantity'] . $value['Measurement'] . ' ';
                        echo $interval->format('%a') . '天前過期<br>';
                    }
                }
             ?>
            </div>
        </div>
        <div class="row">
            <div class="alert alert-warning" role="alert">
            今天要過期了!
            <br>
            <?php
                foreach ($row as $key => $value) {
                    $date_today = new DateTime($date);
                    $expiration_date = new DateTime($value['Expiration_date']);
                    $interval = date_diff($date_today, $expiration_date);
                    if($interval->format('%R%a') == 0){
                        echo $value['Ingredient_name'] . $value['Quantity'] . $value['Measurement'] . ' <br>';
                    }
                }
             ?>
            </div>
        </div>
        <div class="row">
            <div class="alert alert-success" role="alert">
            還在食用期限內
            <br>
            <?php
                foreach ($row as $key => $value) {
                    $date_today = new DateTime($date);
                    $expiration_date = new DateTime($value['Expiration_date']);
                    $interval = date_diff($date_today, $expiration_date);
                    if($interval->format('%R%a') > 0){
                        echo $value['Ingredient_name'] . $value['Quantity'] . $value['Measurement'] . ' ';
                        echo $interval->format('%a') . '天後過期<br>';
                    }
                }
             ?>
            </div>
        </div>

        <h3>推薦食譜：</h3>
        <div class="row mb-2">
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
        <?php 
            require_once('recipes_available.php');
         ?>
        <h3 class="mb-1">搜尋食譜包含：</h3>
        <div class="row">
            <form action="quantity.php" method="post">
                <?php 
                    $sql = "SELECT * FROM ingredients ORDER BY Category DESC";
                    $result=$db->prepare($sql);
                    $result->execute();
                    $row=$result->fetchAll(PDO::FETCH_ASSOC);
                    $category = "";
                    for($i = 0; $i < $result->rowcount(); $i++){
                        if($row[$i]['Seasoning'] != 1){
                            if($row[$i]['Category'] != $category && $category != ""){
                                echo '<br>';
                            }
                            if($row[$i]['Category'] != $category){
                                echo '<h5 class = "mb-0">' . $row[$i]['Category'].'</h5>';
                                $category = $row[$i]['Category'];
                            }
                            echo '<div class="form-check form-check-inline">';
                            echo '<input class="form-check-input" type="checkbox" value="' . $row[$i]['Ingredient_id'] . '" name = "ing_query[]">';
                            echo '<label class="form-check-label">';
                            echo $row[$i]['Ingredient_name'];
                            echo '</label>';
                            echo '</div>';
                        }
                    }
                 ?>
                <div class="form-check form-switch mb-3 mt-1">
                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" name = "advanced_search" checked>
                    <label class="form-check-label" for="flexSwitchCheckDefault">是否輸入食材的重量</label>
                </div>
                <button type="submit" class="btn btn-primary">確認</button>
            </form>
        </div>
        <!--
        <a href="1.php"><button class="btn btn-primary">test link</button></a>
        -->
    </div>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</body>
</html>