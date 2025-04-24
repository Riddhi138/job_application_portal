<?php
// Connect to database
$conn = new mysqli("localhost", "root", "","micro_job");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $first_name = $_POST['first_name'];
  $last_name = $_POST['last_name'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];

  $sql = "INSERT INTO job_seekers (first_name, last_name, email, phone)
          VALUES ('$first_name', '$last_name', '$email', '$phone')";

  if ($conn->query($sql) === TRUE) {
    $message = "Job seeker added successfully!";
  } else {
    $message = "Error: " . $conn->error;
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Job Seeker</title>
  <style>
    body {
      font-family: Arial;
      padding: 40px;
      background: #f5f5f5;
    }
    form {
      max-width: 500px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px #ccc;
    }
    input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
    }
    button {
      padding: 10px 20px;
      background: #4a148c;
      color: white;
      border: none;
      cursor: pointer;
    }
    .message {
      text-align: center;
      color: green;
    }
    a {
      display: block;
      text-align: center;
      margin-top: 20px;
      text-decoration: none;
    }
    .back-button {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 10px 20px;
      background: #4a148c;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      display: inline-block;
      text-align: center;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <a href="admin_panel.php" class="back-button">Back to Admin Panel</a>
  <form method="POST">
    <h2>Add Job Seeker</h2>
    <?php if ($message): ?>
      <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>
    <input type="text" name="first_name" placeholder="First Name" required />
    <input type="text" name="last_name" placeholder="Last Name" required />
    <input type="email" name="email" placeholder="Email" required />
    <input type="text" name="phone" placeholder="Phone Number" required />
    <button type="submit">Add Job Seeker</button>

    <a href="job_seekers_list.php">📋 View All Job Seekers</a>
  </form>
</body>
</html>