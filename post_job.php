<?php

session_start();


// --------------------------------------------------
// 1. Check whether the user is logged in as employer
// --------------------------------------------------

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "employer"
) {
    header("Location: login.php");
    exit();
}


// --------------------------------------------------
// 2. Load any pending form errors / old input
// --------------------------------------------------

$errors = $_SESSION["job_form_errors"] ?? [];
$old    = $_SESSION["job_form_old"]    ?? [];

unset($_SESSION["job_form_errors"]);
unset($_SESSION["job_form_old"]);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Post a Job - Job Vacancy System
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
            max-width: 850px;

            margin: 40px auto;
        }


        .page-title {
            text-align: center;

            color: #1f4e79;

            margin-bottom: 30px;
        }


        /* Form Card */

        .form-container {
            background-color: white;

            padding: 35px;

            border-radius: 10px;

            box-shadow:
                0 3px 10px rgba(0,0,0,0.08);
        }


        .form-container h2 {
            color: #1f4e79;

            margin-bottom: 25px;

            text-align: center;
        }


        /* Alerts */

        .alert {
            padding: 15px;

            border-radius: 6px;

            margin-bottom: 20px;
        }


        .alert.error {
            background-color: #f8d7da;

            color: #842029;
        }


        .alert ul {
            margin-left: 20px;
        }


        .alert li {
            margin-bottom: 5px;
        }


        /* Form Groups */

        .form-group {
            margin-bottom: 20px;
        }


        .form-group label {
            display: block;

            font-weight: bold;

            margin-bottom: 8px;

            color: #333;
        }


        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;

            padding: 12px 14px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 16px;

            background-color: white;
        }


        .form-group textarea {
            min-height: 180px;

            resize: vertical;

            line-height: 1.6;
        }


        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {

            outline: none;

            border-color: #1f4e79;

            box-shadow:
                0 0 0 2px rgba(31,78,121,0.15);
        }


        .form-help {
            display: block;

            margin-top: 6px;

            color: #777;

            font-size: 14px;
        }


        /* Buttons */

        .button-area {
            margin-top: 25px;

            display: flex;

            gap: 10px;

            flex-wrap: wrap;
        }


        .btn {
            display: inline-block;

            padding: 12px 22px;

            border: none;

            border-radius: 6px;

            font-size: 16px;

            text-decoration: none;

            cursor: pointer;

            font-weight: bold;
        }


        .submit-btn {
            background-color: #1f4e79;

            color: white;
        }


        .submit-btn:hover {
            background-color: #163a5c;
        }


        .back-btn {
            background-color: #6c757d;

            color: white;
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


            .form-container {
                padding: 22px;
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
            Post a New Job
        </h1>


        <div class="form-container">

            <h2>
                Job Vacancy Information
            </h2>


            <?php if (!empty($errors)): ?>

                <div class="alert error">

                    <ul>

                        <?php foreach ($errors as $err): ?>

                            <li>
                                <?php echo htmlspecialchars($err); ?>
                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php endif; ?>


            <form
                action="save_job.php"
                method="POST"
            >


                <!-- Job Title -->

                <div class="form-group">

                    <label for="title">
                        Job Title
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        placeholder="e.g. PHP Developer"
                        maxlength="150"
                        value="<?php echo htmlspecialchars($old['title'] ?? ''); ?>"
                        required
                    >

                </div>


                <!-- Company -->

                <div class="form-group">

                    <label for="company">
                        Company
                    </label>

                    <input
                        type="text"
                        id="company"
                        name="company"
                        placeholder="e.g. ABC Technology"
                        maxlength="150"
                        value="<?php echo htmlspecialchars($old['company'] ?? ''); ?>"
                        required
                    >

                </div>


                <!-- Location -->

                <div class="form-group">

                    <label for="location">
                        Location
                    </label>

                    <input
                        type="text"
                        id="location"
                        name="location"
                        placeholder="e.g. Addis Ababa, Ethiopia"
                        maxlength="150"
                        value="<?php echo htmlspecialchars($old['location'] ?? ''); ?>"
                        required
                    >

                </div>


                <!-- Description -->

                <div class="form-group">

                    <label for="description">
                        Job Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        placeholder="Describe the responsibilities, qualifications, skills, and requirements for this position..."
                        minlength="30"
                        maxlength="5000"
                        required
                    ><?php echo htmlspecialchars($old['description'] ?? ''); ?></textarea>

                    <span class="form-help">
                        Provide clear information about the job (30–5000 characters).
                    </span>

                </div>


                <!-- Job Type -->

                <div class="form-group">

                    <label for="job_type">
                        Job Type
                    </label>

                    <select
                        id="job_type"
                        name="job_type"
                        required
                    >

                        <option value="">
                            Select Job Type
                        </option>

                        <?php
                        $types = ["Full-time", "Part-time", "Contract", "Internship", "Temporary"];
                        $current_type = $old['job_type'] ?? '';
                        foreach ($types as $t):
                        ?>

                            <option
                                value="<?php echo htmlspecialchars($t); ?>"
                                <?php echo ($current_type === $t) ? 'selected' : ''; ?>
                            >
                                <?php echo htmlspecialchars($t); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Salary -->

                <div class="form-group">

                    <label for="salary">
                        Salary
                    </label>

                    <input
                        type="text"
                        id="salary"
                        name="salary"
                        placeholder="e.g. 15000 ETB per month"
                        maxlength="100"
                        value="<?php echo htmlspecialchars($old['salary'] ?? ''); ?>"
                    >

                </div>


                <!-- Deadline -->

                <div class="form-group">

                    <label for="deadline">
                        Application Deadline
                    </label>

                    <input
                        type="date"
                        id="deadline"
                        name="deadline"
                        min="<?php echo date('Y-m-d'); ?>"
                        value="<?php echo htmlspecialchars($old['deadline'] ?? ''); ?>"
                        required
                    >

                </div>


                <!-- Buttons -->

                <div class="button-area">

                    <button
                        type="submit"
                        class="btn submit-btn"
                    >
                        Post Job
                    </button>


                    <a
                        href="dashboard.php"
                        class="btn back-btn"
                    >
                        Back to Dashboard
                    </a>

                </div>


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