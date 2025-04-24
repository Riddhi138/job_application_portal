<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "micro_job";

$conn = new mysqli($servername, $username, $password, $dbname);

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
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $success_message = "Registration successful! Redirecting to login...";
                echo "<script>
                        alert('$success_message');
                        window.location.href = 'front.php';
                    </script>";
                exit();
            } else {
                $error_message = "Error: " . $stmt->error;
            }

            $stmt->close();
        }
    } elseif (isset($_POST['login'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $error_message = "Email and password are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email format.";
        } else {
            $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($userId, $userName, $hashedPassword);
                $stmt->fetch();

                if (password_verify($password, $hashedPassword)) {
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user_name'] = $userName;

                    echo "<script>
                            window.location.href = 'job_search.php';
                        </script>";
                    exit();
                } else {
                    $error_message = "Incorrect password.";
                }
            } else {
                $error_message = "User not found.";
            }

            $stmt->close();
        }
    }
}

$conn->close();

// Handle logout properly
if (isset($_GET['logout'])) {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to front.php
    header("Location: front.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            background-color: #E6E6FA;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            color: black;
        }

        .container {
            background: rgba(0, 0, 0, 0.1);
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            width: 350px;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        h2 {
            margin-bottom: 20px;
        }

        .input-box {
            margin-bottom: 15px;
            text-align: left;
            width: 100%;
        }

        .input-box input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.7);
            color: black;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        .input-box input:focus {
            border-color: #6a0dad;
        }

        button {
            padding: 10px 20px;
            background: #6a0dad;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background: #4a148c;
        }

        .error-message, .success-message {
            margin-top: 10px;
            font-size: 16px;
            text-align: center;
            animation: slideIn 0.5s ease-in-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .error-message {
            color: red;
        }

        .success-message {
            color: green;
        }
        .form-switch{
            display:flex;
            justify-content:space-around;
        }
        .form-switch button{
            width:45%;
        }
        .forgot-password {
            margin-top: 10px;
            text-align: center;
        }
        .forgot-password a {
            color: #6a0dad;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .forgot-password a:hover{
            color: #4a148c;
        }
        .logout-btn {
            padding: 10px 20px;
            background: #6a0dad;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .logout-btn:hover {
            background: #4a148c;
        }
    </style>
</head>
<body>
    <?php if (!isset($_SESSION['user_id'])): ?>
    <div class="container">
        <h2>Login</h2>

        <?php if (!empty($error_message)) { ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php } ?>

        <?php if (!empty($success_message)) { ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php } ?>

        <form action="" method="POST" id="loginForm" name="loginForm">
            <div class="input-box">
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" placeholder="Enter your name">
            </div> <div class="input-box">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" placeholder="Enter your email">
            </div>
            <div class="input-box">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" placeholder="Enter your password">
            </div>
            <button type="submit" name="login">Login</button>
        </form>
        <div class="forgot-password">
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
    </div>
    <?php else: ?>
        <div class="container">
            <h1>Hello, <?php echo $_SESSION['user_name']; ?>!</h1>
            <p>You are currently logged in.</p>
            <a href="?logout=true" class="logout-btn">Logout</a>
        </div>
    <?php endif; ?>
</body>
</html>