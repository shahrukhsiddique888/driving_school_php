<?php
$pageTitle = 'Students';
include __DIR__ . '/includes/header.php';

$branches = $pdo->query('SELECT id, name FROM branches ORDER BY name')->fetchAll();
$instructors = $pdo->query("SELECT instructors.id, users.name FROM instructors JOIN users ON instructors.user_id = users.id ORDER BY users.name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_student') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $branchId = isset($_POST['branch_id']) && $_POST['branch_id'] !== '' ? (int)$_POST['branch_id'] : null;
        $licenseStatus = $_POST['license_status'] ?? 'none';
        $password = $_POST['password'] ?? '';
        $initialNote = trim($_POST['note'] ?? '');

        if (!$name || !$email || !$password) {
            header('Location: students.php?error=Please+fill+all+required+fields');
            exit;
        }

        $allowedStatuses = ['none','learner','provisional','full'];
        if (!in_array($licenseStatus, $allowedStatuses, true)) {
            $licenseStatus = 'none';
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

            $userStmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "student")');
            $userStmt->execute([$name, $email, password_hash($password, PASSWORD_BCRYPT)]);
            $userId = (int)$pdo->lastInsertId();

            $studentStmt = $pdo->prepare('INSERT INTO students (user_id, branch_id, phone, license_status) VALUES (?, ?, ?, ?)');
            $studentStmt->execute([$userId, $branchId, $phone ?: null, $licenseStatus]);
            $studentId = (int)$pdo->lastInsertId();

            if ($initialNote) {
                $noteStmt = $pdo->prepare('INSERT INTO student_notes (student_id, created_by, note) VALUES (?, ?, ?)');
                $noteStmt->execute([$studentId, $authUser['id'], $initialNote]);
            }

            $pdo->commit();
            header('Location: students.php?success=Student+created+successfully');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            header('Location: students.php?error=Unable+to+create+student');
            exit;
        }
    }

    if ($action === 'add_note') {
        $studentId = (int)($_POST['student_id'] ?? 0);
        $note = trim($_POST['note'] ?? '');
        if ($studentId && $note) {
            $noteStmt = $pdo->prepare('INSERT INTO student_notes (student_id, created_by, note) VALUES (?, ?, ?)');
            $noteStmt->execute([$studentId, $authUser['id'], $note]);
            header('Location: students.php?student_id=' . $studentId . '&success=Note+added');
            exit;
        }
    }

    if ($action === 'add_progress') {
        $studentId = (int)($_POST['student_id'] ?? 0);
        $instructorId = isset($_POST['instructor_id']) && $_POST['instructor_id'] !== '' ? (int)$_POST['instructor_id'] : null;
        $lessonDate = $_POST['lesson_date'] ?? '';
        $skillArea = trim($_POST['skill_area'] ?? '');
        $rating = (int)($_POST['rating'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        if ($studentId && $lessonDate) {
            $progressStmt = $pdo->prepare('INSERT INTO student_progress (student_id, instructor_id, lesson_date, skill_area, rating, notes) VALUES (?, ?, ?, ?, ?, ?)');
            $progressStmt->execute([$studentId, $instructorId, $lessonDate, $skillArea ?: null, $rating ?: null, $notes ?: null]);
            header('Location: students.php?student_id=' . $studentId . '&success=Progress+updated');
            exit;
        }
    }
}

$students = $pdo->query("SELECT s.id, u.name, u.email, s.phone, s.license_status, s.created_at,
                                b.name AS branch_name,
                                (SELECT COUNT(*) FROM student_progress sp WHERE sp.student_id = s.id) AS lessons_completed,
                                (SELECT AVG(rating) FROM student_progress sp WHERE sp.student_id = s.id AND sp.rating IS NOT NULL) AS avg_rating,
                                (SELECT note FROM student_notes sn WHERE sn.student_id = s.id ORDER BY sn.created_at DESC LIMIT 1) AS latest_note,
                                (SELECT SUM(i.total - IFNULL((SELECT SUM(amount) FROM payments WHERE invoice_id = i.id),0))
                                 FROM invoices i WHERE i.student_id = s.id AND i.status IN ('sent','partial','overdue')) AS balance
                         FROM students s
                         JOIN users u ON s.user_id = u.id
                         LEFT JOIN branches b ON s.branch_id = b.id
                         ORDER BY u.name ASC")->fetchAll();

$selectedStudentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : null;
$selectedStudent = null;
$studentNotes = [];
$studentProgress = [];
$studentInvoices = [];

if ($selectedStudentId) {
    $detailStmt = $pdo->prepare("SELECT s.id, u.name, u.email, s.phone, s.license_status, b.name AS branch_name
                                  FROM students s
                                  JOIN users u ON s.user_id = u.id
                                  LEFT JOIN branches b ON s.branch_id = b.id
                                  WHERE s.id = ?");
    $detailStmt->execute([$selectedStudentId]);
    $selectedStudent = $detailStmt->fetch();

    if ($selectedStudent) {
        $notesStmt = $pdo->prepare("SELECT sn.note, sn.created_at, u.name AS author
                                     FROM student_notes sn
                                     LEFT JOIN users u ON sn.created_by = u.id
                                     WHERE sn.student_id = ?
                                     ORDER BY sn.created_at DESC");
        $notesStmt->execute([$selectedStudentId]);
        $studentNotes = $notesStmt->fetchAll();

        $progressStmt = $pdo->prepare("SELECT sp.lesson_date, sp.skill_area, sp.rating, sp.notes,
                                              iu.name AS instructor_name
                                       FROM student_progress sp
                                       LEFT JOIN instructors inst ON sp.instructor_id = inst.id
                                       LEFT JOIN users iu ON inst.user_id = iu.id
                                       WHERE sp.student_id = ?
                                       ORDER BY sp.lesson_date DESC");
        $progressStmt->execute([$selectedStudentId]);
        $studentProgress = $progressStmt->fetchAll();

        $invoiceStmt = $pdo->prepare("SELECT i.id, i.issue_date, i.due_date, i.total, i.status,
                                             IFNULL((SELECT SUM(amount) FROM payments WHERE invoice_id = i.id),0) AS paid
                                      FROM invoices i
                                      WHERE i.student_id = ?
                                      ORDER BY i.issue_date DESC");
        $invoiceStmt->execute([$selectedStudentId]);
        $studentInvoices = $invoiceStmt->fetchAll();
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
    <h2 class="admin-section-title">Learner directory</h2>
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Student</th>
            <th>Branch</th>
            <th>Licence</th>
            <th>Lessons</th>
            <th>Avg rating</th>
            <th>Balance</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $student): ?>
            <tr>
              <td>
                <strong><?= sanitize($student['name']); ?></strong><br>
                <small><?= sanitize($student['email']); ?></small>
              </td>
              <td><?= sanitize($student['branch_name'] ?? 'Unassigned'); ?></td>
              <td><?= ucfirst($student['license_status']); ?></td>
              <td><?= (int)$student['lessons_completed']; ?></td>
              <td><?= $student['avg_rating'] ? number_format($student['avg_rating'], 1) : '—'; ?></td>
              <td>$<?= number_format(max(0, $student['balance'] ?? 0), 2); ?></td>
              <td><a class="btn" href="students.php?student_id=<?= (int)$student['id']; ?>">View</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <aside class="admin-card">
    <h2 class="admin-section-title">Add new student</h2>
    <form method="post" class="admin-form">
      <input type="hidden" name="action" value="create_student">
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
        <label>Licence status</label>
        <select name="license_status">
          <option value="none">No licence</option>
          <option value="learner">Learner</option>
          <option value="provisional">Provisional</option>
          <option value="full">Full</option>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Initial note</label>
        <textarea name="note" rows="3" placeholder="Optional intake note"></textarea>
      </div>
      <button class="btn" type="submit">Create learner</button>
    </form>
  </aside>
</div>

<?php if ($selectedStudent): ?>
  <section class="admin-card">
    <h2 class="admin-section-title">Learner profile: <?= sanitize($selectedStudent['name']); ?></h2>
    <p>Email: <?= sanitize($selectedStudent['email']); ?> | Phone: <?= sanitize($selectedStudent['phone'] ?? 'N/A'); ?> | Branch: <?= sanitize($selectedStudent['branch_name'] ?? 'Unassigned'); ?> | Licence: <?= ucfirst($selectedStudent['license_status']); ?></p>

    <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
      <div>
        <h3 class="admin-section-title">Progress history</h3>
        <?php if (!$studentProgress): ?>
          <p>No lessons recorded yet.</p>
        <?php else: ?>
          <table class="admin-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Skill</th>
                <th>Rating</th>
                <th>Instructor</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($studentProgress as $progress): ?>
                <tr>
                  <td><?= date('M d, Y', strtotime($progress['lesson_date'])); ?></td>
                  <td><?= sanitize($progress['skill_area'] ?? '—'); ?></td>
                  <td><?= $progress['rating'] ? number_format($progress['rating'], 1) : '—'; ?></td>
                  <td><?= sanitize($progress['instructor_name'] ?? '—'); ?></td>
                </tr>
                <?php if ($progress['notes']): ?>
                  <tr>
                    <td colspan="4"><em><?= nl2br(sanitize($progress['notes'])); ?></em></td>
                  </tr>
                <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <div>
        <h3 class="admin-section-title">Recent notes</h3>
        <?php if (!$studentNotes): ?>
          <p>No notes logged yet.</p>
        <?php else: ?>
          <ul>
            <?php foreach ($studentNotes as $note): ?>
              <li style="margin-bottom:12px;">
                <strong><?= sanitize($note['author'] ?? 'System'); ?></strong>
                <span style="display:block; color:#6f7a90; font-size:0.9rem;"><?= date('M d, Y H:i', strtotime($note['created_at'])); ?></span>
                <p><?= nl2br(sanitize($note['note'])); ?></p>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <div>
        <h3 class="admin-section-title">Financials</h3>
        <?php if (!$studentInvoices): ?>
          <p>No invoices issued.</p>
        <?php else: ?>
          <table class="admin-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($studentInvoices as $invoice): ?>
                <?php $balance = max(0, $invoice['total'] - $invoice['paid']); ?>
                <tr>
                  <td>#<?= (int)$invoice['id']; ?></td>
                  <td>$<?= number_format($invoice['total'], 2); ?></td>
                  <td>$<?= number_format($invoice['paid'], 2); ?></td>
                  <td><?= ucfirst($invoice['status']); ?><?= $balance ? ' ($' . number_format($balance, 2) . ')' : ''; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); margin-top:24px;">
      <form method="post" class="admin-form admin-card">
        <h3 class="admin-section-title">Add lesson progress</h3>
        <input type="hidden" name="action" value="add_progress">
        <input type="hidden" name="student_id" value="<?= (int)$selectedStudent['id']; ?>">
        <div class="input-wrapper">
          <label>Instructor</label>
          <select name="instructor_id">
            <option value="">Select instructor</option>
            <?php foreach ($instructors as $instructor): ?>
              <option value="<?= (int)$instructor['id']; ?>"><?= sanitize($instructor['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="input-wrapper">
          <label>Lesson date *</label>
          <input type="date" name="lesson_date" required>
        </div>
        <div class="input-wrapper">
          <label>Skill focus</label>
          <input type="text" name="skill_area" placeholder="E.g. Parking, Highway driving">
        </div>
        <div class="input-wrapper">
          <label>Rating (1-5)</label>
          <input type="number" name="rating" min="1" max="5" step="1">
        </div>
        <div class="input-wrapper">
          <label>Notes</label>
          <textarea name="notes" rows="3"></textarea>
        </div>
        <button class="btn" type="submit">Save progress</button>
      </form>

      <form method="post" class="admin-form admin-card">
        <h3 class="admin-section-title">Add learner note</h3>
        <input type="hidden" name="action" value="add_note">
        <input type="hidden" name="student_id" value="<?= (int)$selectedStudent['id']; ?>">
        <div class="input-wrapper">
          <label>Observation *</label>
          <textarea name="note" rows="4" required></textarea>
        </div>
        <button class="btn" type="submit">Save note</button>
      </form>
    </div>
  </section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>