<?php
$pageTitle = 'Scheduling';
include __DIR__ . '/includes/header.php';

$branches = $pdo->query('SELECT id, name FROM branches ORDER BY name')->fetchAll();
$students = $pdo->query("SELECT students.id, users.name FROM students JOIN users ON students.user_id = users.id ORDER BY users.name")->fetchAll();
$instructors = $pdo->query("SELECT instructors.id, users.name FROM instructors JOIN users ON instructors.user_id = users.id ORDER BY users.name")->fetchAll();
$vehicles = $pdo->query("SELECT vehicles.id, CONCAT(vehicles.make, ' ', vehicles.model, ' (', vehicles.rego, ')') AS label FROM vehicles ORDER BY vehicles.make")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_schedule') {
        $studentId = (int)($_POST['student_id'] ?? 0);
        $instructorId = (int)($_POST['instructor_id'] ?? 0);
        $vehicleId = isset($_POST['vehicle_id']) && $_POST['vehicle_id'] !== '' ? (int)$_POST['vehicle_id'] : null;
        $branchId = isset($_POST['branch_id']) && $_POST['branch_id'] !== '' ? (int)$_POST['branch_id'] : null;
        $startTime = $_POST['start_time'] ?? '';
        $endTime = $_POST['end_time'] ?? '';

        if (!$studentId || !$instructorId || !$startTime || !$endTime) {
            header('Location: schedule.php?error=Missing+required+fields');
            exit;
        }

        if (strtotime($endTime) <= strtotime($startTime)) {
            header('Location: schedule.php?error=End+time+must+be+after+start+time');
            exit;
        }

        // Check double bookings
        $conflictStmt = $pdo->prepare("SELECT COUNT(*) FROM schedule
                                        WHERE id <> 0 AND status != 'cancelled' AND
                                              ((start_time < :end AND end_time > :start) AND (instructor_id = :instructor OR (:vehicle IS NOT NULL AND vehicle_id = :vehicle)))");
        $conflictStmt->execute([
            ':start' => $startTime,
            ':end' => $endTime,
            ':instructor' => $instructorId,
            ':vehicle' => $vehicleId
        ]);
        if ($conflictStmt->fetchColumn()) {
            header('Location: schedule.php?error=Time+slot+conflict+for+instructor+or+vehicle');
            exit;
        }

        $insertStmt = $pdo->prepare('INSERT INTO schedule (student_id, instructor_id, vehicle_id, branch_id, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?, "booked")');
        $insertStmt->execute([$studentId, $instructorId, $vehicleId, $branchId, $startTime, $endTime]);

        header('Location: schedule.php?success=Lesson+scheduled');
        exit;
    }

    if ($action === 'update_status') {
        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        $status = $_POST['status'] ?? 'booked';
        $allowedStatuses = ['booked','completed','cancelled'];
        if ($scheduleId && in_array($status, $allowedStatuses, true)) {
            $updateStmt = $pdo->prepare('UPDATE schedule SET status = ? WHERE id = ?');
            $updateStmt->execute([$status, $scheduleId]);
            header('Location: schedule.php?success=Schedule+updated');
            exit;
        }
    }
}

if (isset($_GET['delete'])) {
    $scheduleId = (int)$_GET['delete'];
    $delStmt = $pdo->prepare('DELETE FROM schedule WHERE id = ?');
    $delStmt->execute([$scheduleId]);
    header('Location: schedule.php?success=Lesson+removed');
    exit;
}

$filterBranch = isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null;
$filterInstructor = isset($_GET['instructor_id']) && $_GET['instructor_id'] !== '' ? (int)$_GET['instructor_id'] : null;

$sql = "SELECT s.id, s.start_time, s.end_time, s.status,
               stu.name AS student_name,
               ins.name AS instructor_name,
               CONCAT(v.make, ' ', v.model, ' ', IFNULL(v.rego,'')) AS vehicle,
               b.name AS branch_name
        FROM schedule s
        JOIN students st ON s.student_id = st.id
        JOIN users stu ON st.user_id = stu.id
        JOIN instructors inst ON s.instructor_id = inst.id
        JOIN users ins ON inst.user_id = ins.id
        LEFT JOIN vehicles v ON s.vehicle_id = v.id
        LEFT JOIN branches b ON s.branch_id = b.id";

$conditions = [];
$params = [];
if ($filterBranch) {
    $conditions[] = 's.branch_id = ?';
    $params[] = $filterBranch;
}
if ($filterInstructor) {
    $conditions[] = 's.instructor_id = ?';
    $params[] = $filterInstructor;
}
if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY s.start_time DESC';

$listStmt = $pdo->prepare($sql);
$listStmt->execute($params);
$schedules = $listStmt->fetchAll();
?>

<?php if (isset($_GET['success'])): ?>
  <div class="admin-card" style="background:#e3f9e5; color:#1c6b3c;">✅ <?= sanitize($_GET['success']); ?></div>
<?php elseif (isset($_GET['error'])): ?>
  <div class="admin-card" style="background:#fde7e7; color:#a21d1d;">⚠️ <?= sanitize($_GET['error']); ?></div>
<?php endif; ?>

<section class="admin-card">
  <h2 class="admin-section-title">Schedule a lesson</h2>
  <form method="post" class="admin-form">
    <input type="hidden" name="action" value="create_schedule">
    <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
      <div class="input-wrapper">
        <label>Student *</label>
        <select name="student_id" required>
          <option value="">Select student</option>
          <?php foreach ($students as $student): ?>
            <option value="<?= (int)$student['id']; ?>"><?= sanitize($student['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Instructor *</label>
        <select name="instructor_id" required>
          <option value="">Select instructor</option>
          <?php foreach ($instructors as $instructor): ?>
            <option value="<?= (int)$instructor['id']; ?>"><?= sanitize($instructor['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Vehicle</label>
        <select name="vehicle_id">
          <option value="">TBA</option>
          <?php foreach ($vehicles as $vehicle): ?>
            <option value="<?= (int)$vehicle['id']; ?>"><?= sanitize($vehicle['label']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Branch</label>
        <select name="branch_id">
          <option value="">Unassigned</option>
          <?php foreach ($branches as $branch): ?>
            <option value="<?= (int)$branch['id']; ?>"><?= sanitize($branch['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Start time *</label>
        <input type="datetime-local" name="start_time" required>
      </div>
      <div class="input-wrapper">
        <label>End time *</label>
        <input type="datetime-local" name="end_time" required>
      </div>
    </div>
    <button class="btn" type="submit">Create booking</button>
  </form>
</section>

<section class="admin-card">
  <h2 class="admin-section-title">Lesson calendar</h2>
  <form method="get" class="admin-form" style="margin-bottom:16px;">
    <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
      <div class="input-wrapper">
        <label>Filter by branch</label>
        <select name="branch_id">
          <option value="">All branches</option>
          <?php foreach ($branches as $branch): ?>
            <option value="<?= (int)$branch['id']; ?>" <?= $filterBranch == $branch['id'] ? 'selected' : ''; ?>><?= sanitize($branch['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Filter by instructor</label>
        <select name="instructor_id">
          <option value="">All instructors</option>
          <?php foreach ($instructors as $instructor): ?>
            <option value="<?= (int)$instructor['id']; ?>" <?= $filterInstructor == $instructor['id'] ? 'selected' : ''; ?>><?= sanitize($instructor['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="admin-actions">
      <button class="btn" type="submit">Apply filters</button>
      <a class="btn" style="background:#6f7a90;" href="schedule.php">Reset</a>
    </div>
  </form>

  <div style="overflow-x:auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Start</th>
          <th>End</th>
          <th>Student</th>
          <th>Instructor</th>
          <th>Vehicle</th>
          <th>Branch</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($schedules as $item): ?>
          <tr>
            <td><?= date('M d, H:i', strtotime($item['start_time'])); ?></td>
            <td><?= date('M d, H:i', strtotime($item['end_time'])); ?></td>
            <td><?= sanitize($item['student_name']); ?></td>
            <td><?= sanitize($item['instructor_name']); ?></td>
            <td><?= sanitize($item['vehicle'] ?? 'TBA'); ?></td>
            <td><?= sanitize($item['branch_name'] ?? 'Unassigned'); ?></td>
            <td><?= ucfirst($item['status']); ?></td>
            <td>
              <form method="post" style="display:flex; gap:6px; align-items:center;">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="schedule_id" value="<?= (int)$item['id']; ?>">
                <select name="status">
                  <option value="booked" <?= $item['status'] === 'booked' ? 'selected' : ''; ?>>Booked</option>
                  <option value="completed" <?= $item['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                  <option value="cancelled" <?= $item['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                <button class="btn" type="submit">Update</button>
                <a class="btn" style="background:#a21d1d;" href="?delete=<?= (int)$item['id']; ?>" onclick="return confirm('Delete this booking?');">Delete</a>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>