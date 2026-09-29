<?php

session_start();

require_once "config.php";

$message = "";

// Preserve form values on error
$full_name_value = "";
$email_value     = "";
$phone_value     = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $phone     = trim($_POST["phone"] ?? "");
    $password  = $_POST["password"] ?? "";

    // Preserve for re-rendering the form on error
    $full_name_value = htmlspecialchars($full_name);
    $email_value     = htmlspecialchars($email);
    $phone_value     = htmlspecialchars($phone);

    // --- Validation ---
    if (
        empty($full_name) ||
        empty($email) ||
        empty($phone) ||
        empty($password)
    ) {
        $message = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";

    } elseif (strlen($full_name) > 100) {
        $message = "Full name is too long (max 100 characters).";

    } elseif (strlen($email) > 150) {
        $message = "Email is too long (max 150 characters).";

    } elseif (strlen($phone) > 20) {
        $message = "Phone number is too long (max 20 characters).";

    } elseif (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
        $message = "Please enter a valid phone number.";

    } elseif (
        strlen($password) < 8 ||
        !preg_match('/[A-Za-z]/', $password) ||
        !preg_match('/[0-9]/', $password)
    ) {
        $message = "Password must be at least 8 characters and contain both letters and numbers.";

    } else {

        try {

            /*
             * Check whether the email already exists
             */
            $sql = "SELECT id
                    FROM users
                    WHERE email = :email
                    LIMIT 1";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ":email" => $email
            ]);

            if ($stmt->fetch()) {

                $message = "An account with this email already exists.";

            } else {

                /*
                 * Hash the password before storing it
                 */
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                /*
                 * Create a job seeker account
                 */
                $sql = "INSERT INTO users
                        (full_name, email, password, role, phone)
                        VALUES
                        (:full_name, :email, :password,
                         'job_seeker', :phone)";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ":full_name" => $full_name,
                    ":email"     => $email,
                    ":password"  => $hashed_password,
                    ":phone"     => $phone
                ]);

                /*
                 * Redirect to login page with success flag
                 */
                header("Location: login.php?registered=1");
                exit();
            }

        } catch (PDOException $e) {

            error_log("Registration failed: " . $e->getMessage());
            $message = "Registration failed. Please try again.";
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

    <title>Register - Job Vacancy System</title>

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

                <a href="login.php">
                    Login
                </a>

            </div>

        </div>

    </nav>


    <!-- Registration Form -->

    <main class="main-content">

        <div class="form-container">

            <h1 class="page-title">
                Create an Account
            </h1>

            <p>
                Register as a job seeker to browse jobs
                and submit applications.
            </p>


            <?php if (!empty($message)): ?>

                <div class="alert">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <form method="POST" action="">

                <div class="form-group">

                    <label for="full_name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        value="<?php echo $full_name_value; ?>"
                        maxlength="100"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo $email_value; ?>"
                        maxlength="150"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="phone">
                        Phone Number
                    </label>

                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="<?php echo $phone_value; ?>"
                        maxlength="20"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="8"
                        required
                    >

                    <small>
                        At least 8 characters, including letters and numbers.
                    </small>

                </div>


                <button
                    type="submit"
                    class="btn"
                >
                    Register
                </button>

            </form>


            <p>

                Already have an account?

                <a href="login.php">
                    Login here
                </a>

            </p>

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