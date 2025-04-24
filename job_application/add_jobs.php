<?php
// Connect to MySQL
$conn = new mysqli("localhost", "root", "", "micro_job"); // Make sure 'admin' is your DB name

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $title = $_POST['title'];
  $company = $_POST['company_name'];
  $location = $_POST['location'];
  $status = $_POST['status'];

  // Insert into the jobs table
  $sql = "INSERT INTO jobs (title, company_name, location, status, created_at) 
          VALUES ('$title', '$company', '$location', '$status', NOW())";

  if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green;'>✅ Job posted successfully!</p>";
  } else {
    echo "<p style='color:red;'>❌ Error: " . $conn->error . "</p>";
  }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Post a New Job</title>
</head>
<body>
  <h2>Post a New Job</h2>
  <form method="POST" action="">
    <label>Job Title:</label><br>
    <input type="text" name="title" required><br><br>

    <label>Company Name:</label><br>
    <input type="text" name="company_name" required><br><br>

    <label>Location:</label><br>
    <input type="text" name="location" required><br><br>

    <label>Status:</label><br>
    <select name="status">
      <option value="approved">Approved</option>
      <option value="pending">Pending</option>
    </select><br><br>

    <input type="submit" value="Post Job">
  </form>

  <p><a href="dashboard.php">⬅ Back to Dashboard</a></p>
</body>
</html>
