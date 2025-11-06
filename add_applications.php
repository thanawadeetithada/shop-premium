<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userrole']) || $_SESSION['userrole'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$showModal = false;
$modalType = '';
$modalMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_name = $_POST['app_name'];
    $real_price = $_POST['real_price'];
    $profit = $_POST['profit'];

    $sql = "INSERT INTO applications (app_name, real_price, profit) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdd", $app_name, $real_price, $profit);

    if ($stmt->execute()) {
        $showModal = true;
        $modalType = 'success';
        $modalMessage = 'เพิ่มแอปพลิเคชันเรียบร้อยแล้ว!';
    } else {
        $showModal = true;
        $modalType = 'danger';
        $modalMessage = 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล';
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>เพิ่มแอปพลิเคชัน</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    font-family: 'Prompt', sans-serif;
    background: url('bg/sky.png') no-repeat center center/cover;
    height: 100vh;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
}

.container {
    max-width: 600px;
}

.card {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    padding: 30px;
}

h3 {
    font-weight: bold;
    color: #333;
}

.btn-success {
    background-color: #8c99bc;
    border: none;
    transition: 0.3s;
}

.btn-success:hover {
    background-color: #6f7ca1;
}

.btn-secondary {
    background-color: #999;
    border: none;
}
</style>
</head>
<body>
<div class="container">
    <div class="card shadow-lg">
        <h3 class="text-center mb-4">เพิ่มแอปพลิเคชันใหม่</h3>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">ชื่อแอปพลิเคชัน</label>
                <input type="text" name="app_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">ราคาจริง (บาท/เดือน)</label>
                <input type="number" name="real_price" step="0.01" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">กำไรต่อเดือน (บาท)</label>
                <input type="number" name="profit" step="0.01" class="form-control" required>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-success px-4">บันทึก</button>
                <a href="admin_dashboard.php" class="btn btn-secondary px-4">กลับ</a>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4">
      <div class="modal-header 
        <?php echo ($modalType === 'success') ? 'bg-success text-white' : 'bg-danger text-white'; ?>">
        <h5 class="modal-title w-100 text-center">
            <?php echo ($modalType === 'success') ? 'สำเร็จ' : 'เกิดข้อผิดพลาด'; ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center fs-5 py-4">
        <?php echo $modalMessage; ?>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-<?php echo ($modalType === 'success') ? 'success' : 'danger'; ?>" 
                data-bs-dismiss="modal"
                onclick="handleModalClose()">ตกลง</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php if ($showModal): ?>
    var myModal = new bootstrap.Modal(document.getElementById('resultModal'));
    myModal.show();

    function handleModalClose() {
        <?php if ($modalType === 'success'): ?>
            window.location.href = 'admin_dashboard.php';
        <?php endif; ?>
    }
<?php endif; ?>
</script>

</body>
</html>
