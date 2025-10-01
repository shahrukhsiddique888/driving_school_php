<?php
$pageTitle = 'Instructors';
include __DIR__ . '/includes/header.php';

$branches = $pdo->query('SELECT id, name FROM branches ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_instructor') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $branchId = isset($_POST['branch_id']) && $_POST['branch_id'] !== '' ? (int)$_POST['branch_id'] : null;
        $specialty = trim($_POST['specialty'] ?? 'General Driving');
        $availability = trim($_POST['availability'] ?? 'Mon-Fri 9am-5pm');
        $hourlyRate = $_POST['hourly_rate'] !== '' ? (float)$_POST['hourly_rate'] : 0;
        $bio = trim($_POST['bio'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$name || !$email || !$password) {
            header('Location: instructors.php?error=Please+complete+all+required+fields');
            exit;
        }

        if ($branchId) {
            $branchCheck = $pdo->prepare('SELECT id FROM branches WHERE id = ?');
            $branchCheck->execute([$branchId]);
            if (!$branchCheck->fetchColumn()) {
                $branchId = null;
            }
        }

        try {
            $pdo->beginTransaction();

            $userStmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "instructor")');
            $userStmt->execute([$name, $email, password_hash($password, PASSWORD_BCRYPT)]);
            $userId = (int)$pdo->lastInsertId();

            $instStmt = $pdo->prepare('INSERT INTO instructors (user_id, branch_id, specialty, phone, availability, hourly_rate, bio) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $instStmt->execute([$userId, $branchId, $specialty, $phone ?: null, $availability, $hourlyRate, $bio ?: null]);

            $pdo->commit();
            header('Location: instructors.php?success=Instructor+created');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            header('Location: instructors.php?error=Unable+to+create+instructor');
            exit;
        }
    }

    if ($action === 'update_instructor') {
        $instructorId = (int)($_POST['instructor_id'] ?? 0);
        $availability = trim($_POST['availability'] ?? '');
        $hourlyRate = $_POST['hourly_rate'] !== '' ? (float)$_POST['hourly_rate'] : 0;
        $branchId = isset($_POST['branch_id']) && $_POST['branch_id'] !== '' ? (int)$_POST['branch_id'] : null;
        $specialty = trim($_POST['specialty'] ?? 'General Driving');

        if ($branchId) {
            $branchCheck = $pdo->prepare('SELECT id FROM branches WHERE id = ?');
            $branchCheck->execute([$branchId]);
            if (!$branchCheck->fetchColumn()) {
                $branchId = null;
            }
        }

        if ($instructorId) {
            $updateStmt = $pdo->prepare('UPDATE instructors SET availability = ?, hourly_rate = ?, branch_id = ?, specialty = ? WHERE id = ?');
            $updateStmt->execute([$availability ?: null, $hourlyRate, $branchId, $specialty, $instructorId]);
            header('Location: instructors.php?instructor_id=' . $instructorId . '&success=Instructor+updated');
            exit;
        }
    }
}

$instructors = $pdo->query("SELECT ins.id, u.name, u.email, ins.specialty, ins.phone, ins.availability, ins.hourly_rate,
                                   b.name AS branch_name,
                                   (SELECT COUNT(*) FROM schedule s WHERE s.instructor_id = ins.id AND s.start_time >= NOW()) AS upcoming,
                                   (SELECT AVG(rating) FROM student_progress sp WHERE sp.instructor_id = ins.id AND sp.rating IS NOT NULL) AS avg_rating
                            FROM instructors ins
                            JOIN users u ON ins.user_id = u.id
                            LEFT JOIN branches b ON ins.branch_id = b.id
                            ORDER BY u.name")->fetchAll();

$selectedInstructorId = isset($_GET['instructor_id']) ? (int)$_GET['instructor_id'] : null;
$selectedInstructor = null;
$instructorLessons = [];
$feedback = [];

if ($selectedInstructorId) {
    $detailStmt = $pdo->prepare("SELECT ins.id, u.name, u.email, ins.phone, ins.specialty, ins.availability, ins.hourly_rate, ins.bio,
                                        ins.branch_id, b.name AS branch_name
                                 FROM instructors ins
                                 JOIN users u ON ins.user_id = u.id
                                 LEFT JOIN branches b ON ins.branch_id = b.id
                                 WHERE ins.id = ?");
    $detailStmt->execute([$selectedInstructorId]);
    $selectedInstructor = $detailStmt->fetch();

    if ($selectedInstructor) {
        $lessonsStmt = $pdo->prepare("SELECT s.start_time, s.end_time, stu.name AS student_name, b.name AS branch_name, s.status
                                      FROM schedule s
                                      JOIN students st ON s.student_id = st.id
                                      JOIN users stu ON st.user_id = stu.id
                                      LEFT JOIN branches b ON s.branch_id = b.id
                                      WHERE s.instructor_id = ? AND s.start_time >= NOW()
                                      ORDER BY s.start_time ASC");
        $lessonsStmt->execute([$selectedInstructorId]);
        $instructorLessons = $lessonsStmt->fetchAll();

        $feedbackStmt = $pdo->prepare("SELECT sp.lesson_date, sp.skill_area, sp.rating, sp.notes, u.name AS student_name
                                       FROM student_progress sp
                                       JOIN students st ON sp.student_id = st.id
                                       JOIN users u ON st.user_id = u.id
                                       WHERE sp.instructor_id = ?
                                       ORDER BY sp.lesson_date DESC");
        $feedbackStmt->execute([$selectedInstructorId]);
        $feedback = $feedbackStmt->fetchAll();
    }
}
?>

<?php if (isset($_GET['success'])): ?>
  <div class="admin-card" style="background:#e3f9e5; color:#1c6b3c;">✅ <?= sanitize($_GET['success']); ?></div>
<?php elseif (isset($_GET['error'])): ?>
  <div class="admin-card" style="background:#fde7e7; color:#a21d1d;">⚠️ <?= sanitize($_GET['error']); ?></div>
<?php endif; ?>

<div class="admin-grid" style="grid-template-columns: 2fr 1fr; gap:24px;">
  <section class="admin-card">
    <h2 class="admin-section-title">Instructor roster</h2>
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Branch</th>
            <th>Specialty</th>
            <th>Upcoming</th>
            <th>Rating</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($instructors as $instructor): ?>
            <tr>
              <td>
                <strong><?= sanitize($instructor['name']); ?></strong><br>
                <small><?= sanitize($instructor['email']); ?></small>
              </td>
              <td><?= sanitize($instructor['branch_name'] ?? 'Unassigned'); ?></td>
              <td><?= sanitize($instructor['specialty']); ?></td>
              <td><?= (int)$instructor['upcoming']; ?></td>
              <td><?= $instructor['avg_rating'] ? number_format($instructor['avg_rating'], 1) : '—'; ?></td>
              <td><a class="btn" href="instructors.php?instructor_id=<?= (int)$instructor['id']; ?>">View</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <aside class="admin-card">
    <h2 class="admin-section-title">Add instructor</h2>
    <form method="post" class="admin-form">
      <input type="hidden" name="action" value="create_instructor">
      <div class="input-wrapper">
        <label>Full name *</label>
        <input type="text" name="name" required>
      </div>
      <div class="input-wrapper">
        <label>Email *</label>
        <input type="email" name="email" required>
      </div>
      <div class="input-wrapper">
        <label>Password *</label>
        <input type="password" name="password" required minlength="6">
      </div>
      <div class="input-wrapper">
        <label>Phone</label>
        <input type="text" name="phone">
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
        <label>Specialty</label>
        <input type="text" name="specialty" value="General Driving">
      </div>
      <div class="input-wrapper">
        <label>Availability</label>
        <input type="text" name="availability" value="Mon-Fri 9am-5pm">
      </div>
      <div class="input-wrapper">
        <label>Hourly rate</label>
        <input type="number" name="hourly_rate" step="0.5" min="0" value="65">
      </div>
      <div class="input-wrapper">
        <label>Short bio</label>
        <textarea name="bio" rows="3" placeholder="Experience, languages, focus areas"></textarea>
      </div>
      <button class="btn" type="submit">Create instructor</button>
    </form>
  </aside>
</div>

<?php if ($selectedInstructor): ?>
  <section class="admin-card">
    <h2 class="admin-section-title">Instructor profile: <?= sanitize($selectedInstructor['name']); ?></h2>
    <p>Email: <?= sanitize($selectedInstructor['email']); ?> | Phone: <?= sanitize($selectedInstructor['phone'] ?? 'N/A'); ?> | Branch: <?= sanitize($selectedInstructor['branch_name'] ?? 'Unassigned'); ?></p>
    <p>Specialty: <?= sanitize($selectedInstructor['specialty']); ?> | Availability: <?= sanitize($selectedInstructor['availability'] ?? 'Not set'); ?> | Hourly rate: $<?= number_format($selectedInstructor['hourly_rate'] ?? 0, 2); ?></p>
    <?php if ($selectedInstructor['bio']): ?>
      <p><?= nl2br(sanitize($selectedInstructor['bio'])); ?></p>
    <?php endif; ?>

    <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
      <div>
        <h3 class="admin-section-title">Upcoming lessons</h3>
        <?php if (!$instructorLessons): ?>
          <p>No lessons scheduled.</p>
        <?php else: ?>
          <table class="admin-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Student</th>
                <th>Branch</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($instructorLessons as $lesson): ?>
                <tr>
                  <td><?= date('M d, H:i', strtotime($lesson['start_time'])); ?></td>
                  <td><?= sanitize($lesson['student_name']); ?></td>
                  <td><?= sanitize($lesson['branch_name'] ?? 'N/A'); ?></td>
                  <td><?= ucfirst($lesson['status']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <div>
        <h3 class="admin-section-title">Feedback & performance</h3>
        <?php if (!$feedback): ?>
          <p>No feedback captured yet.</p>
        <?php else: ?>
          <ul>
            <?php foreach ($feedback as $item): ?>
              <li style="margin-bottom:12px;">
                <strong><?= sanitize($item['student_name']); ?></strong> — <?= date('M d, Y', strtotime($item['lesson_date'])); ?>
                <div>Skill: <?= sanitize($item['skill_area'] ?? 'General'); ?> | Rating: <?= $item['rating'] ? number_format($item['rating'], 1) : '—'; ?></div>
                <?php if ($item['notes']): ?>
                  <p><?= nl2br(sanitize($item['notes'])); ?></p>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <div>
        <form method="post" class="admin-form admin-card">
          <h3 class="admin-section-title">Update availability</h3>
          <input type="hidden" name="action" value="update_instructor">
          <input type="hidden" name="instructor_id" value="<?= (int)$selectedInstructor['id']; ?>">
          <div class="input-wrapper">
            <label>Branch</label>
            <select name="branch_id">
              <option value="">Unassigned</option>
              <?php foreach ($branches as $branch): ?>
                <option value="<?= (int)$branch['id']; ?>" <?= ($selectedInstructor['branch_id'] ?? null) == $branch['id'] ? 'selected' : ''; ?>><?= sanitize($branch['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="input-wrapper">
            <label>Specialty</label>
            <input type="text" name="specialty" value="<?= sanitize($selectedInstructor['specialty']); ?>">
          </div>
          <div class="input-wrapper">
            <label>Availability</label>
            <textarea name="availability" rows="3"><?= sanitize($selectedInstructor['availability'] ?? ''); ?></textarea>
          </div>
          <div class="input-wrapper">
            <label>Hourly rate</label>
            <input type="number" name="hourly_rate" min="0" step="0.5" value="<?= number_format($selectedInstructor['hourly_rate'] ?? 0, 2, '.', ''); ?>">
          </div>
          <button class="btn" type="submit">Save changes</button>
        </form>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>