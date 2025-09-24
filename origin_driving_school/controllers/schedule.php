<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../models/Schedule.php';

$scheduleModel = new Schedule($pdo);

// Add new schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scheduleModel->create([
        ':student_id'    => $_POST['student_id'],
        ':instructor_id' => $_POST['instructor_id'],
        ':vehicle_id'    => $_POST['vehicle_id'],
        ':start_time'    => $_POST['start_time'],
        ':end_time'      => $_POST['end_time'],
        ':status'        => 'booked'
    ]);
    header("Location: /origin_driving_school/public/schedule.php?success=1");
    exit;
}

// Delete schedule
if (isset($_GET['delete'])) {
    $scheduleModel->delete($_GET['delete']);
    header("Location: /origin_driving_school/public/schedule.php?deleted=1");
    exit;
}

// Fetch all schedules
$schedules = $scheduleModel->all();
