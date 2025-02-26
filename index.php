<?php
include 'includes/functions.php';
if (!isLoggedIn()) {
    header('Location: login.php');
}
$ledgers = getLedgers();
$page_title = '账本管理';
include 'includes/header.php';
?>
    <h2 class="page-title">账本管理</h2>
    <a href="add_ledger.php" class="btn btn-primary">添加账本</a>
    <ul class="list-group mt-3">
        <?php foreach ($ledgers as $ledger): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-book"></i> <?php echo $ledger['name']; ?></span>
                <div>
                    <a href="ledger_transactions.php?ledger_id=<?php echo $ledger['id']; ?>" class="btn btn-info mr-2">查看交易记录</a>
                    <button type="button" class="btn btn-danger"
                        <?php if (count($ledgers) === 1): ?>
                            onclick="alert('不可以删除唯一的账本，请先添加新账本。')"
                        <?php else: ?>
                            onclick="confirmDelete(<?php echo $ledger['id']; ?>)"
                        <?php endif; ?>
                    >
                        <i class="fa-solid fa-trash"></i> 删除
                    </button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- 模态对话框 -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">确认删除</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    账本删除后将删除所有交易记录，是否确认？
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <a id="deleteLedgerLink" href="#" class="btn btn-danger">确认删除</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(ledgerId) {
            // 设置删除链接的 href 属性
            document.getElementById('deleteLedgerLink').href = 'delete_ledger.php?id=' + ledgerId;
            // 显示模态对话框
            var myModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            myModal.show();
        }
    </script>
<?php
include 'includes/footer.php';
?>