<?php

require '../config/db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("
UPDATE posts SET status='approved' WHERE id=?
");

$stmt->execute([$id]);

header("Location: posts.php");