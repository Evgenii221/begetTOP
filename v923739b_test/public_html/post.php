<?php
session_start();
require 'config/db.php';

$id = $_GET['id'];

$pdo->prepare("UPDATE posts SET views = views + 1 WHERE id=?")->execute([$id]);

$stmt = $pdo->prepare("
SELECT posts.*, users.username
FROM posts
LEFT JOIN users ON posts.user_id = users.id
WHERE posts.id=?
");

$stmt->execute([$id]);
$post = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="ru">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars($post['title']) ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>

body{
font-family:'Inter',sans-serif;
background:#f5f7fb;
}

/* контейнер */

.blog-container{
max-width:800px;
margin:auto;
}

/* карточка поста */

.post-card{
background:white;
border-radius:14px;
padding:30px;
box-shadow:0 10px 30px rgba(0,0,0,0.08);
margin-bottom:30px;
}

/* заголовок */

.post-title{
font-size:32px;
font-weight:700;
margin-bottom:10px;
}

/* автор */

.post-meta{
color:#6b7280;
font-size:14px;
margin-bottom:20px;
}

/* текст */

.post-content{
font-size:17px;
line-height:1.7;
}

/* комментарии */

.comment-card{
background:white;
border-radius:12px;
padding:15px;
margin-bottom:12px;
box-shadow:0 4px 15px rgba(0,0,0,0.05);
}

/* имя */

.comment-author{
font-weight:600;
margin-bottom:5px;
}

/* textarea */

textarea{
border-radius:10px!important;
}

/* кнопка */

.btn{
border-radius:10px;
font-weight:600;
}

/* блок комментариев */

.comments-title{
font-size:22px;
font-weight:700;
margin-bottom:20px;
}

</style>

</head>

<body>

<div class="container py-5 blog-container">

<!-- ПОСТ -->

<div class="post-card">

<div class="post-title">
<?= htmlspecialchars($post['title']) ?>
</div>

<div class="post-meta">
Автор: <b><?= htmlspecialchars($post['username']) ?></b> • Просмотров: <?= $post['views'] ?>
</div>

<div class="post-content">
<?= nl2br(htmlspecialchars($post['content'])) ?>
</div>

</div>


<!-- КОММЕНТАРИИ -->

<div class="post-card">

<div class="comments-title">
Комментарии
</div>

<?php

$stmt = $pdo->prepare("
SELECT comments.*, users.username
FROM comments
LEFT JOIN users ON comments.user_id = users.id
WHERE comments.post_id=?
ORDER BY comments.created_at DESC
");

$stmt->execute([$id]);
$comments = $stmt->fetchAll();

?>

<?php if($comments): ?>

<?php foreach($comments as $comment): ?>

<div class="comment-card">

<div class="comment-author">
<?= htmlspecialchars($comment['username']) ?>
</div>

<div>
<?= nl2br(htmlspecialchars($comment['content'])) ?>
</div>

</div>

<?php endforeach; ?>

<?php else: ?>

<p class="text-muted">
Комментариев пока нет.
</p>

<?php endif; ?>


<?php if(isset($_SESSION['user_id'])): ?>

<hr class="my-4">

<form method="POST" action="add_comment.php">

<input type="hidden" name="post_id" value="<?= $id ?>">

<textarea 
name="content"
class="form-control mb-3"
rows="4"
placeholder="Напишите комментарий..."
required
></textarea>

<button class="btn btn-primary">
Отправить комментарий
</button>

</form>

<?php else: ?>

<p class="text-muted">
Войдите в аккаунт чтобы оставить комментарий.
</p>

<?php endif; ?>

</div>

</div>

</body>
</html>