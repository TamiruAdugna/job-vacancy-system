<?php

session_start();

require_once "config.php";


// --------------------------------------------------
// 1. Check if the user is logged in
// --------------------------------------------------

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


// --------------------------------------------------
// 2. Only job seekers can apply
// --------------------------------------------------

if (($_SESSION["role"] ?? "") !== "job_seeker") {
    header("Location: dashboard.php");
    exit();
}


// --------------------------------------------------
// 3. Get logged-in user's ID
// --------------------------------------------------

$user_id = $_SESSION["user_id"];


// --------------------------------------------------
// 4. Get Job ID
// --------------------------------------------------

$job_id = $_GET["job_id"] ?? $_POST["job_id"] ?? "";


// Make sure Job ID is a number

if (!is_numeric($job_id)) {
    die("Invalid Job ID.");
}

$job_id = (int) $job_id;


// --------------------------------------------------
// 5. Get job information
// --------------------------------------------------

$sql = "SELECT *
        FROM jobs
        WHERE id = :job_id
        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":job_id" => $job_id
]);

$job = $stmt->fetch(PDO::FETCH_ASSOC);


// Check whether job exists

if (!$job) {
    die("The selected job does not exist.");
}


// Check whether deadline has passed

if (!empty($job["deadline"]) && strtotime($job["deadline"]) < time()) {
    die("This job posting has expired. Applications are closed.");
}


// --------------------------------------------------
// 6. Handle application submission
// --------------------------------------------------

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $cover_letter = trim($_POST["cover_letter"] ?? "");


    // Validate cover letter

    if (empty($cover_letter)) {

        $message = "Please write a cover letter.";
        $message_type = "error";

    } elseif (strlen($cover_letter) < 20) {

        $message = "Cover letter is too short (minimum 20 characters).";
        $message_type = "error";

    } elseif (strlen($cover_letter) > 5000) {

        $message = "Cover letter is too long (maximum 5000 characters).";
        $message_type = "error";

    } else {

        try {

            // --------------------------------------------------
            // Check whether user already applied
            // --------------------------------------------------

            $check_sql = "SELECT id
                          FROM applications
                          WHERE job_id = :job_id
                          AND user_id = :user_id
                          LIMIT 1";

            $check_stmt = $pdo->prepare($check_sql);

            $check_stmt->execute([
                ":job_id" => $job_id,
                ":user_id" => $user_id
            ]);

            $existing_application =
                $check_stmt->fetch(PDO::FETCH_ASSOC);


            if ($existing_application) {

                $message =
                    "You have already applied for this job.";

                $message_type = "error";

            } else {

                // --------------------------------------------------
                // Insert application
                // --------------------------------------------------

                $insert_sql =
                    "INSERT INTO applications
                    (job_id, user_id, cover_letter, status)
                    VALUES
                    (:job_id, :user_id, :cover_letter, 'Pending')";

                $insert_stmt =
                    $pdo->prepare($insert_sql);

                $insert_stmt->execute([
                    ":job_id" => $job_id,
                    ":user_id" => $user_id,
                    ":cover_letter" => $cover_letter
                ]);


                $message =
                    "Application submitted successfully!";

                $message_type = "success";
            }

        } catch (PDOException $e) {

            // Duplicate key error (from UNIQUE constraint)
            if ($e->getCode() === "23000") {

                $message = "You have already applied for this job.";

            } else {

                error_log("Application submit failed: " . $e->getMessage());

                $message = "Error submitting application. Please try again.";
            }

            $message_type = "error";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Apply for Job - Job Vacancy System
    </title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }


        body {
            background-color: #f4f7fb;
            color: #333;
        }


        /* Navigation */

        .navbar {
            background-color: #1f4e79;
            color: white;
            padding: 18px 40px;
        }


        .nav-container {
            max-width: 1200px;
            margin: auto;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        .navbar h2 {
            font-size: 24px;
        }


        .nav-links {
            display: flex;
            gap: 10px;
            align-items: center;
        }


        .nav-links a {
            color: white;
            text-decoration: none;

            padding: 9px 14px;

            border-radius: 5px;
        }


        .nav-links a:hover {
            background-color: #163a5c;
        }


        .logout-link {
            background-color: #dc3545;
        }


        .logout-link:hover {
            background-color: #b02a37 !important;
        }


        /* Main Content */

        .main-content {
            width: 90%;
            max-width: 1000px;

            margin: 40px auto;
        }


        .page-title {
            text-align: center;

            color: #1f4e79;

            margin-bottom: 30px;
        }


        /* Job Information */

        .job-card {
            background-color: white;

            padding: 30px;

            border-radius: 10px;

            box-shadow:
                0 3px 10px rgba(0,0,0,0.08);

            margin-bottom: 30px;
        }


        .job-card h2 {
            color: #1f4e79;

            margin-bottom: 20px;

            font-size: 28px;
        }


        .job-info {
            margin-bottom: 12px;

            line-height: 1.6;
        }


        .job-description-title {
            color: #1f4e79;

            margin-top: 20px;

            margin-bottom: 8px;
        }


        .job-description {
            color: #555;

            line-height: 1.7;
        }


        .deadline {
            margin-top: 20px;

            background-color: #fff3cd;

            color: #856404;

            padding: 12px;

            border-radius: 6px;
        }


        /* Application Form */

        .form-container {
            background-color: white;

            padding: 30px;

            border-radius: 10px;

            box-shadow:
                0 3px 10px rgba(0,0,0,0.08);
        }


        .form-container h2 {
            color: #1f4e79;

            margin-bottom: 20px;
        }


        /* Alerts */

        .alert {
            padding: 15px;

            border-radius: 6px;

            margin-bottom: 20px;

            font-weight: bold;
        }


        .success {
            background-color: #d1e7dd;

            color: #0f5132;
        }


        .error {
            background-color: #f8d7da;

            color: #842029;
        }


        /* Form */

        .form-group {
            margin-bottom: 20px;
        }


        .form-group label {
            display: block;

            margin-bottom: 8px;

            font-weight: bold;

            color: #333;
        }


        textarea {
            width: 100%;

            min-height: 250px;

            padding: 15px;

            border: 1px solid #ccc;

            border-radius: 6px;

            resize: vertical;

            font-size: 16px;

            line-height: 1.6;
        }


        textarea:focus {
            outline: none;

            border-color: #1f4e79;

            box-shadow:
                0 0 0 2px rgba(31,78,121,0.15);
        }


        /* Buttons */

        .btn {
            display: inline-block;

            border: none;

            background-color: #1f4e79;

            color: white;

            text-decoration: none;

            padding: 12px 20px;

            border-radius: 6px;

            font-size: 16px;

            cursor: pointer;
        }


        .btn:hover {
            background-color: #163a5c;
        }


        .back-btn {
            background-color: #6c757d;

            margin-left: 10px;
        }


        .back-btn:hover {
            background-color: #545b62;
        }


        /* Footer */

        .footer {
            text-align: center;

            padding: 25px;

            margin-top: 40px;

            color: #777;
        }


        /* Mobile */

        @media (max-width: 700px) {

            .navbar {
                padding: 15px 20px;
            }


            .nav-container {
                flex-direction: column;

                gap: 15px;
            }


            .nav-links {
                flex-wrap: wrap;

                justify-content: center;
            }


            .main-content {
                width: 94%;
            }


            .job-card,
            .form-container {
                padding: 20px;
            }


            .job-card h2 {
                font-size: 24px;
            }


            .back-btn {
                margin-left: 0;

                margin-top: 10px;
            }

        }

    </style>

</head>

<body>


    <!-- Navigation -->

    <nav class="navbar">

        <div class="nav-container">

            <h2>
                Job Vacancy System
            </h2>


            <div class="nav-links">

                <a href="index.php">
                    Home
                </a>

                <a href="view_jobs.php">
                    Jobs
                </a>

                <a href="dashboard.php">
                    Dashboard
                </a>

                <a
                    href="logout.php"
                    class="logout-link"
                >
                    Logout
                </a>

            </div>

        </div>

    </nav>


    <!-- Main Content -->

    <main class="main-content">


        <h1 class="page-title">
            Apply for Job
        </h1>


        <!-- Job Information -->

        <div class="job-card">

            <h2>

                <?php
                echo htmlspecialchars($job["title"]);
                ?>

            </h2>


            <p class="job-info">

                <strong>Company:</strong>

                <?php
                echo htmlspecialchars($job["company"]);
                ?>

            </p>


            <p class="job-info">

                <strong>Location:</strong>

                <?php
                echo htmlspecialchars($job["location"]);
                ?>

            </p>


            <p class="job-info">

                <strong>Job Type:</strong>

                <?php
                echo htmlspecialchars($job["job_type"]);
                ?>

            </p>


            <p class="job-info">

                <strong>Salary:</strong>

                <?php
                echo htmlspecialchars($job["salary"]);
                ?>

            </p>


            <h3 class="job-description-title">
                Job Description
            </h3>


            <p class="job-description">

                <?php
                $description = $job["description"];
                $short = mb_strlen($description) > 400
                    ? mb_substr($description, 0, 400) . "..."
                    : $description;
                echo nl2br(htmlspecialchars($short));
                ?>

            </p>


            <div class="deadline">

                <strong>
                    Application Deadline:
                </strong>

                <?php
                echo htmlspecialchars($job["deadline"]);
                ?>

            </div>

        </div>


        <!-- Application Form -->

        <div class="form-container">

            <h2>
                Submit Your Application
            </h2>


            <!-- Message -->

            <?php if (!empty($message)): ?>

                <div
                    class="alert <?php echo $message_type; ?>"
                >

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                action="apply.php?job_id=<?php echo $job_id; ?>"
            >


                <input
                    type="hidden"
                    name="job_id"
                    value="<?php echo $job_id; ?>"
                >


                <div class="form-group">

                    <label for="cover_letter">

                        Cover Letter

                    </label>


                    <textarea
                        id="cover_letter"
                        name="cover_letter"
                        placeholder="Write your cover letter here (minimum 20 characters)..."
                        minlength="20"
                        maxlength="5000"
                        required
                    ></textarea>

                </div>


                <button
                    type="submit"
                    class="btn"
                >

                    Submit Application

                </button>


                <a
                    href="view_jobs.php"
                    class="btn back-btn"
                >

                    Back to Jobs

                </a>


            </form>

        </div>


    </main>


    <!-- Footer -->

    <footer class="footer">

        <p>

            &copy;
            <?php echo date("Y"); ?>
            Job Vacancy System.
            All rights reserved.

        </p>

    </footer>


</body>

</html>