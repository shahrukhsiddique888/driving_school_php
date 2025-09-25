<?php
require "../config/db.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Please login to book a lesson");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $studentName = trim($_POST['student_name']);
    $pickup = trim($_POST['pickup']);
    $dropoff = trim($_POST['dropoff']);
    $date = $_POST['date'];

    if (empty($studentName) || empty($pickup) || empty($dropoff) || empty($date)) {
        header("Location: ../reservation.php?error=All fields are required");
        exit;
    }

    // Insert into reservations
    $stmt = $pdo->prepare("INSERT INTO reservations (student_name, pickup, dropoff, date) 
                           VALUES (?, ?, ?, ?)");
    $stmt->execute([$studentName, $pickup, $dropoff, $date]);

    // Insert into schedule (link to student & default instructor/vehicle)
    $studentIdStmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
    $studentIdStmt->execute([$_SESSION['user_id']]);
    $student = $studentIdStmt->fetch();

    if ($student) {
        $studentId = $student['id'];

        // Assign default instructor + vehicle (can be made dynamic later)
        $instructorId = 1; 
        $vehicleId = 1;

        $startTime = $date . " 10:00:00";
        $endTime   = $date . " 11:00:00";

        $stmt = $pdo->prepare("INSERT INTO schedule (student_id, instructor_id, vehicle_id, start_time, end_time, status) 
                               VALUES (?, ?, ?, ?, ?, 'booked')");
        $stmt->execute([$studentId, $instructorId, $vehicleId, $startTime, $endTime]);
    }

    header("Location: ../reservation.php?success=Lesson booked successfully");
    exit;
} else {
    header("Location: ../reservation.php");
    exit;
}
