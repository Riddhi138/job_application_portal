<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'about_us');

if ($conn->connect_error) {
    die('Connection Failed: ' . $conn->connect_error);
}

// Fetch company details
$sql = "SELECT * FROM company_info LIMIT 1";
$result = $conn->query($sql);
$company = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - CareerConnect</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E6E6FA;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
        }
        .header {
            text-align: center;
            padding: 20px;
            background-color: #4B0082;
            color: white;
            font-size: 28px;
            font-weight: bold;
            width: 100%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            animation: fadeIn 1.5s ease-in-out forwards;
        }
        .container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 90%;
            max-width: 1200px;
            margin-top: 30px;
        }
        .box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            flex: 1;
            margin: 10px;
            text-align: center;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            min-height: 250px; 
            display: flex;
            flex-direction: column;
            justify-content: center;
            animation: slideUp 1s ease-in-out forwards;
        }
        .box:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        h2 {
            color: #4B0082;
            font-size: 22px;
        }
        p {
            color: #333;
            font-size: 16px;
            line-height: 1.6;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @media (max-width: 900px) {
            .container {
                flex-direction: column;
                align-items: center;
            }
            .box {
                width: 90%;
            }
        }
    </style>
</head>
<body>

    <div class="header">About Us</div>

    <div class="container">
        <div class="box">
            <h2><?php echo htmlspecialchars($company['name']); ?></h2>
            <p><strong>Founded in:</strong> <?php echo htmlspecialchars($company['established_year']); ?></p>
            <p><?php echo nl2br(htmlspecialchars($company['description'])); ?></p>
        </div>

        <div class="box">
            <h2>Our Mission</h2>
            <p><?php echo nl2br(htmlspecialchars($company['mission'])); ?></p>
        </div>

        <div class="box">
            <h2>Our Vision</h2>
            <p><?php echo nl2br(htmlspecialchars($company['vision'])); ?></p>
        </div>
    </div>

</body>
</html>

<?php $conn->close(); ?>
