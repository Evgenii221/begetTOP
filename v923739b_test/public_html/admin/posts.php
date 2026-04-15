<?php
session_start();
require '../config/db.php';

$stmt = $pdo->query("
SELECT posts.*, users.username
FROM posts
LEFT JOIN users ON posts.user_id = users.id
ORDER BY posts.created_at DESC
");

$posts = $stmt->fetchAll();
?>

<?php include "sidebar.php"; ?>

<div class="content">

<h1>Посты</h1>

<table width="100%" class="card">

<tr>
<th>ID</th>
<th>Название</th>
<th>Автор</th>
<th>Статус</th>
<th>Действия</th>
</tr>

<?php foreach($posts as $post): ?>

<tr>

<td><?= $post['id'] ?></td>

<td><?= htmlspecialchars($post['title']) ?></td>

<td><?= htmlspecialchars($post['username']) ?></td>

<td><?= $post['status'] ?></td>

<td>

<?php if($post['status']=="pending"): ?>

<a href="approve_post.php?id=<?= $post['id'] ?>">✔</a>

<a href="reject_post.php?id=<?= $post['id'] ?>">❌</a>

<?php endif; ?>

<a href="edit_post.php?id=<?= $post['id'] ?>">✏</a>

<a href="delete_post.php?id=<?= $post['id'] ?>">🗑</a>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>