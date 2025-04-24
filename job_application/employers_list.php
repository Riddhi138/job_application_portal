<?php
// Step 1: Connect to the database
$conn = new mysqli("localhost", "root", "", "micro_job"); // Update with your DB name

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Step 2: Fetch all employers
$sql = "SELECT id, company_name, contact_name, contact_email, phone, created_at FROM employers";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Employers List</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      background: #f7f7f7;
    }

    h2 {
      text-align: center;
      color: #6a0dad;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    th, td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    th {
      background-color: #6a0dad;
      color: white;
    }

    tr:hover {
      background-color: #f1f1f1;
    }

    .container {
      max-width: 1000px;
      margin: auto;
    }
    
    .back-button {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 10px 20px;
      background: #6a0dad;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body>
  <a href="dashboard.php" class="back-button">Back to Dashboard</a>
  <div class="container">
    <h2>List of Employers</h2>

    <?php if ($result->num_rows > 0): ?>
      <table>
        <tr>
          <th>ID</th>
          <th>Company Name</th>
          <th>Contact Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Created At</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['company_name']); ?></td>
            <td><?php echo htmlspecialchars($row['contact_name']); ?></td>
            <td><?php echo htmlspecialchars($row['contact_email']); ?></td>
            <td><?php echo htmlspecialchars($row['phone']); ?></td>
            <td><?php echo $row['created_at']; ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    <?php else: ?>
      <p>No employers found.</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
  </div>
</body>
</html>