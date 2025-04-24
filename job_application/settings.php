<?php
// PHP code to handle the redirection
if (isset($_POST['view_application_history'])) {
    // Redirect to the app_disp.php page
    header('Location: app_disp.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="settings-container">
        <a href="job_search.php" class="back-button">Back</a>
        <h2>Account Settings</h2>
        <form id="settingsForm" method="POST">
            <label>
                Profile Visibility:
                <select name="profile_visibility" id="profile_visibility">
                    <option value="public">Public</option>
                    <option value="private">Private</option>
                    <option value="employers">Employers Only</option>
                </select>
            </label>
            
            <label>
                Job Alerts:
                <input type="checkbox" name="job_alerts" id="job_alerts">
            </label>
            
            <label>
                Resume Privacy:
                <input type="checkbox" name="resume_privacy" id="resume_privacy">
            </label>
            
            <!-- Application History form -->
            <label>
                Application History:
                <button type="submit" name="view_application_history" onclick="window.href='app_disp.php?id=<=$row['id']?>'">View</button>
            </label>
            
           <label>
                Account Security:
                <!-- Redirect to password management page when clicked -->
                <button type="button" onclick="window.location.href='password.php'">Manage</button>
            </label>
            
            <button type="submit" class="search">Save Settings</button>
        </form>
    </div>
</body>
</html>

<style>
body {
    font-family: Arial, sans-serif;
    background-color: #E6E6FA;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.settings-container {
    background: rgba(0, 0, 0, 0.8);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    color: white;
    width: 700px;
    position: relative;
}

h2 {
    text-align: center;
}

form {
    display: flex;
    flex-direction: column;
}

label {
    margin: 10px 0;
}

button {
  width: 20%;
  background-color: #6a0dad; 
  border: none;
  padding: 10px 16px;
  border-radius: 20px;
  cursor: pointer;
  color: white;
  font-size: 14px;
  font-weight: bold;
  transition: 0.3s;  
  margin-left: 20px;
}

.search {
  width: 20%;
  background-color: #6a0dad; 
  border: none;
  padding: 10px 16px;
  border-radius: 20px;
  cursor: pointer;
  font-size: 14px;
  font-weight: bold;
  color: white;
  transition: 0.3s;  
  margin-left: 500px;
  margin-top: 30px;
}

button:hover, .search:hover {
  background-color: #570a9e;  /* Darker purple on hover */
}

.back-button {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: #6a0dad;
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: bold;
    font-size: 14px;
    transition: 0.3s;
}

.back-button:hover {
    background-color: #570a9e;
}
</style>