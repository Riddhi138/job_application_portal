<?php
// Save this as add_employer.php
$conn = new mysqli("localhost", "root", "", "micro_job");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $company_name = $_POST['company_name'];
  $contact_name = $_POST['contact_name'];
  $contact_email = $_POST['contact_email'];
  $phone = $_POST['phone'];

  $sql = "INSERT INTO employers (company_name, contact_name, contact_email, phone) 
          VALUES ('$company_name', '$contact_name', '$contact_email', '$phone')";

  if ($conn->query($sql) === TRUE) {
    $message = "Employer added successfully!";
  } else {
    $message = "Error: " . $conn->error;
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Employer</title>
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
      background: #6a0dad;
      color: white;
      border: none;
      cursor: pointer;
    }
    .message {
      text-align: center;
      color: green;
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
    }
  </style>
</head>
<body>
  <a href="admin_panel.php" class="back-button">Back to Admin Panel</a>
  <form method="POST">
    <h2>Add New Employer</h2>
    <?php if ($message): ?>
      <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>
    <input type="text" name="company_name" placeholder="Company Name" required />
    <input type="text" name="contact_name" placeholder="Contact Name" required />
    <input type="email" name="contact_email" placeholder="Email" required />
    <input type="text" name="phone" placeholder="Phone Number" required />
    <button type="submit">Add Employer</button>
  </form>
</body>
</html>