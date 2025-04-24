<?php
// Database Connection
$conn = new mysqli("localhost", "root", "", "job_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Notification Deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $conn->query("DELETE FROM notifications WHERE id = $delete_id");
    header("Location: notifications.php"); // Refresh page after deletion
    exit();
}

// Fetch Notifications
$result = $conn->query("SELECT id, notification, created_at FROM notifications ORDER BY created_at DESC");
?>

<html>
<head>
    <title>Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #E6E6FA;
        }
        .container {
            width: 60%;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
            position: relative;
        }
        .notification-box {
            border-bottom: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            position: relative;
        }
        .timestamp {
            font-size: 12px;
            color: gray;
        }
        .delete-button {
            background-color: purple;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 5px;
        }
        .delete-button:hover {
            background-color: darkviolet;
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
    <h2>Your Notifications</h2>
    <div class="container">
        <a href="job_search.php" class="back-button">Back</a>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="notification-box">
                <div><?= $row['notification'] ?></div>
                <div class="timestamp"><?= $row['created_at'] ?></div>
                <form method="POST" style="margin-top: 5px;">
                    <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="delete-button">Delete</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>