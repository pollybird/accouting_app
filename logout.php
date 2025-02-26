<?php
// 开启会话
session_start();

// 删除所有会话变量
$_SESSION = array();

// 如果使用了 cookie 来存储会话 ID，删除该 cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 删除自定义的用户登录相关 cookie
if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/');
}

// 销毁会话
session_destroy();

// 重定向到登录页面
header('Location: login.php');
exit;
?>