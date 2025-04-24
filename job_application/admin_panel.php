<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "micro_job");
$message = ""; 

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get company_id associated with the logged-in user
$user_id = $_SESSION['user_id'];
$company_id = null;

// First, check if the companies table exists
$check_table = $conn->query("SHOW TABLES LIKE 'companies'");
if ($check_table->num_rows == 0) {
    // Companies table doesn't exist, create it with the correct structure
    $create_companies = "CREATE TABLE companies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_id (user_id)
    )";
    
    if (!$conn->query($create_companies)) {
        die("Error creating companies table: " . $conn->error);
    }
}

// Check the structure of the companies table to ensure it has user_id column
$check_column = $conn->query("SHOW COLUMNS FROM companies LIKE 'user_id'");
if ($check_column->num_rows == 0) {
    // Add user_id column if it doesn't exist
    $add_column = "ALTER TABLE companies ADD COLUMN user_id INT NOT NULL AFTER id";
    if (!$conn->query($add_column)) {
        die("Error adding user_id column: " . $conn->error);
    }
    
    // Add unique constraint to prevent duplicate user_ids
    $add_unique = "ALTER TABLE companies ADD UNIQUE KEY unique_user_id (user_id)";
    if (!$conn->query($add_unique)) {
        die("Error adding unique constraint: " . $conn->error);
    }
}

// Try to get company_id from companies table - use prepared statement for safety
$company_query = "SELECT id FROM companies WHERE user_id = ?";
$stmt = $conn->prepare($company_query);

if (!$stmt) {
    die("Prepare failed for company query: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $company_row = $result->fetch_assoc();
    $company_id = $company_row['id'];
} else {
    // If no company is associated with user, create one
    $company_name = "Default Company for User " . $user_id;
    
    // Start a transaction to ensure consistency
    $conn->begin_transaction();
    
    try {
        // First check again if the record already exists (to avoid race conditions)
        $check_query = "SELECT id FROM companies WHERE user_id = ?";
        $check_stmt = $conn->prepare($check_query);
        
        if (!$check_stmt) {
            throw new Exception("Prepare failed for check query: " . $conn->error);
        }
        
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result && $check_result->num_rows > 0) {
            // Company found on second check - use this ID
            $company_row = $check_result->fetch_assoc();
            $company_id = $company_row['id'];
        } else {
            // Insert with prepared statement to prevent SQL injection
            $company_insert = "INSERT INTO companies (user_id, name) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($company_insert);
            
            if (!$insert_stmt) {
                throw new Exception("Prepare failed for insert: " . $conn->error);
            }
            
            $insert_stmt->bind_param("is", $user_id, $company_name);
            
            if ($insert_stmt->execute()) {
                $company_id = $conn->insert_id;
            } else {
                throw new Exception("Error executing company insert: " . $insert_stmt->error);
            }
            
            $insert_stmt->close();
        }
        
        $check_stmt->close();
        
        // Commit the transaction
        $conn->commit();
    } catch (Exception $e) {
        // Roll back the transaction if something went wrong
        $conn->rollback();
        die("Error creating or retrieving company: " . $e->getMessage());
    }
}

if ($stmt) {
    $stmt->close();
}

// Store company_id in session for use in other pages
$_SESSION['company_id'] = $company_id;

// Check if jobs table exists and has the correct structure
$check_jobs_table = $conn->query("SHOW TABLES LIKE 'jobs'");
if ($check_jobs_table->num_rows == 0) {
    // Jobs table doesn't exist, create it
    $create_jobs = "CREATE TABLE jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        company VARCHAR(255) NOT NULL,
        location VARCHAR(255) NOT NULL,
        salary VARCHAR(100) NOT NULL,
        job_type VARCHAR(50) NOT NULL,
        description TEXT NOT NULL,
        requirements TEXT NOT NULL,
        company_id INT NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_company_id FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    )";
    
    if (!$conn->query($create_jobs)) {
        die("Error creating jobs table: " . $conn->error);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $company = $_POST['company'];
    $location = $_POST['location'];
    $salary = $_POST['salary'];
    $type = $_POST['type'];
    $description = $_POST['description'];
    $requirements = $_POST['requirements'];
    $status = $_POST['status'];

    // For debugging - can be removed in production
    // echo "Using company_id: " . $company_id;

    // Use the company_id from the logged-in user
    // Insert into jobs table only if status is approved and we have a valid company_id
    if ($company_id && $status === 'approved') {
        $stmt = $conn->prepare("INSERT INTO jobs (title, company, location, salary, job_type, description, requirements, company_id, status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sssssssss", $title, $company, $location, $salary, $type, $description, $requirements, $company_id, $status);

        if ($stmt->execute()) {
            echo "<script>alert('Job posted successfully!'); window.location.href='admin_panel.php';</script>";
        } else {
            echo "Error inserting job: " . $stmt->error;
        }

        $stmt->close();
    } elseif (!$company_id) {
        echo "<script>alert('Could not associate job with a company.'); window.location.href='admin_panel.php';</script>";
    } else {
        echo "<script>alert('Job status is not approved yet.'); window.location.href='admin_panel.php';</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel - Post Job</title>
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      margin: 0;
      display: flex;
      min-height: 100vh;
      background-color: #f4f0fa;
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
      padding: 50px;
      width: calc(100% - 240px);
    }

    .form-container {
      background-color: white;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(106, 27, 154, 0.15);
      max-width: 800px;
      margin: 0 auto;
      animation: slideIn 0.6s ease forwards;
      opacity: 0;
      transform: translateX(50px);
    }

    @keyframes slideIn {
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .form-container h2 {
      color: #6a1b9a;
      margin-bottom: 25px;
      text-align: center;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #333;
    }

    input, textarea, select {
      width: 100%;
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 1rem;
      transition: border-color 0.3s ease;
    }

    input:focus, textarea:focus, select:focus {
      outline: none;
      border-color: #8e24aa;
    }

    button {
      background-color: #6a1b9a;
      color: white;
      border: none;
      padding: 14px 24px;
      font-size: 1rem;
      border-radius: 8px;
      cursor: pointer;
      width: 100%;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #8e24aa;
    }
    
    .user-info {
      text-align: center;
      margin-bottom: 20px;
      color: white;
      padding: 10px;
      background-color: #7b1fa2;
      border-radius: 8px;
    }
    
    /* Added styles to display company ID info */
    .company-info {
      text-align: center;
      margin-top: 10px;
      color: white;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

  <div class="sidebar">
    <h2>Admin Panel</h2>
    
    <div class="user-info">
      <?php if(isset($_SESSION['user_email'])): ?>
        <p>Logged in as: <?= htmlspecialchars($_SESSION['user_email']) ?></p>
      <?php else: ?>
        <p>User ID: <?= isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['user_id']) : 'Unknown' ?></p>
      <?php endif; ?>
      
      <div class="company-info">
        <p>Company ID: <?= isset($company_id) ? htmlspecialchars($company_id) : 'Not assigned' ?></p>
      </div>
    </div>
    
    <ul>
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="admin_application.php">Applications</a></li>
      <li><a href="add_employer.php">Add Employers</a></li>
      <li><a href="add_job_seekers.php">Add Job Seekers</a></li>
      <li><a href="post_job.php">Job Posted</a></li>
      <!-- Updated logout link to properly end the session -->
      <li><a href="admin_login.php?logout=true">Logout</a></li>
    </ul>
  </div>

  <div class="content">
    <div class="form-container">
      <h2>Post a New Job</h2>

      <form action="admin_panel.php" method="POST">
        <label for="title">Job Title</label>
        <input type="text" id="title" name="title" required>

        <label for="company">Company Name</label>
        <input type="text" id="company" name="company" required>

        <label for="location">Location</label>
        <input type="text" id="location" name="location" required>

        <label for="salary">Salary</label>
        <input type="text" id="salary" name="salary" required>

        <label for="type">Job Type</label>
        <select name="type" id="type" required>
          <option value="">-- Select Type --</option>
          <option value="Full-Time">Full-Time</option>
          <option value="Part-Time">Part-Time</option>
          <option value="Internship">Internship</option>
          <option value="Remote">Remote</option>
        </select>

        <label for="description">Job Description</label>
        <textarea id="description" name="description" rows="6" required></textarea>

        <label for="requirements">Job Requirements</label>
        <textarea id="requirements" name="requirements" rows="6" required></textarea>

        <label>Status:</label><br>
        <select name="status">
          <option value="approved">Approved</option>
          <option value="pending">Pending</option>
        </select><br><br>

        <button type="submit">Post Job</button>
      </form>
    </div>
  </div>

</body>
</html>