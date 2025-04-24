<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Use existing session value
if (!isset($_SESSION['company_id'])) {
    // For testing only: set a dummy company_id
    $_SESSION['company_id'] = 1;
}
$admin_company_id = $_SESSION['company_id'];

// Connect to DB
$conn = new mysqli("localhost", "root", "", "micro_job");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Debug: Show current company_id
// echo "<p>Current company_id: " . $admin_company_id . "</p>";

// Initialize message variable
$status_message = '';

// Reject application handling
if (isset($_GET['reject_id'])) {
    $reject_id = intval($_GET['reject_id']);
    $delete_sql = "DELETE FROM applications WHERE id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $reject_id, $_GET['user_id']);
    if ($delete_stmt->execute()) {
        $status_message = "Application rejected and deleted.";
    } else {
        $status_message = "Failed to reject application: " . $conn->error;
    }
    $delete_stmt->close();
}

// Accept application handling
if (isset($_GET['accept_id'])) {
    $accept_id = intval($_GET['accept_id']);
    $update_sql = "UPDATE applications SET status = 'accepted' WHERE id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $accept_id, $_GET['user_id']);
    if ($update_stmt->execute()) {
        $status_message = "Application Accepted. Applicant will be informed by email notification.";
    } else {
        $status_message = "Failed to accept application: " . $conn->error;
    }
    $update_stmt->close();
}

// Update company_id in applications table where it's null
$update_company_sql = "UPDATE applications a
                      JOIN jobs j ON a.job_id = j.id
                      SET a.company_id = j.company_id
                      WHERE a.company_id IS NULL";
$conn->query($update_company_sql);

// Show applications for the company's jobs
$sql = "SELECT 
        a.id AS application_id,
        a.user_id,
        a.name AS candidate_name,
        a.email,
        a.resume_path AS resume,
        j.title AS job_title,
        a.status,
        a.application_date,
        j.company_id
    FROM applications a
    LEFT JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = ?
    ORDER BY a.id DESC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('MySQL Error: ' . $conn->error);
}

$stmt->bind_param("i", $admin_company_id);
$stmt->execute();
$result = $stmt->get_result();

// Debug count
$count = $result->num_rows;
// echo "<p>Found $count applications</p>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Job Applications</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f0fa;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 1000px;
      margin: 50px auto;
      background: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      position: relative;
    }

    h1 {
      color: #6a1b9a;
      text-align: center;
      margin-bottom: 30px;
    }

    .application {
      background-color: #f9f9f9;
      border-left: 4px solid #6a1b9a;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 8px;
      transition: transform 0.2s ease;
    }

    .application:hover {
      transform: scale(1.01);
    }

    .application h3 {
      margin: 0;
      color: #6a1b9a;
    }

    .application p {
      margin: 8px 0;
    }

    .resume-link {
      color: #6a1b9a;
      font-weight: 500;
      text-decoration: underline;
    }

    .no-data {
      text-align: center;
      color: #999;
      font-style: italic;
    }

    .status-badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 12px;
      font-size: 0.8em;
      margin-left: 10px;
      color: white;
    }
    
    .status-pending {
      background-color: #ff9800;
    }
    
    .status-accepted {
      background-color: #4caf50;
    }
    
    .status-rejected {
      background-color: #f44336;
    }

    .action-button {
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      margin-top: 10px;
      margin-right: 10px;
    }

    .accept-button {
      background-color: #4caf50;
    }

    .accept-button:hover {
      background-color: #388e3c;
    }

    .reject-button {
      background-color: #8e24aa;
    }

    .reject-button:hover {
      background-color: #6a1b9a;
    }

    .back-button {
      background-color: #8e24aa;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      text-decoration: none;
      position: absolute;
      top: 20px;
      right: 40px;
    }

    .back-button:hover {
      background-color: #6a1b9a;
    }
    
    .status-message {
      background-color: #e8f5e9;
      color: #2e7d32;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 8px;
      border-left: 4px solid #4caf50;
      font-weight: 500;
    }
    
    .status-message.error {
      background-color: #ffebee;
      color: #c62828;
      border-left: 4px solid #f44336;
    }
  </style>
</head>
<body>

<div class="container">
  <a href="admin_panel.php" class="back-button">Back</a>
  <h1>Applications for Your Jobs</h1>
  
  <?php if (!empty($status_message)): ?>
    <div class="status-message"><?php echo htmlspecialchars($status_message); ?></div>
  <?php endif; ?>

  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="application">
        <h3>
          <?php echo htmlspecialchars($row['candidate_name']); ?>
          <span class="status-badge status-<?php echo htmlspecialchars($row['status'] ?? 'pending'); ?>">
            <?php echo htmlspecialchars(ucfirst($row['status'] ?? 'pending')); ?>
          </span>
        </h3>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
        <p><strong>Job Title:</strong> <?php echo htmlspecialchars($row['job_title'] ?? 'Untitled Job'); ?></p>
        <p><strong>Resume:</strong> <a class="resume-link" href="<?php echo htmlspecialchars($row['resume']); ?>" target="_blank">View Resume</a></p>
        <?php if (!empty($row['application_date'])): ?>
          <p><strong>Date Applied:</strong> <?php echo date('F j, Y, g:i a', strtotime($row['application_date'])); ?></p>
        <?php endif; ?>
        
        <a href="admin_application.php?accept_id=<?php echo $row['application_id']; ?>&user_id=<?php echo $row['user_id']; ?>" class="action-button accept-button">Accept</a>
        <a href="admin_application.php?reject_id=<?php echo $row['application_id']; ?>&user_id=<?php echo $row['user_id']; ?>" class="action-button reject-button">Reject</a>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p class="no-data">No applications found for your posted jobs.</p>
  
  <?php endif; ?>

</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>