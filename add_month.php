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
    $member_id   = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;
    $family_id   = isset($_POST['family_id']) ? intval($_POST['family_id']) : 0;
    $app_id      = isset($_POST['app_id']) ? intval($_POST['app_id']) : 0;
    $start_date  = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $expire_date = !empty($_POST['expire_date']) ? $_POST['expire_date'] : null;

    // ตรวจสอบว่ามีข้อมูล member_id หรือไม่
    if ($member_id > 0) {
        
        // อัปเดตข้อมูล: วันเริ่ม, วันหมดอายุ, เปลี่ยนสถานะเป็นจ่ายแล้ว และตั้งเวลาโอนเงินเป็นเวลาปัจจุบัน
        $sql = "UPDATE family_members 
                SET start_date = ?, 
                    expire_date = ?, 
                    pay_status = 'paid', 
                    transfer_time = NOW() 
                WHERE member_id = ?";
                
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("ssi", $start_date, $expire_date, $member_id);
            if ($stmt->execute()) {
                // อัปเดตสำเร็จ
            } else {
                // กรณีเกิดข้อผิดพลาดในการอัปเดต (สามารถเพิ่มการแจ้งเตือนได้)
                error_log("Update Error: " . $stmt->error);
            }
            $stmt->close();
        } else {
            error_log("Prepare Error: " . $conn->error);
        }
    }

    // ทำการ Redirect กลับไปหน้าที่ส่งมา (หน้าข้อมูลกลุ่ม)
    if (!empty($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        // กรณีไม่มี HTTP_REFERER ให้กลับไปหน้า Default
        header("Location: detail_application.php?family_id=$family_id&app_id=$app_id");
    }
    exit();
    
} else {
    // ถ้าไม่ได้เข้ามาด้วย POST ให้เด้งกลับ
    header("Location: menu.php");
    exit();
}
?>