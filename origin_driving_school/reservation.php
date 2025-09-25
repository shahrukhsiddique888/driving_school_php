<?php
include "includes/header.php";
require "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login to book a lesson");
    exit;
}

// Fetch user name for pre-fill
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<main>
  <section class="section get-start">
    <div class="container">
      <h2 class="h2 section-title">Book a Lesson</h2>
      <p class="section-text">
        Reserve your driving lesson by filling in the details below.
      </p>

      <!-- Reservation Form -->
      <div class="get-start-card">
        <h3 class="card-title">Lesson Reservation</h3>

        <?php if (isset($_GET['success'])): ?>
          <p style="color:green;">Lesson booked successfully!</p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
          <p style="color:red;"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>

        <form method="POST" action="controllers/reservation.php">
          <div class="input-wrapper">
            <label for="student_name">Full Name</label>
            <input type="text" name="student_name" class="input-field"
                   value="<?= htmlspecialchars($user['name']) ?>" readonly>
          </div>

          <div class="input-wrapper">
            <label for="pickup">Pickup Location</label>
            <input type="text" name="pickup" class="input-field" required>
          </div>

          <div class="input-wrapper">
            <label for="dropoff">Drop-off Location</label>
            <input type="text" name="dropoff" class="input-field" required>
          </div>

          <div class="input-wrapper">
            <label for="date">Lesson Date</label>
            <input type="date" name="date" class="input-field" required>
          </div>

          <button type="submit" class="btn">Book Lesson</button>
        </form>
      </div>
    </div>
  </section>
</main>

<?php
include "includes/footer.php";
?>
