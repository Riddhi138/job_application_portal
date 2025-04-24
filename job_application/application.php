<?php
// Database Connection
$conn = new mysqli("localhost", "root", "", "micro_job");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session to maintain user information
session_start();

// Ensure uploads directory exists
$upload_dir = "uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$message = "";
$name = "";
$email = "";
$resume_path = "";
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
$source = isset($_GET['source']) ? $_GET['source'] : '';

// Get current user ID if logged in, otherwise set to NULL
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;

// Get company_id for the job based on source
$job_query = null;
if ($source == 'admin_panel') {
    // Query from admin_panel table
    $job_query = $conn->prepare("SELECT company_id FROM admin_panel WHERE id = ?");
} else {
    // Default to jobs table
    $job_query = $conn->prepare("SELECT company_id FROM jobs WHERE id = ?");
}

if (!$job_query) {
    die("Error preparing job query: " . $conn->error);
}
$job_query->bind_param("i", $job_id);
$job_query->execute();
$job_result = $job_query->get_result();

if ($job_result->num_rows > 0) {
    $job_row = $job_result->fetch_assoc();
    $company_id = $job_row['company_id'];
} else {
    die("Job not found with ID: " . $job_id);
}
$job_query->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["upload"])) { // Upload Resume
        $name = $_POST["name"];
        $email = $_POST["email"];
        $resume_name = time() . '_' . basename($_FILES["resume"]["name"]); // Add timestamp to make filename unique
        $resume_tmp = $_FILES["resume"]["tmp_name"];
        $resume_path = $upload_dir . $resume_name;
        $job_id = isset($_POST["job_id"]) ? intval($_POST["job_id"]) : 0;
        $source = isset($_POST["source"]) ? $_POST["source"] : '';

        if (move_uploaded_file($resume_tmp, $resume_path)) {
            $message = "Application submitted successfully.";
        } else {
            $message = "Resume upload failed. Please try again.";
        }
    } elseif (isset($_POST["confirm"])) { // Confirm Application
        if (empty($_POST["name"]) || empty($_POST["email"]) || empty($_POST["resume_path"])) {
            die("Error: Missing required fields.");
        }

        $name = $_POST["name"];
        $email = $_POST["email"];
        $resume_path = $_POST["resume_path"];
        $job_id = isset($_POST["job_id"]) ? intval($_POST["job_id"]) : 0;
        
        // Check if this email has already applied for this job
        $check_query = $conn->prepare("SELECT id FROM applications WHERE email = ? AND job_id = ?");
        $check_query->bind_param("si", $email, $job_id);
        $check_query->execute();
        $check_result = $check_query->get_result();
        
        if ($check_result->num_rows > 0) {
            $message = "You have already applied for this job.";
        } else {
            // If user_id is available, include it in the query
            if ($user_id) {
                $stmt = $conn->prepare("INSERT INTO applications (name, email, resume_path, job_id, status, user_id) VALUES (?, ?, ?, ?, 'Confirmed', ?)");
                $stmt->bind_param("sssii", $name, $email, $resume_path, $job_id, $user_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO applications (name, email, resume_path, job_id, status) VALUES (?, ?, ?, ?, 'Confirmed')");
                $stmt->bind_param("sssi", $name, $email, $resume_path, $job_id);
            }
            
            if (!$stmt) {
                die("Error in SQL Query: " . $conn->error);
            }

            if ($stmt->execute()) {
                $message = "Thank you, <strong>$name</strong>! Your application has been successfully submitted.";
            } else {
                $message = "Error submitting application. Please try again. Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_query->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Application</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            text-align: center;
            padding: 50px;
            position: relative;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 50%;
            margin: auto;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: left;
        }
        input, button {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #6a0dad;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }
        button:hover {
            background-color: #570a9e;
        }
        .success-message {
            font-size: 18px;
            color: green;
            margin-top: 20px;
        }
        .error-message {
            font-size: 18px;
            color: red;
            margin-top: 20px;
        }
        .back-button {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #6a0dad;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .back-button:hover {
            background-color: #570a9e;
        }
    </style>
</head>
<body>

<a href="job_search.php" class="back-button">Back</a>

<div class="container">
    <h2>Apply for Job</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Your Name" required><br>
        <input type="email" name="email" placeholder="Your Email" required><br>
        <input type="file" name="resume" required><br>
        <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
        <input type="hidden" name="source" value="<?php echo $source; ?>">
        <button type="submit" name="upload">Upload Resume</button>
    </form>

    <?php 
    if (!empty($message)) {
        if (strpos($message, "Error") !== false) {
            echo "<div class='error-message'>$message</div>";
        } else {
            echo "<div class='success-message'>$message</div>";
            
            if (isset($_POST["upload"])) {
                echo "<p><strong>Name:</strong> $name</p>";
                echo "<p><strong>Email:</strong> $email</p>";
                if (!empty($resume_path)) {
                    echo "<p><strong>Resume:</strong> <a href='$resume_path' target='_blank'>View Resume</a></p>";
                    
                    echo "<form method='post'>
                            <input type='hidden' name='name' value='$name'>
                            <input type='hidden' name='email' value='$email'>
                            <input type='hidden' name='resume_path' value='$resume_path'>
                            <input type='hidden' name='job_id' value='$job_id'>
                            <input type='hidden' name='source' value='$source'>
                            <button type='submit' name='confirm'>Confirm Application</button>
                          </form>";
                }
            }
        }
    }
    ?>
</div>

</body>
</html>