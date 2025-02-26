<?php
session_start();
include 'includes/functions.php';
if (isLoggedIn()) {
    header('Location: index.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $captcha = $_POST['captcha'];

    // 验证验证码
    if ($captcha !== $_SESSION['captcha']) {
        $error = '验证码输入错误，请重新输入。';
    } elseif ($password !== $confirmPassword) {
        $error = '两次输入的密码不一致，请重新输入。';
    } else {
        if (registerUser($username, $password)) {
            header('Location: login.php');
        } else {
            $error = '注册失败，请重试。';
        }
    }
}
$page_title = '用户注册';
include 'includes/header.php';
?>
    <h2 class="page-title">用户注册</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="username" class="form-label">用户名</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">密码</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">确认密码</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <div class="mb-3">
            <label for="captcha" class="form-label">验证码</label>
            <div class="input-group">
                <input type="text" class="form-control" id="captcha" name="captcha" required>
                <img src="captcha.php" alt="验证码" onclick="this.src='captcha.php?'+Math.random()">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">注册</button>
    </form>
<?php
include 'includes/footer.php';
?>