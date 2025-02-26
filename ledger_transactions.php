<?php
include 'includes/functions.php';
if (!isLoggedIn()) {
    header('Location: login.php');
}
$ledger_id = isset($_GET['ledger_id']) ? $_GET['ledger_id'] : null;
if (!$ledger_id) {
    $page_title = '交易记录';
    include 'includes/header.php';
    echo '<div class="alert alert-warning">未指定账本，请返回选择账本。</div>';
    include 'includes/footer.php';
    exit;
}
$ledger_name = '';
$ledgers = getLedgers();
foreach ($ledgers as $ledger) {
    if ($ledger['id'] == $ledger_id) {
        $ledger_name = $ledger['name'];
        break;
    }
}
if (!$ledger_name) {
    $page_title = '交易记录';
    include 'includes/header.php';
    echo '<div class="alert alert-warning">未找到指定的账本，请返回重新选择。</div>';
    include 'includes/footer.php';
    exit;
}
$search = isset($_GET['search']) ? $_GET['search'] : '';
$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
// 获取筛选条件，默认为全部
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

$transactions = getTransactionsWithCustomQuery($baseSql, $params);

$page_title = $ledger_name. ' - 交易记录';
include 'includes/header.php';
?>
    <h2 class="page-title"><?php echo $ledger_name; ?> - 交易记录</h2>
    <a href="add_transaction.php?ledger_id=<?php echo $ledger_id; ?>" class="btn btn-primary">添加交易记录</a>
    <!-- 筛选下拉框 -->
    <select class="form-control d-inline-block w-auto ml-3" onchange="window.location.href='ledger_transactions.php?ledger_id=<?php echo $ledger_id; ?>&search=<?php echo $search; ?>&filter=' + this.value">
        <option value="全部" <?php if ($filter === '全部') echo 'selected'; ?>>全部</option>
        <option value="收入" <?php if ($filter === '收入') echo 'selected'; ?>>收入</option>
        <option value="支出" <?php if ($filter === '支出') echo 'selected'; ?>>支出</option>
    </select>
    <form method="get" class="input-group mt-3">
        <input type="hidden" name="ledger_id" value="<?php echo $ledger_id; ?>">
        <input type="hidden" name="filter" value="<?php echo $filter; ?>">
        <input type="text" class="form-control" name="search" placeholder="按摘要搜索" value="<?php echo $search; ?>">
        <div class="input-group-append">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>
    <table class="table table-striped">
        <tbody id="transaction-list">
        <?php foreach ($transactions as $transaction): ?>
            <tr>
                <td>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span style="font-size: 1.2em;"><?php echo $transaction['type']; ?></span>
                            <span style="font-size: 1.2em; color: <?php echo $transaction['type'] === '收入' ? 'red' : 'green'; ?>">
                                    <?php echo number_format($transaction['amount'], 2); ?>
                                </span>
                        </div>
                        <div>
                            <a href="edit_transaction.php?id=<?php echo $transaction['id']; ?>" class="btn btn-sm btn-warning mr-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete_transaction.php?id=<?php echo $transaction['id']; ?>" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex">
                        <span><?php echo $transaction['date']; ?></span>
                        <span style="margin-left: 1ch;"><?php echo $transaction['description']; ?></span>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php if (count($transactions) === 10): ?>
    <button id="load-more" class="btn btn-primary" data-ledger-id="<?php echo $ledger_id; ?>" data-offset="10" data-search="<?php echo $search; ?>" data-filter="<?php echo $filter; ?>">加载更多</button>
<?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const loadMoreButton = document.getElementById('load-more');
            if (loadMoreButton) {
                loadMoreButton.addEventListener('click', function () {
                    const ledgerId = this.dataset.ledgerId;
                    const offset = parseInt(this.dataset.offset);
                    const search = this.dataset.search;
                    const filter = this.dataset.filter;

                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', `get_more_transactions.php?ledger_id=${ledgerId}&offset=${offset}&search=${search}&filter=${filter}`, true);
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            const newTransactions = xhr.responseText;
                            const transactionList = document.getElementById('transaction-list');
                            transactionList.insertAdjacentHTML('beforeend', newTransactions);
                            loadMoreButton.dataset.offset = offset + 10;

                            // 如果返回的记录不足 10 条，隐藏“加载更多”按钮
                            if (newTransactions.trim().length === 0 || newTransactions.split('<tr>').length - 1 < 10) {
                                loadMoreButton.style.display = 'none';
                            }
                        }
                    };
                    xhr.send();
                });
            }
        });
    </script>
<?php
include 'includes/footer.php';
?>