<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$app_id = $_GET['app_id'] ?? 0;

if (!$app_id) {
    die("ไม่พบ app_id");
}

$conn->begin_transaction();

try {
    $sql1 = "UPDATE applications SET deleted_at = NOW() WHERE app_id = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $app_id);
    $stmt1->execute();

    $sql2 = "UPDATE families SET deleted_at = NOW() WHERE app_id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $app_id);
    $stmt2->execute();

    $sql3 = "SELECT family_id FROM families WHERE app_id = ?";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("i", $app_id);
    $stmt3->execute();
    $result3 = $stmt3->get_result();

    $sql4 = "UPDATE family_members SET deleted_at = NOW() WHERE family_id = ?";
    $stmt4 = $conn->prepare($sql4);

    while ($row = $result3->fetch_assoc()) {
        $family_id = $row['family_id'];
        $stmt4->bind_param("i", $family_id);
        $stmt4->execute();
    }

    $conn->commit();
    header("Location: admin_dashboard.php?status=deleted");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}
?>
