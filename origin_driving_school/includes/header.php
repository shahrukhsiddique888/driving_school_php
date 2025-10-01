<?php
// Always start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user if logged in
$user = $_SESSION['user'] ?? null;
$unreadNotifications = 0;

if ($user) {
    require_once __DIR__ . '/../config/db.php';
    $notifStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL");
    $notifStmt->execute([$user['id']]);
    $unreadNotifications = (int)$notifStmt->fetchColumn();
}

// Function to get initials
function getInitials($name) {
    $parts = explode(" ", trim($name));
    $first = isset($parts[0]) ? substr($parts[0], 0, 1) : '';
    $second = isset($parts[1]) ? substr($parts[1], 0, 1) : '';
    return strtoupper($first . $second);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Origin Driving School'); ?></title>
  <link rel="stylesheet" href="./assets/css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&family=Open+Sans&display=swap" rel="stylesheet">
</head>
<body>
<header class="header" data-header>
  <div class="container">
    <div class="overlay" data-overlay></div>
    <a href="/origin_driving_school/index.php" class="logo">
      <img src="./assets/images/logo.jpg" width="70"  alt="Origin Driving School logo">
    </a>
    <nav class="navbar" data-navbar>
      <ul class="navbar-list">
        <li><a href="/origin_driving_school/index.php" class="navbar-link" data-nav-link>Home</a></li>
        <li><a href="/origin_driving_school/about.php" class="navbar-link" data-nav-link>About Us</a></li>
        <li><a href="/origin_driving_school/courses.php" class="navbar-link" data-nav-link>Courses</a></li>
        <li><a href="/origin_driving_school/schedule.php" class="navbar-link" data-nav-link>Schedule</a></li>
        <li><a href="/origin_driving_school/gallery.php" class="navbar-link" data-nav-link>Training Gallery</a></li>
        <li><a href="/origin_driving_school/instructors.php" class="navbar-link" data-nav-link>Instructors</a></li>
        <li><a href="/origin_driving_school/cart.php" class="navbar-link" data-nav-link>my cart</a></li>
        <li><a href="/origin_driving_school/contact.php" class="navbar-link" data-nav-link>Contact</a></li>
    

        <?php if ($user): ?>
          <li>
            <a href="/origin_driving_school/notifications.php" class="navbar-link" data-nav-link>
              Notifications<?= $unreadNotifications ? ' (' . $unreadNotifications . ')' : '' ?>
            </a>
          </li>
          <?php if ($user['role'] === 'admin'): ?>
            <li><a href="/origin_driving_school/admin/index.php" class="navbar-link" data-nav-link>Admin</a></li>
          <?php endif; ?>
          <li>
            <a href="/origin_driving_school/profile.php" class="user-initials" data-nav-link>
              <?= getInitials($user['name']); ?>
            </a>
          </li>
          <li>
            <a href="/origin_driving_school/logout.php" class="navbar-link" data-nav-link>Logout</a>
          </li>
        <?php else: ?>
          <li>
            <a href="/origin_driving_school/login.php" class="navbar-link" data-nav-link>Login</a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>
<main>
