<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "config.php";

$user_id = $_SESSION["user_id"];

$sql = "SELECT full_name, email, role
        FROM users
        WHERE id = :id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":id" => $user_id
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    exit();
}


// Check for job-posted success flag
$job_posted = isset($_GET["job_posted"]) && $_GET["job_posted"] === "1";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Job Vacancy System</title>

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

        .navbar {
            background-color: #1f4e79;
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            font-size: 24px;
        }

        .logout {
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 6px;
        }

        .logout:hover {
            background-color: #b02a37;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .welcome {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .welcome h2 {
            color: #1f4e79;
            margin-bottom: 15px;
        }

        .user-info {
            line-height: 1.8;
        }

        .role {
            display: inline-block;
            background-color: #e8f1fb;
            color: #1f4e79;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
        }

        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .section-title {
            margin-bottom: 20px;
            color: #333;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            text-align: center;
        }

        .card h3 {
            color: #1f4e79;
            margin-bottom: 12px;
        }

        .card p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .btn {
            display: inline-block;
            background-color: #1f4e79;
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 6px;
        }

        .btn:hover {
            background-color: #163a5c;
        }

        .home-btn {
            background-color: #6c757d;
        }

        .home-btn:hover {
            background-color: #545b62;
        }

        .description {
            background-color: white;
            margin-top: 30px;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .description h3 {
            color: #1f4e79;
            margin-bottom: 10px;
        }

        .description p {
            line-height: 1.7;
            color: #555;
        }

        footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: #777;
        }

        @media (max-width: 600px) {

            .navbar {
                padding: 15px 20px;
            }

            .navbar h1 {
                font-size: 18px;
            }

            .container {
                width: 94%;
            }

        }

    </style>

</head>

<body>

    <!-- Navigation Bar -->

    <div class="navbar">

        <h1>Job Vacancy System</h1>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>


    <div class="container">

        <!-- Welcome Section -->

        <div class="welcome">

            <h2>
                Welcome,
                <?php echo htmlspecialchars($user["full_name"]); ?>!
            </h2>

            <div class="user-info">

                <p>
                    <strong>Email:</strong>
                    <?php echo htmlspecialchars($user["email"]); ?>
                </p>

                <p>
                    <strong>Role:</strong>

                    <span class="role">
                        <?php echo htmlspecialchars($user["role"]); ?>
                    </span>

                </p>

            </div>

        </div>


        <!-- Success Message -->

        <?php if ($job_posted): ?>

            <div class="alert-success">

                ✅ Job posted successfully!

            </div>

        <?php endif; ?>


        <!-- EMPLOYER DASHBOARD -->

        <?php if ($user["role"] === "employer"): ?>

            <h2 class="section-title">
                Employer Dashboard
            </h2>


            <div class="cards">

                <!-- Post Job -->

                <div class="card">

                    <h3>Post a Job</h3>

                    <p>
                        Create and publish a new job vacancy
                        for job seekers to apply for.
                    </p>

                    <a href="post_job.php" class="btn">
                        Post a Job
                    </a>

                </div>


                <!-- My Jobs -->

                <div class="card">

                    <h3>My Posted Jobs</h3>

                    <p>
                        View and manage the job vacancies
                        you have posted.
                    </p>

                    <a href="my_jobs.php" class="btn">
                        My Posted Jobs
                    </a>

                </div>


                <!-- Applications -->

                <div class="card">

                    <h3>Applications</h3>

                    <p>
                        View applications submitted by
                        job seekers for your jobs.
                    </p>

                    <a href="applications.php" class="btn">
                        View Applications
                    </a>

                </div>


                <!-- Home -->

                <div class="card">

                    <h3>Home Page</h3>

                    <p>
                        Return to the main page of the
                        Job Vacancy System.
                    </p>

                    <a href="index.php" class="btn home-btn">
                        Go Home
                    </a>

                </div>

            </div>


            <div class="description">

                <h3>What You Can Do</h3>

                <p>
                    Post job vacancies, manage your posted jobs,
                    and review applications submitted by job seekers.
                </p>

            </div>


        <!-- JOB SEEKER DASHBOARD -->

        <?php else: ?>

            <h2 class="section-title">
                Job Seeker Dashboard
            </h2>


            <div class="cards">

                <!-- Available Jobs -->

                <div class="card">

                    <h3>Available Jobs</h3>

                    <p>
                        Browse available job vacancies and
                        find opportunities that match your skills.
                    </p>

                    <a href="view_jobs.php" class="btn">
                        View Jobs
                    </a>

                </div>


                <!-- Applications -->

                <div class="card">

                    <h3>My Applications</h3>

                    <p>
                        View the jobs you have applied for
                        and check the status of your applications.
                    </p>

                    <a href="my_applications.php" class="btn">
                        My Applications
                    </a>

                </div>


                <!-- Home -->

                <div class="card">

                    <h3>Home Page</h3>

                    <p>
                        Return to the main page of the
                        Job Vacancy System.
                    </p>

                    <a href="index.php" class="btn home-btn">
                        Go Home
                    </a>

                </div>

            </div>


            <div class="description">

                <h3>What You Can Do</h3>

                <p>
                    Browse available job vacancies, view job details,
                    apply for positions that match your skills and
                    interests, and track your applications from your
                    dashboard.
                </p>

            </div>

        <?php endif; ?>

    </div>


    <!-- Footer -->

    <footer>

        <p>
            &copy; <?php echo date("Y"); ?>
            Job Vacancy System. All rights reserved.
        </p>

    </footer>

</body>

</html>