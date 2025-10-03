<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: login.php?error=Please login to view your receipt");
    exit;
}

$receipt = $_SESSION['latest_receipt'] ?? null;

if (!$receipt) {
    header("Location: reservation.php");
    exit;
}

// Prevent the same receipt from being reused accidentally
unset($_SESSION['latest_receipt']);

$pageTitle = 'Lesson Booking Receipt';
include "includes/header.php";

$lessonDate = date('l, F j, Y', strtotime($receipt['date']));
$startTime  = date('g:i A', strtotime($receipt['start_time']));
$endTime    = date('g:i A', strtotime($receipt['end_time']));
$generated  = date('F j, Y g:i A', strtotime($receipt['generated_at']));
$bookingStatus = $receipt['schedule_confirmed'] ? 'Confirmed' : 'Pending Confirmation';
?>

<main>
  <section class="section get-start">
    <div class="container">
      <h2 class="h2 section-title">Lesson Booking Receipt</h2>
      <p class="section-text">
        Thank you for booking with Origin Driving School. Below are the details of your reservation.
      </p>

      <div class="get-start-card">
        <h3 class="card-title">Receipt Details</h3>
        <p class="card-subtitle">
          Reservation ID: <strong>#<?= htmlspecialchars($receipt['reservation_id']) ?></strong>
        </p>

        <ul class="receipt-details">
          <li><span>Student Name:</span> <strong><?= htmlspecialchars($receipt['student_name']) ?></strong></li>
          <li><span>Pickup Location:</span> <strong><?= htmlspecialchars($receipt['pickup']) ?></strong></li>
          <li><span>Drop-off Location:</span> <strong><?= htmlspecialchars($receipt['dropoff']) ?></strong></li>
          <li><span>Lesson Date:</span> <strong><?= htmlspecialchars($lessonDate) ?></strong></li>
          <li><span>Lesson Time:</span> <strong><?= htmlspecialchars($startTime) ?> &ndash; <?= htmlspecialchars($endTime) ?></strong></li>
          <li><span>Schedule Status:</span> <strong><?= htmlspecialchars($bookingStatus) ?></strong></li>
          <li><span>Receipt Generated:</span> <strong><?= htmlspecialchars($generated) ?></strong></li>
        </ul>

        <?php if ($receipt['schedule_confirmed']): ?>
          <p class="receipt-note">An instructor and training vehicle have been reserved for your lesson.</p>
        <?php else: ?>
          <p class="receipt-note">Our team will confirm your instructor and training vehicle shortly.</p>
        <?php endif; ?>

        <div class="receipt-actions">
          <a class="btn" href="reservation.php">Book Another Lesson</a>
          <button class="btn btn-secondary" onclick="window.print()">Print Receipt</button>
        </div>
      </div>
    </div>
  </section>
</main>

<?php
include "includes/footer.php";
?>