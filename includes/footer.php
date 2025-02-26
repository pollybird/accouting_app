<footer class="fixed-bottom bg-purple text-white">
    <nav class="navbar navbar-expand navbar-light">
        <div class="container-fluid justify-content-around">
            <!-- 账本菜单项 -->
            <a class="nav-link" href="index.php">
                <i class="fa-solid fa-book"></i> <!-- 账本图标 -->
                账本
            </a>
            <!-- 统计菜单项 -->
            <a class="nav-link" href="statistics.php">
                <i class="fa-solid fa-chart-line"></i> <!-- 统计图标 -->
                统计
            </a>
            <!-- 修改密码菜单项 -->
            <a class="nav-link" href="change_password.php">
                <i class="fa-solid fa-key"></i> <!-- 修改密码图标 -->
                修改密码
            </a>
        </div>
    </nav>
</footer>

<style>
    /* 定义紫色背景色 */
    .bg-purple {
        background-color: #800080; /* 紫色的十六进制代码 */
    }

    /* 设置菜单项文字颜色为白色 */
    .navbar-light .navbar-nav .nav-link {
        color: white;
    }

    /* 鼠标悬停时菜单项文字颜色 */
    .navbar-light .navbar-nav .nav-link:hover {
        color: lightgray;
    }

    /* 调整图标和文字之间的间距 */
    .nav-link i {
        margin-right: 5px;
    }
</style>
<script>
        const protectedArea = document.getElementById('protected');
        protectedArea.addEventListener('contextmenu', function (event) {
            event.preventDefault();
        }, true);
    </script>