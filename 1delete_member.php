<?php
session_start();
require 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'ไม่อนุญาต']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;

    if ($member_id > 0) {
        $deleted_at = date('Y-m-d H:i:s');
        $sql = "UPDATE family_members SET deleted_at = ? WHERE member_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $deleted_at, $member_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ถูกต้อง']);
    }
}
?>
