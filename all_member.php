<?php
session_start();
require 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$sql = "
    SELECT 
        fm.member_name,
        a.app_name,
        a.app_id,
        f.family_name,
        f.family_id,
        fm.email,
        fm.device,
        fm.screen,
        fm.days,
        fm.start_date,
        fm.expire_date,
        fm.transfer_time,
        fm.deleted_at,       -- ✅ เพิ่มถ้าต้องใช้ตรวจสอบ
        f.deleted_at AS family_deleted_at
    FROM family_members fm
    LEFT JOIN families f ON fm.family_id = f.family_id
    LEFT JOIN applications a ON f.app_id = a.app_id
    ORDER BY 
        CASE WHEN fm.deleted_at IS NULL THEN 0 ELSE 1 END,
        fm.member_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$app_query = $conn->query("SELECT app_id, app_name FROM applications WHERE deleted_at IS NULL ORDER BY app_name ASC");

$family_query = $conn->query("SELECT family_id, family_name FROM families WHERE deleted_at IS NULL ORDER BY family_name ASC");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="apple-mobile-web-app-title" content="App Premium">
    <meta name="application-name" content="App Premium">
    <meta name="theme-color" content="#96a1cd">>
    <title>รายชื่อ</title>
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="icons/icon-192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Prompt', sans-serif;
        background: url('bg/sky.png') no-repeat center center;
        background-size: cover;
        background-attachment: fixed;
        min-height: 100vh;
        padding-bottom: 50px;
    }

    .card {
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
        background-color: #ffffff;
        margin-top: 3%;
        margin-bottom: 0px;
        margin-right: 5%;
        margin-left: 5%;
    }

    .table th,
    .table td {
        text-align: center;
        font-size: 14px;

    }

    .table {
        background: #f8f9fa;
        border-radius: 10px;
    }

    .table th {
        background-color: #f9fafc;
        color: black;
    }

    .modal-dialog {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;

    }

    .modal-content {
        width: 100%;
        max-width: 500px;
    }

    .header-card {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }

    .form-control modal-text {
        height: fit-content;
        width: 50%;
    }

    .modal-text {
        width: 100%;
    }

    .modal-header {
        font-weight: bold;
    }

    .nav-item a {
        color: white;
        margin-right: 1rem;
    }

    .navbar {
        padding: 20px;
    }

    .nav-link:hover {
        color: white;
    }

    .modal-body {
        padding: 10px 40px;
    }

    .search-add {
        display: flex;
        gap: 15px;
        align-items: center;
        width: 50%;
    }

    .tab-func {
        width: 100%;
        display: flex;
        justify-content: flex-start;
        align-items: center;
    }

    @media (max-width: 768px) {
        .search-add {
            flex-direction: row;
            gap: 10px;
        }

        .search-name {
            width: 20%;
            flex: 1;
        }

        .tab-func button {
            width: max-content;
        }
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 20px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 20px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 14px;
        width: 14px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #28a745;
    }

    input:checked+.slider:before {
        transform: translateX(18px);
    }

    .btn-purple {
        width: 20%;
        background-color: #8c99bc !important;
        color: white !important;
        border: none;
    }

    .bg-purple {
        background-color: #8c99bc !important;
    }

    .btn.disabled,
    .btn:disabled {
        pointer-events: none;
        opacity: 0.5;
        cursor: not-allowed;
    }

    .row-disabled td {
        background-color: #d7d7d7 !important;
    }

    .pagination-circle .page-item .page-link {
        border-radius: 50%;
        margin: 0 4px;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    .pagination-circle .page-item.active .page-link {
        background-color: #8c99bc;
        border-color: #8c99bc;
        color: white;
        font-weight: bold;
    }

    .pagination-circle .page-item .page-link:hover {
        background-color: #6c7aa0;
        color: white;
    }

    .pagination-circle .page-item.disabled .page-link {
        opacity: 0.5;
        pointer-events: none;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark px-3">
        <div class="d-flex w-100 justify-content-between align-items-center">
            <i class="fa-solid fa-bars text-white" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"
                style="cursor: pointer;"></i>
            <div class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fa-solid fa-user"></i>&nbsp;&nbsp;Logout</a>
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="sidebarMenu">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">รายการ</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="list-unstyled">
                <li><a href="menu.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-list-check"></i> เมนู</a></li>
                <li><a href="admin_dashboard.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-tablet-screen-button"></i> แอปพลิเคชัน</a></li>
                <li><a href="alert_page.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-bell"></i> แจ้งเตือนรายการ</a></li>
                <li><a href="check_list.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-user-check"></i> เช็ครายชื่อ</a></li>
                <li><a href="dashboard_income.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-money-check-dollar"></i> รายรับ-รายจ่าย</a></li>
                <li><a href="payment.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-money-bill-wave"></i> ชำระเงิน</a></li>
                <li><a href="all_member.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-user"></i> รายชื่อทั้งหมด</a></li>
                <li><a href="user_management.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-user-tie"></i> ข้อมูลผู้ใช้งาน</a></li>
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="header-card">
            <h3 class="text-left">รายชื่อทั้งหมด</h3>
            <div class="search-add">
                <div class="tab-func">
                    <input type="text" class="form-control search-name" placeholder="ค้นหา...">
                </div>
                <select name="app_name" class="form-control">
                    <option value="">-- เลือกแอปพลิเคชัน --</option>
                    <?php while ($app = $app_query->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($app['app_id']) ?>"
                        data-name="<?= htmlspecialchars(strtolower($app['app_name'])) ?>">
                        <?= htmlspecialchars($app['app_name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>

                <select name="family_name" class="form-control">
                    <option value="">-- เลือกกลุ่ม --</option>
                    <?php while ($family = $family_query->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($family['family_id']) ?>"
                        data-name="<?= htmlspecialchars(strtolower($family['family_name'])) ?>">
                        <?= htmlspecialchars($family['family_name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>

            </div>
        </div>
        <br>
        <div class="table-responsive">
            <table class="table table-bordered" id="memberTable">
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>ชื่อ</th>
                        <th>แอปพลิเคชัน</th>
                        <th>ชื่อกลุ่ม</th>
                        <th>Email</th>
                        <th>อุปกรณ์</th>
                        <th>จอ</th>
                        <th>จำนวนวัน</th>
                        <th>วันเริ่มต้น</th>
                        <th>วันหมดอายุ</th>
                        <th>วันที่โอน</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
    if ($result->num_rows > 0): 
        $count = 1;
        while ($row = $result->fetch_assoc()): 
            $disabled = !empty($row['deleted_at']); 
            $rowClass = !empty($row['deleted_at']) ? 'table-secondary' : '';
    ?>
                    <tr class="<?= $disabled ? 'row-disabled' : '' ?>">
                        <td><?= $count++ ?></td>
                        <td><?= htmlspecialchars($row['member_name']) ?></td>
                        <td><?= htmlspecialchars($row['app_name']) ?></td>
                        <td><?= htmlspecialchars($row['family_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['device']) ?></td>
                        <td><?= htmlspecialchars($row['screen']) ?></td>
                        <td><?= htmlspecialchars($row['days']) ?></td>
                        <td><?= htmlspecialchars($row['start_date']) ?></td>
                        <td><?= htmlspecialchars($row['expire_date']) ?></td>
                        <td><?= htmlspecialchars($row['transfer_time']) ?></td>
                        <td class='btn-action'>
                            <a href="<?= $disabled 
    ? '#' 
    : 'dashboard_family.php?family_id=' . htmlspecialchars($row['family_id']) 
      . '&app_id=' . htmlspecialchars($row['app_id']) 
      . '&from=all_member.php' ?>" class="btn btn-warning btn-sm <?= $disabled ? 'disabled' : '' ?>"
                                <?= $disabled ? 'aria-disabled="true" tabindex="-1" title="ข้อมูลนี้ถูกลบแล้ว"' : '' ?>>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </td>
                    </tr>

                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="12" class="text-center text-muted">ไม่มีข้อมูล</td>
                    </tr>
                    <?php endif; ?>
                </tbody>

                <tr id="noResult" style="display:none;">
                    <td colspan="12" class="text-center text-muted fw-bold bg-light py-3">
                        <i class="fa-solid fa-circle-info"></i> ไม่พบข้อมูลที่ค้นหา
                    </td>
                </tr>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
            <nav>
                <ul class="pagination pagination-circle" id="pagination"></ul>
            </nav>
        </div>


    </div>


    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-header bg-purple text-white">
                    <h5 class="modal-title" id="alertModalLabel">แจ้งเตือน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-4 pb-4" id="alertMessage">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-purple px-4" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        var rowsPerPage = 10;
        var currentPage = 1;
        var $tableRows = $("#memberTable tbody tr").not("#noResult");
        var filteredRows = $tableRows;

        function showPage(page) {
            $tableRows.hide();
            var start = (page - 1) * rowsPerPage;
            var end = start + rowsPerPage;
            filteredRows.slice(start, end).show();
            currentPage = page;
            renderPagination();
        }


        function renderPagination() {
            var totalRows = filteredRows.length;
            var totalPages = Math.ceil(totalRows / rowsPerPage);
            var $pagination = $("#pagination");
            $pagination.empty();

            if (totalPages <= 1) return;

            $pagination.append(
                `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" aria-label="Previous">&laquo;</a>
        </li>`
            );

            var startPage = Math.max(currentPage - 2, 1);
            var endPage = Math.min(startPage + 4, totalPages);

            startPage = Math.max(endPage - 4, 1);

            for (var i = startPage; i <= endPage; i++) {
                $pagination.append(
                    `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#">${i}</a>
            </li>`
                );
            }

            $pagination.append(
                `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" aria-label="Next">&raquo;</a>
        </li>`
            );

            $(".page-item:not(.disabled) .page-link").click(function(e) {
                e.preventDefault();
                var text = $(this).text();

                if (text === "«") {
                    if (currentPage > 1) showPage(currentPage - 1);
                } else if (text === "»") {
                    if (currentPage < totalPages) showPage(currentPage + 1);
                } else {
                    showPage(parseInt(text));
                }
            });
        }


        function filterTable() {
            var nameFilter = $(".search-name").val().toLowerCase();
            var appFilter = $("select[name='app_name'] option:selected").data("name") || "";
            var familyFilter = $("select[name='family_name'] option:selected").data("name") || "";
            var visibleRows = 0;

            filteredRows = $tableRows.filter(function() {
                var memberName = $(this).find("td:eq(1)").text().toLowerCase();
                var appName = $(this).find("td:eq(2)").text().toLowerCase();
                var familyName = $(this).find("td:eq(3)").text().toLowerCase();
                var device = $(this).find("td:eq(5)").text().toLowerCase();
                var screen = $(this).find("td:eq(6)").text().toLowerCase();

                var matchSearch = nameFilter === "" ||
                    memberName.includes(nameFilter) ||
                    device.includes(nameFilter) ||
                    screen.includes(nameFilter) ||
                    $(this).find("td:eq(4)").text().toLowerCase().includes(nameFilter);

                var matchApp = appFilter === "" || appName.includes(appFilter);
                var matchFamily = familyFilter === "" || familyName.includes(familyFilter);

                return matchSearch && matchApp && matchFamily;
            });

            $("#noResult").toggle(filteredRows.length === 0);
            showPage(1);
        }

        $(".search-name").on("keyup", filterTable);
        $("select[name='app_name'], select[name='family_name']").on("change", filterTable);
        filterTable();
    });
    </script>

</body>

</html>