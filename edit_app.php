<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$app_id = $_GET['app_id'] ?? 0;

$sql_app = "SELECT * FROM applications WHERE app_id = ?";
$stmt = $conn->prepare($sql_app);
$stmt->bind_param("i", $app_id);
$stmt->execute();
$result = $stmt->get_result();
$app = $result->fetch_assoc();

if (!$app) die("ไม่พบข้อมูลแอป");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_name = $_POST['app_name'];
    $real_price = $_POST['real_price'];

    $sql_update = "UPDATE applications SET app_name=?, real_price=? WHERE app_id=?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sdi", $app_name, $real_price, $app_id);
    if ($stmt_update->execute()) {
        header("Location: detail_application.php?id=$app_id");
        exit();
    } else {
        $error = "เกิดข้อผิดพลาด: ".$stmt_update->error;
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดแอปพลิเคชัน</title>
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

    .btn-purple {
        width: 20%;
        background-color: #A996E6 !important;
        color: white !important;
        border: none;
    }

    .btn-purple:hover {
        background-color: #9FA8DA !important;
    }

    .btn-cancel {
        width: 20%;
        background-color: #c7c5c5 !important;
        color: black !important;
    }

    .btn-cancel:hover {
        background-color: #E8E8E8 !important;
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
        <div class="container mt-5">
            <h2 class="mb-4">แก้ไขแอปพลิเคชัน</h2>
            <?php if (!empty($error)) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>
            <form method="post">
                <div class="mb-3">
                    <label>ชื่อแอปพลิเคชัน</label>
                    <input type="text" name="app_name" class="form-control"
                        value="<?=htmlspecialchars($app['app_name'])?>" required>
                </div>
                <div class="mb-3">
                    <label>ราคาจริง (บาท/เดือน)</label>
                    <input type="number" name="real_price" class="form-control" step="0.01"
                        value="<?=htmlspecialchars($app['real_price'])?>" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-purple">บันทึก</button>
                    <a href="detail_application.php?id=<?=$app_id?>" class="btn btn-cancel">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>