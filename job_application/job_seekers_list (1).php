<?php
$conn = new mysqli("localhost", "root", "", "admin");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, first_name, last_name, email, phone, created_at FROM job_seekers";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Job Seekers List</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      background: #f0f0f0;
    }
    h2 {
      text-align: center;
      color: #4a148c;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      margin-top: 20px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    th, td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }
    th {
      background-color: #4a148c;
      color: white;
    }
    a {
      display: inline-block;
      margin-top: 20px;
      text-decoration: none;
      color: #4a148c;
    }
  </style>
</head>
<body>
  <h2>List of Job Seekers</h2>
  <?php if ($result->num_rows > 0): ?>
    <table>
      <tr>
        <th>ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Created At</th>
      </tr>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?php echo $row['id']; ?></td>
          <td><?php echo htmlspecialchars($row['first_name']); ?></td>
          <td><?php echo htmlspecialchars($row['last_name']); ?></td>
          <td><?php echo htmlspecialchars($row['email']); ?></td>
          <td><?php echo htmlspecialchars($row['phone']); ?></td>
          <td><?php echo $row['created_at']; ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p>No job seekers found.</p>
  <?php endif; ?>
</body>
</html>
