<?php
include 'includes/functions.php';
if (!isLoggedIn()) {
    header('Location: login.php');
}
include 'includes/db_connection.php';
$id = $_GET['id'];
$query = $db->prepare('SELECT ledger_id FROM transactions WHERE id = :id');
$query->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $query->execute();
$transaction = $result->fetchArray(SQLITE3_ASSOC);
$ledger_id = $transaction['ledger_id'];
$query = $db->prepare('DELETE FROM transactions WHERE id = :id');
$query->bindValue(':id', $id, SQLITE3_INTEGER);
if ($query->execute()) {
    header('Location: ledger_transactions.php?ledger_id='.$ledger_id);
} else {
    $error = '删除交易记录失败，请重试。';
}
$page_title = '删除交易记录';
include 'includes/header.php';
if (isset($error)) {
    echo '<div class="alert alert-danger">'.$error.'</div>';
}
include 'includes/footer.php';
?>