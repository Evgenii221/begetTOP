<?php
session_start();
require 'config/db.php';

/* настройки */

$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = $_GET['search'] ?? '';

$offset = ($page-1)*$limit;

/* поиск */

$where = "posts.status='approved'";
$params = [];

if($search){
$where .= " AND posts.title LIKE ?";
$params[] = "%$search%";
}

/* посты */

$sql = "
SELECT posts.*, users.username,
(SELECT COUNT(*) FROM likes WHERE likes.post_id=posts.id) as likes_count
FROM posts
LEFT JOIN users ON posts.user_id = users.id
WHERE $where
ORDER BY posts.created_at DESC
LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

/* количество */

$count_sql = "SELECT COUNT(*) FROM posts WHERE $where";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);

$total = $stmt->fetchColumn();
$pages = ceil($total/$limit);

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Блог</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6fb;
font-family:'Segoe UI',sans-serif;
}

/* header */

.blog-header{
background:linear-gradient(135deg,#667eea,#764ba2);
color:white;
padding:70px 30px;
border-radius:20px;
margin-bottom:40px;
text-align:center;
box-shadow:0 20px 50px rgba(0,0,0,0.1);
}

.blog-header h1{
font-weight:700;
font-size:40px;
}

/* search */

.search-box{
max-width:500px;
margin:auto;
}

/* card */

.post-card{
border:none;
border-radius:18px;
overflow:hidden;
background:white;
transition:0.3s;
box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

.post-card:hover{
transform:translateY(-7px);
box-shadow:0 25px 50px rgba(0,0,0,0.15);
}

.post-img{
height:220px;
width:100%;
object-fit:cover;
}

.author{
font-size:14px;
color:#777;
}

/* buttons */

.read-btn{
border-radius:30px;
padding:6px 18px;
}

.like-btn{
border-radius:30px;
padding:6px 16px;
}

.create-btn{
border-radius:30px;
padding:10px 22px;
font-weight:600;
}

/* pagination */

.pagination .page-link{
border-radius:12px;
margin:3px;
}

/* views */

.views{
font-size:13px;
color:#888;
}

</style>

</head>


<body>

<div class="container mt-4">

<div class="blog-header">

<h1>📝 Мой блог</h1>

<p>Интересные статьи и идеи</p>

<form method="GET" class="search-box mt-4">

<div class="input-group">

<input type="text"
name="search"
class="form-control"
placeholder="Поиск статьи..."
value="<?= htmlspecialchars($search) ?>">

<button class="btn btn-light">
Поиск
</button>

</div>

</form>

</div>


<?php if(isset($_SESSION['user_id'])): ?>

<a href="blog_add.php" class="btn btn-primary create-btn mb-4">
+ Создать пост
</a>

<?php endif; ?>


<div class="row">

<?php foreach($posts as $post): ?>

<div class="col-lg-4 col-md-6 mb-4">

<div class="card post-card">

<?php if($post['image']): ?>

<img src="uploads/<?= $post['image'] ?>" class="post-img">

<?php endif; ?>

<div class="card-body">

<h5 class="fw-bold">

<a href="post.php?id=<?= $post['id'] ?>" style="text-decoration:none;color:#222;">

<?= htmlspecialchars($post['title']) ?>

</a>

</h5>

<p class="author">

Автор:
<a href="author.php?id=<?= $post['user_id'] ?>">

<?= htmlspecialchars($post['username']) ?>

</a>

</p>

<p>

<?= mb_substr(strip_tags($post['content']),0,120) ?>...

</p>

<div class="d-flex justify-content-between align-items-center">

<div>

<a href="post.php?id=<?= $post['id'] ?>" class="btn btn-outline-primary btn-sm read-btn">
Читать
</a>

<a href="like_post.php?id=<?= $post['id'] ?>" class="btn btn-outline-danger btn-sm like-btn">
❤️ <?= $post['likes_count'] ?>
</a>

</div>

<div class="views">

👁 <?= $post['views'] ?? 0 ?>

</div>

</div>

</div>

</div>

</div>

<?php endforeach; ?>

</div>


<!-- pagination -->

<nav>

<ul class="pagination justify-content-center mt-4">

<?php for($i=1;$i<=$pages;$i++): ?>

<li class="page-item <?= ($i==$page)?'active':'' ?>">

<a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">

<?= $i ?>

</a>

</li>

<?php endfor; ?>

</ul>

</nav>


</div>

</body>
</html>