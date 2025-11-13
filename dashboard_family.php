<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$back_url = $_GET['from'] ?? 'index.php';

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
                AND deleted_at IS NULL
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
    <title>ข้อมูลกลุ่ม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
    body {
        font-family: 'Prompt', sans-serif;
        height: 100vh;
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
        background-color: #ffffff;
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
    <div class="text-start mb-3">
        <a href="<?= htmlspecialchars($back_url) ?>" class="btn">
            <i class="fa-solid fa-arrow-left"></i> กลับ
        </a>

    </div>
    <div class="card">
        <div class="header-card">
            <h3 class="text-left">ข้อมูลกลุ่ม</h3>
        </div>
        <br>
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
                        <th>จัดการ</th>
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
        echo "<img src='" . htmlspecialchars($family['payment_img']) . "' 
            width='100' height='100' 
            style='border-radius:5px; cursor:pointer;' 
            onclick=\"showSlipModal('" . htmlspecialchars($family['payment_img']) . "')\">";
    } else {
        echo "-";
    }
    echo "</td>
        <td>$note</td>
        <td>
             <a href='edit_family.php?family_id=" . $family['family_id'] . "&app_id=" . $app_id . "&from=" . $back_url . "' 
       class='btn btn-warning btn-sm'>
        <i class='fa-solid fa-pencil'></i>
    </a>
            &nbsp;
            <button class='btn btn-danger btn-sm'
                data-bs-toggle='modal'
                data-bs-target='#deleteFamilyModal'>
                <i class='fa-regular fa-trash-can'></i>
            </button>
        </td>
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
        <div class="header-card">
            <h3 class="text-left">ข้อมูลสมาชิก</h3>
            <div class="search-add">
                <div class="tab-func">
                    <button type="button" class="btn btn-primary"
                        onclick="window.location.href='add_member.php?family_id=<?php echo $family_id; ?>&app_id=<?php echo $app_id; ?>'">
                        เพิ่มสมาชิก
                    </button>
                </div>
                <div class="tab-func">
                    <input type="text" class="form-control search-name" placeholder="ค้นหา...">
                </div>
            </div>
        </div>
        <br>
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
                        <th>จัดการ</th>
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
        $start_date    = (!empty($member['start_date']) && $member['start_date'] !== '0000-00-00')
                            ? date("d/m/Y", strtotime($member['start_date'])) : '-';
       $expire_date = (!empty($member['expire_date']) && $member['expire_date'] !== '0000-00-00')
               ? date("d/m/Y", strtotime($member['expire_date'])) 
               : '-';
        $pay_day_display = (!empty($family['pay_day']) && $family['pay_day'] !== '0000-00-00')
                            ? date("d/m/Y", strtotime($family['pay_day'])) : '-';

        if (!empty($member['status'])) {
            if ($member['status'] === 'admin') {
                $status = 'แอดมิน';
            } elseif ($member['status'] === 'user') {
                $status = 'ผู้ใช้งาน';
            } else {
                $status = htmlspecialchars($member['status']);
            }
        } else {
            $status = '-';
        }

        if (!empty($member['pay_status'])) {
            if ($member['pay_status'] === 'paid') {
                $pay_status = 'จ่ายแล้ว';
            } elseif ($member['pay_status'] === 'unpaid') {
                $pay_status = 'ยังไม่ได้จ่าย';
            } else {
                $pay_status = htmlspecialchars($member['pay_status']);
            }
        } else {
            $pay_status = '-';
        }

        $slip_img = !empty($member['slip_img'])
            ? "<img src='" . htmlspecialchars($member['slip_img']) . "' 
                width='80' height='80' 
                style='border-radius:5px; cursor:pointer;' 
                onclick=\"showSlipModal('" . htmlspecialchars($member['slip_img']) . "')\">"
                : "-";

        $price_per_day = isset($member['price_per_day']) && $member['price_per_day'] !== ''
            ? htmlspecialchars($member['price_per_day'])
            : '-';

        $expire_date_db = $member['expire_date'];
        $row_class = '';

        if (!empty($expire_date_db) && $expire_date_db !== '0000-00-00') {
            $expire_timestamp = strtotime($expire_date_db);
            $today_timestamp = strtotime($today);

            if (!empty($expire_date_db) && $expire_date_db !== '0000-00-00' && $expire_date_db <= $today) {
            $row_class = 'blink-red';
        }
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
                        <td><?= (!empty($member['start_date']) && $member['start_date'] !== '0000-00-00') ? date("d/m/Y", strtotime($member['start_date'])) : '-' ?>
                        </td>
                        <td><?= (!empty($member['expire_date']) && $member['expire_date'] !== '0000-00-00') ? date("d/m/Y", strtotime($member['expire_date'])) : '-' ?>
                        </td>
                        <td>
                            <?php
                                if (!empty($member['transfer_time']) && $member['transfer_time'] !== '0000-00-00 00:00:00') {
                                    echo date("d/m/Y H:i", strtotime($member['transfer_time']));
                                } else {
                                    echo '-';
                                }
                    ?>
                        </td>

                        <td><?= $price_per_day ?></td>
                        <td><?= $status ?></td>
                        <td><?= $pay_status ?></td>
                        <td><?= $slip_img ?></td>
                        <td>
                            <a href="edit_member.php?member_id=<?= $member['member_id'] ?>&family_id=<?= $family_id ?>&app_id=<?= $app_id ?>&from=<?= $back_url ?>"
                                class="btn btn-warning btn-sm">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                            &nbsp;
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                data-bs-target="#deleteModal<?= $member['member_id'] ?>">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>

                    <div class="modal fade" id="deleteModal<?= $member['member_id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">

                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">ยืนยันการลบสมาชิก</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body text-center py-4">
                                    คุณต้องการลบสมาชิก
                                    <b><?= htmlspecialchars($member['member_name']) ?></b>
                                    ใช่หรือไม่?
                                </div>

                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">ยกเลิก</button>
                                    <a href="delete_member.php?member_id=<?= $member['member_id'] ?>&family_id=<?= $family_id ?>&app_id=<?= $app_id ?>"
                                        class="btn btn-danger">ลบข้อมูล</a>
                                </div>

                            </div>
                        </div>
                    </div>
                    <?php
                        $no++;
                    }
                    $noResultDisplay = "none";
                } else {
                    $noResultDisplay = "table-row";
                }
                ?>
                    <tr id="noResult" style="display: <?= $noResultDisplay ?>;">
                        <td colspan="13" class="text-center text-muted fw-bold bg-light py-3">
                            <i class="fa-solid fa-circle-info"></i> ไม่พบข้อมูลที่ค้นหา
                        </td>
                    </tr>
                </tbody>
                <tr id="noResult" style="display:none;">
                    <td colspan="13" class="text-center text-muted fw-bold bg-light py-3">
                        <i class="fa-solid fa-circle-info"></i> ไม่พบข้อมูลที่ค้นหา
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="modal fade" id="deleteFamilyModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">ยืนยันการลบกลุ่ม</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body py-4">
                    คุณต้องการลบกลุ่ม
                    <b><?php echo htmlspecialchars($family['family_name']); ?></b>
                    และสมาชิกทั้งหมดในกลุ่มนี้ ใช่หรือไม่?
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <a href="delete_family.php?family_id=<?php echo $family['family_id']; ?>&app_id=<?php echo $app_id; ?>"
                        class="btn btn-danger">ลบข้อมูล</a>
                </div>
            </div>
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
            if (e.target.tagName === "BUTTON" || e.target.tagName === "A" || e.target.closest('.modal'))
                return;

            this.classList.toggle("checked");
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