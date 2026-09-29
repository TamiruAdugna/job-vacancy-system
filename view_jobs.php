<?php

session_start();

require_once "config.php";


// --------------------------------------------------
// Get all available jobs (newest first)
// --------------------------------------------------

$sql = "SELECT *
        FROM jobs
        ORDER BY id DESC";

$stmt = $pdo->query($sql);

$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);


// --------------------------------------------------
// Get list of job IDs the current user has applied to
// --------------------------------------------------

$applied_job_ids = [];

if (isset($_SESSION["user_id"])) {

    $applied_sql = "SELECT job_id
                    FROM applications
                    WHERE user_id = :user_id";

    $applied_stmt = $pdo->prepare($applied_sql);

    $applied_stmt->execute([
        ":user_id" => $_SESSION["user_id"]
    ]);

    $applied_job_ids = $applied_stmt->fetchAll(PDO::FETCH_COLUMN);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Available Jobs - Job Vacancy System</title>

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

            box-shadow:
                0 3px 10px rgba(0,0,0,0.08);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .job-card:hover {
            transform: translateY(-4px);

            box-shadow:
                0 6px 18px rgba(0,0,0,0.12);
        }

        .job-card.expired {
            opacity: 0.75;
        }

        .job-title {
            color: #1f4e79;
            font-size: 24px;
            margin-bottom: 18px;
        }

        .job-info {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .job-info strong {
            color: #333;
        }

        .description-title {
            margin-top: 18px;
            margin-bottom: 7px;
            color: #1f4e79;
        }

        .description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .deadline {
            background-color: #fff3cd;
            color: #856404;

            padding: 10px;

            border-radius: 6px;

            margin-top: 15px;
            margin-bottom: 20px;
        }

        .deadline.expired-badge {
            background-color: #f8d7da;
            color: #842029;
        }

        /* Apply Button */

        .btn {
            display: inline-block;

            background-color: #1f4e79;

            color: white;

            text-decoration: none;

            padding: 11px 20px;

            border-radius: 6px;

            font-weight: bold;
        }

        .btn:hover {
            background-color: #163a5c;
        }

        .btn.disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        .btn.applied-badge {
            background-color: #198754;
            cursor: default;
        }

        .btn.applied-badge:hover {
            background-color: #198754;
        }

        /* No Jobs */

        .no-jobs {
            background-color: white;

            padding: 40px;

            text-align: center;

            border-radius: 10px;

            box-shadow:
                0 3px 10px rgba(0,0,0,0.08);
        }

        .no-jobs h2 {
            color: #1f4e79;
            margin-bottom: 10px;
        }

        .no-jobs p {
            color: #666;
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

            .jobs-container {
                grid-template-columns: 1fr;
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

                <?php if (isset($_SESSION["user_id"])): ?>

                    <a href="dashboard.php">
                        Dashboard
                    </a>

                    <a
                        href="logout.php"
                        class="logout-link"
                    >
                        Logout
                    </a>

                <?php else: ?>

                    <a href="login.php">
                        Login
                    </a>

                    <a href="register.php">
                        Register
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </nav>


    <!-- Main Content -->

    <main class="main-content">

        <h1 class="page-title">
            Available Job Vacancies
        </h1>


        <?php if (count($jobs) > 0): ?>


            <div class="jobs-container">


                <?php foreach ($jobs as $job): ?>


                    <?php
                    // Is this job expired?
                    $is_expired =
                        !empty($job["deadline"]) &&
                        strtotime($job["deadline"]) < strtotime("today");

                    // Has the current user already applied?
                    $already_applied =
                        in_array($job["id"], $applied_job_ids);
                    ?>


                    <div class="job-card <?php echo $is_expired ? 'expired' : ''; ?>">


                        <!-- Job Title -->

                        <h2 class="job-title">

                            <?php
                            echo htmlspecialchars(
                                $job["title"]
                            );
                            ?>

                        </h2>


                        <!-- Company -->

                        <p class="job-info">

                            <strong>Company:</strong>

                            <?php
                            echo htmlspecialchars(
                                $job["company"]
                            );
                            ?>

                        </p>


                        <!-- Location -->

                        <p class="job-info">

                            <strong>Location:</strong>

                            <?php
                            echo htmlspecialchars(
                                $job["location"]
                            );
                            ?>

                        </p>


                        <!-- Job Type -->

                        <p class="job-info">

                            <strong>Job Type:</strong>

                            <?php
                            echo htmlspecialchars(
                                $job["job_type"]
                            );
                            ?>

                        </p>


                        <!-- Salary -->

                        <p class="job-info">

                            <strong>Salary:</strong>

                            <?php
                            echo htmlspecialchars(
                                $job["salary"]
                            );
                            ?>

                        </p>


                        <!-- Description -->

                        <h3 class="description-title">
                            Job Description
                        </h3>

                        <p class="description">

                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $job["description"]
                                )
                            );
                            ?>

                        </p>


                        <!-- Deadline / Expired -->

                        <?php if ($is_expired): ?>

                            <div class="deadline expired-badge">

                                <strong>
                                    ❌ Applications Closed
                                </strong>

                            </div>

                        <?php else: ?>

                            <div class="deadline">

                                <strong>
                                    Application Deadline:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $job["deadline"]
                                );
                                ?>

                            </div>

                        <?php endif; ?>


                        <!-- Apply / Already Applied / Login / Closed -->

                        <?php if ($is_expired): ?>

                            <span class="btn disabled">
                                Applications Closed
                            </span>

                        <?php elseif ($already_applied): ?>

                            <span class="btn applied-badge">
                                ✅ Already Applied
                            </span>

                        <?php elseif (isset($_SESSION["user_id"])): ?>

                            <a
                                href="apply.php?job_id=<?php echo (int) $job["id"]; ?>"
                                class="btn"
                            >
                                Apply Now
                            </a>

                        <?php else: ?>

                            <a
                                href="login.php"
                                class="btn"
                            >
                                Login to Apply
                            </a>

                        <?php endif; ?>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <div class="no-jobs">

                <h2>
                    No Jobs Available
                </h2>

                <p>
                    There are currently no job vacancies
                    available.
                </p>

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