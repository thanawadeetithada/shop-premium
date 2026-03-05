<?php
session_start();
include 'db.php';

// ตรวจสอบสิทธิ์แอดมิน
if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจาก Form
    $family_id = isset($_POST['family_id']) ? intval($_POST['family_id']) : 0;
    $app_id    = isset($_POST['app_id']) ? intval($_POST['app_id']) : 0;
    $pay_day   = !empty($_POST['pay_day']) ? $_POST['pay_day'] : null;

    // ตรวจสอบว่ามีข้อมูล family_id และวันที่ที่ถูกต้องหรือไม่
    if ($family_id > 0 && $pay_day) {
        
        // อัปเดตข้อมูลรอบชำระใหม่ลงในตาราง families
        $sql = "UPDATE families SET pay_day = ? WHERE family_id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("si", $pay_day, $family_id);
            
            if ($stmt->execute()) {
                // อัปเดตสำเร็จ
            } else {
                // กรณีเกิดข้อผิดพลาดในการอัปเดต (เก็บ log ไว้ดู error ได้)
                error_log("Update Family Payday Error: " . $stmt->error);
            }
            $stmt->close();
        } else {
            error_log("Prepare Error: " . $conn->error);
        }
    }

    // ทำการ Redirect กลับไปหน้าที่กดส่งมา (หน้าข้อมูลกลุ่ม)
    if (!empty($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        // กรณีไม่มี HTTP_REFERER ให้กลับไปหน้า Default
        header("Location: detail_application.php?family_id=$family_id&app_id=$app_id");
    }
    exit();
    
} else {
    // ถ้าไม่ได้เข้ามาด้วย POST (เช่น พิมพ์ URL ตรงๆ) ให้เด้งกลับหน้าเมนู
    header("Location: menu.php");
    exit();
}
?>