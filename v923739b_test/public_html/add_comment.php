<?php
session_start();
require 'config/db.php';

if(!isset($_SESSION['user_id'])) exit;

$stmt = $pdo->prepare("
INSERT INTO comments (post_id,user_id,content)
VALUES (?,?,?)
");

$stmt->execute([
$_POST['post_id'],
$_SESSION['user_id'],
$_POST['content']
]);

header("Location: post.php?id=".$_POST['post_id']);