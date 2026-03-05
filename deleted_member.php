<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$back_url = $_GET['from'] ?? 'detail_application.php?id=' . ($_GET['app_id'] ?? 0);

$family_id = $_GET['family_id'] ?? 0;
$app_id = $_GET['app_id'] ?? 0;

$sql_family = "SELECT f.*, a.app_name, a.real_price 
               FROM families f 
               JOIN applications a ON f.app_id = a.app_id
               WHERE f.family_id = ?";
$stmt = $conn->prepare($sql_family);
$stmt->bind_param("i", $family_id);
$stmt->execute();
$result_family = $stmt->get_result();
$family = $result_family->fetch_assoc();

if (!$family) {
    die("ไม่พบข้อมูลกลุ่มนี้");
}

$sql_members = "SELECT * FROM family_members 
                WHERE family_id = ? 
                AND deleted_at IS NOT NULL
                ORDER BY 
                    CASE WHEN status = 'admin' THEN 1 ELSE 2 END, 
                    CASE 
                        WHEN screen REGEXP '^s[0-9]+$' THEN CAST(SUBSTRING(screen, 2) AS UNSIGNED) 
                        ELSE 9999 
                    END";

$stmt_members = $conn->prepare($sql_members);
$stmt_members->bind_param("i", $family_id);
$stmt_members->execute();
$result_members = $stmt_members->get_result();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="apple-mobile-web-app-title" content="App Premium">
    <meta name="application-name" content="App Premium">
    <meta name="theme-color" content="#96a1cd">
    <title>ข้อมูลกลุ่ม (สมาชิกที่ถูกลบ)</title>
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
        min-height: 100vh;
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
    }

    .table th,
    .table td {
        text-align: center;
        font-size: 14px;
        vertical-align: middle;
    }

    .table {
        background: #f8f9fa;
        border-radius: 10px;
    }

    .table th {
        background-color: #f9fafc;
        color: black;
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

    .table img:hover {
        transform: scale(1.1);
        transition: 0.2s ease-in-out;
    }

    #memberTable tr.checked td {
        text-decoration: line-through;
        color: #888;
        background-color: #f2f2f2;
    }

    @keyframes blink-animation {
        0% {
            background-color: #ffcccc;
        }

        50% {
            background-color: #ffffff;
        }

        100% {
            background-color: #ffcccc;
        }
    }

    .blink-red td {
        animation: blink-animation 3s infinite;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark px-3">
        <div class="d-flex w-100 justify-content-between align-items-center">
            <i class="fa-solid fa-bars text-white" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"
                style="cursor: pointer;"></i>
            <div class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fa-solid fa-user"></i>&nbsp;&nbsp;Logout</a>
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

    <div class="text-start mb-3 mt-3 px-4">
        <a href="<?= htmlspecialchars($back_url) ?>" class="btn">
            <i class="fa-solid fa-arrow-left"></i> กลับ
        </a>
    </div>

    <div class="card">
        <h3 class="text-left mb-4">ข้อมูลกลุ่ม</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ชื่อกลุ่ม / เบอร์ / Email</th>
                        <th>รหัสผ่าน</th>
                        <th>จำนวนคน</th>
                        <th>รูปกลุ่ม</th>
                        <th>QR Code</th>
                        <th>ลิงก์กลุ่ม Line</th>
                        <th>วันชำระ (ทุกวันที่)</th>
                        <th>ชื่อแอปพลิเคชัน</th>
                        <th>ราคาจริง (บาท/เดือน)</th>
                        <th>รูปแบบการชำระ</th>
                        <th>หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
if ($family) {
    $password_display = !empty($family['password_group']) ? '********' : '-';
    $pay_day_display = (!empty($family['pay_day']) && $family['pay_day'] !== '0000-00-00')
        ? date("d/m/Y", strtotime($family['pay_day']))
        : '-';
    $family_name = !empty($family['family_name']) ? htmlspecialchars($family['family_name']) : '-';
    $total_people = !empty($family['total_people']) ? htmlspecialchars($family['total_people']) : '-';
    $app_name = !empty($family['app_name']) ? htmlspecialchars($family['app_name']) : '-';
    $real_price = isset($family['real_price']) && $family['real_price'] !== ''
        ? htmlspecialchars($family['real_price'])
        : '-';
    $note = !empty($family['note']) ? htmlspecialchars($family['note']) : '-';

    echo "<tr>
        <td>$family_name</td>
        <td>$password_display</td>
        <td>$total_people</td>
       <td>";
    if (!empty($family['line_group_img'])) {
        echo "<img src='" . htmlspecialchars($family['line_group_img']) . "' 
            width='100' height='100' 
            style='border-radius:5px; cursor:pointer;' 
            onclick=\"showSlipModal('" . htmlspecialchars($family['line_group_img']) . "')\">";
    } else {
        echo "-";
    }
echo "</td>
<td>";
    if (!empty($family['line_qr_img'])) {
        echo "<img src='" . htmlspecialchars($family['line_qr_img']) . "' 
            width='100' height='100' 
            style='border-radius:5px; cursor:pointer;' 
            onclick=\"showSlipModal('" . htmlspecialchars($family['line_qr_img']) . "')\">";
    } else {
        echo "-";
    }

echo "</td>
        <td>";
            if (empty($family['line_group_link']) || strtolower($family['line_group_link']) === 'no' || $family['line_group_link'] === '-') {
                echo "-";
            } else {
                echo "<a href='" . htmlspecialchars($family['line_group_link']) . "' target='_blank'>LINE</a>";
            }
    echo "</td>
        <td>$pay_day_display</td>
        <td>$app_name</td>
        <td>$real_price</td>
        <td>";
    if (!empty($family['payment_img'])) {
        $raw_payment = $family['payment_img'];
        $len = mb_strlen($raw_payment, 'UTF-8');
        
        echo "<span class='payment-mask'>";
        if ($len > 3) {
            echo 'XXXXXX' . htmlspecialchars(mb_substr($raw_payment, -3, 3, 'UTF-8'));
        } elseif ($len > 0) {
            echo htmlspecialchars($raw_payment);
        } else {
            echo "-";
        }
        echo "</span>";
    } else {
        echo "-";
    }
    echo "</td>
        <td>$note</td>
    </tr>";
} else {
    echo "<tr>
        <td colspan='12' class='text-center text-muted fw-bold bg-light py-3'>
            <i class='fa-solid fa-circle-info'></i> ไม่พบข้อมูลกลุ่ม
        </td>
    </tr>";
}
?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <h3 class="mb-0">ข้อมูลสมาชิก (ที่ถูกลบ)</h3>
            <div class="d-flex gap-2">
                <input type="text" class="form-control search-name" style="max-width: 250px;" placeholder="ค้นหา...">
            </div>
        </div>

        <div class="table-responsive">
            <table id="memberTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>เลือก</th>
                        <th>ลำดับ</th>
                        <th>ชื่อสมาชิก</th>
                        <th>อีเมล</th>
                        <th>มาจาก</th>
                        <th>จอ</th>
                        <th>อุปกรณ์</th>
                        <th>จำนวนวัน</th>
                        <th>วันเริ่มต้น</th>
                        <th>วันหมดอายุ</th>
                        <th>วันเวลาโอน</th>
                        <th>ราคา / คน / วัน</th>
                        <th>สถานะ</th>
                        <th>สถานะการจ่ายเงิน</th>
                        <th>รูปสลิป</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
if ($result_members->num_rows > 0) {
    $no = 1;
    $today = date("Y-m-d");
    while ($member = $result_members->fetch_assoc()) {

        $member_name   = !empty($member['member_name']) ? htmlspecialchars($member['member_name']) : '-';
        $email         = !empty($member['email']) ? htmlspecialchars($member['email']) : '-';
        $source        = !empty($member['source']) ? htmlspecialchars($member['source']) : '-';
        $screen        = !empty($member['screen']) ? htmlspecialchars($member['screen']) : '-';
        $device        = !empty($member['device']) ? htmlspecialchars($member['device']) : '-';
        $days          = isset($member['days']) && $member['days'] !== '' ? htmlspecialchars($member['days']) : '-';
        
        $start_date    = (!empty($member['start_date']) && $member['start_date'] !== '0000-00-00') ? date("d/m/Y", strtotime($member['start_date'])) : '-';
        $expire_date   = (!empty($member['expire_date']) && $member['expire_date'] !== '0000-00-00') ? date("d/m/Y", strtotime($member['expire_date'])) : '-';
        
        $status = (!empty($member['status'])) ? ($member['status'] === 'admin' ? 'แอดมิน' : ($member['status'] === 'user' ? 'ผู้ใช้งาน' : htmlspecialchars($member['status']))) : '-';
        $pay_status = (!empty($member['pay_status'])) ? ($member['pay_status'] === 'paid' ? 'จ่ายแล้ว' : ($member['pay_status'] === 'unpaid' ? 'ยังไม่ได้จ่าย' : htmlspecialchars($member['pay_status']))) : '-';

        $slip_img = !empty($member['slip_img'])
            ? "<img src='" . htmlspecialchars($member['slip_img']) . "' width='80' height='80' style='border-radius:5px; cursor:pointer;' onclick=\"showSlipModal('" . htmlspecialchars($member['slip_img']) . "')\">"
            : "-";

        $price_per_day = isset($member['price_per_day']) && $member['price_per_day'] !== '' ? htmlspecialchars($member['price_per_day']) : '-';

        $expire_date_db = $member['expire_date'];
        $row_class = '';
        if (!empty($expire_date_db) && $expire_date_db !== '0000-00-00' && $expire_date_db <= $today) {
            $row_class = 'blink-red';
        }
        ?>

                    <tr class="<?= $row_class ?>">
                        <td><input type="checkbox" class="row-check"></td>
                        <td><?= $no ?></td>
                        <td><?= $member_name ?></td>
                        <td><?= $email ?></td>
                        <td><?= $source ?></td>
                        <td><?= $screen ?></td>
                        <td><?= $device ?></td>
                        <td><?= $days ?></td>
                        <td><?= $start_date ?></td>
                        <td><?= $expire_date ?></td>
                        <td><?= (!empty($member['transfer_time']) && $member['transfer_time'] !== '0000-00-00 00:00:00') ? date("d/m/Y H:i", strtotime($member['transfer_time'])) : '-' ?>
                        </td>
                        <td><?= $price_per_day ?></td>
                        <td><?= $status ?></td>
                        <td><?= $pay_status ?></td>
                        <td><?= $slip_img ?></td>
                    </tr>
                    <?php
        $no++;
    }
    $noResultDisplay = "none";
} else {
    $noResultDisplay = "table-row";
}
?>
                    <tr id="noResult" style="display: <?= $noResultDisplay ?>;">
                        <td colspan="16" class="text-center text-muted fw-bold bg-light py-3">
                            <i class="fa-solid fa-circle-info"></i> ไม่พบข้อมูลที่ค้นหา
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="slipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body text-center p-0">
                    <img id="slipModalImg" src="" class="img-fluid rounded" alt="Slip Preview">
                </div>
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3 bg-light rounded-circle"
                    data-bs-dismiss="modal"></button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(".search-name").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        var visibleRows = 0;

        $("#memberTable tbody tr").each(function() {
            if ($(this).attr("id") === "noResult" || $(this).find(".modal-dialog").length > 0) return;
            var match = $(this).text().toLowerCase().indexOf(value) > -1;
            $(this).toggle(match);
            if (match) visibleRows++;
        });

        if (visibleRows === 0) {
            $("#noResult").show();
        } else {
            $("#noResult").hide();
        }
    });

    function showSlipModal(imageSrc) {
        const modalImg = document.getElementById('slipModalImg');
        modalImg.src = imageSrc;
        const modal = new bootstrap.Modal(document.getElementById('slipModal'));
        modal.show();
    }

    document.querySelectorAll("#memberTable tbody tr").forEach(row => {
        if (row.id === "noResult") return;
        row.addEventListener("click", function(e) {
            if (e.target.tagName === "BUTTON" || e.target.tagName === "A" || e.target.closest(
                '.modal') || e.target.closest('.row-check')) return;
            this.classList.toggle("checked");
            const checkbox = this.querySelector(".row-check");
            if (checkbox) checkbox.checked = this.classList.contains("checked");
        });
    });

    document.querySelectorAll("#memberTable .row-check").forEach(checkbox => {
        checkbox.addEventListener("change", function() {
            const row = this.closest("tr");
            row.classList.toggle("checked", this.checked);
        });
    });
    </script>
</body>

</html>