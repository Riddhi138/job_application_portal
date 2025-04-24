<?php
session_start(); // Start the session to access user data

// Database connection (XAMPP)
$conn = new mysqli("localhost", "root", "", "micro_job");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // SQL query to fetch user data based on email
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row['name'];
        $email = $row['email'];
        // ... other fields you want to display

        // Display user profile
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Profile Display</title>
            <style>
                /* Add your CSS for styling the profile page here */
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
                    text-align: center;
                }

                h2 {
                    color: #6a0dad;
                }

                p {
                    margin: 10px 0;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h2>Your Profile</h2>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                </div>
        </body>
        </html>
        <?php
    } else {
        echo "User not found.";
    }
    $conn->close();
} else {
    // Redirect to registration page if no user is logged in
    header("Location: registration_form.php"); // Replace with the actual filename of your registration form
    exit();
}
?>