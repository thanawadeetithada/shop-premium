<?php
require_once 'db.php';

date_default_timezone_set("Asia/Bangkok");
set_time_limit(0);

$conn->set_charset("utf8mb4");

$filename = "backup_" . date("Y-m-d_H-i-s") . ".sql";
$backupDir = __DIR__ . "/backup";
$filepath = $backupDir . "/" . $filename;

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$sqlContent  = "-- Backup Database: app_premium\n";
$sqlContent .= "-- Generated on: " . date("Y-m-d H:i:s") . "\n\n";
$sqlContent .= "SET NAMES utf8mb4;\n\n";

$resultTables = $conn->query("SHOW TABLES");

while ($rowTable = $resultTables->fetch_array()) {
    $table = $rowTable[0];

    $sqlContent .= "\n-- ----------------------------\n";
    $sqlContent .= "-- Structure for table `$table`\n";
    $sqlContent .= "-- ----------------------------\n\n";

    $resultCreate = $conn->query("SHOW CREATE TABLE `$table`");
    $rowCreate = $resultCreate->fetch_assoc();
    $sqlContent .= $rowCreate['Create Table'] . ";\n\n";

    $sqlContent .= "-- ----------------------------\n";
    $sqlContent .= "-- Data for table `$table`\n";
    $sqlContent .= "-- ----------------------------\n\n";

    $resultData = $conn->query("SELECT * FROM `$table`");
    while ($row = $resultData->fetch_assoc()) {
        $columns = array_keys($row);
        $values = array_map(function ($val) use ($conn) {
            return $val === null ? "NULL" : "'" . $conn->real_escape_string($val) . "'";
        }, array_values($row));

        $sqlContent .= "INSERT INTO `$table` (`" . implode("`,`", $columns) . "`) VALUES (" . implode(",", $values) . ");\n";
    }
}

file_put_contents($filepath, $sqlContent);


$to = "thanawadeetit@gmail.com";
$subject = "Backup Database วันที่ " . date("d/m/Y");
$message = "แนบไฟล์ Backup ฐานข้อมูลเรียบร้อยแล้วครับ";
$from = "backup@app-premium.com";

$fileContent = chunk_split(base64_encode(file_get_contents($filepath)));
$boundary = md5(time());

$headers  = "From: {$from}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

$body  = "--{$boundary}\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
$body .= $message . "\r\n\r\n";

$body .= "--{$boundary}\r\n";
$body .= "Content-Type: application/sql; name=\"{$filename}\"\r\n";
$body .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
$body .= $fileContent . "\r\n";
$body .= "--{$boundary}--";

$sendStatus = mail($to, $subject, $body, $headers);

$logText = date("Y-m-d H:i:s") . " | Send Mail: " . ($sendStatus ? "SUCCESS" : "FAILED") . PHP_EOL;
file_put_contents(__DIR__ . "/backup_log.txt", $logText, FILE_APPEND);

unlink($filepath);

if ($sendStatus) {
    echo "Backup & Send Email Success ✅";
} else {
    echo "Backup Success แต่ส่ง Email ไม่สำเร็จ ❌";
}
