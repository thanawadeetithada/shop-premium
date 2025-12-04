<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$app_id = $_GET['id'] ?? 0;

$sql_app = "SELECT * FROM applications WHERE app_id = ?";
$stmt_app = $conn->prepare($sql_app);
$stmt_app->bind_param("i", $app_id);
$stmt_app->execute();
$result_app = $stmt_app->get_result();
$app = $result_app->fetch_assoc();

$sql_family = "SELECT * FROM families 
               WHERE app_id = ? AND deleted_at IS NULL 
               ORDER BY 
                 CASE 
                   WHEN pay_day IS NULL OR pay_day = '0000-00-00' THEN 1
                   ELSE 0 
                 END,
                 pay_day ASC";

$stmt_family = $conn->prepare($sql_family);
$stmt_family->bind_param("i", $app_id);
$stmt_family->execute();
$result_family = $stmt_family->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="apple-mobile-web-app-title" content="App Premium">
    <meta name="application-name" content="App Premium">
    <meta name="theme-color" content="#96a1cd">
    <title>รายละเอียดแอปพลิเคชัน</title>
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
            <?php if ($app): ?>
            <div class="text-start mb-3">
                <a href="admin_dashboard.php" class="btn"><i class="fa-solid fa-arrow-left"></i> กลับ</a>
            </div>
            <h2 class="text-center mb-4"><?php echo htmlspecialchars($app['app_name']); ?></h2>
            <p class="text-center mb-4">ราคา <?php echo htmlspecialchars($app['real_price']); ?> ฿</p>
            <div class="text-end mb-3">
                <a href="edit_app.php?app_id=<?php echo $app_id; ?>" class="btn btn-warning">แก้ไข</a>
                <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAppModal"
                    data-app-id="<?php echo $app_id; ?>">
                    ลบแอปพลิเคชัน
                </a>
            </div>

            <div class="row">
                <?php if ($result_family->num_rows > 0): ?>
                <?php while ($row = $result_family->fetch_assoc()): ?>
                <?php
                    $family_id = $row['family_id'];
                    $sql_count = "SELECT COUNT(*) as member_count FROM family_members WHERE family_id = ? AND deleted_at IS NULL";
                    $stmt_count = $conn->prepare($sql_count);
                    $stmt_count->bind_param("i", $family_id);
                    $stmt_count->execute();
                    $result_count = $stmt_count->get_result();
                    $count_data = $result_count->fetch_assoc();
                    $member_count = $count_data['member_count'];

                $sql_total_people = "SELECT total_people FROM families WHERE family_id = ? AND deleted_at IS NULL";
                $stmt_total_people = $conn->prepare($sql_total_people);
                $stmt_total_people->bind_param("i", $family_id);
                $stmt_total_people->execute();
                $result_total_people = $stmt_total_people->get_result();
                $total_people = 0;
                if($row_total = $result_total_people->fetch_assoc()){
                    $total_people = $row_total['total_people'];
                }
                $stmt_total_people->close();
                    ?>
                <div class="col-md-3 mb-3">
                    <a href="dashboard_family.php?family_id=<?php echo $family_id; ?>&app_id=<?php echo $app_id; ?>&from=detail_application.php?id=<?php echo $app_id; ?>"
                        style="text-decoration: none;">
                        <div class="card p-3 text-center">
                            <?php if ($row['line_group_img']): ?>
                            <img src="<?php echo $row['line_group_img']; ?>" alt="Group Image" class="img-fluid mb-2"
                                style="max-height:150px;">
                            <?php endif; ?>
                            <h4 style="font-weight: bold;"><?php echo htmlspecialchars($row['family_name']); ?></h4>
                            <p class="text-start" style="margin-bottom: 8px;">
                                สมาชิก : <?php echo $member_count; ?> / <?php echo $total_people; ?> คน 
                            </p>
                            <p class="text-start">
                                ครบกำหนด :
                                <?php 
        echo (!empty($row['pay_day']) && $row['pay_day'] !== '0000-00-00')
            ? date("d/m/Y", strtotime($row['pay_day']))
            : '-';
    ?>
                            </p>

                        </div>
                    </a>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <p class="text-center text-muted">ยังไม่มีกลุ่มในแอปพลิเคชันนี้</p>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-danger text-center">ไม่พบแอปพลิเคชัน</div>
            <?php endif; ?>

            <div class="text-end mb-3">
                <a href="add_family.php?app_id=<?php echo $app_id; ?>" class="btn btn-primary">เพิ่มกลุ่ม</a>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteAppModal" tabindex="-1" aria-labelledby="deleteAppModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteAppModalLabel">ยืนยันการลบแอปพลิเคชัน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p>คุณแน่ใจหรือไม่ว่าต้องการลบแอปนี้?</p>
                    <p class="text-muted text-danger">กลุ่มและสมาชิกจะถูกลบไปด้วย</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary mx-2" data-bs-dismiss="modal">ยกเลิก</button>
                    <a href="#" id="confirmDeleteAppBtn" class="btn btn-danger mx-2">ยืนยันการลบ</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var deleteModal = document.getElementById('deleteAppModal');
        deleteModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var appId = button.getAttribute('data-app-id');
            var confirmBtn = deleteModal.querySelector('#confirmDeleteAppBtn');
            confirmBtn.href = 'delete_app.php?app_id=' + appId;
        });
    });
    </script>
</body>

</html>