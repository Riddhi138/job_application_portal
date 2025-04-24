<?php
// Database Connection
$conn = new mysqli("localhost", "root", "", "micro_job");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch job applications grouped by email with job details
$sql = "SELECT a.email, GROUP_CONCAT(j.title SEPARATOR ', ') AS jobs_applied 
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        GROUP BY a.email";

$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Applications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            text-align: center;
            padding: 50px;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            margin: auto;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: left;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #6a0dad;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Job Applications</h2>
    <table>
        <tr>
            <th>Email</th>
            <th>Jobs Applied</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['jobs_applied']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='2'>No applications found</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>

<?php
$conn->close();
?>