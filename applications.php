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
// 2. Get and validate job_id
// --------------------------------------------------

$job_id = $_GET["job_id"] ?? $_POST["job_id"] ?? "";

if (!is_numeric($job_id)) {
    header("Location: my_jobs.php");
    exit();
}

$job_id = (int) $job_id;

if ($job_id <= 0) {
    header("Location: my_jobs.php");
    exit();
}


// --------------------------------------------------
// 3. Verify this job belongs to the logged-in employer
//    (prevents IDOR — an employer can't view another
//     employer's applications)
// --------------------------------------------------

$job_sql = "SELECT id, title, company
            FROM jobs
            WHERE id = :id
            AND employer_id = :employer_id
            LIMIT 1";

$job_stmt = $pdo->prepare($job_sql);

$job_stmt->execute([
    ":id"          => $job_id,
    ":employer_id" => $employer_id
]);

$job = $job_stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header("Location: my_jobs.php");
    exit();
}


// --------------------------------------------------
// 4. Handle status update (Accept / Reject)
// --------------------------------------------------

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["application_id"], $_POST["new_status"])) {

    $application_id = (int) ($_POST["application_id"] ?? 0);
    $new_status     = trim($_POST["new_status"] ?? "");

    $allowed_statuses = ["Pending", "Accepted", "Rejected"];

    if ($application_id <= 0) {

        $message = "Invalid application.";
        $message_type = "error";

    } elseif (!in_array($new_status, $allowed_statuses, true)) {

        $message = "Invalid status.";
        $message_type = "error";

    } else {

        try {

            // Update ONLY if the application belongs to a job owned by this employer
            $update_sql = "UPDATE applications a
                           INNER JOIN jobs j ON j.id = a.job_id
                           SET a.status = :status
                           WHERE a.id = :application_id
                           AND j.employer_id = :employer_id";

            $update_stmt = $pdo->prepare($update_sql);

            $update_stmt->execute([
                ":status"         => $new_status,
                ":application_id" => $application_id,
                ":employer_id"    => $employer_id
            ]);

            if ($update_stmt->rowCount() > 0) {
                $message = "Application status updated to " . $new_status . ".";
                $message_type = "success";
            } else {
                $message = "Application not found or you don't have permission.";
                $message_type = "error";
            }

        } catch (PDOException $e) {

            error_log("Status update failed: " . $e->getMessage());

            $message = "Failed to update status. Please try again.";
            $message_type = "error";
        }
    }
}


// --------------------------------------------------
// 5. Fetch all applications for this job
// --------------------------------------------------

$app_sql = "SELECT
                a.id            AS application_id,
                a.cover_letter,
                a.status,
                a.applied_at,
                u.full_name,
                u.email,
                u.phone
            FROM applications a
            INNER JOIN users u ON u.id = a.user_id
            WHERE a.job_id = :job_id
            ORDER BY a.id DESC";

$app_stmt = $pdo->prepare($app_sql);

$app_stmt->execute([
    ":job_id" => $job_id
]);

$applications = $app_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Applications - <?php echo htmlspecialchars($job["title"]); ?>
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
            margin-bottom: 10px;
        }


        .page-subtitle {
            text-align: center;
            color: #555;
            margin-bottom: 30px;
            font-size: 18px;
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


        /* Back link */

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #1f4e79;
            text-decoration: none;
            font-weight: bold;
        }


        .back-link:hover {
            text-decoration: underline;
        }


        /* Application Card */

        .application-card {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }


        .applicant-name {
            color: #1f4e79;
            font-size: 22px;
            margin-bottom: 15px;
        }


        .applicant-info {
            line-height: 1.7;
            margin-bottom: 8px;
        }


        .applicant-info strong {
            color: #333;
        }


        /* Status badge */

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


        /* Cover Letter */

        .cover-letter {
            margin-top: 20px;
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
        }


        /* Action buttons */

        .action-area {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }


        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            font-weight: bold;
        }


        .btn-accept {
            background-color: #198754;
            color: white;
        }


        .btn-accept:hover {
            background-color: #146c43;
        }


        .btn-reject {
            background-color: #dc3545;
            color: white;
        }


        .btn-reject:hover {
            background-color: #b02a37;
        }


        .btn-pending {
            background-color: #ffc107;
            color: #333;
        }


        .btn-pending:hover {
            background-color: #d39e00;
        }


        .btn-current {
            opacity: 0.5;
            cursor: not-allowed;
        }


        /* Empty state */

        .no-applications {
            background-color: white;
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }


        .no-applications h2 {
            color: #1f4e79;
            margin-bottom: 12px;
        }


        .no-applications p {
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


            .application-card {
                padding: 20px;
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

                <a href="my_jobs.php">
                    My Jobs
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


        <a href="my_jobs.php" class="back-link">
            ← Back to My Posted Jobs
        </a>


        <h1 class="page-title">
            Applications Received
        </h1>


        <p class="page-subtitle">

            For:
            <strong>
                <?php echo htmlspecialchars($job["title"]); ?>
            </strong>
            —
            <?php echo htmlspecialchars($job["company"]); ?>

        </p>


        <!-- Message -->

        <?php if (!empty($message)): ?>

            <div class="alert <?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <?php if (count($applications) > 0): ?>


            <?php foreach ($applications as $application): ?>


                <?php

                $status = strtolower($application["status"]);

                if ($status === "pending") {
                    $status_class = "status-pending";
                } elseif ($status === "accepted") {
                    $status_class = "status-accepted";
                } elseif ($status === "rejected") {
                    $status_class = "status-rejected";
                } else {
                    $status_class = "status-pending";
                }

                ?>


                <div class="application-card">


                    <!-- Applicant Name -->

                    <h2 class="applicant-name">

                        <?php
                        echo htmlspecialchars($application["full_name"]);
                        ?>

                    </h2>


                    <!-- Email -->

                    <p class="applicant-info">

                        <strong>Email:</strong>

                        <?php
                        echo htmlspecialchars($application["email"]);
                        ?>

                    </p>


                    <!-- Phone -->

                    <p class="applicant-info">

                        <strong>Phone:</strong>

                        <?php
                        echo htmlspecialchars($application["phone"]);
                        ?>

                    </p>


                    <!-- Applied Date -->

                    <?php if (!empty($application["applied_at"])): ?>

                        <p class="applicant-info">

                            <strong>Applied on:</strong>

                            <?php
                            echo htmlspecialchars(
                                date("M d, Y", strtotime($application["applied_at"]))
                            );
                            ?>

                        </p>

                    <?php endif; ?>


                    <!-- Status -->

                    <p class="applicant-info">

                        <strong>Current Status:</strong>

                        <span class="status <?php echo $status_class; ?>">

                            <?php
                            echo htmlspecialchars($application["status"]);
                            ?>

                        </span>

                    </p>


                    <!-- Cover Letter -->

                    <div class="cover-letter">

                        <h3>
                            Cover Letter
                        </h3>

                        <div class="cover-letter-text">

                            <?php
                            echo nl2br(
                                htmlspecialchars($application["cover_letter"])
                            );
                            ?>

                        </div>

                    </div>


                    <!-- Action Buttons -->

                    <div class="action-area">


                        <?php if ($application["status"] === "Accepted"): ?>

                            <button class="btn btn-accept btn-current" disabled>
                                ✅ Accepted
                            </button>

                        <?php else: ?>

                            <form method="POST" action="applications.php?job_id=<?php echo $job_id; ?>" style="display:inline;">

                                <input type="hidden" name="application_id" value="<?php echo (int) $application["application_id"]; ?>">
                                <input type="hidden" name="new_status" value="Accepted">
                                <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">

                                <button type="submit" class="btn btn-accept">
                                    ✅ Accept
                                </button>

                            </form>

                        <?php endif; ?>


                        <?php if ($application["status"] === "Rejected"): ?>

                            <button class="btn btn-reject btn-current" disabled>
                                ❌ Rejected
                            </button>

                        <?php else: ?>

                            <form method="POST" action="applications.php?job_id=<?php echo $job_id; ?>" style="display:inline;">

                                <input type="hidden" name="application_id" value="<?php echo (int) $application["application_id"]; ?>">
                                <input type="hidden" name="new_status" value="Rejected">
                                <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">

                                <button type="submit" class="btn btn-reject">
                                    ❌ Reject
                                </button>

                            </form>

                        <?php endif; ?>


                        <?php if ($application["status"] !== "Pending"): ?>

                            <form method="POST" action="applications.php?job_id=<?php echo $job_id; ?>" style="display:inline;">

                                <input type="hidden" name="application_id" value="<?php echo (int) $application["application_id"]; ?>">
                                <input type="hidden" name="new_status" value="Pending">
                                <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">

                                <button type="submit" class="btn btn-pending">
                                    ⏳ Reset to Pending
                                </button>

                            </form>

                        <?php endif; ?>


                    </div>


                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <div class="no-applications">

                <h2>
                    No Applications Yet
                </h2>

                <p>
                    Nobody has applied to this job yet.
                    Check back later.
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