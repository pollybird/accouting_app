<?php
include 'includes/functions.php';
if (!isLoggedIn()) {
    header('Location: login.php');
}
include 'includes/db_connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $user_id = $_SESSION['user_id'];
    $query = $db->prepare('INSERT INTO ledgers (user_id, name) VALUES (:user_id, :name)');
    $query->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $query->bindValue(':name', $name, SQLITE3_TEXT);
    if ($query->execute()) {
        header('Location: index.php');
    } else {
        $error = '添加账本失败，请重试。';
    }
}
$page_title = '添加账本';
include 'includes/header.php';
?>
    <h2 class="page-title">添加账本</h2>
<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="name">账本名称</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <button type="submit" class="btn btn-primary">添加</button>
    </form>
<?php
include 'includes/footer.php';
?>