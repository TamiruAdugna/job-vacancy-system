<?php

session_start();

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

require_once "config.php";

$user_id = $_SESSION["user_id"];


/*
 * Get applications belonging to the logged-in user
 * together with the related job information.
 */

$sql = "SELECT
            applications.id AS application_id,
            applications.cover_letter,
            applications.status,
            jobs.title AS job_title,
            jobs.company,
            jobs.location,
            jobs.job_type,
            jobs.salary
        FROM applications
        INNER JOIN jobs
            ON applications.job_id = jobs.id
        WHERE applications.user_id = :user_id
        ORDER BY applications.id DESC";


$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":user_id" => $user_id
]);

$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        My Applications - Job Vacancy System
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
            max-width: 1100px;

            margin: 40px auto;
        }


        .page-title {
            text-align: center;

            color: #1f4e79;

            margin-bottom: 30px;
        }


        /* Application Card */

        .application-card {
            background-color: white;

            padding: 30px;

            border-radius: 10px;

            box-shadow:
                0 3px 10px rgba(0,0,0,0.08);

            margin-bottom: 25px;
        }


        .job-title {
            color: #1f4e79;

            font-size: 26px;

            margin-bottom: 20px;
        }


        .application-info {
            line-height: 1.7;

            margin-bottom: 10px;
        }


        .application-info strong {
            color: #333;
        }


        /* Application ID */

        .application-id {
            display: inline-block;

            background-color: #e8f1fb;

            color: #1f4e79;

            padding: 6px 12px;

            border-radius: 20px;

            font-weight: bold;

            margin-bottom: 18px;
        }


        /* Status */

        .status {
            display: inline-block;

            padding: 6px 14px;

            border-radius: 20px;

            font-weight: bold;
        }


        .status-pending {
            background-color: #fff3cd;

            color: #856404;
        }


        .status-accepted {
            background-color: #d1e7dd;

            color: #0f5132;
        }


        .status-rejected {
            background-color: #f8d7da;

            color: #842029;
        }


        .status-default {
            background-color: #e2e3e5;

            color: #41464b;
        }


        /* Cover Letter */

        .cover-letter {
            margin-top: 25px;

            padding-top: 20px;

            border-top: 1px solid #ddd;
        }


        .cover-letter h3 {
            color: #1f4e79;

            margin-bottom: 12px;
        }


        .cover-letter-text {
            background-color: #f8f9fa;

            border-left: 4px solid #1f4e79;

            padding: 18px;

            border-radius: 5px;

            line-height: 1.7;

            color: #555;

            white-space: normal;
        }


        /* No Applications */

        .no-applications {
            background-color: white;

            padding: 40px;

            text-align: center;

            border-radius: 10px;

            box-shadow:
                0 3px 10px rgba(0,0,0,0.08);
        }


        .no-applications h2 {
            color: #1f4e79;

            margin-bottom: 12px;
        }


        .no-applications p {
            color: #666;

            margin-bottom: 20px;
        }


        /* Button */

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


            .application-card {
                padding: 20px;
            }


            .job-title {
                font-size: 23px;
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
            My Applications
        </h1>


        <?php if (count($applications) > 0): ?>


            <?php foreach ($applications as $application): ?>


                <div class="application-card">


                    <!-- Application ID -->

                    <div class="application-id">

                        Application ID:
                        <?php
                        echo htmlspecialchars(
                            $application["application_id"]
                        );
                        ?>

                    </div>


                    <!-- Job Title -->

                    <h2 class="job-title">

                        <?php
                        echo htmlspecialchars(
                            $application["job_title"]
                        );
                        ?>

                    </h2>


                    <!-- Company -->

                    <p class="application-info">

                        <strong>
                            Company:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $application["company"]
                        );
                        ?>

                    </p>


                    <!-- Location -->

                    <p class="application-info">

                        <strong>
                            Location:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $application["location"]
                        );
                        ?>

                    </p>


                    <!-- Job Type -->

                    <p class="application-info">

                        <strong>
                            Job Type:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $application["job_type"]
                        );
                        ?>

                    </p>


                    <!-- Salary -->

                    <p class="application-info">

                        <strong>
                            Salary:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $application["salary"]
                        );
                        ?>

                    </p>


                    <!-- Status -->

                    <p class="application-info">

                        <strong>
                            Application Status:
                        </strong>


                        <?php

                        $status = strtolower(
                            $application["status"]
                        );


                        if ($status === "pending") {

                            $status_class =
                                "status-pending";

                        } elseif ($status === "accepted") {

                            $status_class =
                                "status-accepted";

                        } elseif ($status === "rejected") {

                            $status_class =
                                "status-rejected";

                        } else {

                            $status_class =
                                "status-default";

                        }

                        ?>


                        <span
                            class="status
                            <?php echo $status_class; ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $application["status"]
                            );
                            ?>

                        </span>

                    </p>


                    <!-- Cover Letter -->

                    <div class="cover-letter">

                        <h3>
                            Your Cover Letter
                        </h3>


                        <div class="cover-letter-text">

                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $application["cover_letter"]
                                )
                            );
                            ?>

                        </div>

                    </div>


                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <div class="no-applications">

                <h2>
                    No Applications Yet
                </h2>


                <p>
                    You have not applied for any jobs yet.
                </p>


                <a
                    href="view_jobs.php"
                    class="btn"
                >
                    Browse Available Jobs
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