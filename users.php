<?php
    
    session_start();

    require('dbconnect.php');
    require('function.php');


    // サインインしている人の情報を取得
    $signin_user = get_user($dbh, $_SESSION["id"]);

    $sql = 'SELECT * FROM `users`';
    $stmt = $dbh->prepare($sql);
    $stmt->execute();


    $users = [];
    while (true) {
        $rec = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($rec == false) {
            break;
        }

        $rec["feed_cnt"] = count_feed($dbh, $rec["id"]);

        $users[] = $rec;
    }

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Learn SNS</title>
  <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="assets/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body style="margin-top: 60px; background: #E4E6EB;">
  <?php include('navbar.php'); ?>
    <div class="container">
      <?php foreach ($users as $user): ?>
        <div class="row">
            <div class="col-xs-12">
                <div class="thumbnail">
                    <div class="row">
                        <div class="col-xs-1">
                            <img src="user_profile_img/<?php echo $user["img_name"]; ?>" width="80">
                        </div>
                        <div class="col-xs-11">
                            名前 <?php echo $user["name"]; ?><br>
                            <a href="profile.php?" style="color: #7F7F7F;"><?php echo $user["created"]; ?>からメンバー</a>
                        </div>
                    </div>

                    <div class="row feed_sub">
                        <div class="col-xs-12">
                            <span class="comment_count">つぶやき数 : <?php echo $user["feed_cnt"]; ?></span>
                        </div>
                    </div>
                </div><!-- thumbnail -->
            </div><!-- class="col-xs-12" -->
        </div><!-- class="row" -->
      <?php endforeach; ?>
    </div><!-- class="cotainer" -->
  <script src="assets/js/jquery-3.1.1.js"></script>
  <script src="assets/js/jquery-migrate-1.4.1.js"></script>
  <script src="assets/js/bootstrap.js"></script>
</body>
</html>
