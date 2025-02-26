<?php
include 'includes/functions.php';
if (!isLoggedIn()) {
    header('Location: login.php');
}
$ledger_id = isset($_GET['ledger_id']) ? $_GET['ledger_id'] : null;
$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '全部';

// 根据筛选条件构建 SQL 查询语句
$baseSql = 'SELECT * FROM transactions WHERE ledger_id = :ledger_id';
$params = [':ledger_id' => $ledger_id];
if ($filter === '收入') {
    $baseSql .= ' AND type = "收入"';
} elseif ($filter === '支出') {
    $baseSql .= ' AND type = "支出"';
}
if (!empty($search)) {
    $baseSql .= ' AND description LIKE :search';
    $params[':search'] = "%$search%";
}
$baseSql .= ' ORDER BY date DESC LIMIT :limit OFFSET :offset';
$params[':limit'] = 10;
$params[':offset'] = $offset;

if ($ledger_id) {
    $transactions = getTransactionsWithCustomQuery($baseSql, $params);
    foreach ($transactions as $transaction) {
        echo '<tr>';
        echo '<td>';
        echo '<div class="d-flex justify-content-between align-items-center">';
        echo '<div>';
        echo '<span style="font-size: 1.2em;">'. $transaction['type'] .'</span>';
        echo '<span style="font-size: 1.2em; color: '. ($transaction['type'] === '收入' ? 'red' : 'green') .'">';
        echo number_format($transaction['amount'], 2);
        echo '</span>';
        echo '</div>';
        echo '<div>';
        echo '<a href="edit_transaction.php?id='. $transaction['id'] .'" class="btn btn-sm btn-warning mr-2">';
        echo '<i class="fas fa-edit"></i>';
        echo '</a>';
        echo '<a href="delete_transaction.php?id='. $transaction['id'] .'" class="btn btn-sm btn-danger">';
        echo '<i class="fas fa-trash"></i>';
        echo '</a>';
        echo '</div>';
        echo '</div>';
        echo '<div class="d-flex">';
        echo '<span>'. $transaction['date'] .'</span>';
        echo '<span style="margin-left: 1ch;">'. $transaction['description'] .'</span>';
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
}