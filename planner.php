<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>My Lovely Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <style>
        body { 
            background-color: #fdf2f8; 
            font-family: 'Itim', cursive;
            color: #5b21b6;
        }
        .mood-bar {
            background: white;
            border-radius: 20px;
            padding: 15px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(255, 182, 193, 0.3);
        }
        .mood-item {
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .mood-item:hover { transform: scale(1.1); }
        
        .kanban-column {
            background: rgba(255, 255, 255, 0.6);
            border-radius: 20px;
            min-height: 500px;
            padding: 15px;
            border: 2px dashed #ddd6fe;
        }
        .column-todo { background-color: #dcfce7; } /* เขียวอ่อน/ฟ้า */
        .column-progress { background-color: #e0f2fe; } /* ฟ้า */
        .column-done { background-color: #fce7f3; } /* ชมพู */

        .card-task {
            background: white;
            border-radius: 15px;
            padding: 12px;
            margin-bottom: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            cursor: grab;
            border-left: 5px solid #a78bfa;
        }
        .btn-check-task {
            float: right;
            border-radius: 50%;
            border: 2px solid #ddd;
            width: 25px; height: 25px;
            cursor: pointer;
        }
        .modal-content { border-radius: 25px; border: none; }
        .mood-btn { font-size: 2rem; border: none; background: none; }
    </style>
</head>
<body>

<div class="container py-4">
    <h1 class="text-center mb-4">🌸 My Daily Planner 🌸</h1>

    <div class="mood-bar d-flex justify-content-around align-items-center">
        <?php
        // Logic จำลองการดึงวันที่ 1 อาทิตย์
        $days = ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'];
        for($i=0; $i<7; $i++) {
            echo "<div class='mood-item' onclick='openMoodModal()'>
                    <div class='small text-muted'>{$days[$i]}</div>
                    <div style='font-size: 1.5rem;'>❓</div>
                  </div>";
        }
        ?>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <h4 class="text-center">📝 To Do</h4>
            <div id="todoList" class="kanban-column column-todo">
                <div class="card-task" data-id="1">
                    <span onclick="showDetail(1)">ทำการบ้าน PHP</span>
                    <div class="btn-check-task" onclick="moveStatus(1, 'inprogress')"></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <h4 class="text-center">🚀 In Progress</h4>
            <div id="progressList" class="kanban-column column-progress">
                </div>
        </div>

        <div class="col-md-4">
            <h4 class="text-center">✨ Done</h4>
            <div id="doneList" class="kanban-column column-done">
                </div>
        </div>
    </div>
</div>

<div class="modal fade" id="moodModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center p-5">
        <h3>วันนี้คุณรู้สึกอย่างไร?</h3>
        <div class="d-flex justify-content-around mt-4">
            <button class="mood-btn" onclick="saveMood('😊', 'Happy')">😊</button>
            <button class="mood-btn" onclick="saveMood('😴', 'Tired')">😴</button>
            <button class="mood-btn" onclick="saveMood('🤩', 'Excited')">🤩</button>
            <button class="mood-btn" onclick="saveMood('😢', 'Sad')">😢</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// ทำงานหลังจากโหลดหน้าเสร็จ
document.addEventListener('DOMContentLoaded', function() {
    // ตั้งค่าการลากวาง (Drag and Drop)
    const columns = ['todoList', 'progressList', 'doneList'];
    columns.forEach(id => {
        new Sortable(document.getElementById(id), {
            group: 'tasks',
            animation: 150,
            ghostClass: 'bg-light',
            onEnd: function (evt) {
                // ส่งค่า id และ status ใหม่ไปอัปเดตที่ไฟล์ update_task.php ผ่าน AJAX
                console.log('ย้าย Task:', evt.item.dataset.id, 'ไปที่:', evt.to.id);
            }
        });
    });
});

function openMoodModal() {
    const now = new Date();
    // เช็คว่าก่อนเที่ยงคืนหรือไม่ (ปกติ JS เช็คได้ตลอด แต่ Logic บังคับที่ PHP)
    var myModal = new bootstrap.Modal(document.getElementById('moodModal'));
    myModal.show();
}

function moveStatus(id, newStatus) {
    // Logic กดติ๊กแล้วย้ายช่อง
    alert('กำลังย้ายงาน ID: ' + id + ' ไปที่ ' + newStatus);
    // ใช้ fetch() ส่งไป PHP เพื่อ Update status ใน DB แล้ว reload หน้า
}

function showDetail(id) {
    alert('แสดงรายละเอียดของ Task ID: ' + id);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>