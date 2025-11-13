<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$showModal = false;
$modalType = '';
$modalMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_name = $_POST['app_name'];
    $real_price = $_POST['real_price'];

    $sql = "INSERT INTO applications (app_name, real_price) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sd", $app_name, $real_price);

    if ($stmt->execute()) {
        $showModal = true;
        $modalType = 'success';
        $modalMessage = 'เพิ่มแอปพลิเคชันเรียบร้อยแล้ว!';
    } else {
        $showModal = true;
        $modalType = 'danger';
        $modalMessage = 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล';
    }
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
    <title>เพิ่มแอปพลิเคชัน</title>
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="icons/icon-192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
    body {
        font-family: 'Prompt', sans-serif;
        height: auto;
        background: url('bg/sky.png') no-repeat center center/cover;
        margin: 0;
    }

    .card {
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
        background: white;
        margin-top: 50px;
        margin: 3% 5%;
        transition: 0.3s;
        background-color: #96a1cd;
        color: white;
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
        width: fit-content;
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

    .text-danger {
        font-weight: bold;
        color: red !important;
    }

    .btn-success {
        background-color: #8c99bc;
        border: none;
        transition: 0.3s;
    }

    .btn-success:hover {
        background-color: #6f7ca1;
    }

    .btn-secondary {
        background-color: #999;
        border: none;
    }

    .bg-purple {
        background-color: #A996E6;
    }

    .btn-confirm {
        background-color: #F5E096FF;
    }

    .btn-confirm:hover {
        background-color: #E0D29FFF;
        color: black;
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
            <div class="card shadow-lg">
                <h3 class="text-center mb-4">เพิ่มแอปพลิเคชันใหม่</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">ชื่อแอปพลิเคชัน</label>
                        <input type="text" name="app_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ราคาจริง (บาท/เดือน)</label>
                        <input type="number" name="real_price" step="0.01" class="form-control" required>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-confirm px-4">บันทึก</button>
                        <a href="admin_dashboard.php" class="btn btn-light px-4">กลับ</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header 
        <?php echo ($modalType === 'success') ? 'bg-purple text-white' : 'bg-danger text-white'; ?>">
                        <h5 class="modal-title w-100 text-center">
                            <?php echo ($modalType === 'success') ? 'สำเร็จ' : 'เกิดข้อผิดพลาด'; ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center fs-5 py-4">
                        <?php echo $modalMessage; ?>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button"
                            class="btn btn-<?php echo ($modalType === 'success') ? 'success' : 'danger'; ?>"
                            data-bs-dismiss="modal" onclick="handleModalClose()">ตกลง</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    <?php if ($showModal): ?>
    var myModal = new bootstrap.Modal(document.getElementById('resultModal'));
    myModal.show();

    function handleModalClose() {
        <?php if ($modalType === 'success'): ?>
        window.location.href = 'admin_dashboard.php';
        <?php endif; ?>
    }
    <?php endif; ?>
    </script>

</body>

</html>