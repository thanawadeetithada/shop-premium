<?php include "db.php"; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    .chat-container {
        max-width: 600px;
        margin: auto;
        height: 500px;
        overflow-y: auto;
        padding: 10px;
        border: 1px solid #ccc;
        background: #f8f9fa;
        display: flex;
        flex-direction: column;
    }

    .message-wrapper {
        display: flex;
        width: 100%;
        margin-bottom: 10px;
    }

    .message {
        max-width: 75%;
        padding: 10px;
        border-radius: 15px;
        word-wrap: break-word;
    }

    .message.user {
        background-color: white;
        color: black;
        justify-content: flex-end;
        border: 1px solid #ccc;
    }

    .message.admin {
        background-color: #0d6efd;
        color: white;
        justify-content: flex-start;
    }

    .user-message {
        display: flex;
        justify-content: flex-end;
    }

    .admin-message {
        display: flex;
        justify-content: flex-start;
    }
    </style>
</head>

<body>

    <div class="container mt-4">
        <h3 class="text-center">User Chat</h3>
        <div class="chat-container" id="chatBox"></div>

        <div class="d-flex">
            <input type="text" style="height:fit-content" class="form-control me-2" id="chatInput" placeholder="พิมพ์ข้อความ...">
            <button class="btn btn-primary" onclick="sendMessage()">ส่ง</button>
        </div>
    </div>

    <script>
    function loadMessages() {
        $.ajax({
            url: "load_messages.php",
            method: "GET",
            success: function(data) {
                $("#chatBox").html(data);
                $("#chatBox").scrollTop($("#chatBox")[0].scrollHeight);
            }
        });
    }

    function sendMessage() {
        let message = $("#chatInput").val();
        if (message.trim() === "") return;

        $.post("send_message.php", {
            sender: "user",
            message: message
        }, function() {
            $("#chatInput").val("");
            loadMessages();
        });
    }

    setInterval(loadMessages, 2000);
    $(document).ready(loadMessages);
    </script>

</body>

</html>