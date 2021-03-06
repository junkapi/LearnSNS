<?php

  session_start();

  require('dbconnect.php');
  require('function.php');


  //require
  //require_once

  //include
  //include_once


  //エラーが発生した場合の処理
  //requireはエラーが発生した場合は致命的なエラーで処理を止める
  //includeはエラーが発生した場合に警告だけ表示して処理を実行する

  //既に読み込まれていた場合に改めて読み込むかどうか
  //require→一回読み込んでいても改めて読み込む
  //require_once→一回読み込まれてたら改めて読み込むことはしない


// 特に変える必要がないから定数を定義
// 定数は分かりやすいように大文字にする
  const CONTENT_PER_PAGE = 5;

  $signin_user = get_user($dbh, $_SESSION['id']);

  // create_feed($dbh, $feed, $signin_user['id']);

  // $rec["like_cnt"] = $like["like_cnt"];

  // $rec["is_liked"] = is_liked($dbh,$signin_user['id'],$rec["id"]);

  // $last_page = get_last_page($dbh);



  if(!isset($_SESSION['id'])) {
    header('Location:signup.php');
    exit();
  }


  $errors = array();



// 何ページ目を開いているか取得する
  if (isset($_GET['page'])) {
    $page = $_GET['page'];

  } else {
    $page = 1;

  }


// ユーザーが投稿ボタンを押したら発動
  if (!empty($_POST)) {
      $feed = $_POST['feed'];
    

    if ($feed !='') {

      // $sql = 'INSERT INTO `feeds` SET `feed`=?, `user_id`=?, `created`=NOW()';
      // $data = array($feed, $signin_user['id']);
      // $stmt = $dbh->prepare($sql);
      // $stmt->execute($data);

      create_feed($dbh, $feed, $signin_user['id']);


      header('Location: timeline.php');
      exit();


    }else {

      $errors['feed'] = 'blank';
    }



    }



    // -1などのページ数として不正な値を渡された場合の対策
    $page = max($page, 1);


    // ヒットしたレコードの数を取得するSQL
    // $sql_count = "SELECT COUNT(*) AS `cnt` FROM `feeds`";
    // $stmt_count = $dbh->prepare($sql_count);
    // $stmt_count->execute();

    // $record_cnt = $stmt_count->fetch(PDO::FETCH_ASSOC);


    // // 取得したページ数を１ページあたりに表示する件数で割って何ページが最後になるか取得
    // $last_page = ceil($record_cnt['cnt']/CONTENT_PER_PAGE);

    $last_page = get_last_page($dbh);


    // 最後のページより大きい値を渡された場合の対策
    $page = min($page, $last_page);


    $start = ($page - 1)*CONTENT_PER_PAGE;



    if (isset($_GET['search_word'])) {
      $sql = 'SELECT `f`.*, `u`.`name`, `u`.`img_name` FROM `feeds` AS `f` LEFT JOIN `users` AS `u` ON `f`.`user_id`=`u`.`id` WHERE f.feed LIKE "%"? "%" ORDER BY `created` DESC LIMIT '. CONTENT_PER_PAGE .' OFFSET ' . $start;
        $data = [$_GET['search_word']];
    
    } else {
        // LEFT JOINで全件取得
    $sql = 'SELECT `feeds`.*, `users`.`name`, `users`.`img_name` FROM `feeds` RIGHT JOIN `users` ON `feeds`.`user_id` = `users`.id ORDER BY `created` DESC LIMIT '. CONTENT_PER_PAGE .' OFFSET ' . $start;
    $data = [];
    }

    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);


    $feeds = array();
    while (1) {
    // データを１件ずつ取得
        $rec = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($rec == false) {
           break;
        }


        //いいね済みかどうかの確認
        // $like_flg_sql = "SELECT * FROM `likes` WHERE `user_id` = ? AND `feed_id` = ?";

        // $like_flg_data = [$signin_user['id'], $rec["id"]];

        // $like_flg_stmt = $dbh->prepare($like_flg_sql);
        // $like_flg_stmt->execute($like_flg_data);

        // $is_liked = $like_flg_stmt->fetch(PDO::FETCH_ASSOC);

        // //三項演算子　条件式　? trueだった場合 : falseだった場合
        // $rec["is_liked"] = $is_liked ? true : false;

        //↑と同じ意味
        // if ($is_liked) {
        //   $rec["is_liked"] = true
        // } else {
        //   $rec["is_liked"] = false
        // }

        $rec["is_liked"] = is_liked($dbh,$signin_user['id'],$rec["id"]);



        // //何件いいねされているか確認
        // $like_sql = "SELECT COUNT(*) AS `like_cnt` FROM `likes` WHERE `feed_id` = ?";

        // $like_data = [$rec["id"]];

        // $like_stmt = $dbh->prepare($like_sql);
        // $like_stmt->execute($like_data);

        // $like = $like_stmt->fetch(PDO::FETCH_ASSOC);

        // $rec["like_cnt"] = $like["like_cnt"];

        $rec["like_cnt"] = count_like($dbh, $rec["id"]);



        // 投稿したコメントを取得する
        $rec["comments"] = get_comment($dbh, $rec["id"]);


        // コメント数を取得
        $rec["comment_cnt"] = count_comment($dbh, $rec["id"]);



         $feeds[] = $rec;
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
    <div class="row">
      <div class="col-xs-3">
        <ul class="nav nav-pills nav-stacked">
          <li class="active"><a href="timeline.php?feed_select=news">新着順</a></li>
          <li><a href="timeline.php?feed_select=likes">いいね！済み</a></li>
          <!-- <li><a href="timeline.php?feed_select=follows">フォロー</a></li> -->
        </ul>
      </div>
      <div class="col-xs-9">
        <div class="feed_form thumbnail">
          <form method="POST" action="">
            <div class="form-group">
              <textarea name="feed" class="form-control" rows="3" placeholder="Happy Hacking!" style="font-size: 24px;"></textarea><br>
              <?php if (isset($errors['feed']) && ($errors['feed']) == 'blank'): ?>
                  <p class="alert alert-danger">投稿データを入力してください</p>
                <?php endif; ?>
            </div>
            <input type="submit" value="投稿する" class="btn btn-primary">
          </form>
        </div>

        <?php  foreach ($feeds as $fed): ?>
          <div class="thumbnail">
            <div class="row">
              <div class="col-xs-1">
                <img src="user_profile_img/<?php echo $fed['img_name']; ?>" width="40">
              </div>
              <div class="col-xs-11">
                <?php echo $fed['name']; ?><br>
                <a href="#" style="color: #7F7F7F;"><?php echo $fed['created']; ?></a>
              </div>
            </div>
            <div class="row feed_content">
              <div class="col-xs-12" >
                <span style="font-size: 24px;"><</span><?php echo $fed['feed']; ?>
              </div>
            </div>
            <div class="row feed_sub">
              <div class="col-xs-12">
                <?php if ($fed['is_liked']): ?>
                  <button class="btn btn-default btn-xs js-unlike">
                    <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                    <span>いいねを取り消す</span>
                  </button>
                <?php else: ?>
                <button class="btn btn-default btn-xs js-like">
                    <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                    <span>いいね！</span>
                </button>
                <?php endif; ?>
                <span>いいね数 : </span>
                <span class="like_count"><?= $fed['like_cnt'] ?></span> 
                <a href="#collapseComment<?= $fed["id"] ?>" data-toggle="collapse" aria-expanded="false">
                  <span>コメントする</span>
                </a>
                <span class="comment_count">コメント数 : <?= $fed["comment_cnt"] ?></span>
                <?php if($fed["user_id"]==$_SESSION["id"]): ?>
                  <a href="edit.php?feed_id=<?php echo $fed["id"] ?>" class="btn btn-success btn-xs">編集</a>
                  <a onclick="return confilm('ほんとに消すの？');" href="delete.php?feed_id=<?php echo $fed["id"] ?>" class="btn btn-danger btn-xs">削除</a>
                <?php endif; ?>
              </div>
              <?php include("comment_view.php"); ?>
            </div>
          </div>
          <?php endforeach; ?>


        <div aria-label="Page navigation">
          <ul class="pager">
            <?php if($page == 1): ?>
            <li class="previous disabled"><a><span aria-hidden="true">&larr;</span></a></li>
            <?php else: ?>
            <li class="previous"><a href="timeline.php?page=<?php echo $page- 1;?>"><span aria-hidden="true">&larr;</span> Newer</a></li>
            <?php endif; ?>

            <?php if($page == $last_page): ?>
            <li class="next disabled"><a>Older <span aria-hidden="true">&rarr;</span></a></li>
            <?php else: ?>
            <li class="next"><a href="timeline.php?page=<?php echo $page +1;?>">Older <span aria-hidden="true">&rarr;</span></a></li>
          <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <script src="assets/js/jquery-3.1.1.js"></script>
  <script src="assets/js/jquery-migrate-1.4.1.js"></script>
  <script src="assets/js/bootstrap.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>
