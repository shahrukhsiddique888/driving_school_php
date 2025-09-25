<?php
include "includes/header.php";
require "config/db.php";

// Fetch confirmed schedule lessons
$stmt = $pdo->query("
    SELECT s.id, u.name AS student_name, i.specialty, s.start_time, s.end_time, s.status, v.make, v.model
    FROM schedule s
    JOIN students st ON s.student_id = st.id
    JOIN users u ON st.user_id = u.id
    JOIN instructors i ON s.instructor_id = i.id
    LEFT JOIN vehicles v ON s.vehicle_id = v.id
    ORDER BY s.start_time DESC
");
$schedules = $stmt->fetchAll();

// Fetch reservations
$resStmt = $pdo->query("SELECT student_name, pickup, dropoff, date, created_at 
                        FROM reservations ORDER BY date DESC");
$reservations = $resStmt->fetchAll();
?>

<main>
  <section class="section get-start">
    <div class="container">
      <h2 class="h2 section-title">Lesson Schedule</h2>

      <!-- Confirmed Lessons -->
      <h3 class="h3" style="margin-bottom:15px;">Confirmed Lessons</h3>
      <ul class="featured-car-list">
        <?php if ($schedules): ?>
          <?php foreach ($schedules as $s): ?>
            <li>
              <div class="featured-car-card">
                <div class="card-content">
                  <div class="card-title-wrapper">
                    <h3 class="h3 card-title"><?= htmlspecialchars($s['student_name']) ?></h3>
                    <data class="year"><?= date("M d, Y H:i", strtotime($s['start_time'])) ?> - <?= date("H:i", strtotime($s['end_time'])) ?></data>
                  </div>
                  <p><strong>Instructor:</strong> <?= htmlspecialchars($s['specialty']) ?></p>
                  <p><strong>Vehicle:</strong> <?= htmlspecialchars($s['make'] . " " . $s['model']) ?></p>
                  <p><strong>Status:</strong> <?= ucfirst($s['status']) ?></p>
                </div>
              </div>
            </li>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No confirmed lessons yet.</p>
        <?php endif; ?>
      </ul>

      <hr style="margin:40px 0;">

      <!-- Reservations -->
      <h3 class="h3" style="margin-bottom:15px;">Pending Reservations</h3>
      <ul class="featured-car-list">
        <?php if ($reservations): ?>
          <?php foreach ($reservations as $r): ?>
            <li>
              <div class="featured-car-card">
                <div class="card-content">
                  <div class="card-title-wrapper">
                    <h3 class="h3 card-title"><?= htmlspecialchars($r['student_name']) ?></h3>
                    <data class="year"><?= date("M d, Y", strtotime($r['date'])) ?></data>
                  </div>
                  <p><strong>Pickup:</strong> <?= htmlspecialchars($r['pickup']) ?></p>
                  <p><strong>Dropoff:</strong> <?= htmlspecialchars($r['dropoff']) ?></p>
                  <p><em>Requested on <?= date("M d, Y H:i", strtotime($r['created_at'])) ?></em></p>
                </div>
              </div>
            </li>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No pending reservations.</p>
        <?php endif; ?>
      </ul>
    </div>
  </section>
</main>

<?php include "includes/footer.php"; ?>
