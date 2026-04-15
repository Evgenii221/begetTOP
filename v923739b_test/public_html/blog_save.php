<?php
session_start();
require 'config/db.php';

$title = htmlspecialchars($_POST['title']);
$content = htmlspecialchars($_POST['content']);
$user = $_SESSION['user_id'];

$image = null;

if(!empty($_FILES['image']['name'])){
$name = time().$_FILES['image']['name'];
move_uploaded_file($_FILES['image']['tmp_name'],"uploads/".$name);
$image = $name;
}

$stmt=$pdo->prepare("
INSERT INTO posts(user_id,title,content,image,status)
VALUES(?,?,?,?, 'pending')
");

$stmt->execute([$user,$title,$content,$image]);

header("Location: blog.php");