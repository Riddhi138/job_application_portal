<?php
$conn = new mysqli("localhost", "root", "", "micro_job");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT title, company, location, salary, job_type, description, requirements, status, created_at FROM admin_panel ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Jobs Posted</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f4f0fa;
      margin: 0;
      padding: 20px;
    }

    h2 {
      text-align: center;
      background-color: #6a0dad;
      color: white;
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 30px;
    }

    .job-listing {
      max-width: 900px;
      margin: 0 auto;
    }

    .job-card {
      background: #fff;
      padding: 20px;
      border-left: 6px solid #7b1fa2;
      border-radius: 10px;
      margin-bottom: 20px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .job-card h3 {
      margin-top: 0;
      color: #6a1b9a;
    }

    .job-card p {
      margin: 5px 0;
    }

    .status {
      font-weight: bold;
      color: #388e3c;
    }

    .status.pending {
      color: #f57c00;
    }
  </style>
</head>
<body>

<h2>Jobs Posted</h2>

<div class="job-listing">
  <?php
  if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
          echo "<div class='job-card'>";
          echo "<h3>" . htmlspecialchars($row['title']) . " at " . htmlspecialchars($row['company']) . "</h3>";
          echo "<p><strong>Location:</strong> " . htmlspecialchars($row['location']) . "</p>";
          echo "<p><strong>Salary:</strong> ₹" . htmlspecialchars($row['salary']) . "</p>";
          echo "<p><strong>Type:</strong> " . htmlspecialchars($row['job_type']) . "</p>";
          echo "<p><strong>Description:</strong> " . htmlspecialchars($row['description']) . "</p>";
          echo "<p><strong>Requirements:</strong> " . htmlspecialchars($row['requirements']) . "</p>";
          echo "<p class='status " . ($row['status'] == 'pending' ? 'pending' : '') . "'><strong>Status:</strong> " . htmlspecialchars($row['status']) . "</p>";
          echo "<p><strong>Posted On:</strong> " . htmlspecialchars($row['created_at']) . "</p>";
          echo "</div>";
      }
  } else {
      echo "<p style='text-align:center;'>No jobs posted yet.</p>";
  }

  $conn->close();
  ?>
</div>

</body>
</html>
