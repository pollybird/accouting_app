<?php
include 'includes/functions.php';
if (!isLoggedIn()) {
    header('Location: login.php');
}
include 'includes/db_connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];
    $query = $db->prepare('SELECT password FROM users WHERE id = :user_id');
    $query->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $result = $query->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    if (sha1($old_password) === $user['password']) {
        if ($new_password === $confirm_password) {
            $hashed_password = sha1($new_password);
            $query = $db->prepare('UPDATE users SET password = :password WHERE id = :user_id');
            $query->bindValue(':password', $hashed_password, SQLITE3_TEXT);
            $query->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
            if ($query->execute()) {
                $success = '密码修改成功。';
            } else {
                $error = '密码修改失败，请重试。';
            }
        } else {
            $error = '两次输入的新密码不一致。';
        }
    } else {
        $error = '旧密码错误。';
    }
}
$page_title = '修改密码';
include 'includes/header.php';
?>
    <h2 class="page-title">修改密码</h2>
<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="old_password">旧密码</label>
            <input type="password" class="form-control" id="old_password" name="old_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">新密码</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">确认新密码</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary">修改密码</button>
    </form>
<?php
include 'includes/footer.php';
?>