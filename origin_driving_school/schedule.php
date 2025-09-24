<?php
include "./includes/header.php";
require_once __DIR__ . "/controllers/schedule.php";
?>

<main>
  <section class="section schedule">
    <div class="container">
      <h2 class="h2 section-title">Schedule Lessons</h2>

      <?php if (isset($_GET['success'])): ?>
        <p style="color:green;">✅ Lesson scheduled successfully!</p>
      <?php endif; ?>

      <?php if (isset($_GET['deleted'])): ?>
        <p style="color:red;">❌ Lesson deleted successfully!</p>
      <?php endif; ?>

      <!-- Add Lesson Form -->
      <form method="POST" action="./controllers/schedule.php" style="margin-bottom:20px;">
        <input type="number" name="student_id" placeholder="Student ID" required>
        <input type="number" name="instructor_id" placeholder="Instructor ID" required>
        <input type="number" name="vehicle_id" placeholder="Vehicle ID" required>
        <input type="datetime-local" name="start_time" required>
        <input type="datetime-local" name="end_time" required>
        <button type="submit">Book Lesson</button>
      </form>

      <!-- Scheduled Lessons List -->
      <h3 class="h3">Upcoming Lessons</h3>
      <table border="1" cellpadding="8" style="width:100%; margin-top:10px;">
        <tr>
          <th>ID</th>
          <th>Student</th>
          <th>Instructor</th>
          <th>Vehicle</th>
          <th>Start</th>
          <th>End</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
        <?php if (!empty($schedules)): ?>
          <?php foreach ($schedules as $s): ?>
            <tr>
              <td><?= $s['id'] ?></td>
              <td><?= htmlspecialchars($s['student_name']) ?></td>
              <td><?= htmlspecialchars($s['instructor_name']) ?></td>
              <td><?= htmlspecialchars($s['vehicle_name']) ?></td>
              <td><?= $s['start_time'] ?></td>
              <td><?= $s['end_time'] ?></td>
              <td><?= $s['status'] ?></td>
              <td><a href="./controllers/schedule.php?delete=<?= $s['id'] ?>" style="color:red;">Delete</a></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8">No lessons scheduled yet.</td></tr>
        <?php endif; ?>
      </table>
    </div>
  </section>
</main>

<?php include "./includes/footer.php"; ?>
