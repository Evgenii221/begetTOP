<?php
// logout.php
session_start(); // начинаем сессию

// Очищаем все данные сессии
$_SESSION = [];

// Удаляем сессионные куки, если они используются
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),      // имя куки сессии
        '',                  // пустое значение
        time() - 42000,      // истекшее время
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

// Разрушаем сессию на сервере
session_destroy();

// Редирект на страницу входа
header('Location: login.php');
exit;
?>
