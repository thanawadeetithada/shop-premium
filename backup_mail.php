<?php
ob_start();
session_start();
require 'db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Ifsnop\Mysqldump\Mysqldump;

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Bangkok');
ob_clean();

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    echo json_encode(["status" => "danger", "message" => "ไม่มีสิทธิ์ใช้งาน"]);
    exit;
}

try {
    // รับค่า email (แม้จะเป็นค่าว่าง)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $input_email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $default_email = 'baifern24260@gmail.com'; 

        // จัดการรายชื่ออีเมลที่จะส่ง
        $target_emails = [];
        if (!empty($input_email)) {
            $target_emails[] = $input_email; // เมลที่ระบุเพิ่ม
            if ($input_email !== $default_email) {
                $target_emails[] = $default_email; // เมลหลัก (ส่งคู่กัน)
            }
            $log_display = $input_email;
        } else {
            $target_emails[] = $default_email; // กรณีปล่อยว่าง ส่งเข้าเมลหลักอย่างเดียว
            $log_display = "อีเมลหลักระบบ";
        }

        $dbHost = 'sql304.infinityfree.com';
        $dbUser = 'if0_40395584';
        $dbPass = 'paYICvuRe6IwG';
        $dbName = 'if0_40395584_app_premium';

        $dateString = date('Y-m-d_H-i-s');
        $backupFileName = "backup_{$dbName}_{$dateString}.sql";
        $backupFilePath = __DIR__ . '/' . $backupFileName;
        
        // สร้างไฟล์ Backup
        try {
            $dump = new Mysqldump("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
            $dump->start($backupFilePath);
        } catch (\Exception $e) {
            echo json_encode(["status" => "danger", "message" => "Backup Error: " . $e->getMessage()]);
            exit;
        }

        if (file_exists($backupFilePath) && filesize($backupFilePath) > 0) {
            
            $mail = new PHPMailer(true);
            $mail->CharSet = "UTF-8";
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'respond.noreply@gmail.com';
            $mail->Password = 'lucagvjbtwnbxzit';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('respond.noreply@gmail.com', 'System Auto Backup');

            // วนลูปส่งอีเมลตามรายชื่อที่ตั้งไว้
            foreach ($target_emails as $address) {
                $mail->addAddress($address);
            }

            $mail->isHTML(true);
            $mail->Subject = 'สำรองฐานข้อมูล (Database Backup) ประจำวันที่ ' . date('d/m/Y');
            
            // ปรับรายละเอียดใน Email Body
            $email_list_text = implode(', ', $target_emails);
            $mail->Body = "
                <h3>ไฟล์สำรองฐานข้อมูลสำเร็จ</h3>
                <p>ระบบส่งไฟล์ฐานข้อมูล <b>{$dbName}</b> เรียบร้อยแล้ว</p>
                <ul>
                    <li><b>ส่งไปยัง:</b> {$email_list_text}</li>
                    <li><b>วันที่ทำรายการ:</b> " . date('d/m/Y H:i:s') . "</li>
                </ul>
                <p><i>โปรดเก็บไฟล์นี้ไว้ในที่ปลอดภัยเพื่อความปลอดภัยของข้อมูล</i></p>
            ";

            $mail->addAttachment($backupFilePath);
            $mail->send();

            unlink($backupFilePath); // ลบไฟล์ชั่วคราว

            echo json_encode([
                "status" => "success",
                "message" => "ส่งไฟล์ Backup ไปยัง {$log_display} สำเร็จแล้ว!"
            ]);
            exit;

        } else {
            echo json_encode(["status" => "danger", "message" => "ไฟล์ Backup ไม่มีข้อมูล"]);
            exit;
        }

    }
} catch (Exception $e) {
    if (isset($backupFilePath) && file_exists($backupFilePath)) unlink($backupFilePath);
    echo json_encode(["status" => "danger", "message" => "Mail Error: " . $mail->ErrorInfo]);
    exit;
}
?>