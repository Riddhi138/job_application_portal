<?php
session_start();
header('Content-Type: application/json'); // So it returns JSON to JavaScript

$conn = new mysqli("localhost", "root", "", "micro_job"); // Make sure this matches your DB name

if ($conn->connect_error) {
  die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Get company_id from session
$company_id = null;
if (isset($_SESSION['company_id'])) {
    $company_id = $_SESSION['company_id'];
} else if (isset($_SESSION['user_id'])) {
    // Try to get company_id from user_id if not in session
    $user_id = $_SESSION['user_id'];
    $company_query = $conn->prepare("SELECT id FROM companies WHERE user_id = ?");
    
    if ($company_query) {
        $company_query->bind_param("i", $user_id);
        $company_query->execute();
        $result = $company_query->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $company_id = $row['id'];
            $_SESSION['company_id'] = $company_id; // Store for future use
        }
        
        $company_query->close();
    }
}

if ($company_id) {
    // Prepare query with company_id filter for jobs
    $stmt = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM job_seekers) AS total_seekers,
        (SELECT COUNT(*) FROM companies) AS total_employers,
        (SELECT COUNT(*) FROM jobs WHERE company_id = ?) AS total_jobs,
        (SELECT COUNT(*) FROM jobs WHERE company_id = ? AND status = 'pending') AS pending_jobs
    ");
    
    if ($stmt) {
        $stmt->bind_param("ii", $company_id, $company_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            $data = $result->fetch_assoc();
            echo json_encode($data);
        } else {
            echo json_encode(["error" => $stmt->error]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(["error" => "Failed to prepare statement: " . $conn->error]);
    }
} else {
    // If no company_id available, get unfiltered counts
    $query = "
    SELECT 
        (SELECT COUNT(*) FROM job_seekers) AS total_seekers,
        (SELECT COUNT(*) FROM companies) AS total_employers,
        0 AS total_jobs,
        0 AS pending_jobs
    ";
    
    $result = $conn->query($query);
    
    if ($result) {
        $data = $result->fetch_assoc();
        echo json_encode($data);
    } else {
        echo json_encode(["error" => $conn->error]);
    }
}

$conn->close();
?>