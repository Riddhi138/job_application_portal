<?php
session_start();
$conn = new mysqli("localhost", "root", "", "micro_job");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in and has a company_id
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get or verify company_id
$company_id = null;
if (isset($_SESSION['company_id'])) {
    $company_id = $_SESSION['company_id'];
} else {
    // If company_id is not in session, try to retrieve it
    $user_id = $_SESSION['user_id'];
    $company_query = "SELECT id FROM companies WHERE user_id = ?";
    $stmt = $conn->prepare($company_query);
    
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $company_row = $result->fetch_assoc();
            $company_id = $company_row['id'];
            $_SESSION['company_id'] = $company_id; // Store for future use
        }
        
        $stmt->close();
    }
}

// Get stats counts for the dashboard - filtered by company_id
$total_jobs = 0;
$pending_jobs = 0;
$total_seekers = 0;
$total_employers = 0;

if ($company_id) {
    // Count jobs for this company
    $jobs_query = "SELECT COUNT(*) as count FROM jobs WHERE company_id = ?";
    $stmt = $conn->prepare($jobs_query);
    
    if ($stmt) {
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $total_jobs = $row['count'];
        }
        
        $stmt->close();
    }

    // Count pending jobs for this company
    $pending_query = "SELECT COUNT(*) as count FROM jobs WHERE company_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($pending_query);
    
    if ($stmt) {
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $pending_jobs = $row['count'];
        }
        
        $stmt->close();
    }
}

// Check if job_seekers table exists and count
// This is not filtered by company_id since job seekers aren't company-specific
$seekers_query = "SHOW TABLES LIKE 'job_seekers'";
$result = $conn->query($seekers_query);
if ($result && $result->num_rows > 0) {
    $count_query = "SELECT COUNT(*) as count FROM job_seekers";
    $result = $conn->query($count_query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_seekers = $row['count'];
    }
}

// Check if employers table exists and count - this stays as is because we're counting total employers
$employers_query = "SELECT COUNT(*) as count FROM companies";
$result = $conn->query($employers_query);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_employers = $row['count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard</title>
  <style>
    body {
      margin: 0;
      padding: 20px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f9f9f9;
      color: #333;
      background: linear-gradient(to right, #f7f0ff, #ffffff);
    }

    h2 {
      text-align: center;
      background-color: #6a0dad;
      color: white;
      padding: 15px;
      border-radius: 10px;
      margin: 0px auto 40px auto; 
      font-size: 2rem;
    }

    .dashboard {
      max-width: 800px;
      margin: 0 auto;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      margin-bottom: 30px;
    }

    .card {
      background-color: #ffffff;
      border-left: 6px solid #4a148c;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      padding: 20px;
      transition: transform 0.3s ease;
      display: flex;
      flex-direction: column;
      justify-content: center;
      text-align: center;
      cursor: pointer;
    }

    .card:hover {
      transform: translateY(-5px);
    }

    .card h3 {
      margin: 0;
      color: #4a148c;
      font-size: 1.2rem;
      font-weight: 600;
    }

    .card span {
      display: block;
      margin-top: 10px;
      font-size: 1.5rem;
      font-weight: bold;
      color: #4a148c;
    }

    a.card-link {
      text-decoration: none;
      color: inherit;
    }

    .back-button {
      display: block;
      width: 150px;
      margin: 30px auto 0;
      background-color: #6a0dad;
      color: white;
      padding: 12px;
      text-decoration: none;
      text-align: center;
      border-radius: 5px;
      font-weight: bold;
    }

    .company-info {
      text-align: center;
      margin-bottom: 20px;
      color: #4a148c;
      font-weight: bold;
    }

    @media (max-width: 768px) {
      .grid {
        grid-template-columns: 1fr;
      }

      h2 {
        font-size: 1.8rem;
      }

      .card h3 {
        font-size: 1rem;
      }

      .card span {
        font-size: 1.3rem;
      }
    }
  </style>
</head>
<body>
  <h2>Dashboard</h2>
  
  <?php if($company_id): ?>
  <div class="company-info">
    Company ID: <?= htmlspecialchars($company_id) ?>
  </div>
  <?php endif; ?>
  
  <div class="dashboard">
    <div class="grid">
      <a href="job_seekers_list.php" class="card-link">
        <div class="card">
          <h3>Total Job Seekers</h3>
       
        </div>
      </a>

      <a href="employers_list.php" class="card-link">
        <div class="card">
          <h3>Total Employers</h3>
          
        </div>
      </a>

      <a href="post_job.php" class="card-link">
        <div class="card">
          <h3>Total Jobs Posted</h3>
       
        </div>
      </a>

      
        <div class="card">
          <h3>Pending Job Posts</h3>
        
        </div>
      </a>
    </div>
  </div>

  <a href="admin_panel.php" class="back-button">Back to Admin Panel</a>

</body>
</html>