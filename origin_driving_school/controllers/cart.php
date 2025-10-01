<?php
// controllers/cart.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php?error=Please+login+to+add+courses");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id  = $_SESSION['user']['id'];
    $course_id = $_POST['course_id'] ?? null;

    if ($course_id) {
        try {
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, course_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $course_id]);

            header("Location: ../cart.php?added=1");
            exit;
        } catch (Exception $e) {
            header("Location: ../courses.php?error=Could+not+add+course");
            exit;
        }
    }
}
