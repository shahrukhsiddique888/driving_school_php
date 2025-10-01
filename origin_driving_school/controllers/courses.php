<?php
// controllers/courses.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/db.php";

// Ensure courses array is always defined
$courses = [];

/**
 * Fetch all courses
 */
try {
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC");
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching courses: " . $e->getMessage());
}

/**
 * Add a new course (admin only)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $user = $_SESSION['user'] ?? null;
    if ($user && $user['role'] === 'admin') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $duration = trim($_POST['duration']);
        $price = floatval($_POST['price']);

        if (!empty($title) && !empty($description) && $price > 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO courses (title, description, duration, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $description, $duration, $price]);
                header("Location: ../courses.php?success=1");
                exit;
            } catch (PDOException $e) {
                die("Error adding course: " . $e->getMessage());
            }
        } else {
            header("Location: ../courses.php?error=invalid_input");
            exit;
        }
    } else {
        header("Location: ../login.php?error=not_authorized");
        exit;
    }
}

/**
 * Delete a course (admin only)
 */
if (isset($_GET['delete'])) {
    $user = $_SESSION['user'] ?? null;
    if ($user && $user['role'] === 'admin') {
        $courseId = intval($_GET['delete']);
        try {
            $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$courseId]);
            header("Location: ../courses.php?deleted=1");
            exit;
        } catch (PDOException $e) {
            die("Error deleting course: " . $e->getMessage());
        }
    } else {
        header("Location: ../login.php?error=not_authorized");
        exit;
    }
}
