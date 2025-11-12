<?php
include 'db.php';
session_start();

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$member_id = $_GET['member_id'] ?? 0;
$family_id = $_GET['family_id'] ?? 0;
$app_id = $_GET['app_id'] ?? 0;

$sql = "UPDATE family_members SET deleted_at = NOW() WHERE member_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();

header("Location: dashboard_family.php?family_id=$family_id&app_id=$app_id");
exit();
