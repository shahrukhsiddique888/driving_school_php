<?php
// controllers/upload.php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php?error=Please+login+first");
    exit;
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadDir = __DIR__ . "/../uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES['file']['name']);
    $targetPath = $uploadDir . $fileName;
    $dbPath = "uploads/" . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare("INSERT INTO user_files (user_id, file_path) VALUES (?, ?)");
        $stmt->execute([$user_id, $dbPath]);

        header("Location: ../profile.php?uploaded=1");
        exit;
    } else {
        header("Location: ../profile.php?error=Upload+failed");
        exit;
    }
}
