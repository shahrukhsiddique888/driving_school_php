<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/Schedule.php";

$scheduleModel = new Schedule($pdo);

// Handle create (form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id   = $_POST['student_id'];
    $instructor_id = $_POST['instructor_id'];
    $vehicle_id   = $_POST['vehicle_id'];
    $start_time   = $_POST['start_time'];
    $end_time     = $_POST['end_time'];

    if ($scheduleModel->create($student_id, $instructor_id, $vehicle_id, $start_time, $end_time)) {
        header("Location: ../schedule.php?success=1");
        exit;
    } else {
        die("❌ Error: Could not create schedule.");
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($scheduleModel->delete($id)) {
        header("Location: ../schedule.php?deleted=1");
        exit;
    } else {
        die("❌ Error: Could not delete schedule.");
    }
}

// Always fetch schedules for the view
$schedules = $scheduleModel->all();
