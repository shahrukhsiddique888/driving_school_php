<?php
require "../config/db.php"; // your PDO connection

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Store session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        header("Location: ../index.php");
        exit;
    } else {
        header("Location: ../login.php?error=Invalid email or password");
        exit;
    }
} else {
    header("Location: ../login.php");
    exit;
}
