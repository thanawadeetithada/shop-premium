<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$sql_total_income = "
SELECT 
    SUM(fm.price_per_day) AS total_income
FROM family_members fm
JOIN families f ON fm.family_id = f.family_id
";
$result = $conn->query($sql_total_income);
$totalIncome = $result->fetch_assoc();

$sql_income_by_app = "
SELECT a.app_name,
    SUM(fm.price_per_day) AS total_income
FROM family_members fm
JOIN families f ON fm.family_id=f.family_id
JOIN applications a ON f.app_id=a.app_id
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

$sql_total_expense = "
SELECT SUM(total_expense) AS total_expense
FROM (
    SELECT COUNT(f.family_id) * a.real_price AS total_expense
    FROM applications a
    LEFT JOIN families f ON a.app_id = f.app_id
    GROUP BY a.app_id
) AS t
";
$result_expense = $conn->query($sql_total_expense);
$totalExpense = $result_expense->fetch_assoc();

$sql_recent = "
SELECT 
    a.app_name, 
    f.family_name,
    a.real_price,
    COALESCE(SUM(fm.price_per_day), 0) AS total_receive,
    MAX(CASE WHEN fm.status = 'admin' THEN fm.start_date END) AS start_date,
    MAX(CASE WHEN fm.status = 'admin' THEN fm.expire_date END) AS expire_date,
    COUNT(fm.member_id) AS member_count,
    (f.total_people - COUNT(fm.member_id)) AS slot_left
FROM families f
JOIN applications a ON f.app_id = a.app_id
LEFT JOIN family_members fm ON f.family_id = fm.family_id
GROUP BY f.family_id
ORDER BY f.family_id DESC
LIMIT 10
";
$recentParcels = $conn->query($sql_recent);

$netProfit = $totalIncome['total_income'] - $totalExpense['total_expense'];

$sql_monthly_income = "
SELECT 
    DATE_FORMAT(f.pay_day, '%Y-%m') AS month,
    SUM(fm.price_per_day) AS total_income
FROM family_members fm
JOIN families f ON fm.family_id = f.family_id
GROUP BY DATE_FORMAT(f.pay_day, '%Y-%m')
ORDER BY month ASC
";
$result_monthly = $conn->query($sql_monthly_income);

$monthlyLabels = [];
$monthlyData = [];
while ($row = $result_monthly->fetch_assoc()) {
    $monthlyLabels[] = $row['month'];
    $monthlyData[] = (float)$row['total_income'];
}

$sql_expense_by_app = "
SELECT a.app_name,
       COUNT(f.family_id) * a.real_price AS total_expense
FROM applications a
LEFT JOIN families f ON a.app_id = f.app_id
GROUP BY a.app_id
ORDER BY a.app_name
";
$result_expense_app = $conn->query($sql_expense_by_app);

$expenseLabels = [];
$expenseData = [];
while ($row = $result_expense_app->fetch_assoc()) {
    $expenseLabels[] = $row['app_name'];
    $expenseData[] = (float)$row['total_expense'];
}

$sql_monthly_expense = "
SELECT 
    DATE_FORMAT(f.pay_day, '%Y-%m') AS month,
    SUM(a.real_price) AS total_expense
FROM families f
JOIN applications a ON f.app_id = a.app_id
GROUP BY DATE_FORMAT(f.pay_day, '%Y-%m')
ORDER BY month ASC
";
$result_monthly_expense = $conn->query($sql_monthly_expense);

$monthlyExpenseData = [];
while ($row = $result_monthly_expense->fetch_assoc()) {
    $monthlyExpenseData[] = (float)$row['total_expense'];
}

// ดึงกำไรสุทธิแยกตามแอป
$sql_app_profit_only = "
SELECT 
    a.app_name,
    COALESCE(SUM(fm.price_per_day), 0) AS total_income,
    COUNT(DISTINCT f.family_id) * a.real_price AS total_expense,
    (COALESCE(SUM(fm.price_per_day), 0) - COUNT(DISTINCT f.family_id) * a.real_price) AS profit
FROM applications a
LEFT JOIN families f ON a.app_id = f.app_id
LEFT JOIN family_members fm ON f.family_id = fm.family_id
GROUP BY a.app_id
ORDER BY a.app_name
";

$result_app_profit_only = $conn->query($sql_app_profit_only);

$appLabels = [];
$appProfit = [];

while ($row = $result_app_profit_only->fetch_assoc()) {
    $appLabels[] = $row['app_name'];
    $appProfit[] = (float)$row['profit'];
}

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="apple-mobile-web-app-title" content="App Premium">
    <meta name="application-name" content="App Premium">
    <meta name="theme-color" content="#96a1cd">
    <title>รายรับ-รายจ่าย</title>
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="icons/icon-192.png">
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

    .summary-card {
        background: #ffffff;
        border-radius: 10px;
        text-align: center;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 10px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
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
        height: 300px;
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

    <div class="container-wrapper">
        <div class="container my-4">
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="summary-card">
                        <h4>รายรับรวมทุกแอป</h4>
                        <div class="number">฿ <?=number_format($totalIncome['total_income'],2)?></div>
                        <p class="mb-0">&nbsp;</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card" style="background-color:#ffe4cc;">
                        <h4>รายจ่ายรวมทุกแอป</h4>
                        <div class="number">฿ <?= number_format($totalExpense['total_expense'], 2) ?>
                        </div>
                        <p class="mb-0">&nbsp;</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card" style="background-color: <?= $netProfit >= 0 ? '#d4edda' : '#f8d7da' ?>;">
                        <h4>กำไรสุทธิ</h4>
                        <div class="number" style="color: <?= $netProfit >= 0 ? '#155724' : '#721c24' ?>">
                            ฿ <?= number_format($netProfit, 2) ?>
                        </div>
                        <p class="mb-0" style="color: <?= $netProfit >= 0 ? '#155724' : '#721c24' ?>">
                            <?= $netProfit >= 0 ? 'รายรับมากกว่ารายจ่าย' : 'รายจ่ายมากกว่ารายรับ' ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="row mb-4 d-flex align-items-stretch">
                <div class="col-md-6">
                    <div class="summary-card h-100">
                        <h5>กราฟรายรับ</h5>
                        <div class="chart-container">
                            <canvas id="assetPieChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="summary-card h-100">
                        <h5>กราฟรายจ่าย</h5>
                        <div class="chart-container">
                            <canvas id="monthlyBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4 d-flex align-items-stretch">
                <div class="col-md-6">
                    <div class="summary-card h-100">
                        <h5>กราฟกำไร-ขาดทุน รายเดือน</h5>
                        <div class="chart-container" style="max-width: 700px;">
                            <canvas id="profitChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="summary-card h-100">
                        <h5>กราฟกำไร-ขาดทุน แยกตามแอป</h5>
                        <div class="chart-container" style="max-width: 800px;">
                            <canvas id="appProfitChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>



            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">รายการล่าสุด</h5>
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
                                    <td><?= htmlspecialchars($row['app_name']) ?></td>
                                    <td><?= htmlspecialchars($row['family_name']) ?></td>
                                    <td><?= number_format($row['real_price'], 2) ?></td>
                                    <td><?= number_format($row['total_receive'], 2) ?></td>
                                    <td><?= $row['start_date'] ? date('d/m/Y', strtotime($row['start_date'])) : '-' ?>
                                    </td>
                                    <td><?= $row['expire_date'] ? date('d/m/Y', strtotime($row['expire_date'])) : '-' ?>
                                    </td>
                                    <td><?= $row['member_count'] ?></td>
                                    <td><?= $row['slot_left'] ?></td>
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

    const ctxExpense = document.getElementById('monthlyBarChart').getContext('2d');
    const monthlyLabels = <?= json_encode($monthlyLabels) ?>;
    const monthlyData = <?= json_encode($monthlyData) ?>;

    const colors = categoryCounts.map(() => `hsl(${Math.random()*360}, 70%, 50%)`);
    const barColors = <?= json_encode($expenseData) ?>.map(() => `hsl(${Math.random()*360}, 70%, 50%)`);

    new Chart(ctxExpense, {
        type: 'pie',
        data: {
            labels: <?= json_encode($expenseLabels) ?>,
            datasets: [{
                data: <?= json_encode($expenseData) ?>,
                backgroundColor: barColors,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'จำนวนเงิน (บาท)'
                    }
                },
                x: {
                    title: {
                        display: true,
                    }
                }
            },
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

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryCounts,
                backgroundColor: colors,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'จำนวนเงิน (บาท)'
                    }
                },
                x: {
                    title: {
                        display: true,
                    }
                }
            },
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

    const ctxProfit = document.getElementById('profitChart').getContext('2d');
    const monthlyExpenseData = <?= json_encode($monthlyExpenseData) ?>;

    new Chart(ctxProfit, {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [{
                    label: 'รายรับ (บาท)',
                    data: monthlyData,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'รายจ่าย (บาท)',
                    data: monthlyExpenseData,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'จำนวนเงิน (บาท)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'เดือน'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ฿ ' + context.formattedValue;
                        }
                    }
                }
            }
        }
    });

    const ctxAppProfit = document.getElementById('appProfitChart').getContext('2d');
    const appLabels = <?= json_encode($appLabels) ?>;
    const appProfit = <?= json_encode($appProfit) ?>;

    // ตั้งสีแท่ง: ถ้ากำไรบวกเป็นสีเขียว ถ้าขาดทุนเป็นสีแดง
    const profitColors = appProfit.map(value => value >= 0 ? 'rgba(75, 192, 75, 0.7)' : 'rgba(255, 99, 132, 0.7)');

    new Chart(ctxAppProfit, {
        type: 'bar',
        data: {
            labels: appLabels,
            datasets: [{
                label: 'กำไรสุทธิ (บาท)',
                data: appProfit,
                backgroundColor: profitColors,
                borderColor: profitColors.map(c => c.replace('0.7', '1')),
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'จำนวนเงิน (บาท)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'ชื่อแอป'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'กำไรสุทธิ: ฿ ' + context.formattedValue;
                        }
                    }
                }
            }
        }
    });
    </script>

</body>

</html>