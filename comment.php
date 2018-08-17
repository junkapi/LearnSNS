<?php


    session_start();
    require("dbconnect.php");


    $user_id = $_SESSION["id"];
    $comment = $_POST["write_comment"];
    $feed_id = $_POST["feed_id"];


    $sql = "INSERT INTO `comments` (`comment`, `user_id`, `feed_id`, `created`) VALUES (?,?,?,now());";
    $data = [$comment, $user_id, $feed_id];
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    header("Location: timeline.php");
    exit();