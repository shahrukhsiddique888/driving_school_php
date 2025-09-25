<?php
require "../config/db.php"; // PDO connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
    $password = $_POST['password'];
    $role = $_POST['role']; // student or instructor

    // Security: prevent self-registering as admin
    if ($role === "admin") {
        header("Location: ../register.php?error=Admin accounts must be created by system administrator");
        exit;
    }

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: ../register.php?error=Email already registered");
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        $pdo->beginTransaction();

        // Insert into users table
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword, $role]);
        $userId = $pdo->lastInsertId();

        // Insert into students or instructors
        if ($role === "student") {
            $stmt = $pdo->prepare("INSERT INTO students (user_id, phone, license_status) VALUES (?, ?, 'none')");
            $stmt->execute([$userId, $phone]);
        } elseif ($role === "instructor") {
            $stmt = $pdo->prepare("INSERT INTO instructors (user_id, specialty, availability) VALUES (?, 'General Driving', 'Mon-Fri 9am-5pm')");
            $stmt->execute([$userId]);
        }

        $pdo->commit();

        header("Location: ../login.php?success=Account created successfully, please login");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: ../register.php?error=Registration failed, try again");
        exit;
    }
} else {
    header("Location: ../register.php");
    exit;
}
