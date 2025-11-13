<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$family_id = $_GET['family_id'] ?? 0;
$app_id = $_GET['app_id'] ?? 0;
$back_url = $_GET['from'] ?? 'detail_application.php';

$sql = "SELECT * FROM families WHERE family_id = ? AND deleted_at IS NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $family_id);
$stmt->execute();
$result = $stmt->get_result();
$family = $result->fetch_assoc();

if (!$family) {
    die("ไม่พบข้อมูลกลุ่มนี้หรือถูกลบแล้ว");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $family_name = $_POST['family_name'];
    $password_group = $_POST['password_group'];
    $total_people = $_POST['total_people'];
    $line_group_link = $_POST['line_group_link'];
    $pay_day = $_POST['pay_day'];
    $note = $_POST['note'];

    $upload_dir = 'uploads/';
    $line_group_img = $family['line_group_img'];
    $line_qr_img = $family['line_qr_img'];
    $payment_img = $family['payment_img'];

    if (!empty($_FILES['line_group_img']['name'])) {
        $file_tmp = $_FILES['line_group_img']['tmp_name'];
        $file_name = uniqid().'_'.basename($_FILES['line_group_img']['name']);
        move_uploaded_file($file_tmp, $upload_dir.$file_name);
        $line_group_img = $upload_dir.$file_name;
    }

    if (!empty($_FILES['line_qr_img']['name'])) {
        $file_tmp = $_FILES['line_qr_img']['tmp_name'];
        $file_name = uniqid().'_'.basename($_FILES['line_qr_img']['name']);
        move_uploaded_file($file_tmp, $upload_dir.$file_name);
        $line_qr_img = $upload_dir.$file_name;
    }

    if (!empty($_FILES['payment_img']['name'])) {
        $file_tmp = $_FILES['payment_img']['tmp_name'];
        $file_name = uniqid().'_'.basename($_FILES['payment_img']['name']);
        move_uploaded_file($file_tmp, $upload_dir.$file_name);
        $payment_img = $upload_dir.$file_name;
    }

    $update_sql = "UPDATE families SET 
        family_name = ?, 
        password_group = ?, 
        total_people = ?, 
        line_group_img = ?, 
        line_qr_img = ?, 
        line_group_link = ?, 
        pay_day = ?, 
        payment_img = ?, 
        note = ? 
        WHERE family_id = ?";
    
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param(
        "ssissssssi",
        $family_name,
        $password_group,
        $total_people,
        $line_group_img,
        $line_qr_img,
        $line_group_link,
        $pay_day,
        $payment_img,
        $note,
        $family_id
    );

    if ($stmt->execute()) {
        $_SESSION['modal_message'] = "อัปเดตข้อมูลกลุ่มเรียบร้อยแล้ว";
        $_SESSION['modal_type'] = "success";
        header("Location: dashboard_family.php?family_id=$family_id&app_id=$app_id&from=$back_url");
        exit();
    } else {
        $error = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
    }
}
?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลกลุ่ม</title>
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
        width: 30%;
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
            <h2 class="mb-4">แก้ไขข้อมูลกลุ่ม</h2>
            <?php if (!empty($error)) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label>ชื่อกลุ่ม / เบอร์ / Email</label>
                    <input type="text" name="family_name" class="form-control"
                        value="<?=htmlspecialchars($family['family_name'])?>" required>
                </div>
                <div class="mb-3">
                    <label>รหัสผ่าน</label>
                    <input type="text" name="password_group" class="form-control" autocomplete="off"
                        value="<?=htmlspecialchars($family['password_group'])?>">
                </div>
                <div class="mb-3">
                    <label>จำนวนคน</label>
                    <input type="number" name="total_people" class="form-control"
                        value="<?=htmlspecialchars($family['total_people'])?>">
                </div>
                <div class="mb-3">
                    <label>รูปกลุ่ม</label><br>
                    <?php if(!empty($family['line_group_img'])): ?>
                    <img src="<?=htmlspecialchars($family['line_group_img'])?>" width="100" height="100"><br><br>
                    <?php endif; ?>
                    <input type="file" name="line_group_img" class="form-control">
                </div>
                <div class="mb-3">
                    <label>QR Code</label><br>
                    <?php if(!empty($family['line_qr_img'])): ?>
                    <img src="<?=htmlspecialchars($family['line_qr_img'])?>" width="100" height="100"><br><br>
                    <?php endif; ?>
                    <input type="file" name="line_qr_img" class="form-control">
                </div>
                <div class="mb-3">
                    <label>ลิงก์กลุ่ม Line</label>
                    <input type="text" name="line_group_link" class="form-control"
                        value="<?=htmlspecialchars($family['line_group_link'])?>">
                </div>
                <div class="mb-3">
                    <label>วันชำระ (ทุกวันที่)</label>
                    <input type="date" name="pay_day" class="form-control"
                        value="<?=htmlspecialchars($family['pay_day'])?>">
                </div>
                <div class="mb-3">
                    <label>รูปแบบการชำระ</label><br>
                    <?php if(!empty($family['payment_img'])): ?>
                    <img src="<?=htmlspecialchars($family['payment_img'])?>" width="100" height="100"><br><br>
                    <?php endif; ?>
                    <input type="file" name="payment_img" class="form-control">
                </div>
                <div class="mb-3">
                    <label>หมายเหตุ</label>
                    <textarea name="note" class="form-control"><?=htmlspecialchars($family['note'])?></textarea>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-purple">บันทึก</button>
                    <button type="button" class="btn btn-cancel"
                        onclick="window.location.href='dashboard_family.php?family_id=<?= $family_id ?>&app_id=<?= $app_id ?>&from=<?= $back_url ?>'">
                        ยกเลิก
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>