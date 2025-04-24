<?php
// Database Connection
$dbConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'micro_job'
];

$conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session to get current user ID
session_start();

// Ensure the session is valid
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if session doesn't exist
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle Search Query - Improved to handle partial matches better
$jobTitle = isset($_GET['job_title']) ? trim($_GET['job_title']) : '';
$city = isset($_GET['city']) ? trim($_GET['city']) : '';

// Check if there's any search query
$isSearching = (!empty($jobTitle) || !empty($city));

// Modified query to find jobs that match any part of the search terms
// Using LIKE with wildcards on both sides for better matching
$sql = "SELECT id, title, company, location, salary, job_type, description, requirements, company_id, created_at 
        FROM jobs WHERE 1=1";

// Only add title condition if title search is provided
if (!empty($jobTitle)) {
    $sql .= " AND (
        title LIKE ? OR
        description LIKE ? OR
        requirements LIKE ?
    )";
}

// Only add location condition if city search is provided
if (!empty($city)) {
    $sql .= " AND location LIKE ?";
}

$sql .= " ORDER BY created_at DESC";

// Prepare statement with dynamic parameter binding
$stmt = $conn->prepare($sql);

// Bind parameters based on search criteria
if (!empty($jobTitle) && !empty($city)) {
    // Both title and city are provided
    $searchTitle = "%$jobTitle%";
    $searchCity = "%$city%";
    $stmt->bind_param("ssss", $searchTitle, $searchTitle, $searchTitle, $searchCity);
} elseif (!empty($jobTitle)) {
    // Only title is provided
    $searchTitle = "%$jobTitle%";
    $stmt->bind_param("sss", $searchTitle, $searchTitle, $searchTitle);
} elseif (!empty($city)) {
    // Only city is provided
    $searchCity = "%$city%";
    $stmt->bind_param("s", $searchCity);
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch user skills
$userSkills = [];
$skillsQuery = "SELECT skills FROM profiles WHERE user_id = ?";
$skillsStmt = $conn->prepare($skillsQuery);
$skillsStmt->bind_param("i", $user_id);
$skillsStmt->execute();
$skillsResult = $skillsStmt->get_result();

if ($skillsResult && $skillsResult->num_rows > 0) {
    $skillsRow = $skillsResult->fetch_assoc();
    if (!empty($skillsRow['skills'])) {
        $userSkills = explode(',', strtolower($skillsRow['skills']));
        $userSkills = array_map('trim', $userSkills);
        $userSkills = array_filter($userSkills);
    }
}
$skillsStmt->close();

// Modified recommended jobs query to fetch all jobs and filter for matches
$recommendedJobs = [];
if (!empty($userSkills)) {
    $recommendedSql = "SELECT id, title, company, location, salary, job_type, description, requirements, company_id, created_at 
                      FROM jobs ORDER BY created_at DESC";
    $recommendedResult = $conn->query($recommendedSql);

    if ($recommendedResult && $recommendedResult->num_rows > 0) {
        while ($job = $recommendedResult->fetch_assoc()) {
            // Check for skill matches in title, description, and requirements
            $jobText = strtolower($job['title'] . ' ' . $job['description'] . ' ' . $job['requirements']);
            $matchCount = 0;
            $matchedSkills = [];

            foreach ($userSkills as $skill) {
                if (!empty($skill) && strpos($jobText, $skill) !== false) {
                    $matchCount++;
                    $matchedSkills[] = $skill;
                }
            }

            $matchPercentage = count($userSkills) > 0 ? ($matchCount / count($userSkills)) * 100 : 0;

            if ($matchCount > 0) {
                $job['match_percentage'] = $matchPercentage;
                $job['matched_skills'] = $matchedSkills;
                $recommendedJobs[] = $job;
            }
        }

        // First sort by match percentage, then by date for jobs with the same match percentage
        usort($recommendedJobs, function ($a, $b) {
            if ($a['match_percentage'] == $b['match_percentage']) {
                return strtotime($b['created_at']) - strtotime($a['created_at']); // Newer first
            }
            return $b['match_percentage'] <=> $a['match_percentage'];
        });
    }
}

// Get company information for display
function getCompanyInfo($conn, $company_id) {
    // Check if company_id is null or empty
    if (empty($company_id)) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT name, email FROM companies WHERE id = ?");
    if (!$stmt) {
        // Return null if prepare statement fails
        return null;
    }
    
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Debug function - can be removed in production
function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug: " . $output . "');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings</title>
    <style>
        body {
            background-color: #F4F4F4;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }

        .top-bar {
            position: absolute;
            top: 20px;
            left: 40px;
            right: 80px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: calc(100% - 100px);
        }

        .logo {
            margin-top: 2%;
            font-size: 22px;
            font-weight: bold;
            color: #6a0dad;
            text-decoration: none;
        }

        .logo span {
            color: black;
        }

        .icons {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .icon {
            margin-top: 2%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            object-fit: contain;
            border: 2px solid black;
            transition: 0.3s ease-in-out;
            border-radius: 50%;
        }

        .settings-icon {
            width: 30px;
            height: 30px;
            cursor: pointer;
            object-fit: contain;
            border: none;
        }

        .search-bar {
            width: 90%;
            margin-top: 7%;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            gap: 10px;
        }

        .search-bar input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .search-button, .apply-button {
            background-color: #6a0dad;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .search-button:hover, .apply-button:hover {
            background-color: #570a9e;
        }

        .container {
            width: 90%;
            height: 400px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px;
            border-radius: 8px;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }

        .job-box {
            width: 95%;
            border: 1px solid gray;
            padding: 12px;
            border-radius: 8px;
            text-align: left;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .job-title {
            font-size: 18px;
            font-weight: bold;
        }

        .job-details {
            margin-top: 5px;
            font-size: 14px;
        }
        
        .company-info {
            font-style: italic;
            font-size: 12px;
            color: #ccc;
            margin-top: 3px;
        }

        .section-title {
            margin-left: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            padding-bottom: 5px;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .recommended-section {
            margin-bottom: 10px;
        }

        .match-badge {
            background-color: #6a0dad30;
            color: white;
            border-radius: 12px;
            padding: 3px 8px;
            font-size: 12px;
            margin-left: 8px;
        }

        .match-info {
            display: flex;
            align-items: center;
        }

        .matched-skills {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 5px;
        }

        .skill-tag {
            background-color: #6a0dad30;
            color: #e0c0ff;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 11px;
        }
        
        .search-info {
            margin-left: 20px;
            font-size: 14px;
            color: #ccc;
            font-style: italic;
        }
        
        .clear-search {
            background-color: transparent;
            color: #ccc;
            border: 1px solid #ccc;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-left: 10px;
            text-decoration: none;
        }
        
        .clear-search:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* Logout button style */
        .logout-btn {
            background-color: #6a0dad;
            color: white;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            margin-top: 2%;
            transition: 0.3s;
        }
        
        .logout-btn:hover {
            background-color: #570a9e;
        }
    </style>
</head>

<body>
    <div class="top-bar">
        <a href="index.php" class="logo">Career<span>Connect</span></a> 
        <div class="icons">
            <!-- Added logout button before settings icon -->
            <a href="loginpage.php?logout=true" class="logout-btn">Logout</a>
            <a href="settings.php"><img src="settings.jpg" class="settings-icon" alt="Settings"></a>
            <a href="inbox.php"><img src="msg.jpg" class="icon" alt="Inbox"></a>
            <a href="notifications.php"><img src="n.jpg" class="icon" alt="Notifications"></a>
            <a href="display_profile.php" onclick="myFunction();"><img src="pro.jpg" class="icon" alt="Profile"></a>
        </div>
    </div>

    <script>
    function myFunction() {
        console.log("Link clicked!");
    }
    </script>

    <form method="GET" class="search-bar">
        <input type="text" name="job_title" placeholder="Search by Job Title, Description, or Skills" value="<?= htmlspecialchars($jobTitle) ?>">
        <input type="text" name="city" placeholder="Search by City" value="<?= htmlspecialchars($city) ?>">
        <button type="submit" class="search-button">Search</button>
    </form>

    <div class="container">
        <?php if($isSearching): ?>
            <div class="search-info">
                Search results for: <?= !empty($jobTitle) ? '"'.htmlspecialchars($jobTitle).'"' : '' ?> 
                <?= (!empty($jobTitle) && !empty($city)) ? 'in' : '' ?> 
                <?= !empty($city) ? '"'.htmlspecialchars($city).'"' : '' ?>
                <a href="job_search.php" class="clear-search">Clear search</a>
            </div>
        <?php endif; ?>
        
        <?php if(!$isSearching): ?>
        <div class="recommended-section">
            <h2 class="section-title">Recommended Jobs Based on Your Skills</h2>
            <?php if (!empty($recommendedJobs)): ?>
                <?php foreach ($recommendedJobs as $job): 
                    $companyInfo = getCompanyInfo($conn, $job['company_id']);
                ?>
                    <div class="job-box">
                        <div>
                            <div class="match-info">
                                <div class="job-title"><?= htmlspecialchars($job['title']) ?></div>
                                <div class="match-badge"><?= round($job['match_percentage']) ?>% Match</div>
                            </div>
                            <div class="job-details">
                                <?= htmlspecialchars($job['company']) ?> · <?= htmlspecialchars($job['location']) ?> · <?= htmlspecialchars($job['salary']) ?>
                            </div>
                            <?php if ($companyInfo !== null): ?>
                            <div class="company-info">
                                Posted by: <?= htmlspecialchars($companyInfo['name']) ?>
                            </div>
                            <?php endif; ?>
                            <div class="matched-skills">
                                <?php foreach ($job['matched_skills'] as $skill): ?>
                                    <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <a href="job_details.php?id=<?= $job['id'] ?>" class="apply-button">Apply Now</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="margin-left: 20px;">No recommended jobs found based on your skills.</p>
            <?php endif; ?>
        </div>

        <hr style="border: 0; height: 1px; background: rgba(255, 255, 255, 0.3); margin: 2px 0;">
        <?php endif; ?>

        <h2 class="section-title"><?= $isSearching ? 'Search Results' : 'Jobs For You' ?></h2>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $companyInfo = getCompanyInfo($conn, $row['company_id']);
            ?>
                <div class="job-box">
                    <div>
                        <div class="job-title"><?= htmlspecialchars($row['title']) ?></div>
                        <div class="job-details">
                            <?= htmlspecialchars($row['company']) ?> · <?= htmlspecialchars($row['location']) ?> · <?= htmlspecialchars($row['salary']) ?>
                        </div>
                        <?php if ($companyInfo !== null): ?>
                        <div class="company-info">
                            Posted by: <?= htmlspecialchars($companyInfo['name']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <a href="job_details.php?id=<?= $row['id'] ?>" class="apply-button">Apply Now</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="margin-left: 20px;">No jobs found matching your criteria.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>