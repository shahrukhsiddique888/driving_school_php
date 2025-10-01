<?php
// controllers/reservation.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id      = $_POST['user_id'] ?? null;
    $student_name = trim($_POST['student_name'] ?? '');
    $pickup       = trim($_POST['pickup'] ?? '');
    $dropoff      = trim($_POST['dropoff'] ?? '');
    $date         = $_POST['date'] ?? '';
    $branch_id    = isset($_POST['branch_id']) ? (int)$_POST['branch_id'] : null;

    // Validate required fields
    if (!$user_id || !$student_name || !$pickup || !$dropoff || !$date) {
        header("Location: ../reservation.php?error=Please+fill+all+fields");
        exit;
    }

    try {
        // Insert into reservations table
        if ($branch_id) {
            $branchCheck = $pdo->prepare("SELECT id FROM branches WHERE id = ?");
            $branchCheck->execute([$branch_id]);
            if (!$branchCheck->fetchColumn()) {
                $branch_id = null;
            }
        }

        $stmt = $pdo->prepare(" INSERT INTO reservations (student_name, pickup, dropoff, date, branch_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$student_name, $pickup, $dropoff, $date, $branch_id]);

        // OPTIONAL: Insert into schedule for visibility
        // Assuming default instructor_id = 1, vehicle_id = 1
        // You can change this later to allow choosing instructor/vehicle
        $schedule = $pdo->prepare(" 
        INSERT INTO schedule (student_id, instructor_id, vehicle_id, branch_id, start_time, end_time, status)
            VALUES (?, 1, 1, ?, ?, ?, 'booked')
        ");

        // For now, we’ll make start_time = date @ 10:00 and end_time = 11:00
        $start_time = $date . " 10:00:00";
        $end_time   = $date . " 11:00:00";

        // Find student_id from students table based on user_id
        $s = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
        $s->execute([$user_id]);
        $student = $s->fetch();

        if ($student) {
            $schedule->execute([$student['id'], $branch_id, $start_time, $end_time]);
        }

        header("Location: ../reservation.php?success=1");
        exit;
    } catch (Exception $e) {
        header("Location: ../reservation.php?error=Booking+failed:+please+try+again");
        exit;
    }
} else {
    header("Location: ../reservation.php");
    exit;
}



