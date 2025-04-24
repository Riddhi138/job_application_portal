<?php
session_start(); // Start session to track logged in user

$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "micro_job";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // For testing purposes - set a default user_id if not logged in
    // In production, you should redirect to login page
    $_SESSION['user_id'] = 1; // Default user for testing
    // Uncomment the below lines when login system is ready
    // header("Location: login.php");
    // exit();
}

$user_id = $_SESSION['user_id']; // Get the current user's ID

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user_id column exists in profiles table
$columnCheckQuery = "SHOW COLUMNS FROM profiles LIKE 'user_id'";
$columnResult = $conn->query($columnCheckQuery);

// If user_id column doesn't exist, add it
if ($columnResult && $columnResult->num_rows == 0) {
    $alterTableQuery = "ALTER TABLE profiles ADD COLUMN user_id INT NOT NULL DEFAULT 1 AFTER id";
    if ($conn->query($alterTableQuery) !== TRUE) {
        die("Error altering table: " . $conn->error);
    }
}

$name = $email = $phone = $skills = $experience = $resume = "";
$profile_exists = false;

// Fetch profile for current user
$query = "SELECT * FROM profiles WHERE user_id = $user_id LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $profile_exists = true;
    $row = $result->fetch_assoc();
    $name = isset($row["name"]) ? $row["name"] : "";
    $email = isset($row["email"]) ? $row["email"] : "";
    $phone = isset($row["phone"]) ? $row["phone"] : "";
    $skills = isset($row["skills"]) ? $row["skills"] : "No skills added yet";
    $experience = isset($row["experience"]) ? $row["experience"] : "No experience added yet";
    $resume = isset($row["resume"]) ? $row["resume"] : "";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $skills = $_POST["skills"];
    $experience = $_POST["experience"];

    // Keep existing resume if no new file is uploaded
    if (empty($_FILES["resume"]["name"]) && !empty($resume)) {
        // Keep existing resume path
    } else if (!empty($_FILES["resume"]["name"])) {
        // Handle file upload for new resume
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $resumeFileName = time() . "_" . basename($_FILES["resume"]["name"]);
        $targetFilePath = $targetDir . $resumeFileName;

        if (move_uploaded_file($_FILES["resume"]["tmp_name"], $targetFilePath)) {
            $resume = $targetFilePath;
        }
    }

    // Save or update data
    if ($profile_exists) {
        // Update existing profile
        $sql = "UPDATE profiles SET 
                name = '" . $conn->real_escape_string($name) . "', 
                email = '" . $conn->real_escape_string($email) . "', 
                phone = '" . $conn->real_escape_string($phone) . "', 
                skills = '" . $conn->real_escape_string($skills) . "', 
                experience = '" . $conn->real_escape_string($experience) . "', 
                resume = '" . $conn->real_escape_string($resume) . "',
                user_id = $user_id
                WHERE user_id = $user_id";
    } else {
        // Insert new profile
        $sql = "INSERT INTO profiles (user_id, name, email, phone, skills, experience, resume) 
                VALUES ($user_id, 
                '" . $conn->real_escape_string($name) . "', 
                '" . $conn->real_escape_string($email) . "', 
                '" . $conn->real_escape_string($phone) . "', 
                '" . $conn->real_escape_string($skills) . "', 
                '" . $conn->real_escape_string($experience) . "', 
                '" . $conn->real_escape_string($resume) . "')";
    }

    if ($conn->query($sql) === TRUE) {
        header("Location: display_profile.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E6E6FA;
            text-align: center;
            position: relative;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .container {
            width: 40%;
            background-color: white;
            padding: 20px;
            margin: auto;
            margin-top: 50px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.0);
            animation: slideIn 1.2s ease-out;
        }

        input, textarea, button {
            width: 90%;
            background-color: rgba(0, 0, 0, 0.0);
            margin: 10px 0;
            border: 1px solid;
            border-radius: 5px;
            padding: 10px;
        }

        button {
            background-color: #6a0dad;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #5a0cad;
        }

        .resume-link {
            margin-top: 10px;
        }

        #uploadResumeButton {
            background-color: #6a0dad;
        }

        #uploadResumeButton:hover {
            background-color: #5a0cad;
        }

        #buildResumeButton {
            background-color: #6a0dad;
        }

        #buildResumeButton:hover {
            background-color: #5a0cad;
        }
        
        /* Add this to show the selected file name */
        .file-selected {
            margin-top: 5px;
            font-size: 0.9em;
            color: #666;
        }
        
        /* Back button style */
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
            background-color: #5a0cad;
        }
    </style>
    <script>
        function triggerFileUpload() {
            document.getElementById("resume").click();
        }
        
        // Display the selected file name
        function showFileName() {
            const fileInput = document.getElementById('resume');
            const fileNameDisplay = document.getElementById('selectedFileName');
            
            if (fileInput.files.length > 0) {
                fileNameDisplay.textContent = 'Selected file: ' + fileInput.files[0].name;
            }
        }
    </script>
</head>
<body>
    <a href="job_search.php" class="back-button">Back</a>
    
    <div class="container">
        <h2>Your Information</h2>
        <form id="profileForm" method="POST" enctype="multipart/form-data">
            <input type="text" id="name" name="name" placeholder="Enter Name" value="<?php echo htmlspecialchars($name); ?>" required>
            <input type="email" id="email" name="email" placeholder="Enter Email" value="<?php echo htmlspecialchars($email); ?>" required>
            <input type="text" id="phone" name="phone" placeholder="Enter Phone Number" value="<?php echo htmlspecialchars($phone); ?>" required>
            <textarea id="skills" name="skills" placeholder="Enter Skills (e.g., PHP, MySQL, JavaScript)" required><?php echo htmlspecialchars($skills); ?></textarea>
            <textarea id="experience" name="experience" placeholder="Enter Experience (e.g., 2 years as Web Developer)" required><?php echo htmlspecialchars($experience); ?></textarea>

            <!-- Hidden file input -->
            <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" style="display:none;" onchange="showFileName()">
            <div id="selectedFileName" class="file-selected"></div>

            <!-- Upload Resume Button -->
            <button type="button" id="uploadResumeButton" onclick="triggerFileUpload()">Upload Resume</button>

            <!-- ✅ Build Resume Button -->
            <button type="button" id="buildResumeButton" onclick="window.location.href='build_resume.php'">Build Resume</button>

            <!-- Submit Button -->
            <button type="submit">Save</button>
        </form>

        <?php if (!empty($resume)): ?>
            <div class="resume-link">
                <a href="<?php echo htmlspecialchars($resume); ?>" target="_blank">View Uploaded Resume</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>