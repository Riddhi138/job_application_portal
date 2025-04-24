<?php
// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'job_portal');
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// Forgot password handling
if (isset($_POST['forgot_submit'])) {
    $email = $_POST['email'];
    $result = mysqli_query($conn, "SELECT id, email FROM users WHERE email = '$email'");
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $token = bin2hex(random_bytes(50));
        $expires = time() + 1800; // 30 minutes
        if (mysqli_query($conn, "INSERT INTO password_resets (user_id, token, expires) VALUES ('{$row['id']}', '$token', '$expires')")) {
            $reset_link = "http://localhost/reset_password.php?token=$token";
            if (mail($email, "Password Reset Request", "To reset your password, click here: $reset_link", "From: no-reply@jobportal.com")) {
                $success_message = "A password reset link has been sent to your email!";
            } else {
                $error_message = "Failed to send email. Please try again.";
            }
        }
    } else {
        $error_message = "No account found with that email.";
    }
}

// Reset password handling
if (isset($_POST['reset_submit'], $_GET['token'])) {
    $token = $_GET['token'];
    $result = mysqli_query($conn, "SELECT * FROM password_resets WHERE token = '$token' AND expires >= " . time());
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            $hashed_new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            if (mysqli_query($conn, "UPDATE users SET password = '$hashed_new_password' WHERE id = '{$row['user_id']}'")) {
                mysqli_query($conn, "DELETE FROM password_resets WHERE token = '$token'");
                $success_message = "Your password has been updated!";
            } else {
                $error_message = "Error updating password: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Passwords do not match!";
        }
    } else {
        $error_message = "Invalid or expired token.";
    }
}

// Change password handling
if (isset($_POST['change_submit'])) {
    $user_id = 1; // Placeholder for the logged-in user's ID
    $result = mysqli_query($conn, "SELECT password FROM users WHERE id = '$user_id'");
    $row = mysqli_fetch_assoc($result);
    if ($_POST['new_password'] === $_POST['confirm_password']) {
        if (password_verify($_POST['current_password'], $row['password'])) {
            $hashed_new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            if (mysqli_query($conn, "UPDATE users SET password = '$hashed_new_password' WHERE id = '$user_id'")) {
                $success_message = "Password updated successfully!";
            } else {
                $error_message = "Error updating password: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Current password is incorrect! <a href='?forgot=true' class='forgot-link'>Forgot your password?</a>";
        }
    } else {
        $error_message = "Passwords do not match!";
    }
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Management</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f2f2f2; margin: 0; padding: 0; }
        .container { max-width: 500px; margin: 100px auto; padding: 20px; background-color: #fff; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        h2, h3 { text-align: center; }
        form { display: flex; flex-direction: column; }
        label { margin-bottom: 5px; font-weight: bold; }
        input { padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px; }
        button { padding: 10px; background-color: #6a0dad; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #6a0dad; }
        .message { text-align: center; padding: 10px; background-color: #f9f9f9; border-radius: 5px; margin-bottom: 20px; }
        .error { background-color: #ffdddd; color: #ff0000; }
        .success { background-color: #ddffdd; color: #00cc00; }
        .forgot-link { color: #6a0dad; text-decoration: none; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php elseif (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['forgot'])): ?>
            <form action="" method="POST">
                <h3>Forgot Your Password?</h3>
                <label for="email">Enter your email:</label>
                <input type="email" name="email" required>
                <button type="submit" name="forgot_submit">Send Reset Link</button>
            </form>
        <?php elseif (isset($_GET['token'])): ?>
            <form action="" method="POST">
                <h3>Reset Your Password</h3>
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" required>
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" required>
                <button type="submit" name="reset_submit">Reset Password</button>
            </form>
        <?php else: ?>
            <form action="" method="POST">
                <h3>Change Your Password</h3>
                <label for="current_password">Current Password:</label>
                <input type="password" name="current_password" required>
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" required>
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" required>
                <button type="submit" name="change_submit">Change Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
