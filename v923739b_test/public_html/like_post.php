<?php
session_start();
require 'config/db.php';

$post=$_GET['id'];
$user=$_SESSION['user_id'];

$stmt=$pdo->prepare("
SELECT id FROM likes WHERE post_id=? AND user_id=?
");

$stmt->execute([$post,$user]);

if(!$stmt->fetch()){

$pdo->prepare("
INSERT INTO likes(post_id,user_id)
VALUES(?,?)
")->execute([$post,$user]);

}

header("Location: blog.php");