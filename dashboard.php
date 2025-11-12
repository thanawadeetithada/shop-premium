<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$userrole = $_SESSION['user_role'];

if ($userrole == 'admin' || $userrole == 'superadmin') {
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);
} else {
    header("Location: index.php");
    exit();
}

$sqlRecent = "SELECT item_name, usage_duration, price, budget_year, start_date, end_date, user_responsible 
              FROM parcels ORDER BY created_at DESC LIMIT 5";
$recentParcels = $conn->query($sqlRecent);

$sqlTotal = "SELECT COUNT(*) as total FROM parcels";
$totalResult = $conn->query($sqlTotal)->fetch_assoc()['total'];

$today = date('Y-m-d');
$sqlNew = "SELECT COUNT(*) as new_count FROM parcels WHERE DATE(created_at) = '$today'";
$newResult = $conn->query($sqlNew)->fetch_assoc()['new_count'];

$today = date('Y-m-d');
$next3Days = date('Y-m-d', strtotime('+30 days'));
$sqlExpiring = "SELECT COUNT(*) as expiring FROM parcels WHERE end_date BETWEEN '$today' AND '$next3Days'";
$expiringResult = $conn->query($sqlExpiring)->fetch_assoc()['expiring'];

$sqlExpiringItems = "SELECT item_name, end_date FROM parcels WHERE end_date BETWEEN '$today' AND '$next3Days'";
$expiringItems = $conn->query($sqlExpiringItems);

$categoryData = [];
$result = $conn->query("SELECT category, COUNT(*) as count FROM parcels GROUP BY category");

while ($row = $result->fetch_assoc()) {
    $categoryData[] = [
        'label' => $row['category'],
        'count' => $row['count']
    ];
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ระบบจัดการพัสดุในหน่วยงาน</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
    body {
        background-color: #d6d6d6;
        font-family: 'Prompt', sans-serif;
    }

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
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #004085; padding-left: 2rem;">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">ระบบจัดการพัสดุในหน่วยงาน</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'superadmin')): ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">ระบบจัดการพัสดุในหน่วยงาน</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="parcel_management.php">จัดการพัสดุ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="parcel_approve.php">ขออนุมัติ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user_management.php">จัดการผู้ใช้งาน</a>
                    </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'user')): ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="parcel_management_user.php">จัดการพัสดุ</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container my-4">
        <!-- Summary Row -->
        <div class="row text-center mb-2">
            <div class="col-md-4">
                <div class="summary-card">
                    <h4>รายการทั้งหมด</h4>
                    <div class="number"><?= $totalResult ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <a href="expiring_result.php" style="text-decoration: none;">
                    <div class="summary-card" style="background-color: #ffe4cc;">
                        <h4 class="text-dark">รายการใกล้หมดอายุ</h4>
                        <div class="number text-danger"><?= $expiringResult ?></div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <div class="summary-card" style="background-color: #e3ffe3;">
                    <h4>รายการเข้าใหม่</h4>
                    <div class="number text-success"><?= $newResult ?></div>
                </div>
            </div>
        </div>

        <!-- Chart and Warning -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="summary-card">
                    <h5>กราฟแสดงสัดส่วนประเภทพัสดุ</h5>
                    <div class="chart-container">
                        <canvas id="assetPieChart"></canvas>
                    </div>
                    <ul class="mt-3 text-start" style="font-size: 14px;">
                        <?php
    $total = array_sum(array_column($categoryData, 'count'));
    foreach ($categoryData as $cat) {
        $percent = $total > 0 ? round(($cat['count'] / $total) * 100) : 0;
        echo "<li>" . htmlspecialchars($cat['label']) . " {$percent}%</li>";
    }
    ?>
                    </ul>

                </div>
            </div>
            <div class="col-md-6">
                <div class="warning-box">
                    <strong>แจ้งเตือน</strong>
                    <ul class="mt-2">
                        <?php if ($expiringItems && $expiringItems->num_rows > 0): ?>
                        <?php while ($row = $expiringItems->fetch_assoc()): ?>
                        <li><?= htmlspecialchars($row['item_name']) ?> <span class="text-danger">จะหมดอายุในวันที่
                            </span><?= date('d/m/Y', strtotime($row['end_date'])) ?></li>

                        <?php endwhile; ?>
                        <?php else: ?>
                        <li>ไม่มีรายการใกล้หมดอายุภายใน 3 วัน</li>
                        <?php endif; ?>
                    </ul>

                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">รายการพัสดุล่าสุด</h5>
                <div class="table-responsive table-rounded shadow-sm">
                    <table class="table table-bordered table-hover data-table mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ชื่อ</th>
                                <th>ระยะเวลา</th>
                                <th>ราคา</th>
                                <th>งปม.</th>
                                <th>เริ่มต้นใช้งาน</th>
                                <th>สิ้นสุดการใช้งาน</th>
                                <th>ผู้ใช้งาน</th>
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <script>
    console.log("Session Info:");
    console.log("User ID:", <?php echo json_encode($_SESSION['user_id'] ?? 'ไม่พบ'); ?>);
    console.log("Full Name:", <?php echo json_encode($_SESSION['fullname'] ?? 'ไม่พบ'); ?>);
    console.log("Email:", <?php echo json_encode($_SESSION['user_email'] ?? 'ไม่พบ'); ?>);
    console.log("Role:", <?php echo json_encode($_SESSION['user_role'] ?? 'ไม่พบ'); ?>);

    const ctx = document.getElementById('assetPieChart').getContext('2d');

    const categoryLabels = <?= json_encode(array_column($categoryData, 'label')) ?>;
    const rawCounts = <?= json_encode(array_column($categoryData, 'count')) ?>;
    const categoryCounts = rawCounts.map(x => Number(x));

    const pieChart = new Chart(document.getElementById('assetPieChart'), {
        type: 'pie',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryCounts,
                backgroundColor: ['#4e73df', '#f6c23e', '#36b9cc', '#e74a3b', '#1cc88a']
            }]
        },
        options: {
            plugins: {
                datalabels: {
                    formatter: (value, ctx) => {
                        const dataArr = ctx.chart.data.datasets[0].data;
                        const total = dataArr.reduce((a, b) => a + b, 0);
                        return ((value / total) * 100).toFixed(0) + '%';
                    },
                    color: '#fff',
                    font: {
                        weight: 'bold'
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
    </script>

</body>

</html>