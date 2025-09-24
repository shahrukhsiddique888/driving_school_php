<?php // includes/header.php
?><!DOCTYPE html>
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
    <a href="/public/index.php" class="logo">
      <img src="/assets/images/logo.png" width="70" alt="Origin Driving School logo">
    </a>
    <nav class="navbar" data-navbar>
      <ul class="navbar-list">
        <li><a href="/origin_driving_school/index.php" class="navbar-link" data-nav-link>Home</a></li>
        <li><a href="/origin_driving_school/about.php" class="navbar-link" data-nav-link>About Us</a></li>
        <li><a href="/origin_driving_school/courses.php" class="navbar-link" data-nav-link>Courses</a></li>
        <li><a href="/origin_driving_school/schedule.php" class="navbar-link" data-nav-link>Schedule</a></li>
        <li><a href="/origin_driving_school/gallery.php" class="navbar-link" data-nav-link>Training Gallery</a></li>
        <li><a href="/origin_driving_school/instructors.php" class="navbar-link" data-nav-link>Instructors</a></li>
        <li><a href="/origin_driving_school/payments.php" class="navbar-link" data-nav-link>Payments</a></li>
        <li><a href="/origin_driving_school/contact.php" class="navbar-link" data-nav-link>Contact</a></li>
        <li><a href="/origin_driving_school/login.php" class="navbar-link" data-nav-link>Login</a></li>
      </ul>
    </nav>
  </div>
</header>
<main>
