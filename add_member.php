<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$family_id = $_GET['family_id'] ?? 0;
$app_id = $_GET['app_id'] ?? 0;


$sql_family = "SELECT * FROM families WHERE family_id = ?";
$stmt_family = $conn->prepare($sql_family);
$stmt_family->bind_param("i", $family_id);
$stmt_family->execute();
$result_family = $stmt_family->get_result();
$family = $result_family->fetch_assoc();

if (!$family) {
    die("ไม่พบข้อมูลกลุ่มนี้");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_name = $_POST['member_name'];
    $email = $_POST['email'];
    $device = $_POST['device'];
    $source = $_POST['source'];
    $screen = $_POST['screen'];
    $start_date = $_POST['start_date'];
    $days = $_POST['days'];
    $expire_date = $_POST['expire_date'];
    $price_per_day = $_POST['price_per_day'];
    $status = $_POST['status'];
    $pay_status = $_POST['pay_status'];

    $transfer_time = null;
        if (!empty($_POST['transfer_time'])) {
            $transfer_time = str_replace('T', ' ', $_POST['transfer_time']) . ':00';
    }

    $slip_img = '';
    if (!empty($_FILES['slip_img']['name'])) {
        $target_dir = "uploads/slips/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = time() . '_' . basename($_FILES['slip_img']['name']);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['slip_img']['tmp_name'], $target_file)) {
            $slip_img = $target_file;
        }
    }

    $sql_insert = "INSERT INTO family_members 
    (family_id, member_name, email, device, source, screen, start_date, days, expire_date, price_per_day, status, pay_status, slip_img, transfer_time) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param(
    "issssssissssss",
    $family_id,
    $member_name,
    $email,
    $device,
    $source,
    $screen,
    $start_date,
    $days,
    $expire_date,
    $price_per_day,
    $status,
    $pay_status,
    $slip_img,
    $transfer_time
);


    if ($stmt_insert->execute()) {
        header("Location: dashboard_family.php?family_id=$family_id&app_id=$app_id");
        exit();
    } else {
        echo "<div class='alert alert-danger'>เกิดข้อผิดพลาดในการเพิ่มสมาชิก</div>";
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
    <title>เพิ่มสมาชิก</title>
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
        background-color: white;
        color: black;
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

    .btn-action {
        display: flex;
        justify-content: center;
        align-items: center;
    }


    .modal-text {
        width: 100%;
    }

    .modal-header {
        font-weight: bold;
        padding: 25px;
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
    }

    .tab-func {
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

    .text-add-mem {
        font-weight: bold;
    }

    .btn-group-responsive {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        flex-wrap: nowrap;
    }

    .btn-group-responsive .btn {
        flex: 1 1 200px;
        max-width: 200px;
    }

    @media (max-width: 576px) {
        .btn-group-responsive .btn {
            width: 100%;
            max-width: none;
        }
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
        <div class="container mt-5">
            <div class="card">
                <div class="text-center mb-5">
                    <h3 class="mb-4 text-add-mem">เพิ่มสมาชิก</h3>
                    <h4>Group : <?php echo htmlspecialchars($family['family_name']); ?></h4>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">ชื่อสมาชิก</label>
                            <input type="text" name="member_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">อีเมล</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">อุปกรณ์</label>
                            <input type="text" name="device" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">มาจาก</label>
                            <select name="source" class="form-control">
                                <option value="">-- เลือกช่องทาง --</option>
                                <option value="facebook">Facebook</option>
                                <option value="twitter">Twitter</option>
                                <option value="line">LINE</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">จอ</label>
                            <input type="text" name="screen" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">วันเริ่ม</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">วันหมดอายุ</label>
                            <input type="date" name="expire_date" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">จำนวนวัน</label>
                            <input type="number" name="days" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ราคา / คน / วัน</label>
                            <input type="number" name="price_per_day" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">สถานะการจ่ายเงิน</label>
                            <select name="pay_status" class="form-control">
                                <option value="paid">จ่ายแล้ว</option>
                                <option value="unpaid">ยังไม่จ่าย</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">สถานะ</label>
                            <select name="status" class="form-control">
                                <option value="user">ผู้ใช้งาน</option>
                                <option value="admin">แอดมิน</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">เวลาการโอน</label>
                            <input type="datetime-local" name="transfer_time" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รูปสลิป</label>
                        <input type="file" name="slip_img" class="form-control">
                    </div>

                    <div class="text-center btn-group-responsive mt-4">
                        <button type="submit" class="btn btn-purple">บันทึก</button>
                        <a href="dashboard_family.php?family_id=<?php echo $family_id; ?>&app_id=<?php echo $app_id; ?>"
                            class="btn btn-cancel">ยกเลิก</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const startInput = document.querySelector('input[name="start_date"]');
        const expireInput = document.querySelector('input[name="expire_date"]');
        const daysInput = document.querySelector('input[name="days"]');

        function calculateDays() {
            const startDate = new Date(startInput.value);
            const expireDate = new Date(expireInput.value);

            if (startInput.value && expireInput.value) {
                const diffTime = expireDate - startDate;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
                daysInput.value = diffDays > 0 ? diffDays : 0;
            } else {
                daysInput.value = '';
            }
        }

        startInput.addEventListener('change', calculateDays);
        expireInput.addEventListener('change', calculateDays);
    });
    </script>

</body>

</html>