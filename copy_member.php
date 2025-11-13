<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// รับค่าจาก URL
$member_id = isset($_GET['member_id']) ? intval($_GET['member_id']) : 0;
$family_id = isset($_GET['family_id']) ? intval($_GET['family_id']) : 0;
$app_id    = isset($_GET['app_id']) ? intval($_GET['app_id']) : 0;

// ตรวจสอบข้อมูล
if (!$member_id || !$family_id || !$app_id) {
    echo "URL ไม่ถูกต้อง";
    exit();
}

// ดึงข้อมูลกลุ่ม
$sql_family = "SELECT * FROM families WHERE family_id = ?";
$stmt_family = $conn->prepare($sql_family);
$stmt_family->bind_param("i", $family_id);
$stmt_family->execute();
$result_family = $stmt_family->get_result();
$family = $result_family->fetch_assoc();

if (!$family) {
    echo "ไม่พบข้อมูลกลุ่ม";
    exit();
}


// ดึงข้อมูลสมาชิกเดิม
$sql = "SELECT * FROM family_members WHERE member_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

if (!$member) {
    echo "ไม่พบข้อมูลสมาชิก";
    exit();
}

// เมื่อกด submit form
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ทำ soft delete ให้ member เดิม
    $deleted_at = date('Y-m-d H:i:s');
    $sql_delete = "UPDATE family_members SET deleted_at = ? WHERE member_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("si", $deleted_at, $member_id);
    $stmt_delete->execute();

    // เตรียมข้อมูลสำหรับ insert ใหม่
    $member_name   = $_POST['member_name'];
    $email         = $_POST['email'];
    $device        = $_POST['device'];
    $source        = $_POST['source'];
    $screen        = $_POST['screen'];
    $days          = $_POST['days'];
    $price_per_day = $_POST['price_per_day'];
    $status        = $_POST['status'];
    $pay_status    = $_POST['pay_status'];
    $start_date    = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $expire_date   = !empty($_POST['expire_date']) ? $_POST['expire_date'] : null;
    $transfer_time = !empty($_POST['transfer_time']) ? $_POST['transfer_time'] : null;

    // slip
    $slip_img = $member['slip_img'];
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
(member_name, email, device, source, screen, start_date, days, expire_date, price_per_day, status, pay_status, slip_img, transfer_time, family_id) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt_insert = $conn->prepare($sql_insert); // ต้องมีบรรทัดนี้
if (!$stmt_insert) {
    die("Prepare failed: " . $conn->error);
}

$stmt_insert->bind_param(
    "ssssssisdssssi",
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
    $transfer_time,
    $family_id 
);


    if ($stmt_insert->execute()) {
        header("Location: check_list.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>เกิดข้อผิดพลาดในการสร้างสมาชิกใหม่</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนวันหมดอายุ</title>
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
            <div class="card">
                <div class="text-center mb-5">
                    <h3 class="mb-4 text-add-mem">เปลี่ยนวันหมดอายุ</h3>
                    <h4>Group : <?php echo htmlspecialchars($family['family_name']); ?></h4>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">ชื่อสมาชิก</label>
                            <input type="text" name="member_name" class="form-control"
                                value="<?php echo $member['member_name']; ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">อีเมล</label>
                            <input type="email" name="email" class="form-control"
                                value="<?php echo $member['email']; ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">อุปกรณ์</label>
                            <input type="text" name="device" class="form-control"
                                value="<?php echo $member['device']; ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">มาจาก</label>
                            <select name="source" class="form-control">
                                <option value="" <?php echo ($member['source'] == '') ? 'selected' : ''; ?>>--
                                    เลือกช่องทาง --</option>
                                <option value="facebook"
                                    <?php echo ($member['source'] == 'facebook') ? 'selected' : ''; ?>>Facebook</option>
                                <option value="twitter"
                                    <?php echo ($member['source'] == 'twitter') ? 'selected' : ''; ?>>Twitter</option>
                                <option value="line" <?php echo ($member['source'] == 'line') ? 'selected' : ''; ?>>LINE
                                </option>
                                <option value="other" <?php echo ($member['source'] == 'other') ? 'selected' : ''; ?>>
                                    Other</option>
                            </select>

                        </div>

                        <div class="col-md-6">
                            <label class="form-label">จอ</label>
                            <input type="text" name="screen" class="form-control"
                                value="<?php echo $member['screen']; ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">วันเริ่ม</label>
                            <input type="date" name="start_date" class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">วันหมดอายุ</label>
                            <input type="date" name="expire_date" class="form-control" value="">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">จำนวนวัน</label>
                            <input type="number" name="days" class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ราคา / คน / วัน</label>
                            <input type="number" name="price_per_day" class="form-control"
                                value="<?php echo $member['price_per_day']; ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">สถานะการจ่ายเงิน</label>
                            <select name="pay_status" class="form-control">
                                <option value="paid" <?php echo ($member['pay_status'] == 'paid') ? 'selected' : ''; ?>>
                                    จ่ายแล้ว</option>
                                <option value="unpaid"
                                    <?php echo ($member['pay_status'] == 'unpaid') ? 'selected' : ''; ?>>ยังไม่จ่าย
                                </option>
                            </select>

                        </div>
                        <div class="col-md-6">
                            <label class="form-label">สถานะ</label>
                            <select name="status" class="form-control">
                                <option value="user" <?php echo ($member['status'] == 'user') ? 'selected' : ''; ?>>
                                    ผู้ใช้งาน</option>
                                <option value="admin" <?php echo ($member['status'] == 'admin') ? 'selected' : ''; ?>>
                                    แอดมิน</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">เวลาการโอน</label>
                            <input type="datetime-local" name="transfer_time" class="form-control" value="">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รูปสลิป</label>
                        <input type="file" name="slip_img" class="form-control" accept="image/*"
                            onchange="previewSlip(event)">
                    </div>

                    <div class="mb-3" id="preview-container" style="display:none;">
                        <label class="form-label">พรีวิวรูปใหม่</label><br>
                        <img id="previewSlipImg" style="width:100px; border-radius:8px; border:1px solid #ccc;"
                            onclick="showSlipModal(this.src)">
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-purple">บันทึก</button>
                        <a href="check_list.php" class="btn btn-cancel">ยกเลิก</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal แสดงรูปสลิป -->
    <div class="modal fade" id="slipModal" tabindex="-1" aria-labelledby="slipModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body text-center p-0">
                    <img id="slipModalImg" src="" class="img-fluid rounded" alt="Slip Preview">
                </div>
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3 bg-light rounded-circle"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
    function previewSlip(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('previewSlipImg');
        const container = document.getElementById('preview-container');

        if (file) {
            preview.src = URL.createObjectURL(file);
            container.style.display = 'block';
        }
    }

    function showSlipModal(imageSrc) {
        const modalImg = document.getElementById('slipModalImg');
        modalImg.src = imageSrc;
        const modal = new bootstrap.Modal(document.getElementById('slipModal'));
        modal.show();
    }

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