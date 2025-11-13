<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
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
    <title>เมนู</title>
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="icons/icon-192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Prompt', sans-serif;
        background: url('bg/sky.png') no-repeat center center;
        background-size: cover;
        background-attachment: fixed;
        min-height: 100vh;
        padding-bottom: 50px;
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

    .menu-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
        transition: 0.3s;
    }

    .menu-card:hover {
        transform: translateY(-3px);
    }

    .menu-title {
        font-weight: 600;
        color: #d32f2f;
        font-size: 1.2rem;
    }

    .menu-content {
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .action-btns {
        text-align: right;
        margin-top: 10px;
    }

    .action-btns i {
        cursor: pointer;
        font-size: 1.2rem;
        margin-left: 10px;
        color: #444;
        transition: color 0.2s;
    }

    .action-btns i:hover {
        color: #d32f2f;
    }

    .header-img {
        width: 100%;
        border-radius: 20px;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .menu-title {
            font-size: 1rem;
        }

        .menu-content {
            font-size: 0.9rem;
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
        <div class="container my-4 text-center">
            <div class="menu-card mx-auto" style="max-width: 500px;">
                <h2 class="text-center text-danger fw-bold my-3">🌸 BEST FLOWER 🌻</h2>
            </div>

            <div class="row">
                <?php
                $apps = [
                    ["name" => "🌸หาร Youtube Premium🌸", "info" => "เดือนละ 79฿\n\n#หารยูทูปพรีเมี่ยม #หารYouTubePremium #หารYouTube
#หารยูทูปพรีเมี่ยมราคาถูก"],
                    ["name" => "หาร Disney+ จอไม่ชน🌻", "info" => "🌸จอละ 85฿ / 30 วัน\n🌸จอละ 30฿ / 7วัน\n🌸จอละ 10฿ / วัน\n\n#หาdisneyplus #หารdisneyplus #ดิสนีย์พลัส #หารดิสนีย์พลัส #DisneyPlusหาร"],
                    ["name" => "หาร WE TV🌸", "info" => "🌸 ส่วนตัว 40฿ / 30 วัน\n\n#หารwetv #หารวีทีวี #หาwetv #หาวีทีวี #หารwetvIราคาถูก"],
                    ["name" => "หาร IQIYI🌸", "info" => "🌸 ส่วนตัว 35฿ / 30 วัน\n\n#หารiQiyi #หารอ้ายฉี้อี้ #หารอ้ายฉีอี้ #หาiqiyi #หารiqiyipremium #หารIQIYIราคาถูก"],                   
                    ["name" => "หาร VIU🌻", "info" => "🌸 ส่วนตัว 40฿ / 30 วัน\n\n#หาviu #หารviuพรีเมี่ยม #หารviu #หารviuราคาถูก #หารviupremiumราคาถูก"],
                    ["name" => "หาร NETFLIX🌻", "info" => "จอละ 135฿ / 30 วัน\nจอละ 40฿ / 7วัน\nจอละ 15฿ / วัน\n\n#หาnetflix #หารเน็ตฟลิกซ์ #หารเน็ตฟลิกซ์ราคาถูก #หารnetflix"],
                    ["name" => "หาร PRIME VIDEO", "info" => "จอละ 59฿ / 30 วัน"],
                    ["name" => "หาร HBO", "info" => "จอละ 80฿ / 30 วัน\nจอละ 29฿/7วัน"],  
                    ["name" => "หาร BUGABOO.TV", "info" => "จอละ 110฿ / 30 วัน\nจอละ 35฿/7วัน\nจอละ 15฿/วัน"],
                    ["name" => "หาร 3PLUS", "info" => "จอละ 65฿ / 30 วัน\nจอละ 30฿/7วัน\nจอละ 9฿/วัน"],
                ];

                foreach ($apps as $a) {
                    $id = "text-" . md5($a["name"]);
                    echo '
                    <div class="col-md-4">
                        <div class="menu-card" id="card-' . md5($a["name"]) . '">
                            <div class="menu-title" id="title-' . md5($a["name"]) . '">' . htmlspecialchars($a["name"]) . '</div>
                            <div class="menu-content" id="' . $id . '">' . nl2br(htmlspecialchars($a["info"])) . '</div>
                            <div class="action-btns">
                                <i class="fa-solid fa-copy" onclick="copyText(\'' . md5($a["name"]) . '\')"></i>
                            </div>
                        </div>
                    </div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script>
    function copyText(id) {
        const titleEl = document.getElementById('title-' + id);
        const contentEl = document.getElementById('text-' + id);

        if (!titleEl || !contentEl) {
            alert("ไม่พบข้อมูลที่จะคัดลอก ❌");
            return;
        }

        const text = titleEl.innerText + "\n" + contentEl.innerText;

        // ✅ วิธีใหม่: ใช้ textarea ชั่วคราวเพื่อให้รองรับทุกเบราว์เซอร์
        const tempInput = document.createElement("textarea");
        tempInput.value = text;
        document.body.appendChild(tempInput);
        tempInput.select();
        tempInput.setSelectionRange(0, 99999); // สำหรับมือถือ
        document.execCommand("copy");
        document.body.removeChild(tempInput);

    }
    </script>

</body>

</html>