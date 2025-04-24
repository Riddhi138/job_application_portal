<?php
// Database Connection
$conn = new mysqli("localhost", "root", "", "job_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle reply submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply_message'])) {
    $message_id = $_POST['message_id'];
    $reply_text = $conn->real_escape_string($_POST['reply_message']);
    $conn->query("INSERT INTO replies (message_id, reply_text) VALUES ('$message_id', '$reply_text')");
}

// Handle message deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_message'])) {
    $message_id = $_POST['message_id'];
    $conn->query("DELETE FROM messages WHERE id = '$message_id'");
    $conn->query("DELETE FROM replies WHERE message_id = '$message_id'");
}

// Fetch Messages with Replies
$result = $conn->query("SELECT * FROM messages ORDER BY sent_at DESC");
?>

<html>
<head>
    <title>Inbox</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .container {
            width: 60%;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #E6E6FA;
            position: relative;
        }
        .message-box {
            border-bottom: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .sender {
            font-weight: bold;
        }
        .timestamp {
            font-size: 12px;
            color: gray;
        }
        .reply-button, .delete-button {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            color: white;
            margin-top: 5px;
        }
        .reply-button { background-color: purple; }
        .delete-button { background-color: blue; }
        .reply-box {
            margin-top: 10px;
        }
        textarea {
            width: 100%;
            height: 50px;
            margin-top: 5px;
        }
        .back-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: purple;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
        }
        .back-button:hover {
            background-color: darkviolet;
        }
    </style>
</head>
<body>
    <h2>Your Inbox</h2>
    <div class="container">
        <a href="job_search.php" class="back-button">Back</a>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="message-box">
                <div class="sender">From: <?= $row['sender'] ?></div>
                <div><?= $row['message'] ?></div>
                <div class="timestamp">Sent at: <?= $row['sent_at'] ?></div>
                
                <!-- Reply Form -->
                <form method="POST">
                    <input type="hidden" name="message_id" value="<?= $row['id'] ?>">
                    <textarea name="reply_message" placeholder="Type your reply..."></textarea>
                    <button type="submit" class="reply-button">Send Reply</button>
                </form>
                
                <!-- Delete Button -->
                <form method="POST">
                    <input type="hidden" name="message_id" value="<?= $row['id'] ?>">
                    <button type="submit" name="delete_message" class="delete-button">Delete</button>
                </form>
                
                <!-- Fetch and Display Replies -->
                <?php 
                $message_id = $row['id'];
                $reply_result = $conn->query("SELECT * FROM replies WHERE message_id = '$message_id'");
                while ($reply = $reply_result->fetch_assoc()): ?>
                    <div class="reply-box">
                        <strong>Reply:</strong> <?= $reply['reply_text'] ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>