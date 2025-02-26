<?php
include 'includes/db_connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $hashed_password = sha1($password);
    $query = $db->prepare('SELECT * FROM users WHERE username = :username AND password = :password');
    $query->bindValue(':username', $username, SQLITE3_TEXT);
    $query->bindValue(':password', $hashed_password, SQLITE3_TEXT);
    $result = $query->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    if ($user) {
        session_start();
        $_SESSION['user_id'] = $user['id'];

        // 设置 Cookies，有效期为 7 天
        setcookie('user_id', $user['id'], time() + (86400 * 30), "/");

        header('Location: index.php');
    } else {
        $error = '用户名或密码错误。';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title>用户登录</title>
</head>
<body>
<div class="container">
    <h2 class="page-title">用户登录</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="username">用户名</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">密码</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">登录</button>
        <a href="register.php" class="btn btn-secondary">注册</a>
    </form>
</div>
<script src="js/bootstrap.min.js"></script>
</body>
</html>