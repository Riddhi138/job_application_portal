<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "micro_job";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($name) || empty($email) || empty($password)) {
            $error_message = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email format.";
        } elseif (!preg_match("/^[a-zA-Z ]*$/", $name)) {
            $error_message = "Name can only contain letters and spaces.";
        } elseif (strlen($password) != 6 || !ctype_digit($password)) {
            $error_message = "Password must be exactly 6 digits.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Prepare SQL to insert the user data
            $stmt = $conn->prepare("INSERT INTO registration_admin (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            // Execute and check for success
            if ($stmt->execute()) {
                $success_message = "Registration successful! Redirecting you to login...";
                // Redirect to login page after a brief pause (1 second)
                header("Refresh: 1; url=admin_login.php");
                exit;
            } else {
                $error_message = "Error: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #E6E6FA;
        }

        .container {
            width: 350px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            width: calc(100% - 12px);
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        button {
            background-color: #6a0dad;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            width: 100%;
        }

        .error {
            color: red;
            font-size: 0.8em;
            display: none;
        }

        .error.show {
            display: block;
        }

        .form-switch {
            text-align: center;
            margin-top: 10px;
        }

        .form-switch a {
            text-decoration: none;
            color: blue;
        }

        .success {
            color: green;
            font-size: 1em;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container" id="registrationFormContainer">
        <h2>Registration</h2>

        <!-- Display error or success messages -->
        <?php if (!empty($error_message)) { ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php } ?>

        <?php if (!empty($success_message)) { ?>
            <div class="success"><?php echo $success_message; ?></div>
        <?php } ?>

        <!-- Registration Form -->
        <form id="registrationForm" method="post" action="">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
                <div class="error" id="nameError"></div>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <div class="error" id="emailError"></div>
            </div>
            
            <div class="form-group">
                <label for="password">Password (6 digits):</label>
                <input type="password" id="password" name="password" required>
                <div class="error" id="passwordError"></div>
            </div>

            <button type="submit" name="register">Register Now</button>
        </form>
        
        <div class="form-switch">
            <a href="admin_login.php">Already have an account? Login</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const registrationForm = document.getElementById('registrationForm');
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            const nameError = document.getElementById('nameError');
            const emailError = document.getElementById('emailError');
            const passwordError = document.getElementById('passwordError');

            registrationForm.addEventListener('submit', function(event) {
                let isValid = true;

                if (!nameInput.value.trim()) {
                    nameError.textContent = 'Name is required.';
                    nameError.classList.add('show');
                    isValid = false;
                } else {
                    nameError.textContent = '';
                    nameError.classList.remove('show');
                }

                if (!emailInput.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
                    emailError.textContent = 'Invalid email format.';
                    emailError.classList.add('show');
                    isValid = false;
                } else {
                    emailError.textContent = '';
                    emailError.classList.remove('show');
                }

                if (!passwordInput.value.trim() || !/^\d{6}$/.test(passwordInput.value)) {
                    passwordError.textContent = 'Password must be 6 digits.';
                    passwordError.classList.add('show');
                    isValid = false;
                } else {
                    passwordError.textContent = '';
                    passwordError.classList.remove('show');
                }

                if (!isValid) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
