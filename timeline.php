<?php

  session_start();

  require('dbconnect.php');


// 特に変える必要がないから定数を定義
// 定数は分かりやすいように大文字にする
  const CONTENT_PER_PAGE = 5;



  if(!isset($_SESSION['id'])) {
    header('Location:signup.php');
    exit();
  }


  $errors = array();


  $sql = 'SELECT * FROM  `users` WHERE `id` = ?';
  $data = array($_SESSION['id']);
  $stmt = $dbh->prepare($sql);
  $stmt->execute($data);

  $signin_user = $stmt->fetch(PDO::FETCH_ASSOC);


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

      $sql = 'INSERT INTO `feeds` SET `feed`=?, `user_id`=?, `created`=NOW()';
      $data = array($feed, $signin_user['id']);
      $stmt = $dbh->prepare($sql);
      $stmt->execute($data);


      header('Location: timeline.php');
      exit();


    }else {

      $errors['feed'] = 'blank';
    }



    }



    // -1などのページ数として不正な値を渡された場合の対策
    $page = max($page, 1);


    // ヒットしたレコードの数を取得するSQL
    $sql_count = "SELECT COUNT(*) AS `cnt` FROM `feeds`";
    $stmt_count = $dbh->prepare($sql_count);
    $stmt_count->execute();

    $record_cnt = $stmt_count->fetch(PDO::FETCH_ASSOC);


    // 取得したページ数を１ページあたりに表示する件数で割って何ページが最後になるか取得
    $last_page = ceil($record_cnt['cnt']/CONTENT_PER_PAGE);


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
        $like_flg_sql = "SELECT * FROM `likes` WHERE `user_id` = ? AND `feed_id` = ?";

        $like_flg_data = [$signin_user['id'], $rec["id"]];

        $like_flg_stmt = $dbh->prepare($like_flg_sql);
        $like_flg_stmt->execute($like_flg_data);

        $is_liked = $like_flg_stmt->fetch(PDO::FETCH_ASSOC);

        //三項演算子　条件式　? trueだった場合 : falseだった場合
        $rec["is_liked"] = $is_liked ? true : false;

        //↑と同じ意味
        // if ($is_liked) {
        //   $rec["is_liked"] = true
        // } else {
        //   $rec["is_liked"] = false
        // }


        //何件いいねざれているか確認
        $like_sql = "SELECT COUNT(*) AS `like_cnt` FROM `likes` WHERE `feed_id` = ?";

        $like_data = [$rec["id"]];

        $like_stmt = $dbh->prepare($like_sql);
        $like_stmt->execute($like_data);

        $like = $like_stmt->fetch(PDO::FETCH_ASSOC);

        $rec["like_cnt"] = $like["like_cnt"];



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
  <div class="navbar navbar-default navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse1" aria-expanded="false">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">Learn SNS</a>
      </div>
      <div class="collapse navbar-collapse" id="navbar-collapse1">
        <ul class="nav navbar-nav">
          <li class="active"><a href="#">タイムライン</a></li>
          <li><a href="#">ユーザー一覧</a></li>
        </ul>
        <form method="GET" action="" class="navbar-form navbar-left" role="search">
          <div class="form-group">
            <input type="text" name="search_word" class="form-control" placeholder="投稿を検索">
          </div>
          <button type="submit" class="btn btn-default">検索</button>
        </form>
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
            <span hidden id="signin-user"><?php echo $signin_user['id']; ?></span>
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src="user_profile_img/<?php echo $signin_user['img_name']; ?>" width="18" class="img-circle"><?php echo $signin_user['name']; ?><span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="#">マイページ</a></li>
              <li><a href="signout.php">サインアウト</a></li>
            </ul>
          </li>
        </ul>
      </div>
   </div>
  </div>

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
                <span class="comment_count">コメント数 : 9</span>
                <?php if($fed["user_id"]==$_SESSION["id"]): ?>
                  <a href="edit.php?feed_id=<?php echo $fed["id"] ?>" class="btn btn-success btn-xs">編集</a>
                  <a onclick="return confilm('ほんとに消すの？');" href="delete.php?feed_id=<?php echo $fed["id"] ?>" class="btn btn-danger btn-xs">削除</a>
                <?php endif; ?>
              </div>
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
