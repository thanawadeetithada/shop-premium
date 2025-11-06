<?php
include 'db.php';
$app_id = $_GET['id'] ?? 0;

$sql = "SELECT * FROM applications WHERE app_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $app_id);
$stmt->execute();
$result = $stmt->get_result();
$app = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดแอปพลิเคชัน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <?php if ($app): ?>
        <div class="card p-4 shadow-sm">
            <h3><?php echo htmlspecialchars($app['app_name']); ?></h3>
            <p><strong>ราคาจริง:</strong> <?php echo number_format($app['real_price'], 2); ?> บาท</p>
            <p><strong>กำไร:</strong> <?php echo number_format($app['profit'], 2); ?> บาท</p>
            <a href="admin_dashboard.php" class="btn btn-secondary mt-3">กลับ</a>
        </div>
    <?php else: ?>
        <div class="alert alert-danger text-center">ไม่พบข้อมูลแอปพลิเคชัน</div>
    <?php endif; ?>
</div>
</body>
</html>
