<?php
session_start();
require '../config/db.php';

$stmt = $pdo->query("
SELECT comments.*, users.username, posts.title
FROM comments
LEFT JOIN users ON comments.user_id = users.id
LEFT JOIN posts ON comments.post_id = posts.id
ORDER BY comments.created_at DESC
");

$comments = $stmt->fetchAll();
?>

<?php include "sidebar.php"; ?>

<div class="content">

<h1>Комментарии</h1>

<?php foreach($comments as $comment): ?>

<div class="card" style="margin-bottom:15px">

<b><?= htmlspecialchars($comment['username']) ?></b>

<p><?= htmlspecialchars($comment['content']) ?></p>

<small>Пост: <?= htmlspecialchars($comment['title']) ?></small>

</div>

<?php endforeach; ?>

</div>