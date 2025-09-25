<?php
require "../config/db.php";
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
  header("Location: ../login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
  $courseId = intval($_POST['course_id']);
  $studentId = $_SESSION['user']['id'];

  // Save to a "cart" table (create it if not exists)
  $stmt = $pdo->prepare("INSERT INTO cart (student_id, course_id) VALUES (?, ?)");
  $stmt->execute([$studentId, $courseId]);

  header("Location: ../courses.php?added=1");
  exit();
}
