<?php
require_once __DIR__ . '/../bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= sanitize($pageTitle ?? 'Admin Dashboard'); ?> - Origin Driving School</title>
  <link rel="stylesheet" href="/origin_driving_school/assets/css/style.css">
  <style>
    .admin-layout { display: flex; min-height: 100vh; }
    .admin-sidebar { width: 240px; background: #0e1a2a; color: #fff; padding: 24px 16px; }
    .admin-sidebar h2 { margin-bottom: 24px; font-size: 1.4rem; }
    .admin-sidebar a { color: #fff; display: block; padding: 8px 12px; margin-bottom: 6px; border-radius: 8px; text-decoration: none; }
    .admin-sidebar a.active, .admin-sidebar a:hover { background: rgba(255,255,255,0.12); }
    .admin-content { flex: 1; background: #f5f7fb; padding: 32px; }
    .admin-card { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 8px 24px rgba(15,30,64,0.08); margin-bottom: 24px; }
    .admin-grid { display: grid; gap: 20px; }
    .admin-grid-3 { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
    .admin-metrics span { display: block; font-size: 2rem; font-weight: 700; margin-top: 8px; }
    table.admin-table { width: 100%; border-collapse: collapse; }
    table.admin-table th, table.admin-table td { padding: 10px 12px; border-bottom: 1px solid #e1e5ee; text-align: left; }
    table.admin-table th { background: #eef2fb; font-weight: 600; }
    .admin-table caption { text-align: left; font-weight: 700; margin-bottom: 12px; }
    .admin-actions { display: flex; flex-wrap: wrap; gap: 10px; }
    .admin-form { display: grid; gap: 16px; }
    .admin-form .input-wrapper { display: flex; flex-direction: column; }
    .admin-form label { font-weight: 600; margin-bottom: 6px; }
    .admin-form input, .admin-form select, .admin-form textarea { padding: 10px 12px; border: 1px solid #d4d9e5; border-radius: 10px; }
    .admin-section-title { margin-bottom: 12px; }
    .admin-breadcrumb { margin-bottom: 16px; color: #6f7a90; }
  </style>
</head>
<body>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <h2>Origin Admin</h2>
    <a href="/origin_driving_school/admin/index.php" class="<?= ($_SERVER['PHP_SELF'] === '/origin_driving_school/admin/index.php') ? 'active' : '' ?>">Dashboard</a>
    <a href="/origin_driving_school/admin/students.php" class="<?= str_contains($_SERVER['PHP_SELF'], 'students.php') ? 'active' : '' ?>">Students</a>
    <a href="/origin_driving_school/admin/instructors.php" class="<?= str_contains($_SERVER['PHP_SELF'], 'instructors.php') ? 'active' : '' ?>">Instructors</a>
    <a href="/origin_driving_school/admin/schedule.php" class="<?= str_contains($_SERVER['PHP_SELF'], 'schedule.php') ? 'active' : '' ?>">Scheduling</a>
    <a href="/origin_driving_school/admin/invoices.php" class="<?= str_contains($_SERVER['PHP_SELF'], 'invoices.php') ? 'active' : '' ?>">Invoices</a>
    <a href="/origin_driving_school/admin/payments.php" class="<?= str_contains($_SERVER['PHP_SELF'], 'payments.php') ? 'active' : '' ?>">Payments</a>
    <a href="/origin_driving_school/admin/branches.php" class="<?= str_contains($_SERVER['PHP_SELF'], 'branches.php') ? 'active' : '' ?>">Branches</a>
    <a href="/origin_driving_school/admin/fleet.php" class="<?= str_contains($_SERVER['PHP_SELF'], 'fleet.php') ? 'active' : '' ?>">Fleet</a>
    <a href="/origin_driving_school/admin/communications.php" class="<?= str_contains($_SERVER['PHP_SELF'], 'communications.php') ? 'active' : '' ?>">Communications</a>
    <a href="/origin_driving_school/index.php">Back to site</a>
  </aside>
  <main class="admin-content">
    <div class="admin-breadcrumb">Logged in as <?= sanitize($authUser['name']); ?></div>