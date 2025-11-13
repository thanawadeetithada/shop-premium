<?php
require_once 'db.php';
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

    .navbar {
        padding: 20px;
    }

    .menu-card {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        padding: 10px;
        margin-bottom: 20px;
        transition: 0.3s;
        max-width: 350px;
        margin-left: auto;
        margin-right: auto;
        max-width: 250px;
    }

    .menu-card:hover {
        transform: translateY(-3px);
    }

    .menu-title {
        font-weight: 600;
        color: #d32f2f;
    }

    .header-img {
        width: 90%;
        height: auto;
        border-radius: 15px;
        margin-top: 10px;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
    }

    @media (max-width: 768px) {
        .menu-title {
            font-size: 1rem;
        }
    }


    .position-relative {
        display: inline-block;
    }

    .btn-danger i {
        color: white;
        font-size: 1.1rem;
    }

    .btn-danger:hover {
        background-color: #c62828;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark px-3">
        <div class="d-flex w-100 justify-content-between align-items-center">
            <i class="fa-solid fa-bars text-white" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"
                style="cursor: pointer;"></i>
        </div>
    </nav>

    <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="sidebarMenu">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">รายการ</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="list-unstyled">
                <li><a href="index.php" class="text-white text-decoration-none d-block py-2"><i
                            class="fa-solid fa-list-check"></i> เมนู</a></li>
            </ul>
        </div>
    </div>

    <div class="container my-4 text-center">
        <div class="menu-card">
            <h2 class="text-center text-danger fw-bold my-3">🌸 รายการ 🌻</h2>
        </div>
        <div class="col-12 my-3 text-center">
            <div class="position-relative d-inline-block">
                <img src="index/menu.png" alt="รายการ" class="img-fluid header-img">

                <a href="index/menu.png" download
                    class="btn btn-danger position-absolute top-0 end-0 m-2 rounded-circle shadow"
                    title="ดาวน์โหลดรูปภาพ">
                    <i class="fa-solid fa-download"></i>
                </a>
            </div>
        </div>

    </div>
</body>

</html>