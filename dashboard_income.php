<?php
session_start();
require_once 'db.php';

// ตรวจสอบผู้ใช้งาน admin
if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ======================
// รายรับรวมทั้งหมด
// ======================
$sql_total_income = "
SELECT 
    SUM(fm.price_per_day * fm.days) AS total_income,
    SUM(CASE WHEN fm.pay_status='paid' THEN fm.price_per_day*fm.days ELSE 0 END) AS paid_income,
    SUM(CASE WHEN fm.pay_status='unpaid' THEN fm.price_per_day*fm.days ELSE 0 END) AS unpaid_income
FROM family_members fm
JOIN families f ON fm.family_id = f.family_id
WHERE fm.deleted_at IS NULL AND f.deleted_at IS NULL
";
$result = $conn->query($sql_total_income);
$totalIncome = $result->fetch_assoc();

// ======================
// รายรับแยกตามแอป
// ======================
$sql_income_by_app = "
SELECT a.app_name,
    SUM(fm.price_per_day * fm.days) AS total_income
FROM family_members fm
JOIN families f ON fm.family_id=f.family_id
JOIN applications a ON f.app_id=a.app_id
WHERE fm.deleted_at IS NULL AND f.deleted_at IS NULL AND a.deleted_at IS NULL
GROUP BY a.app_id
ORDER BY a.app_name
";
$result_app = $conn->query($sql_income_by_app);

$categoryData = [];
while($row = $result_app->fetch_assoc()) {
    $categoryData[] = [
        'label' => $row['app_name'],
        'count' => (float)$row['total_income']
    ];
}

// ======================
// รายจ่ายรวมทั้งหมด (ตัวอย่างใช้ payments)
// ======================
$sql_total_expense = "
SELECT 
    SUM(CASE WHEN status='paid' THEN 1 ELSE 0 END) AS paid_count,
    SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) AS pending_count
FROM payments p
JOIN families f ON p.family_id = f.family_id
WHERE p.deleted_at IS NULL AND f.deleted_at IS NULL
";
$result_expense = $conn->query($sql_total_expense);
$totalExpense = $result_expense->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายรับ-รายจ่าย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <style>
    body {
        font-family: 'Prompt', sans-serif;
        height: auto;
        background: url('bg/sky.png') no-repeat center center/cover;
        margin: 0;
    }

    /* .card {
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
        background: white;
        margin-top: 50px;
        margin: 3% 5%;
        transition: 0.3s;
        background-color: #96a1cd;
    } */

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

    /* .container {
        background: rgba(255, 255, 255, 0.9);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 700px;
        margin: 20px;
    }

    h2 {
        margin-bottom: 20px;
        color: black;
        text-align: center;
        margin-top: 20px;
    }

    button {
        width: 48%;
        padding: 12px;
        font-size: 18px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .container-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 56px);
    }

    .card:hover {
        transform: scale(1.05);
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    }

    .btn-add {
        width: 20%;
    }

    .text-add {
        text-align: end;
    } */

    .summary-card {
        background: #ffffff;
        border-radius: 10px;
        text-align: center;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 10px;
    }

    .summary-card h4 {
        font-size: 20px;
        margin-bottom: 5px;
    }

    .summary-card .number {
        font-size: 28px;
        font-weight: bold;
    }

    .chart-container {
        width: 100%;
        max-width: 350px;
        margin: auto;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .warning-box {
        background-color: #fff3cd;
        border-left: 6px solid #ffc107;
        padding: 15px;
        border-radius: 8px;
    }

    .data-table td,
    .data-table th {
        vertical-align: middle !important;
    }

    .table-rounded {
        border-radius: 12px;
        border: 1px solid #dee2e6;
    }

    tbody td:first-child,
    th:first-child {
        padding-left: 15px;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark px-3">
        <div class="d-flex w-100 justify-content-between align-items-center">
            <i class="fa-solid fa-bars text-white" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"
                style="cursor: pointer;"></i>
            <div class="nav-item">
                <a class="nav-link text-white" href="logout.php"><i class="fa-solid fa-user"></i>&nbsp;&nbsp;Logout</a>
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
                <li><a href="alert_page.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-bell"></i> แจ้งเตือนรายการ</a></li>
                <li><a href="check_list.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-user-check"></i> เช็ครายชื่อ</a></li>
                <li><a href="dashboard_income.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-money-check-dollar"></i> รายรับ-รายจ่าย</a></li>
                <li><a href="admin_dashboard.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-tablet-screen-button"></i> แอปพลิเคชัน</a></li>
                <li><a href="payment.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-money-bill-wave"></i> ชำระเงิน</a></li>
                <li><a href="index.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-list-check"></i> เมนู</a></li>
                <li><a href="all_member.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-user"></i> รายชื่อทั้งหมด</a></li>
                <li><a href="user_management.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-user-tie"></i> ข้อมูลผู้ใช้งาน</a></li>
            </ul>
        </div>
    </div>

    <div class="container-wrapper">
        <div class="container my-4">

            <!-- Summary -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="summary-card">
                        <h4>รายรับรวมทุกแอป</h4>
                        <div class="number">฿ <?=number_format($totalIncome['total_income'],2)?></div>
                        <small>จ่ายแล้ว: ฿ <?=number_format($totalIncome['paid_income'],2)?> | ค้างจ่าย: ฿
                            <?=number_format($totalIncome['unpaid_income'],2)?></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card" style="background-color:#ffe4cc;">
                        <h4>รายจ่ายรวมทุกแอป</h4>
                        <div class="number"><?= $totalExpense['paid_count'] + $totalExpense['pending_count'] ?> รายการ
                        </div>
                        <small>ชำระแล้ว: <?= $totalExpense['paid_count'] ?> | รอดำเนินการ:
                            <?= $totalExpense['pending_count'] ?></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="warning-box">
                        <strong>แจ้งเตือน</strong>
                        <ul class="mt-2 mb-0">
                            <li>ไม่มีรายการใกล้หมดอายุภายใน 3 วัน</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Chart -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="summary-card">
                        <h5>กราฟรายรับแยกตามแอป</h5>
                        <div class="chart-container">
                            <canvas id="assetPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">รายการพัสดุล่าสุด</h5>
                    <div class="table-responsive table-rounded shadow-sm">
                        <table class="table table-bordered table-hover data-table mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>แอป</th>
                                    <th>ชื่อกลุ่ม</th>
                                    <th>ราคาจ่ายแอป</th>
                                    <th>ราคารับจากกลุ่มแอป</th>
                                    <th>เริ่มต้นใช้งาน</th>
                                    <th>วันครบกำหนดจ่ายกลุ่ม</th>
                                    <th>จำนวนคน</th>
                                    <th>จำนวนว่าง</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $recentParcels->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                                    <td><?= htmlspecialchars($row['usage_duration']) ?></td>
                                    <td><?= number_format($row['price'], 2) ?></td>
                                    <td><?= htmlspecialchars($row['budget_year']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['start_date'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['end_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['user_responsible']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script>
    const ctx = document.getElementById('assetPieChart').getContext('2d');
    const categoryLabels = <?= json_encode(array_column($categoryData,'label')) ?>;
    const categoryCounts = <?= json_encode(array_column($categoryData,'count')) ?>;

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryCounts,
                backgroundColor: ['#4e73df', '#f6c23e', '#36b9cc', '#e74a3b', '#1cc88a']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                datalabels: {
                    formatter: (value, ctx) => {
                        let dataArr = ctx.chart.data.datasets[0].data;
                        let total = dataArr.reduce((a, b) => a + b, 0);
                        return ((value / total) * 100).toFixed(0) + '%';
                    },
                    color: '#fff',
                    font: {
                        weight: 'bold'
                    }
                },
                legend: {
                    position: 'bottom'
                }
            }
        },
        plugins: [ChartDataLabels]
    });
    </script>

</body>

</html>