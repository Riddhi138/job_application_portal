<?php
// Database Connection
$dbConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'micro_job'
];

$conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session to get current user ID
session_start();
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to 1 if not set

// Get job ID from URL parameter
$job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$job_id) {
    die("No job ID provided");
}

// SQL query to get job details - Make sure to select all relevant columns including company
$sql = "SELECT * FROM jobs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Job not found");
}

$job = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($job['title']) ?> - Job Details</title>
    <style>
        body {
            background-color: #F4F4F4;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .top-bar {
            background-color: white;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
        }
        .logo {
            font-size: 22px;
            font-weight: bold;
            color: #6a0dad;
            text-decoration: none;
        }
        .logo span {
            color: black;
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0,0,0,0.1);
            position: relative;
        }
        .job-title {
            font-size: 28px;
            color: #6a0dad;
            margin-bottom: 5px;
        }
        .company-name {
            font-size: 18px;
            color: #555;
            margin-bottom: 15px;
        }
        .job-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .detail-icon {
            color: #6a0dad;
            font-size: 18px;
        }
        .section-title {
            font-size: 20px;
            color: #333;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .job-description, .job-requirements {
            line-height: 1.6;
            color: #444;
        }
        .apply-button {
            background-color: #6a0dad;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            display: inline-block;
            text-decoration: none;
        }
        .apply-button:hover {
            background-color: #570a9e;
        }
        .apply-container {
            text-align: center;
            margin-top: 30px;
        }
        .icons {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .icon {
            width: 30px;
            height: 30px;
            cursor: pointer;
            object-fit: contain;
            border: 2px solid black;
            border-radius: 50%;
        }
        .settings-icon {
            width: 30px;
            height: 30px;
            cursor: pointer;
            object-fit: contain;
            border: none;
        }
        .back-button {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: #6a0dad;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .back-button:hover {
            background-color: #570a9e;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <a href="index.php" class="logo">Career<span>Connect</span></a>
        <div class="icons">
            <a href="settings.php"><img src="settings.jpg" class="settings-icon" alt="Settings"></a>
            <a href="inbox.php"><img src="msg.jpg" class="icon" alt="Messages"></a>
            <a href="notifications.php"><img src="n.jpg" class="icon" alt="Notifications"></a>
            <a href="display_profile.php"><img src="pro.jpg" class="icon" alt="Profile"></a>
        </div>
    </div>

    <div class="container">
        <a href="job_search.php" class="back-button">Back</a>
        
        <h1 class="job-title"><?= htmlspecialchars($job['title']) ?></h1>
        <div class="company-name"><?= htmlspecialchars($job['company'] ?? $job['company_name'] ?? 'Unknown Company') ?></div>

        <div class="job-details">
            <div class="detail-item">
                <span class="detail-icon">📍</span>
                <span><?= htmlspecialchars($job['location']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-icon">💼</span>
                <span><?= htmlspecialchars($job['job_type'] ?? '') ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-icon">💰</span>
                <span><?= htmlspecialchars($job['salary']) ?></span>
            </div>
        </div>

        <h2 class="section-title">Job Description</h2>
        <div class="job-description">
            <?= nl2br(htmlspecialchars($job['description'] ?? 'No description available.')) ?>
        </div>

        <h2 class="section-title">Requirements</h2>
        <div class="job-requirements">
            <?= nl2br(htmlspecialchars($job['requirements'] ?? 'No requirements specified.')) ?>
        </div>

        <!-- Apply Button -->
        <div class="apply-container">
            <form method="GET" action="application.php">
                <input type="hidden" name="job_id" value="<?= $job['id']; ?>">
                <button type="submit" class="apply-button">Apply Now</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>