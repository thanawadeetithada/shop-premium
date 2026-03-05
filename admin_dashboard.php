<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$sql = "
    SELECT a.*, 
           COUNT(f.family_id) AS total_groups
    FROM applications a
    LEFT JOIN families f 
           ON a.app_id = f.app_id 
           AND f.deleted_at IS NULL
    WHERE a.deleted_at IS NULL
    GROUP BY a.app_id
";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="apple-mobile-web-app-title" content="App Premium">
    <meta name="application-name" content="App Premium">
    <meta name="theme-color" content="#96a1cd">
    <title>หน้าหลัก</title>
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
        width: 48%;
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

    .btn-add {
        width: fit-content;
    }

    .text-add {
        text-align: end;
    }

    /* CSS สำหรับ Modal เหมือนหน้าลืมรหัสผ่าน */
    .input-email {
        width: 90%;
        padding: 8px;
        margin: 5px auto;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 16px;
        display: block;
    }

    .input-email:focus {
        border-color: #8c99bc;
        outline: none;
        box-shadow: 0 0 5px rgba(140, 153, 188, 0.5);
    }

    .btn-send {
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .btn-send button {
        width: auto;
        margin: 5px;
    }

    .btn-custom {
        background-color: #8c99bc;
        border: none;
        transition: 0.3s;
    }

    .btn-custom:hover {
        background-color: #6f7ca1;
    }

    .modal-content {
        background: rgba(255, 255, 255, 0.97);
        border-radius: 20px;
        transition: transform 0.25s ease-in-out;
        animation: fadeInUp 0.3s ease-in-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal.fade .modal-dialog {
        transform: translateY(-30px);
        transition: transform 0.3s ease-out;
    }

    .modal.show .modal-dialog {
        transform: translateY(0);
    }

    .text-muted {
        color: #333 !important;
    }

    /* CSS สำหรับ Loading Overlay */
    #loadingOverlay {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #loadingOverlay .overlay-bg {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
    }

    #loadingOverlay .overlay-spinner {
        position: relative;
        text-align: center;
    }

    /* เพิ่มต่อท้ายในแท็ก <style> เดิมของคุณ */
    .blink-btn {
        animation: blink-animation 1.5s steps(5, start) infinite;
        -webkit-animation: blink-animation 1.5s steps(5, start) infinite;
        background-color: #0d6efd;
        border-color: #0d6efd;
        box-shadow: 0 0 10px rgba(13, 110, 253, 0.8);
    }

    @keyframes blink-animation {
        to {
            visibility: hidden;
        }
    }

    @-webkit-keyframes blink-animation {
        to {
            visibility: hidden;
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
                <li><a href="planner.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-user"></i> Planner</a></li>
                <li><a href="user_management.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-user-tie"></i> ข้อมูลผู้ใช้งาน</a></li>
            </ul>
        </div>
    </div>

    <div class="container-wrapper">
        <div class="container">
            <h2>แอปพลิเคชัน</h2>

            <div class="row mb-4">
                <div class="col-6 d-flex justify-content-start align-items-center gap-2">
                    <?php 
                        $today = date('d'); // ดึงวันที่ปัจจุบัน (01-31)
                        $blinkClass = ($today == '01' || $today == '15') ? 'blink-btn' : '';
                    ?>
                    <a class="btn btn-primary <?php echo $blinkClass; ?>" data-bs-toggle="modal"
                        data-bs-target="#sendtoEmailModal">
                        Send SQL to Email
                    </a>
                </div>
                <div class="col-6 d-flex justify-content-end align-items-center gap-2">
                    <input type="text" id="searchInput" class="form-control" style="max-width: 300px;"
                        placeholder="ค้นหาแอปพลิเคชัน...">
                    <a href="add_applications.php" class="btn btn-warning btn-add px-4 mb-0">เพิ่ม</a>
                </div>
            </div>

            <div class="row" id="appContainer">
                <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3 app-card">
                    <a href="detail_application.php?id=<?php echo $row['app_id']; ?>" class="text-decoration-none">
                        <div class="card text-center p-4 text-white m-0">
                            <h4 class="app-name" style="font-weight: bold;">
                                <?php echo htmlspecialchars($row['app_name']); ?></h4>
                            <p style="font-weight: bold;margin-bottom: 0px;">ราคา
                                <?php echo htmlspecialchars($row['real_price']); ?> ฿</p>
                            <p style="font-weight: bold;">จำนวน <?php echo $row['total_groups']; ?> กลุ่ม</p>
                        </div>
                    </a>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <div class="col-12">
                    <p class="text-center text-muted">ยังไม่มีแอปพลิเคชันในระบบ</p>
                </div>
                <?php endif; ?>
                <div class="col-12" id="noResultMessage" style="display: none;">
                    <p class="text-center text-muted mt-3">ไม่พบแอปพลิเคชันที่ค้นหา</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="sendtoEmailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header justify-content-center border-0 pt-4">
                    <h5 class="modal-title font-weight-bold" style="font-weight: bold;">ส่งไฟล์ Backup ไปยัง Email</h5>
                </div>
                <div class="modal-body text-center px-4 pb-4 pt-2">
                    <p class="text-muted mb-3">
                        กรุณากรอกอีเมลที่คุณต้องการจะส่งข้อมูลไป<br>(ระบบจะส่งไปยังอีเมลหลักอัตโนมัติด้วย)</p>

                    <form id="sendtoEmailForm">
                        <input type="email" id="forgotEmail" name="email" class="input-email"
                            placeholder="กรุณาใส่อีเมลของคุณ" autocomplete="off">

                        <div class="btn-send pt-2">
                            <button type="submit" id="sendLinkBtn" class="btn btn-custom text-white px-4 py-2"
                                style="font-weight: bold;">ส่งไฟล์ SQL</button>

                            <button type="button" class="btn btn-outline-secondary px-4 py-2" style="font-weight: bold;"
                                data-bs-dismiss="modal">ยกเลิก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="messageModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header justify-content-center border-0 pt-4">
                    <h5 class="modal-title font-weight-bold" id="messageModalTitle" style="font-weight: bold;">
                        แจ้งเตือน
                    </h5>
                </div>
                <div class="modal-body text-center px-4 pb-4 pt-2">
                    <p class="text-muted mb-3" id="messageModalText"></p>
                    <div class="btn-send pt-2">
                        <button type="button" id="messageModalBtn" class="btn px-4 py-2 text-white"
                            style="font-weight: bold;" data-bs-dismiss="modal">
                            ตกลง
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="loadingOverlay" class="d-none">
        <div class="overlay-bg"></div>
        <div class="overlay-spinner">
            <div class="spinner-border text-light" role="status" style="width: 4rem; height: 4rem;">
            </div>
            <div class="text-light mt-3 fs-5">กำลังส่งอีเมล...</div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $("#searchInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            var hasVisibleCards = false;

            $(".app-card").each(function() {
                var appName = $(this).find(".app-name").text().toLowerCase();
                if (appName.indexOf(value) > -1) {
                    $(this).show();
                    hasVisibleCards = true;
                } else {
                    $(this).hide();
                }
            });

            if (!hasVisibleCards && value !== "") {
                $("#noResultMessage").show();
            } else {
                $("#noResultMessage").hide();
            }
        });

        $('#forgotEmail').keypress(function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#sendLinkBtn').click();
            }
        });

        $('#sendtoEmailForm').on('submit', function(e) {
            e.preventDefault();
            const email = $('#forgotEmail').val().trim();

            $('#loadingOverlay').removeClass('d-none');

            $.ajax({
                url: 'backup_mail.php',
                type: 'POST',
                data: {
                    email: email
                },
                dataType: 'json',
                success: function(res) {
                    $('#sendtoEmailModal').modal('hide');
                    $('#forgotEmail').val('');
                    showMessageModal(res.status, res.message);
                },
                error: function() {
                    showMessageModal('danger', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์!');
                },
                complete: function() {
                    $('#loadingOverlay').addClass('d-none');
                }
            });
        });

        $('#sendtoEmailModal').on('hidden.bs.modal', function() {
            $('#forgotEmail').val('');
        });

        function showMessageModal(type, message) {
            const title = (type === 'success') ? '✅ สำเร็จ' : '⚠️ แจ้งเตือน';
            const btnColor = (type === 'success') ? '#28a745' : '#dc3545';

            $('#messageModalTitle').text(title);
            $('#messageModalText').text(message);
            $('#messageModalBtn').css('background-color', btnColor);

            $('#messageModal').modal('show');
        }
    });
    </script>
</body>

</html>