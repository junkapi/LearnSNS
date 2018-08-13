<?php 


    session_start();

    require('dbconnect.php');
    require('function.php');

    $signin_user = get_user($dbh, $_SESSION["id"]);



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
    <?php include("navbar.php") ?>
    <div class="container">
        <div class="row">
            <div class="col-xs-3 text-center">
                <img src="user_profile_img/" class="img-thumbnail" />
                <h2>{ ユーザー名 }</h2>
                <a href="follow.php?following_id="><button class="btn btn-default btn-block">フォローする</button></a>
            </div>

            <div class="col-xs-9">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#tab1" data-toggle="tab">Followers</a>
                    </li>
                    <li>
                        <a href="#tab2" data-toggle="tab">Following</a>
                    </li>
                </ul>
                <!--タブの中身-->
                <div class="tab-content">
                    <div id="tab1" class="tab-pane fade in active">
                        <div class="thumbnail">
                            <div class="row">
                                <div class="col-xs-2">
                                    <img src="user_profile_img/" width="80">
                                </div>
                                <div class="col-xs-10">
                                    名前 { ユーザー名 }<br>
                                    <a href="#" style="color: #7F7F7F;">{ ここに日付 }からメンバー</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="tab2" class="tab-pane fade">
                        <div class="thumbnail">
                            <div class="row">
                                <div class="col-xs-2">
                                    <img src="user_profile_img/" width="80">
                                </div>
                                <div class="col-xs-10">
                                    名前 { ここにユーザー名 }<br>
                                    <a href="#" style="color: #7F7F7F;">{ ここに日付 }からメンバー</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
</body>
</html>
