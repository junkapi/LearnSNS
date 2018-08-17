<?php 


    session_start();

    require('dbconnect.php');
    require('function.php');


    $signin_user = get_user($dbh, $_SESSION["id"]);

    $profile_user = get_user($dbh, $_GET['user_id']);

    $is_followed = is_followed($dbh, $profile_user["id"], $signin_user["id"]);

    $followers = get_follower($dbh, $profile_user['id']);

    $followings = get_following($dbh, $profile_user['id']);




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
                <img src="user_profile_img/<?= $profile_user['img_name'] ?>" class="img-thumbnail" />
                <h2><?php echo $profile_user["name"];?></h2>
                <?php if ($signin_user['id'] !=$profile_user['id']): ?>
                    <?php if ($is_followed): ?>
                        <a href="follow.php?following_id=<?= $profile_user["id"]; ?>&unfollow"><button class="btn btn-default btn-block">フォロー解除する</button></a>
                        <?php else: ?>
                            <a href="follow.php?following_id=<?php echo $profile_user["id"]; ?>"> <button class="btn btn-default btn-block">フォローする</button></a>
                        <?php endif; ?>
                    <?php endif; ?>
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
                        <?php foreach ($followers as $follower): ?>
                        <div class="thumbnail">
                            <div class="row">
                                <div class="col-xs-2">
                                    <img src="user_profile_img/<?= $follower['img_name'] ?>" width="80">
                                </div>
                                <div class="col-xs-10">
                                    名前 <?= $follower['name'] ?><br>
                                    <a href="profile.php?user_id=<?= $follower['id'] ?>" style="color: #7F7F7F;"><?= $follower['created'] ?>からメンバー</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>

                    <div id="tab2" class="tab-pane fade">
                        <?php foreach ($followings as $following): ?>
                        <div class="thumbnail">
                            <div class="row">
                                <div class="col-xs-2">
                                    <img src="user_profile_img/<?= $following['img_name'] ?>" width="80">
                                </div>
                                <div class="col-xs-10">
                                    名前 <?= $following['name'] ?><br>
                                    <a href="profile.php?user_id=<?= $following['id'] ?>" style="color: #7F7F7F;"><?= $following['created'] ?>からメンバー</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
