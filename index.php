<?php
session_start();
include 'db.php';

// ตรวจสอบผู้ใช้งาน (admin)
if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ดึงรายรับรวมทุกแอป
$sql_total = "
SELECT SUM(fm.price_per_day * fm.days) AS total_income,
       SUM(CASE WHEN fm.pay_status='paid' THEN fm.price_per_day*fm.days ELSE 0 END) AS paid_income,
       SUM(CASE WHEN fm.pay_status='unpaid' THEN fm.price_per_day*fm.days ELSE 0 END) AS unpaid_income
FROM family_members fm
JOIN families f ON fm.family_id=f.family_id
WHERE fm.deleted_at IS NULL AND f.deleted_at IS NULL
";
$result_total = $conn->query($sql_total);
$total = $result_total->fetch_assoc();

// ดึงรายรับแยกตามแอป
$sql_app = "
SELECT a.app_name,
       SUM(fm.price_per_day * fm.days) AS total_income,
       SUM(CASE WHEN fm.pay_status='paid' THEN fm.price_per_day*fm.days ELSE 0 END) AS paid_income,
       SUM(CASE WHEN fm.pay_status='unpaid' THEN fm.price_per_day*fm.days ELSE 0 END) AS unpaid_income
FROM family_members fm
JOIN families f ON fm.family_id=f.family_id
JOIN applications a ON f.app_id=a.app_id
WHERE fm.deleted_at IS NULL AND f.deleted_at IS NULL AND a.deleted_at IS NULL
GROUP BY a.app_id
ORDER BY a.app_name
";
$result_app = $conn->query($sql_app);

// เตรียมข้อมูลสำหรับ Chart.js
$chart_labels = [];
$chart_data = [];
if ($result_app->num_rows > 0) {
    while ($row = $result_app->fetch_assoc()) {
        $chart_labels[] = $row['app_name'];
        $chart_data[] = (float)$row['total_income'];
    }
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Dashboard รายรับ-รายจ่าย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 1rem; }
        .card-header { font-weight: bold; font-size: 1.2rem; }
        .chart-container { height: 400px; }
    </style>
</head>
<body>
<div class="container py-4">
    <h1 class="mb-4">Dashboard รายรับ-รายจ่าย</h1>

    <!-- รวมรายรับ -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-bg-success mb-3">
                <div class="card-header">รายรับรวมทั้งหมด</div>
                <div class="card-body">
                    <h5 class="card-title">฿ <?=number_format($total['total_income'],2)?></h5>
                    <p class="card-text">จ่ายแล้ว: ฿ <?=number_format($total['paid_income'],2)?></p>
                    <p class="card-text">ค้างจ่าย: ฿ <?=number_format($total['unpaid_income'],2)?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- รายรับแยกแต่ละแอป -->
    <div class="card mb-4">
        <div class="card-header">รายรับแยกตามแอป</div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>แอป</th>
                        <th>รายรับรวม (฿)</th>
                        <th>จ่ายแล้ว (฿)</th>
                        <th>ค้างจ่าย (฿)</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($result_app->num_rows > 0): ?>
                    <?php foreach($result_app as $row): ?>
                    <tr>
                        <td><?=htmlspecialchars($row['app_name'])?></td>
                        <td><?=number_format($row['total_income'],2)?></td>
                        <td><?=number_format($row['paid_income'],2)?></td>
                        <td><?=number_format($row['unpaid_income'],2)?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted">ไม่มีข้อมูล</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- กราฟรายรับตามแอป -->
    <div class="card">
        <div class="card-header">กราฟรายรับตามแอป</div>
        <div class="card-body chart-container">
            <canvas id="incomeChart"></canvas>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('incomeChart').getContext('2d');
const incomeChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?=json_encode($chart_labels)?>,
        datasets: [{
            label: 'รายรับรวม (฿)',
            data: <?=json_encode($chart_data)?>,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

</body>
</html>
