<?php
session_start();
$conn = new mysqli("localhost", "root", "", "micro_job");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the company_id associated with the current admin
$company_id = isset($_SESSION['company_id']) ? $_SESSION['company_id'] : 0;

// Fetch only jobs with matching company_id
$sql = "SELECT id, title, company, location, salary, job_type, description, requirements, status FROM jobs WHERE company_id = '$company_id' ORDER BY id DESC";
$result = $conn->query($sql);

// Check if query was successful
if (!$result) {
    echo "Error in query: " . $conn->error;
    $job_count = 0;
} else {
    $job_count = $result->num_rows;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Posted Jobs</title>
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      margin: 0;
      display: flex;
      min-height: 100vh;
      background-color: #f9f6ff;
      overflow-x: hidden;
    }

    .sidebar {
      width: 220px;
      background-color: #6a1b9a;
      color: white;
      padding: 20px;
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 30px;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
    }

    .sidebar li {
      padding: 10px;
      margin-bottom: 10px;
      background-color: #7b1fa2;
      border-radius: 8px;
      cursor: pointer;
      text-align: center;
      transition: background-color 0.3s ease;
    }

    .sidebar li:hover {
      background-color: #9c27b0;
    }

    .sidebar a {
      text-decoration: none;
      color: white;
      display: block;
    }

    .content {
      margin-left: 240px;
      padding: 40px;
      width: calc(100% - 240px);
    }

    .job-counter {
      text-align: center;
      background-color: white;
      padding: 15px;
      margin-bottom: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(106, 27, 154, 0.1);
      font-size: 1.2rem;
      color: #6a1b9a;
      font-weight: bold;
    }

    .job-card {
      background-color: white;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(106, 27, 154, 0.1);
      transition: transform 0.2s ease;
    }

    .job-card:hover {
      transform: translateY(-4px);
    }

    .job-title {
      font-size: 1.5rem;
      color: #6a1b9a;
      margin-bottom: 10px;
    }

    .job-company {
      font-weight: bold;
      color: #555;
    }

    .job-meta {
      margin: 10px 0;
      color: #666;
    }

    .job-desc, .job-req {
      margin-top: 10px;
      color: #444;
    }

    .status {
      display: inline-block;
      background-color: #d1c4e9;
      color: #4a148c;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.9rem;
      margin-top: 10px;
    }
    
    .job-id {
      float: right;
      color: #999;
      font-size: 0.9rem;
    }
    
    .back-button {
      position: absolute;
      top: 20px;
      right: 20px;
      padding: 10px 20px;
      background: #6a1b9a;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      z-index: 100;
    }
  </style>
</head>
<body>

  <div class="sidebar">
    <h2>Admin Panel</h2>
    <ul>
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="admin_application.php">Applications</a></li>
      <li><a href="add_employer.php">Add Employers</a></li>
      <li><a href="add_job_seekers.php">Add Job Seekers</a></li>
      <li><a href="post_job.php">Job Posted</a></li>
      <li><a href="front.php">Logout</a></li>
    </ul>
  </div>

  <div class="content">
    <a href="admin_panel.php" class="back-button">Back to Admin Panel</a>
    <h2 style="color:#6a1b9a;">Your Posted Jobs</h2>

    <div class="job-counter">
      Your Jobs: <?php echo $job_count; ?>
    </div>

    <?php
    if ($result && $job_count > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='job-card'>
                    <div class='job-id'>Job ID: {$row['id']}</div>
                    <div class='job-title'>{$row['title']}</div>
                    <div class='job-company'>{$row['company']} - {$row['location']}</div>
                    <div class='job-meta'>Salary: {$row['salary']} | Type: {$row['job_type']}</div>
                    <div class='job-desc'><strong>Description:</strong><br>{$row['description']}</div>
                    <div class='job-req'><strong>Requirements:</strong><br>{$row['requirements']}</div>
                    <div class='status'>Status: {$row['status']}</div>
                  </div>";
        }
    } else {
        echo "<p>No jobs found for your company. Please check your job listings.</p>";
    }

    $conn->close();
    ?>
  </div>

</body>
</html>