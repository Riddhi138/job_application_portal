<?php
// Start session for user authentication
session_start();

// Database connection configuration
$dbConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'micro_job'
];

// Connect to database
function connectDB() {
    global $dbConfig;
    $conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get job categories with counts
function getJobCategories() {
    $conn = connectDB();
    
    $categories = [];
    $sql = "SELECT c.id, c.name, c.icon, COUNT(j.id) as job_count 
            FROM categories c
            LEFT JOIN jobs j ON c.id = j.category_id
            GROUP BY c.id
            ORDER BY job_count DESC
            LIMIT 6";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    } else {
        // Default categories if no data in database
        $categories = [
            ["id" => 1, "name" => "Technology", "icon" => "💻", "job_count" => 1240],
            ["id" => 2, "name" => "Business", "icon" => "💼", "job_count" => 840],
            ["id" => 3, "name" => "Design", "icon" => "🎨", "job_count" => 560],
            ["id" => 4, "name" => "Healthcare", "icon" => "🏥", "job_count" => 950],
            ["id" => 5, "name" => "Science", "icon" => "🔬", "job_count" => 480],
            ["id" => 6, "name" => "Education", "icon" => "🏫", "job_count" => 620]
        ];
    }
    
    $conn->close();
    return $categories;
}

// Handle job search
function searchJobs($keyword, $location) {
    $conn = connectDB();
    
    $keyword = $conn->real_escape_string($keyword);
    $location = $conn->real_escape_string($location);
    
    $sql = "SELECT j.*, c.name as company_name 
            FROM jobs j
            JOIN companies c ON j.company_id = c.id
            WHERE (j.title LIKE '%$keyword%' OR j.description LIKE '%$keyword%')";
            
    if (!empty($location)) {
        $sql .= " AND j.location LIKE '%$location%'";
    }
    
    $result = $conn->query($sql);
    $jobs = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $jobs[] = $row;
        }
    }
    
    $conn->close();
    return $jobs;
}

// We're removing the login and registration handling code from front.php
// since these are handled by loginpage.php and registration.php

// Handle search form submission
$searchResults = [];
if (isset($_GET['search'])) {
    $keyword = $_GET['keyword'] ?? '';
    $location = $_GET['location'] ?? '';
    
    if (!empty($keyword)) {
        $searchResults = searchJobs($keyword, $location);
    }
}

// Get job categories
$categories = getJobCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareerConnect | Find Your Dream Job</title>
    <style>
        :root {
            --primary: #6a0dad;
            --primary-light: #9c27b0;
            --primary-dark: #4a0072;
            --secondary: #f3e5f5;
            --text-light: #ffffff;
            --text-dark: #333333;
            --success: #4caf50;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f9f9f9;
        }
        
        .navbar {
            background-color: var(--primary);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--text-light);
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .logo span {
            color: var(--secondary);
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-link {
            margin-left: 20px;
            position: relative;
        }
        
        .nav-link a {
            color: var(--text-light);
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
            padding: 8px 12px;
            border-radius: 4px;
        }
        
        .nav-link a:hover {
            background-color: var(--primary-light);
        }
        
        .nav-link.active a {
            background-color: var(--primary-light);
        }
        
        .auth-buttons {
            display: flex;
        }
        
        .auth-btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .login-btn {
            color: var(--primary);
            background-color: var(--text-light);
            margin-right: 10px;
        }
        
        .login-btn:hover {
            background-color: #f0f0f0;
        }
        
        .register-btn {
            color: var(--text-light);
            background-color: var(--primary-light);
            border: 1px solid var(--text-light);
        }
        
        .register-btn:hover {
            background-color: var(--primary-dark);
        }
        
        /* Dropdown styles */
        .dropdown {
            position: relative;
            display: inline-block;
            margin-right: 10px;
        }
        
        .dropdown:last-child {
            margin-right: 0;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 120px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 4px;
            right: 0;
        }
        
        .dropdown-content a {
            color: var(--text-dark);
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
            transition: background-color 0.3s;
        }
        
        .dropdown-content a:hover {
            background-color: #f1f1f1;
            color: var(--primary);
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
        }
        
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            padding: 80px 0;
            text-align: center;
            color: var(--text-light);
        }
        
        .hero h1 {
            font-size: 42px;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 18px;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .search-box {
            background-color: var(--text-light);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
        }
        
        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .search-btn {
            background-color: var(--primary);
            color: var(--text-light);
            border: none;
            border-radius: 4px;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .search-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .categories {
            padding: 60px 0;
            background-color: var(--secondary);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
            color: var(--primary);
            font-size: 32px;
        }
        
        .category-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .category-card {
            background-color: white;
            border-radius: 8px;
            text-align: center;
            padding: 25px 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
        }
        
        .category-icon {
            width: 50px;
            height: 50px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--secondary);
            border-radius: 50%;
            color: var(--primary);
            font-size: 20px;
        }
        
        .category-name {
            font-size: 18px;
            color: var(--primary);
            margin-bottom: 8px;
        }
        
        .job-count {
            font-size: 14px;
            color: #666;
        }
        
        .cta {
            padding: 60px 0;
            text-align: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: var(--text-light);
        }
        
        .cta h2 {
            font-size: 32px;
            margin-bottom: 20px;
        }
        
        .cta p {
            font-size: 18px;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cta-btn {
            display: inline-block;
            background-color: var(--text-light);
            color: var(--primary);
            padding: 12px 25px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .cta-btn:hover {
            background-color: #f0f0f0;
        }
        
        footer {
            background-color: var(--primary-dark);
            color: var(--text-light);
            padding: 40px 0 20px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .footer-section h3 {
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: var(--text-light);
        }
        
        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #aaa;
            font-size: 14px;
        }
        
        /* Auth Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 8px;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .modal-header h3 {
            color: var(--primary);
            font-size: 24px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: var(--primary);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-dark);
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        .form-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .form-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .error-message {
            color: #e53935;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .search-results {
            padding: 60px 0;
        }
        
        .job-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .job-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 25px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .company-logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--secondary);
            color: var(--primary);
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .job-title {
            font-size: 20px;
            color: var(--primary);
            margin-bottom: 8px;
        }
        
        .company-name {
            font-size: 16px;
            color: var(--text-dark);
            margin-bottom: 12px;
        }
        
        .job-details {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .job-detail {
            font-size: 14px;
            color: #666;
            margin-right: 15px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        
        .job-description {
            color: #555;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        
        .apply-btn {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .apply-btn:hover {
            background-color: var(--primary-dark);
        }
        
        /* Mobile Navigation */
        .menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }
        
        .bar {
            width: 25px;
            height: 3px;
            background-color: var(--text-light);
            margin: 3px 0;
            transition: 0.4s;
        }
        
        @media screen and (max-width: 768px) {
            .menu-toggle {
                display: flex;
            }
            
            .nav-links {
                position: fixed;
                left: -100%;
                top: 70px;
                flex-direction: column;
                background-color: var(--primary);
                width: 100%;
                text-align: center;
                transition: 0.3s;
                box-shadow: 0 10px 10px rgba(0, 0, 0, 0.1);
                padding: 20px 0;
            }
            
            .nav-links.active {
                left: 0;
            }
            
            .nav-link {
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo">Career<span>Connect</span></a>
            
            <div class="menu-toggle">
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
            </div>
            
            <ul class="nav-links">
                <li class="nav-link <?php echo empty($_GET['page']) ? 'active' : ''; ?>">
                 <a href="#" onclick="alert('To explore more Register or Login First'); return false;">Home</a>

                </li>
                <li class="nav-link <?php echo ($_GET['page'] ?? '') == 'jobs' ? 'active' : ''; ?>">
                   <a href="#" onclick="alert('To explore more Register or Login First'); return false;">Jobs</a>
                </li>
                <li class="nav-link <?php echo ($_GET['page'] ?? '') == 'search' ? 'active' : ''; ?>">
              <a href="#" onclick="alert('To explore more Register or Login First'); return false;">Search</a>
                </li>
                <li class="nav-link <?php echo ($_GET['page'] ?? '') == 'companies' ? 'active' : ''; ?>">
                   <a href="#" onclick="alert('To explore more Register or Login First'); return false;">Companies</a>
                </li>
                <li class="nav-link <?php echo ($_GET['page'] ?? '') == 'about' ? 'active' : ''; ?>">
                    <a href="about_us.php">About Us</a>
                </li>
            </ul>
            
            <div class="auth-buttons">
                <?php if (isLoggedIn()): ?>
                    <a href="index.php?page=dashboard" class="auth-btn login-btn">Dashboard</a>
                    <a href="logoutpage.php" class="auth-btn register-btn">Logout</a>
                <?php else: ?>
                    <div class="dropdown">
                        <a href="loginpage.php" class="auth-btn login-btn">Login</a>
                        <div class="dropdown-content login-dropdown">
                            <a href="loginpage.php">User</a>
                            <a href="admin_login.php">Admin</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a href="registration.php" class="auth-btn register-btn">Register</a>
                        <div class="dropdown-content register-dropdown">
                            <a href="registration.php">User</a>
                            <a href="admin_registration.php">Admin</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <?php
    // Page content based on the 'page' parameter
    $page = $_GET['page'] ?? 'home';
    
    switch ($page) {
        case 'jobs':
            include 'pages/jobs.php';
            break;
        case 'search':
            include 'pages/search.php';
            break;
        case 'companies':
            include 'pages/companies.php';
            break;
        case 'about':
            include 'pages/about.php';
            break;
        case 'dashboard':
            include 'pages/dashboard.php';
            break;
        default:
            // Home page content
    ?>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Find Your Dream Job Today</h1>
            <p>Discover thousands of job opportunities with the best companies across different industries and locations.</p>
            
            <form action="index.php" method="GET" class="search-box">
                <input type="hidden" name="page" value="search">
                <input type="hidden" name="search" value="1">
                <input type="text" name="keyword" class="search-input" placeholder="Job title or keyword">
                <input type="text" name="location" class="search-input" placeholder="Location">
             <button type="button" class="search-btn" onclick="alert('To Explore this category , Login or Register first....')">Search Jobs</button>
            </form>
        </div>
    </section>
    
    <!-- Display search results if any -->
    <?php if (!empty($searchResults)): ?>
    <section class="search-results">
        <div class="container">
            <h2 class="section-title">Search Results</h2>
            
            <div class="job-cards">
                <?php foreach ($searchResults as $job): ?>
                <div class="job-card">
                    <div class="company-logo"><?php echo substr($job['company_name'], 0, 2); ?></div>
                    <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                    <p class="company-name"><?php echo htmlspecialchars($job['company_name']); ?></p>
                    <div class="job-details">
                        <span class="job-detail"><?php echo htmlspecialchars($job['job_type']); ?></span>
                        <span class="job-detail"><?php echo htmlspecialchars($job['location']); ?></span>
                        <span class="job-detail"><?php echo htmlspecialchars($job['salary']); ?></span>
                    </div>
                    <p class="job-description"><?php echo htmlspecialchars(substr($job['description'], 0, 150)) . '...'; ?></p>
                    <a href="index.php?page=job_details&id=<?php echo $job['id']; ?>" class="apply-btn">View Details</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Job Categories Section -->
    <section class="categories">
        <div class="container">
            <h2 class="section-title">Explore Job Categories</h2>
            
            <div class="category-cards">
                <?php foreach ($categories as $category): ?>
                <div class="category-card">
                    <div class="category-icon"><?php echo $category['icon']; ?></div>
                    <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p class="job-count"><?php echo number_format($category['job_count']); ?> jobs</p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Call to Action Section -->
    <section class="cta">
        <div class="container">
            <h2>Ready to take the next step in your career?</h2>
            <p>Create an account today and get access to thousands of job opportunities tailored to your skills and experience.</p>
            <?php if (isLoggedIn()): ?>
                <a href="index.php?page=dashboard" class="cta-btn">View Dashboard</a>
            <?php else: ?>
                <a href="registration.php" class="cta-btn">Create Account</a>
            <?php endif; ?>
        </div>
    </section>
    
    <?php
        } // End of switch case default
    ?>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>CareerConnect</h3>
                    <p>Find your dream job with the perfect company match for your skills and experience.</p>
                </div>
                
                <div class="footer-section">
                    <h3>For Job Seekers</h3>
                    <ul class="footer-links">
                        <li><a href="index.php?page=jobs">Browse Jobs</a></li>
                        <li><a href="index.php?page=resources">Career Resources</a></li>
                        <li><a href="index.php?page=resume">Resume Builder</a></li>
                        <li><a href="index.php?page=salary">Salary Calculator</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>For Employers</h3>
                    <ul class="footer-links">
                        <li><a href="index.php?page=post">Post a Job</a></li>
                        <li><a href="index.php?page=talent">Find Talent</a></li>
                        <li><a href="index.php?page=pricing">Pricing</a></li>
                        <li><a href="index.php?page=resources">Resources</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>About Us</h3>
                    <ul class="footer-links">
                        <li><a href="about_us.php">About CareerConnect</a></li>
                        <li><a href="index.php?page=contact">Contact Us</a></li>
                        <li><a href="index.php?page=privacy">Privacy Policy</a></li>
                        <li><a href="index.php?page=terms">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> CareerConnect. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Mobile Navigation Toggle
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });
        
        // CTA Register Button
        const ctaRegisterBtn = document.getElementById('ctaRegisterBtn');
        if (ctaRegisterBtn) {
            ctaRegisterBtn.addEventListener('click', function() {
                window.location.href = 'registration.php';
            });
        }
    </script>
</body>
</html>