<?php

session_start();

require_once "config.php";

$message = "";
$email_value = "";

// Show success message after registration
if (isset($_GET["registered"]) && $_GET["registered"] === "1") {
    $message = "Registration successful! You can now log in.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = filter_var(trim($_POST["email"] ?? ""), FILTER_VALIDATE_EMAIL);
    $password = $_POST["password"] ?? "";

    // Keep the entered email so we can refill the form on error
    $email_value = htmlspecialchars($_POST["email"] ?? "");

    if (!$email) {
        $message = "Please enter a valid email address.";
    } elseif ($password === "") {
        $message = "Please enter your password.";
    } else {

        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":email" => $email
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {

            // Create a new session ID after login
            session_regenerate_id(true);

            // Store user information in the session
            $_SESSION["user_id"]   = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["role"]      = $user["role"];

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();

        } else {
            $message = "Invalid email or password.";
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

    <title>Login - Job Vacancy System</title>

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

                <a href="register.php">
                    Register
                </a>

            </div>

        </div>

    </nav>


    <!-- Login Form -->

    <main class="main-content">

        <div class="form-container">

            <h1 class="page-title">
                Login
            </h1>

            <p>
                Login to your Job Vacancy System account.
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

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo $email_value; ?>"
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
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn"
                >
                    Login
                </button>

            </form>


            <p>

                Don't have an account?

                <a href="register.php">
                    Create an account
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