<?php
// controllers/login.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/db.php"; // $pdo connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        header("Location: ../login.php?error=Please+fill+in+all+fields");
        exit;
    }

    try {
        // Find user by email in USERS table
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Check password
        if ($user && password_verify($password, $user['password'])) {
            // Save minimal safe session info
            $_SESSION['user'] = [
                'id'   => (int)$user['id'],   // must match users.id
                'name' => $user['name'],
                'role' => $user['role']
            ];

            // Redirect to home (or courses if you want)
            header("Location: ../index.php");
            exit;
        } else {
            header("Location: ../login.php?error=Invalid+email+or+password");
            exit;
        }
    } catch (Exception $e) {
        // Never expose raw DB error to user
        header("Location: ../login.php?error=Server+error,+please+try+again");
        exit;
    }
} else {
    header("Location: ../login.php");
    exit;
}
