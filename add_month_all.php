<?php
session_start();
include 'db.php'; // ตรวจสอบชื่อไฟล์เชื่อมต่อ DB ให้ตรงกับระบบของคุณ

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $family_id = $_POST['family_id'];
    $app_id = $_POST['app_id'];
    $start_date = $_POST['start_date'];
    $expire_date = $_POST['expire_date'];
    $from = $_POST['from'] ?? '';

    // อัปเดตข้อมูลเฉพาะคนที่มี family_id ตรงกัน และมี status เป็น 'user'
    $sql = "UPDATE family_members 
            SET start_date = ?, expire_date = ? 
            WHERE family_id = ? AND status = 'user'";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $start_date, $expire_date, $family_id);

    if ($stmt->execute()) {
        // แก้ไขบรรทัดนี้: ให้กลับไปที่หน้า dashboard_family.php
        $redirect_url = "dashboard_family.php?family_id=" . $family_id . "&app_id=" . $app_id;
        
        if (!empty($from)) {
            $redirect_url .= "&from=" . urlencode($from);
        }
        
        header("Location: " . $redirect_url);
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>