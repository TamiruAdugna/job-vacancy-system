<?php

$host     = "localhost";
$dbname   = "job_vacancy_system";
$username = "your_db_username";
$password = "your_db_password";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("DB connection failed: " . $e->getMessage());
    die("Sorry, we're having trouble connecting. Please try again later.");
}