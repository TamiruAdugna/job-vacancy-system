<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Job Vacancy System</title>

    <link rel="stylesheet"
          href="assets/style.css">

</head>

<body>

    <!-- Navigation -->

    <nav class="navbar">

        <div class="container">

            <h2>
                Job Vacancy System
            </h2>

            <div>

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

                    <a href="logout.php">
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

        <div class="container">

            <div class="card">

                <h1 class="page-title">
                    Welcome to the Job Vacancy System
                </h1>

                <p>
                    Find job opportunities and apply for
                    positions that match your skills,
                    education, and interests.
                </p>

                <br>

                <a href="view_jobs.php" class="btn">
                    View Available Jobs
                </a>

                <?php if (!isset($_SESSION["user_id"])): ?>

                    <a href="register.php" class="btn">
                        Create an Account
                    </a>

                <?php endif; ?>

            </div>


            <!-- Features -->

            <div class="card">

                <h2>
                    Why Use Our Job Vacancy System?
                </h2>

                <ul>

                    <li>
                        Browse available job vacancies
                    </li>

                    <li>
                        Apply for suitable positions
                    </li>

                    <li>
                        Track your applications
                    </li>

                    <li>
                        View application status
                    </li>

                </ul>

            </div>

        </div>

    </main>


    <!-- Footer -->

    <footer class="footer">

        <p>
            &copy; 2026 Job Vacancy System.
            All rights reserved.
        </p>

    </footer>

</body>

</html>