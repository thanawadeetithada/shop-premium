<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['mark_paid'])) {
    $family_id = $_POST['family_id'];
    $stmt = $conn->prepare("UPDATE families SET deleted_at = NOW() WHERE family_id = ?");
    $stmt->bind_param("i", $family_id);
    $stmt->execute();
}

$today = date('Y-m-d');
$in3days = date('Y-m-d', strtotime('+3 days'));

$sql_today = "
    SELECT f.*, a.app_name
    FROM families f
    JOIN applications a ON f.app_id = a.app_id
    WHERE f.deleted_at IS NULL 
      AND f.pay_day = '$today'
";
$today_result = $conn->query($sql_today);

$sql_soon = "
    SELECT f.*, a.app_name
    FROM families f
    JOIN applications a ON f.app_id = a.app_id
    WHERE f.deleted_at IS NULL 
      AND f.pay_day BETWEEN '$today' AND '$in3days'
      AND f.pay_day <> '$today'
";
$soon_result = $conn->query($sql_soon);

$sql_paid = "
    SELECT f.*, a.app_name
    FROM families f
    JOIN applications a ON f.app_id = a.app_id
    WHERE f.deleted_at IS NOT NULL
    ORDER BY f.deleted_at DESC
    LIMIT 10
";
$paid_result = $conn->query($sql_paid);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="apple-mobile-web-app-title" content="App Premium">
    <meta name="application-name" content="App Premium">
    <meta name="theme-color" content="#96a1cd">
    <title>แจ้งเตือนการจ่าย</title>
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="icons/icon-192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

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

    .container {
        background: #eaf8fb;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        margin: 20px;
    }

    .container-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 56px);
    }

    .nav-tabs {
        justify-content: center;
        border-bottom: none;
        margin-bottom: 1rem;
    }

    .card {
        border-radius: 1rem;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    }

    .card-title {
        font-weight: 600;
        color: #333;
    }

    .card small {
        color: #666;
    }

    .btn-paid {
        background-color: #9FA8DA;
        color: white;
        border-radius: 50px;
        box-shadow: 0px 3px 4px rgba(0, 0, 0, 0.2);
    }

    .btn-paid:hover {
        background-color: #A996E6;
        color: white;
    }

    .container-lane {
        display: flex;
        gap: 20px;
        justify-content: space-between;
    }

    .container-lane>div {
        flex: 1;
        background: rgba(255, 255, 255, 0.8);
        border-radius: 12px;
        padding: 15px;
    }


    @media (max-width: 768px) {
        .container-lane {
            flex-direction: column;
        }
    }

    .btn-nav {
        color: black;
    }

    #soon-tab.active, #today-tab.active, #paid-tab.active{
        border-radius: 20px;
        border: 0px;
        box-shadow: 0px 3px 4px rgba(0, 0, 0, 0.2);
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
        <div class="container">
            <h2 class="text-center mb-4 fw-bold">💰 แจ้งเตือนการจ่าย</h2>

            <ul class="nav nav-tabs d-md-none" id="todoTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active btn-nav" id="today-tab" data-bs-toggle="tab" data-bs-target="#today"
                        type="button" role="tab">จ่ายวันนี้</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link btn-nav" id="soon-tab" data-bs-toggle="tab" data-bs-target="#soon" type="button"
                        role="tab">อีก 3 วัน</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link btn-nav" id="paid-tab" data-bs-toggle="tab" data-bs-target="#paid" type="button"
                        role="tab">จ่ายแล้วล่าสุด</button>
                </li>
            </ul>

            <div class="tab-content d-md-none">
                <div class="tab-pane fade show active" id="today" role="tabpanel">
                    <div class="mt-4">
                        <h4 class="mb-3">📅 จ่ายวันนี้</h4>
                        <div class="row g-3">
                            <?php if ($today_result->num_rows > 0): ?>
                            <?php while ($row = $today_result->fetch_assoc()): ?>
                            <div class="col-12">
                                <div class="card p-3">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($row['app_name']) ?></h5>
                                        <small>กลุ่ม: <?= htmlspecialchars($row['family_name']) ?></small><br>
                                        <small>วันจ่าย: <?= date('d/m/Y', strtotime($row['pay_day'])) ?></small>
                                        <form method="POST" class="mt-3">
                                            <input type="hidden" name="family_id" value="<?= $row['family_id'] ?>">
                                            <button type="submit" name="mark_paid" class="btn btn-m btn-paid w-100">
                                                จ่ายแล้ว</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <p class="text-muted">ไม่มีรายการวันนี้</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="soon" role="tabpanel">
                    <div class="mt-4">
                        <h4 class="mb-3">⏳ จ่ายในอีก 3 วัน</h4>
                        <div class="row g-3">
                            <?php if ($soon_result->num_rows > 0): ?>
                            <?php while ($row = $soon_result->fetch_assoc()): ?>
                            <div class="col-12">
                                <div class="card p-3 border-warning">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($row['app_name']) ?></h5>
                                        <small>กลุ่ม: <?= htmlspecialchars($row['family_name']) ?></small><br>
                                        <small>วันจ่าย: <?= date('d/m/Y', strtotime($row['pay_day'])) ?></small>
                                        <form method="POST" class="mt-3">
                                            <input type="hidden" name="family_id" value="<?= $row['family_id'] ?>">
                                            <button type="submit" name="mark_paid" class="btn btn-m btn-paid w-100">
                                                จ่ายแล้ว</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <p class="text-muted">ไม่มีรายการใน 3 วันข้างหน้า</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="paid" role="tabpanel">
                    <div class="mt-4">
                        <h4 class="mb-3">💸 จ่ายแล้วล่าสุด</h4>
                        <div class="row g-3">
                            <?php if ($paid_result->num_rows > 0): ?>
                            <?php while ($row = $paid_result->fetch_assoc()): ?>
                            <div class="col-12">
                                <div class="card p-3">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($row['app_name']) ?></h5>
                                        <small>กลุ่ม: <?= htmlspecialchars($row['family_name']) ?></small><br>
                                        <small>วันจ่าย: <?= date('d/m/Y', strtotime($row['pay_day'])) ?></small><br>
                                        <small class="text-muted">จ่ายเมื่อ:
                                            <?= date('d/m/Y H:i', strtotime($row['deleted_at'])) ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <p class="text-muted">ยังไม่มีรายการที่จ่ายแล้ว</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-lane d-none d-md-flex">
                <div class="flex-fill">
                    <h4 class="mb-3">📅 จ่ายวันนี้</h4>
                    <div class="overflow-auto" style="max-height: 70vh;">
                        <?php if ($today_result->num_rows > 0): ?>
                        <?php $today_result->data_seek(0); while ($row = $today_result->fetch_assoc()): ?>
                        <div class="card mb-3 p-3 border-danger">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['app_name']) ?></h5>
                                <small>กลุ่ม: <?= htmlspecialchars($row['family_name']) ?></small><br>
                                <small>วันจ่าย: <?= date('d/m/Y', strtotime($row['pay_day'])) ?></small>
                                <form method="POST" class="mt-3 d-flex justify-content-center">
                                    <input type="hidden" name="family_id" value="<?= $row['family_id'] ?>">
                                    <button type="submit" name="mark_paid" class="btn btn-m btn-paid w-50">
                                        จ่ายแล้ว</button>
                                </form>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <p class="text-muted">ไม่มีรายการวันนี้</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex-fill">
                    <h4 class="mb-3">⏳ อีก 3 วัน</h4>
                    <div class="overflow-auto" style="max-height: 70vh;">
                        <?php if ($soon_result->num_rows > 0): ?>
                        <?php $soon_result->data_seek(0); while ($row = $soon_result->fetch_assoc()): ?>
                        <div class="card mb-3 p-3 border-warning">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['app_name']) ?></h5>
                                <small>กลุ่ม: <?= htmlspecialchars($row['family_name']) ?></small><br>
                                <small>วันจ่าย: <?= date('d/m/Y', strtotime($row['pay_day'])) ?></small>
                                <form method="POST" class="mt-3 d-flex justify-content-center">
                                    <input type="hidden" name="family_id" value="<?= $row['family_id'] ?>">
                                    <button type="submit" name="mark_paid" class="btn btn-m btn-paid w-50">
                                        จ่ายแล้ว</button>
                                </form>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <p class="text-muted">ไม่มีรายการใน 3 วันข้างหน้า</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex-fill">
                    <h4 class="mb-3">💸 จ่ายแล้วล่าสุด</h4>
                    <div class="overflow-auto" style="max-height: 70vh;">
                        <?php if ($paid_result->num_rows > 0): ?>
                        <?php $paid_result->data_seek(0); while ($row = $paid_result->fetch_assoc()): ?>
                        <div class="card mb-3 p-3">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['app_name']) ?></h5>
                                <small>กลุ่ม: <?= htmlspecialchars($row['family_name']) ?></small><br>
                                <small>วันจ่าย: <?= date('d/m/Y', strtotime($row['pay_day'])) ?></small><br>
                                <small class="text-muted">จ่ายเมื่อ:
                                    <?= date('d/m/Y H:i', strtotime($row['deleted_at'])) ?></small>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <p class="text-muted">ยังไม่มีรายการที่จ่ายแล้ว</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>