<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    exit('Permission denied');
}

header("Content-Type: application/sql");
header("Content-Disposition: attachment; filename=backup_" . date("Y-m-d_H-i-s") . ".sql");

echo "-- Backup Database: app_premium\n";
echo "-- Generated on: " . date("Y-m-d H:i:s") . "\n\n";

$conn->set_charset("utf8");

// ✅ ดึงรายชื่อตารางทั้งหมด
$resultTables = $conn->query("SHOW TABLES");

while ($rowTable = $resultTables->fetch_array()) {

    $table = $rowTable[0];

    echo "\n\n-- ----------------------------\n";
    echo "-- Structure for table `$table`\n";
    echo "-- ----------------------------\n\n";

    // ✅ โครงสร้างตาราง
    $resultCreate = $conn->query("SHOW CREATE TABLE `$table`");
    $rowCreate = $resultCreate->fetch_assoc();
    echo $rowCreate["Create Table"] . ";\n\n";

    // ✅ ข้อมูลในตาราง
    echo "-- ----------------------------\n";
    echo "-- Data for table `$table`\n";
    echo "-- ----------------------------\n\n";

    $resultData = $conn->query("SELECT * FROM `$table`");
    while ($rowData = $resultData->fetch_assoc()) {

        $columns = array_keys($rowData);
        $values = array_map(function ($value) use ($conn) {
            if ($value === null) return "NULL";
            return "'" . $conn->real_escape_string($value) . "'";
        }, array_values($rowData));

        echo "INSERT INTO `$table` (`" . implode("`,`", $columns) . "`) VALUES (" . implode(",", $values) . ");\n";
    }
}

exit;
?>
