<?php
session_start();
require 'config/db.php';

if(!isset($_GET['id'])){
    die("Пользователь не найден");
}

$user_id = (int)$_GET['id'];

/* ЛАЙК ПОСТА */

if(isset($_POST['like_post'])){
    $post_id = (int)$_POST['post_id'];

    $pdo->prepare("UPDATE posts SET likes = likes + 1 WHERE id=?")
        ->execute([$post_id]);

    $stmt = $pdo->prepare("SELECT likes FROM posts WHERE id=?");
    $stmt->execute([$post_id]);
    $likes = $stmt->fetchColumn();

    echo $likes;
    exit;
}

/* ДАННЫЕ ПОЛЬЗОВАТЕЛЯ */

$stmt = $pdo->prepare("
SELECT id, username, avatar, banner
FROM users
WHERE id=?
");

$stmt->execute([$user_id]);
$user = $stmt->fetch();

if(!$user){
    die("Пользователь не найден");
}

/* ПОСТЫ АВТОРА */

$stmt = $pdo->prepare("
SELECT *
FROM posts
WHERE user_id=? AND status='approved'
ORDER BY created_at DESC
");

$stmt->execute([$user_id]);
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars($user['username']) ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>

body{
font-family:'Inter',sans-serif;
background:#f5f7fb;
}

/* BACK BUTTON */

.back-btn{
margin-bottom:20px;
}

/* PROFILE */

.profile-card{
border:none;
border-radius:18px;
overflow:hidden;
box-shadow:0 15px 40px rgba(0,0,0,0.08);
margin-bottom:40px;
}

.profile-banner{
height:300px;
background-size:cover;
background-position:center;
}

.avatar-wrapper{
margin-top:-70px;
}

.avatar{
width:150px;
height:150px;
border-radius:50%;
border:6px solid white;
object-fit:cover;
box-shadow:0 10px 25px rgba(0,0,0,0.25);
}

.username{
font-size:26px;
font-weight:700;
margin-top:10px;
}

/* POSTS */

.post-card{
border:none;
border-radius:14px;
overflow:hidden;
box-shadow:0 8px 25px rgba(0,0,0,0.06);
transition:0.25s;
margin-bottom:25px;
}

.post-card:hover{
transform:translateY(-4px);
box-shadow:0 15px 35px rgba(0,0,0,0.1);
}

.post-card img{
height:260px;
object-fit:cover;
}

.post-title{
font-size:20px;
font-weight:700;
text-decoration:none;
color:#111;
}

.post-title:hover{
color:#0d6efd;
}

.post-text{
color:#555;
line-height:1.6;
}

.post-stats{
margin-top:10px;
font-size:14px;
color:#777;
display:flex;
gap:15px;
align-items:center;
}

.like-btn{
border:none;
background:#0d6efd;
color:white;
padding:5px 12px;
border-radius:8px;
font-size:14px;
cursor:pointer;
}

.like-btn:hover{
background:#0b5ed7;
}

</style>

</head>

<body>

<div class="container py-5" style="max-width:900px">

<!-- НАЗАД -->

<a href="blog.php" class="btn btn-dark back-btn">
← Назад к блогу
</a>

<!-- ПРОФИЛЬ -->

<div class="card profile-card">

<div class="profile-banner"
style="background-image:url('<?= !empty($user['banner']) ? htmlspecialchars($user['banner']) : 'https://picsum.photos/1200/400' ?>');">
</div>

<div class="card-body text-center">

<div class="avatar-wrapper">

<img class="avatar"
src="<?= !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'https://i.pravatar.cc/300' ?>">

</div>

<div class="username">
<?= htmlspecialchars($user['username']) ?>
</div>

</div>

</div>

<!-- ПОСТЫ -->

<h3 class="mb-4">Посты автора</h3>

<?php if($posts): ?>

<?php foreach($posts as $post): ?>

<div class="card post-card">

<?php if($post['image']): ?>

<img src="uploads/<?= htmlspecialchars($post['image']) ?>" class="card-img-top">

<?php endif; ?>

<div class="card-body">

<a class="post-title" href="posts.php?id=<?= $post['id'] ?>">
<?= htmlspecialchars($post['title']) ?>
</a>

<p class="post-text mt-2">
<?= mb_substr(strip_tags($post['content']),0,200) ?>...
</p>

<div class="post-stats">

<span>
👁 <?= $post['views'] ?> просмотров
</span>

<span id="likes<?= $post['id'] ?>">
❤️ <?= $post['likes'] ?>
</span>

<button class="like-btn" onclick="likePost(<?= $post['id'] ?>)">
Лайк
</button>

</div>

</div>

</div>

<?php endforeach; ?>

<?php else: ?>

<p class="text-muted">
У пользователя пока нет постов
</p>

<?php endif; ?>

</div>

<script>

function likePost(postId){

fetch("",{
method:"POST",
headers:{
'Content-Type':'application/x-www-form-urlencoded'
},
body:"like_post=1&post_id="+postId
})
.then(res=>res.text())
.then(data=>{
document.getElementById("likes"+postId).innerHTML="❤️ "+data
})

}

</script>

</body>
</html>