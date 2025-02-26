<?php
include 'includes/functions.php';
if (!isLoggedIn()) {
    header('Location: login.php');
}
include 'includes/db_connection.php';
$ledgers = getLedgers();
// 获取用户选择的账本 ID，如果未选择则默认为第一个账本
$selected_ledger_id = isset($_GET['ledger_id']) ? $_GET['ledger_id'] : (isset($ledgers[0]['id']) ? $ledgers[0]['id'] : null);
if ($selected_ledger_id === null) {
    $page_title = '统计信息';
    include 'includes/header.php';
    echo '<div class="alert alert-warning">暂无可用账本，请先添加账本。</div>';
    include 'includes/footer.php';
    exit;
}

// 获取自定义时间区间
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// 按月统计
$monthly_stats = [];
$yearly_stats = [];
// 自定义统计
$custom_stats = [];

// 按月统计查询
$query = $db->prepare('SELECT strftime("%Y-%m", date) as month, type, SUM(amount) as total FROM transactions WHERE ledger_id = :ledger_id GROUP BY month, type');
$query->bindValue(':ledger_id', $selected_ledger_id, SQLITE3_INTEGER);
$result = $query->execute();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $month = $row['month'];
    $type = $row['type'];
    $total = $row['total'];
    if (!isset($monthly_stats[$month])) {
        $monthly_stats[$month] = ['收入' => 0, '支出' => 0];
    }
    $monthly_stats[$month][$type] += $total;
}

// 按年统计查询
$query = $db->prepare('SELECT strftime("%Y", date) as year, type, SUM(amount) as total FROM transactions WHERE ledger_id = :ledger_id GROUP BY year, type');
$query->bindValue(':ledger_id', $selected_ledger_id, SQLITE3_INTEGER);
$result = $query->execute();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $year = $row['year'];
    $type = $row['type'];
    $total = $row['total'];
    if (!isset($yearly_stats[$year])) {
        $yearly_stats[$year] = ['收入' => 0, '支出' => 0];
    }
    $yearly_stats[$year][$type] += $total;
}

// 自定义统计查询
if (!empty($start_date) && !empty($end_date)) {
    $custom_query = $db->prepare('SELECT type, SUM(amount) as total FROM transactions WHERE ledger_id = :ledger_id AND date BETWEEN :start_date AND :end_date GROUP BY type');
    $custom_query->bindValue(':ledger_id', $selected_ledger_id, SQLITE3_INTEGER);
    $custom_query->bindValue(':start_date', $start_date, SQLITE3_TEXT);
    $custom_query->bindValue(':end_date', $end_date, SQLITE3_TEXT);
    $custom_result = $custom_query->execute();
    while ($row = $custom_result->fetchArray(SQLITE3_ASSOC)) {
        $type = $row['type'];
        $total = $row['total'];
        $custom_stats[$type] = $total;
    }
}

// 近12个月统计
$last_12_months = [];
$current_month = date('Y-m');
for ($i = 0; $i < 12; $i++) {
    $month = date('Y-m', strtotime("-$i months", strtotime($current_month)));
    if (!isset($monthly_stats[$month])) {
        $monthly_stats[$month] = ['收入' => 0, '支出' => 0];
    }
    $last_12_months[$month] = $monthly_stats[$month];
}
ksort($last_12_months);

$income_data = [];
$expense_data = [];
$balance_data = [];
foreach ($last_12_months as $month => $stats) {
    $income = $stats['收入'];
    $expense = $stats['支出'];
    $balance = $income - $expense;
    $income_data[] = $income;
    $expense_data[] = $expense;
    $balance_data[] = $balance;
}
$page_title = '统计信息';
include 'includes/header.php';
?>
    <h2 class="page-title">统计信息</h2>
    <select class="form-control mb-3" onchange="window.location.href='statistics.php?ledger_id=' + this.value">
        <?php foreach ($ledgers as $ledger): ?>
            <option value="<?php echo $ledger['id']; ?>" <?php if ($ledger['id'] == $selected_ledger_id) echo 'selected'; ?>>
                <?php echo $ledger['name']; ?>
            </option>
        <?php endforeach; ?>
    </select>
    <!-- 自定义统计表单 -->
    <form method="get" class="form-inline mb-3">
        <input type="hidden" name="ledger_id" value="<?php echo $selected_ledger_id; ?>">
        <label for="start_date" class="mr-2">开始时间:</label>
        <input type="date" class="form-control mr-2" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
        <label for="end_date" class="mr-2">结束时间:</label>
        <input type="date" class="form-control mr-2" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
        <button type="submit" class="btn btn-primary">统计</button>
    </form>
    <div class="accordion" id="statisticsAccordion">
        <!-- 按月统计部分 -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    按月统计
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#statisticsAccordion">
                <div class="accordion-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>月份</th>
                            <th>收入</th>
                            <th>支出</th>
                            <th>结余</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($monthly_stats as $month => $stats): ?>
                            <tr>
                                <td><?php echo $month; ?></td>
                                <td class="text-danger"><?php echo number_format($stats['收入'], 2); ?></td>
                                <td class="text-success"><?php echo number_format($stats['支出'], 2); ?></td>
                                <td><?php echo number_format($stats['收入'] - $stats['支出'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- 按年统计部分 -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    按年统计
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#statisticsAccordion">
                <div class="accordion-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>年份</th>
                            <th>收入</th>
                            <th>支出</th>
                            <th>结余</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($yearly_stats as $year => $stats): ?>
                            <tr>
                                <td><?php echo $year; ?></td>
                                <td class="text-danger"><?php echo number_format($stats['收入'], 2); ?></td>
                                <td class="text-success"><?php echo number_format($stats['支出'], 2); ?></td>
                                <td><?php echo number_format($stats['收入'] - $stats['支出'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- 自定义统计部分 -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFour">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                    自定义统计（<?php echo $start_date; ?> - <?php echo $end_date; ?>）
                </button>
            </h2>
            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#statisticsAccordion">
                <div class="accordion-body">
                    <?php if (!empty($custom_stats)): ?>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>类型</th>
                                <th>金额</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>收入</td>
                                <td class="text-danger"><?php echo number_format(isset($custom_stats['收入']) ? $custom_stats['收入'] : 0, 2); ?></td>
                            </tr>
                            <tr>
                                <td>支出</td>
                                <td class="text-success"><?php echo number_format(isset($custom_stats['支出']) ? $custom_stats['支出'] : 0, 2); ?></td>
                            </tr>
                            <tr>
                                <td>结余</td>
                                <td><?php echo number_format((isset($custom_stats['收入']) ? $custom_stats['收入'] : 0) - (isset($custom_stats['支出']) ? $custom_stats['支出'] : 0), 2); ?></td>
                            </tr>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>请选择开始时间和结束时间进行统计。</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- 近12个月统计图表部分 -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    近12个月统计图表
                </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#statisticsAccordion">
                <div class="accordion-body">
                    <canvas id="myChart" height="200"></canvas>
                    <script>
                        const ctx = document.getElementById('myChart').getContext('2d');
                        const myChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: <?php echo json_encode(array_keys($last_12_months)); ?>,
                                datasets: [
                                    {
                                        label: '收入',
                                        data: <?php echo json_encode($income_data); ?>,
                                        borderColor: 'red',
                                        backgroundColor: 'rgba(255, 0, 0, 0.2)',
                                        fill: true
                                    },
                                    {
                                        label: '支出',
                                        data: <?php echo json_encode($expense_data); ?>,
                                        borderColor: 'green',
                                        backgroundColor: 'rgba(0, 255, 0, 0.2)',
                                        fill: true
                                    },
                                    {
                                        label: '结余',
                                        data: <?php echo json_encode($balance_data); ?>,
                                        borderColor: 'blue',
                                        backgroundColor: 'rgba(0, 0, 255, 0.2)',
                                        fill: true
                                    }
                                ]
                            },
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
<?php
include 'includes/footer.php';
?>