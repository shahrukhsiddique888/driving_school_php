<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../models/Course.php';

$courseModel = new Course($pdo);

// Create new course
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseModel->create([
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'duration' => $_POST['duration'],
        'price' => $_POST['price']
    ]);
    header("Location: /public/courses.php?success=1");
    exit;
}

// Delete course
if (isset($_GET['delete'])) {
    $courseModel->delete($_GET['delete']);
    header("Location: /public/courses.php?deleted=1");
    exit;
}

// Fetch all courses
$courses = $courseModel->all();
