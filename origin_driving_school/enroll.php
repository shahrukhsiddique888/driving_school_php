<?php
require "config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// Get course details
$courseId = $_GET['course_id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

// Fetch instructors for dropdown
$instructors = $pdo->query("
  SELECT i.id, u.name 
  FROM instructors i 
  JOIN users u ON i.user_id = u.id
")->fetchAll();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instructorId = $_POST['instructor_id'];
    $location     = trim($_POST['location']);
    $date         = $_POST['date'];
    $studentName  = $user['name'];

    $insert = $pdo->prepare("INSERT INTO reservations (student_name, pickup, dropoff, date) VALUES (?, ?, ?, ?)");
    $insert->execute([$studentName, $location, "Driving School HQ", $date]);

    $message = "✅ Enrollment request submitted!";
}
?>

<?php include "includes/header.php"; ?>
<div class="container" style="margin-top:100px;max-width:600px;">
  <h2 class="h2">Enroll in Course</h2>
  <?php if ($message): ?><p style="color:green;"><?= $message ?></p><?php endif; ?>

  <?php if ($course): ?>
    <h3><?= htmlspecialchars($course['title']) ?></h3>
    <p><?= htmlspecialchars($course['description']) ?></p>
    <p><strong>Price:</strong> $<?= number_format($course['price'],2) ?></p>

    <form method="POST">
      <div class="input-wrapper">
        <label>Select Instructor</label>
        <select name="instructor_id" class="input-field" required>
          <option value="">-- Choose Instructor --</option>
          <?php foreach ($instructors as $i): ?>
            <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Location</label>
        <input type="text" name="location" required class="input-field" placeholder="e.g., Sydney CBD">
      </div>
      <div class="input-wrapper">
        <label>Date</label>
        <input type="date" name="date" required class="input-field">
      </div>
      <button type="submit" class="btn">Confirm Enrollment</button>
    </form>
  <?php else: ?>
    <p style="color:red;">Course not found.</p>
  <?php endif; ?>
</div>
<?php include "includes/footer.php"; ?>
