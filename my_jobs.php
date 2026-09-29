<?php

session_start();

require_once "config.php";


// --------------------------------------------------
// 1. Access control — employers only
// --------------------------------------------------

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "employer"
) {
    header("Location: login.php");
    exit();
}


$employer_id = $_SESSION["user_id"];


// --------------------------------------------------
// 2. Handle delete (optional feature)
// --------------------------------------------------

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_job_id"])) {

    $delete_id = (int) ($_POST["delete_job_id"] ?? 0);

    if ($delete_id > 0) {

        try {

            // Delete only if it belongs to this employer
            $del_sql = "DELETE FROM jobs
                        WHERE id = :id
                        AND employer_id = :employer_id";

            $del_stmt = $pdo->prepare($del_sql);

            $del_stmt->execute([
                ":id"          => $delete_id,
                ":employer_id" => $employer_id
            ]);

            if ($del_stmt->rowCount() > 0) {
                $message = "Job deleted successfully.";
                $message_type = "success";
            } else {
                $message = "Job not found or you don't have permission to delete it.";
                $message_type = "error";
            }

        } catch (PDOException $e) {

            error_log("Job delete failed: " . $e->getMessage());

            $message = "Failed to delete the job. Please try again.";
            $message_type = "error";
        }
    }
}


// --------------------------------------------------
// 3. Fetch this employer's jobs + application counts
// --------------------------------------------------

$sql = "SELECT
            j.id,
            j.title,
            j.company,
            j.location,
            j.job_type,
            j.salary,
            j.deadline,
            j.created_at,
            COUNT(a.id) AS application_count
        FROM jobs j
        LEFT JOIN applications a ON a.job_id = j.id
        WHERE j.employer_id = :employer_id
        GROUP BY j.id
        ORDER BY j.id DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":employer_id" => $employer_id
]);

$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Posted Jobs - Job Vacancy System</title>

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
            max-width: 1100px;
            margin: 40px auto;
        }

        .page-title {
            color: #1f4e79;
            text-align: center;
            margin-bottom: 30px;
        }

        /* Alerts */

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .alert.success {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #842029;
        }

        /* Job Cards */

        .jobs-container {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(320px, 1fr)
            );
            gap: 25px;
        }

        .job-card {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .job-title {
            color: #1f4e79;
            font-size: 22px;
            margin-bottom: 15px;
        }

        .job-info {
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .job-info strong {
            color: #333;
        }

        .applications-badge {
            display: inline-block;
            background-color: #e8f1fb;
            color: #1f4e79;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 15px;
        }

        .deadline {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 6px;
            margin-top: 12px;
            margin-bottom: 18px;
        }

        /* Buttons */

        .button-area {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
        }

        .btn.view-btn {
            background-color: #1f4e79;
            color: white;
        }

        .btn.view-btn:hover {
            background-color: #163a5c;
        }

        .btn.delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .btn.delete-btn:hover {
            background-color: #b02a37;
        }

        /* Empty state */

        .no-jobs {
            background-color: white;
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .no-jobs h2 {
            color: #1f4e79;
            margin-bottom: 10px;
        }

        .no-jobs p {
            color: #666;
            margin-bottom: 20px;
        }

        .no-jobs .btn {
            background-color: #1f4e79;
            color: white;
        }

        .no-jobs .btn:hover {
            background-color: #163a5c;
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

                <a href="post_job.php">
                    Post Job
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
            My Posted Jobs
        </h1>


        <?php if (!empty($message)): ?>

            <div class="alert <?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <?php if (count($jobs) > 0): ?>


            <div class="jobs-container">


                <?php foreach ($jobs as $job): ?>


                    <div class="job-card">


                        <!-- Title -->

                        <h2 class="job-title">

                            <?php
                            echo htmlspecialchars($job["title"]);
                            ?>

                        </h2>


                        <!-- Company -->

                        <p class="job-info">

                            <strong>Company:</strong>

                            <?php
                            echo htmlspecialchars($job["company"]);
                            ?>

                        </p>


                        <!-- Location -->

                        <p class="job-info">

                            <strong>Location:</strong>

                            <?php
                            echo htmlspecialchars($job["location"]);
                            ?>

                        </p>


                        <!-- Job Type -->

                        <p class="job-info">

                            <strong>Job Type:</strong>

                            <?php
                            echo htmlspecialchars($job["job_type"]);
                            ?>

                        </p>


                        <!-- Salary -->

                        <p class="job-info">

                            <strong>Salary:</strong>

                            <?php
                            echo htmlspecialchars($job["salary"]);
                            ?>

                        </p>


                        <!-- Applications Count -->

                        <div class="applications-badge">

                            📨
                            <?php
                            echo (int) $job["application_count"];
                            ?>
                            application<?php echo ($job["application_count"] == 1) ? "" : "s"; ?>

                        </div>


                        <!-- Deadline -->

                        <div class="deadline">

                            <strong>Deadline:</strong>

                            <?php
                            echo htmlspecialchars($job["deadline"]);
                            ?>

                        </div>


                        <!-- Buttons -->

                        <div class="button-area">

                            <a
                                href="applications.php?job_id=<?php echo (int) $job["id"]; ?>"
                                class="btn view-btn"
                            >
                                View Applications
                            </a>


                            <form
                                method="POST"
                                action="my_jobs.php"
                                onsubmit="return confirm('Are you sure you want to delete this job? This will also delete all applications for it.');"
                                style="display:inline;"
                            >

                                <input
                                    type="hidden"
                                    name="delete_job_id"
                                    value="<?php echo (int) $job["id"]; ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn delete-btn"
                                >
                                    Delete Job
                                </button>

                            </form>

                        </div>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <div class="no-jobs">

                <h2>
                    You Haven't Posted Any Jobs Yet
                </h2>

                <p>
                    Start by creating your first job vacancy
                    for job seekers to apply to.
                </p>

                <a href="post_job.php" class="btn">
                    Post a Job
                </a>

            </div>


        <?php endif; ?>


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