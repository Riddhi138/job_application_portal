<?php
// Assuming you have the database connection details from front.docx
$dbConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'micro_job' // Use the correct database name
];

function connectDB() {
    global $dbConfig;
    $conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

$errorMessage = ""; // Initialize error message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // Hash the password

    $conn = connectDB();

    $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"; // Removed mobile from query
    $stmt = $conn->prepare($sql);

    if ($stmt === false) { // Check if prepare failed
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        echo "Registration successful!";
        // Redirect to login or home page
        header("Location: loginpage.php");
        exit;
    } else {
        if ($stmt->errno == 1062) { // Check for duplicate entry error
            $errorMessage = "You already have an account";
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
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
        .error-message{
            text-align: center;
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container" id="registrationFormContainer">
        <h2>Registration</h2>

        <form id="registrationForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
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
            <button type="submit">Register</button>
        </form>
        <div class="form-switch">
            <a href="loginpage.php">Already have an account? Login</a>
        </div>
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
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