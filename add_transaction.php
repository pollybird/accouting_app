<?php
include 'includes/functions.php';
if (!isLoggedIn()) {
    header('Location: login.php');
}
include 'includes/db_connection.php';
$ledger_id = $_GET['ledger_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    // 验证金额是否为有效的数值
    if (!is_numeric($amount)) {
        $error = '请输入有效的金额数值。';
    } else {
        $amount = floatval($amount); // 确保转换为浮点数
        $query = $db->prepare('INSERT INTO transactions (ledger_id, type, amount, date, description) VALUES (:ledger_id, :type, :amount, :date, :description)');
        $query->bindValue(':ledger_id', $ledger_id, SQLITE3_INTEGER);
        $query->bindValue(':type', $type, SQLITE3_TEXT);
        $query->bindValue(':amount', $amount, SQLITE3_FLOAT);
        $query->bindValue(':date', $date, SQLITE3_TEXT);
        $query->bindValue(':description', $description, SQLITE3_TEXT);
        if ($query->execute()) {
            header('Location: ledger_transactions.php?ledger_id='.$ledger_id);
        } else {
            $error = '添加交易记录失败，请重试。';
        }
    }
}
$page_title = '添加交易记录';
include 'includes/header.php';
// 获取当前日期
$current_date = date('Y-m-d');
?>
    <h2  class="page-title">添加交易记录</h2>
<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="type">交易类型</label>
            <select class="form-control" id="type" name="type" required>
                <option value="收入">收入</option>
                <option value="支出">支出</option>
            </select>
        </div>
        <div class="form-group">
            <label for="amount">金额</label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
        </div>
        <div class="form-group">
            <label for="date">日期</label>
            <!-- 将日期输入框的默认值设置为当前日期 -->
            <input type="date" class="form-control" id="date" name="date" value="<?php echo $current_date; ?>" required>
        </div>
        <div class="form-group">
            <label for="description">摘要</label>
            <input type="text" class="form-control" id="description" name="description">
        </div>
        <button type="submit" class="btn btn-primary">添加</button>
    </form>
<?php
include 'includes/footer.php';
?>