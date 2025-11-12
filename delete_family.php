<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$family_id = $_GET['family_id'] ?? 0;
$app_id = $_GET['app_id'] ?? 0;

if (!$family_id) {
    die("ไม่พบข้อมูลกลุ่มที่จะลบ");
}

$check = $conn->prepare("SELECT * FROM families WHERE family_id = ? AND deleted_at IS NULL");
$check->bind_param("i", $family_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    die("ไม่พบกลุ่มนี้ หรือถูกลบไปแล้ว");
}

$conn->begin_transaction();
try {
    $sql1 = "UPDATE families SET deleted_at = NOW() WHERE family_id = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $family_id);
    $stmt1->execute();

    $sql2 = "UPDATE family_members SET deleted_at = NOW() WHERE family_id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $family_id);
    $stmt2->execute();

    $sql3 = "UPDATE payments SET deleted_at = NOW() WHERE family_id = ?";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("i", $family_id);
    $stmt3->execute();

    $conn->commit();

    echo "<script>
        window.location.href = 'admin_dashboard.php';
    </script>";
} catch (Exception $e) {
    $conn->rollback();
    echo "<script>
        alert('เกิดข้อผิดพลาด: " . addslashes($e->getMessage()) . "');
        window.history.back();
    </script>";
}

$conn->close();
?>
