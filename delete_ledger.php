<?php
include 'includes/functions.php';
if (!isLoggedIn()) {
    header('Location: login.php');
}
include 'includes/db_connection.php';
$id = $_GET['id'];
$ledgers = getLedgers();

if (count($ledgers) === 1) {
    $error = '不可以删除唯一的账本，请先添加新账本。';
    $page_title = '删除账本';
    include 'includes/header.php';
    echo '<div class="alert alert-danger">'.$error.'</div>';
    include 'includes/footer.php';
} else {
    // 先删除该账本下的所有交易记录
    $deleteTransactionsQuery = $db->prepare('DELETE FROM transactions WHERE ledger_id = :ledger_id');
    $deleteTransactionsQuery->bindValue(':ledger_id', $id, SQLITE3_INTEGER);
    $deleteTransactionsQuery->execute();

    // 再删除账本
    $deleteLedgerQuery = $db->prepare('DELETE FROM ledgers WHERE id = :id');
    $deleteLedgerQuery->bindValue(':id', $id, SQLITE3_INTEGER);
    if ($deleteLedgerQuery->execute()) {
        header('Location: index.php');
    } else {
        $error = '删除账本失败，请重试。';
        $page_title = '删除账本';
        include 'includes/header.php';
        echo '<div class="alert alert-danger">'.$error.'</div>';
        include 'includes/footer.php';
    }
}
?>