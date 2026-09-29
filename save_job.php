<?php

session_start();

require_once "config.php";


// --------------------------------------------------
// 1. Access control — logged-in employers only
// --------------------------------------------------

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "employer"
) {
    header("Location: login.php");
    exit();
}


// --------------------------------------------------
// 2. Only accept POST
// --------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: post_job.php");
    exit();
}


// --------------------------------------------------
// 3. Collect + trim input
// --------------------------------------------------

$employer_id = $_SESSION["user_id"];

$title       = trim($_POST["title"] ?? "");
$company     = trim($_POST["company"] ?? "");
$location    = trim($_POST["location"] ?? "");
$description = trim($_POST["description"] ?? "");
$job_type    = trim($_POST["job_type"] ?? "");
$salary      = trim($_POST["salary"] ?? "");
$deadline    = trim($_POST["deadline"] ?? "");


// --------------------------------------------------
// 4. Validate
// --------------------------------------------------

$allowed_types = ["Full-time", "Part-time", "Contract", "Internship", "Temporary"];

$errors = [];

if (empty($title) || strlen($title) > 150) {
    $errors[] = "Job title is required (max 150 characters).";
}

if (empty($company) || strlen($company) > 150) {
    $errors[] = "Company name is required (max 150 characters).";
}

if (empty($location) || strlen($location) > 150) {
    $errors[] = "Location is required (max 150 characters).";
}

if (empty($description) || strlen($description) < 30 || strlen($description) > 5000) {
    $errors[] = "Description must be 30–5000 characters.";
}

if (!in_array($job_type, $allowed_types, true)) {
    $errors[] = "Please select a valid job type.";
}

if (strlen($salary) > 100) {
    $errors[] = "Salary text is too long (max 100 characters).";
}

if (empty($deadline)) {
    $errors[] = "Deadline is required.";
} else {
    $deadline_ts = strtotime($deadline);
    if ($deadline_ts === false) {
        $errors[] = "Invalid deadline date.";
    } elseif ($deadline_ts < strtotime("today")) {
        $errors[] = "Deadline must be today or a future date.";
    }
}


// --------------------------------------------------
// 5. If errors, send back to form
// --------------------------------------------------

if (!empty($errors)) {

    $_SESSION["job_form_errors"] = $errors;

    $_SESSION["job_form_old"] = [
        "title"       => $title,
        "company"     => $company,
        "location"    => $location,
        "description" => $description,
        "job_type"    => $job_type,
        "salary"      => $salary,
        "deadline"    => $deadline,
    ];

    header("Location: post_job.php");
    exit();
}


// --------------------------------------------------
// 6. Insert into DB
// --------------------------------------------------

try {

    $sql = "INSERT INTO jobs
            (employer_id, title, company, location, description,
             job_type, salary, deadline, created_at)
            VALUES
            (:employer_id, :title, :company, :location, :description,
             :job_type, :salary, :deadline, NOW())";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":employer_id" => $employer_id,
        ":title"       => $title,
        ":company"     => $company,
        ":location"    => $location,
        ":description" => $description,
        ":job_type"    => $job_type,
        ":salary"      => $salary,
        ":deadline"    => $deadline,
    ]);

    header("Location: dashboard.php?job_posted=1");
    exit();

} catch (PDOException $e) {

    error_log("Job posting failed: " . $e->getMessage());

    $_SESSION["job_form_errors"] = [
        "Failed to post job. Please try again."
    ];

    header("Location: post_job.php");
    exit();
}