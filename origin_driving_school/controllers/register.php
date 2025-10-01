<?php
require "../config/db.php"; // PDO connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
    $password = $_POST['password'];
    $role = $_POST['role']; // student or instructor
    $branchId = isset($_POST['branch_id']) && $_POST['branch_id'] !== '' ? (int)$_POST['branch_id'] : null;
    $licenseStatus = $_POST['license_status'] ?? 'none';
    $allowedStatuses = ['none','learner','provisional','full'];
    if (!in_array($licenseStatus, $allowedStatuses, true)) {
        $licenseStatus = 'none';
    }

    if ($branchId) {
        $branchCheck = $pdo->prepare("SELECT id FROM branches WHERE id = ?");
        $branchCheck->execute([$branchId]);
        if (!$branchCheck->fetchColumn()) {
            $branchId = null;
        }
    }
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
            $stmt = $pdo->prepare("INSERT INTO students (user_id, branch_id, phone, license_status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $branchId, $phone, $licenseStatus]);
        } elseif ($role === "instructor") {
            $stmt = $pdo->prepare("INSERT INTO instructors (user_id, branch_id, specialty, phone, availability, hourly_rate, bio) VALUES (?, ?, 'General Driving', ?, 'Mon-Fri 9am-5pm', 0, NULL)");
            $stmt->execute([$userId, $branchId, $phone]);
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
