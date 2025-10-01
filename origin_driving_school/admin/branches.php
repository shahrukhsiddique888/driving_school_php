<?php
$pageTitle = 'Branches';
include __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_branch') {
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $manager = trim($_POST['manager'] ?? '');

        if ($name) {
            $stmt = $pdo->prepare('INSERT INTO branches (name, address, phone, email, manager) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $address ?: null, $phone ?: null, $email ?: null, $manager ?: null]);
            header('Location: branches.php?success=Branch+created');
            exit;
        }
        header('Location: branches.php?error=Branch+name+is+required');
        exit;
    }

    if ($action === 'update_branch') {
        $branchId = (int)($_POST['branch_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $manager = trim($_POST['manager'] ?? '');

        if ($branchId && $name) {
            $stmt = $pdo->prepare('UPDATE branches SET name = ?, address = ?, phone = ?, email = ?, manager = ? WHERE id = ?');
            $stmt->execute([$name, $address ?: null, $phone ?: null, $email ?: null, $manager ?: null, $branchId]);
            header('Location: branches.php?success=Branch+updated');
            exit;
        }
        header('Location: branches.php?error=Unable+to+update+branch');
        exit;
    }
}

$branches = $pdo->query("SELECT b.*, 
                                (SELECT COUNT(*) FROM students WHERE branch_id = b.id) AS student_count,
                                (SELECT COUNT(*) FROM instructors WHERE branch_id = b.id) AS instructor_count,
                                (SELECT COUNT(*) FROM vehicles WHERE branch_id = b.id) AS vehicle_count
                         FROM branches b
                         ORDER BY b.name")->fetchAll();

$selectedBranchId = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : null;
$selectedBranch = null;
$branchSchedules = [];

if ($selectedBranchId) {
    $stmt = $pdo->prepare('SELECT * FROM branches WHERE id = ?');
    $stmt->execute([$selectedBranchId]);
    $selectedBranch = $stmt->fetch();

    if ($selectedBranch) {
        $scheduleStmt = $pdo->prepare("SELECT s.start_time, s.end_time, u.name AS student_name, ui.name AS instructor_name, s.status
                                        FROM schedule s
                                        JOIN students st ON s.student_id = st.id
                                        JOIN users u ON st.user_id = u.id
                                        JOIN instructors ins ON s.instructor_id = ins.id
                                        JOIN users ui ON ins.user_id = ui.id
                                        WHERE s.branch_id = ?
                                        ORDER BY s.start_time DESC
                                        LIMIT 10");
        $scheduleStmt->execute([$selectedBranchId]);
        $branchSchedules = $scheduleStmt->fetchAll();
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
    <h2 class="admin-section-title">Branch network</h2>
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Branch</th>
            <th>Manager</th>
            <th>Learners</th>
            <th>Instructors</th>
            <th>Vehicles</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($branches as $branch): ?>
            <tr>
              <td>
                <strong><?= sanitize($branch['name']); ?></strong><br>
                <small><?= sanitize($branch['address'] ?? ''); ?></small>
              </td>
              <td><?= sanitize($branch['manager'] ?? 'Not assigned'); ?></td>
              <td><?= (int)$branch['student_count']; ?></td>
              <td><?= (int)$branch['instructor_count']; ?></td>
              <td><?= (int)$branch['vehicle_count']; ?></td>
              <td><a class="btn" href="branches.php?branch_id=<?= (int)$branch['id']; ?>">View</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <aside class="admin-card">
    <h2 class="admin-section-title">Create branch</h2>
    <form method="post" class="admin-form">
      <input type="hidden" name="action" value="create_branch">
      <div class="input-wrapper">
        <label>Name *</label>
        <input type="text" name="name" required>
      </div>
      <div class="input-wrapper">
        <label>Address</label>
        <textarea name="address" rows="2"></textarea>
      </div>
      <div class="input-wrapper">
        <label>Phone</label>
        <input type="text" name="phone">
      </div>
      <div class="input-wrapper">
        <label>Email</label>
        <input type="email" name="email">
      </div>
      <div class="input-wrapper">
        <label>Manager</label>
        <input type="text" name="manager">
      </div>
      <button class="btn" type="submit">Add branch</button>
    </form>
  </aside>
</div>

<?php if ($selectedBranch): ?>
  <section class="admin-card">
    <h2 class="admin-section-title">Branch overview: <?= sanitize($selectedBranch['name']); ?></h2>
    <p>Address: <?= sanitize($selectedBranch['address'] ?? 'Not provided'); ?> | Phone: <?= sanitize($selectedBranch['phone'] ?? '—'); ?> | Email: <?= sanitize($selectedBranch['email'] ?? '—'); ?> | Manager: <?= sanitize($selectedBranch['manager'] ?? 'Not assigned'); ?></p>

    <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
      <form method="post" class="admin-form admin-card">
        <h3 class="admin-section-title">Update branch details</h3>
        <input type="hidden" name="action" value="update_branch">
        <input type="hidden" name="branch_id" value="<?= (int)$selectedBranch['id']; ?>">
        <div class="input-wrapper">
          <label>Name *</label>
          <input type="text" name="name" value="<?= sanitize($selectedBranch['name']); ?>" required>
        </div>
        <div class="input-wrapper">
          <label>Address</label>
          <textarea name="address" rows="2"><?= sanitize($selectedBranch['address'] ?? ''); ?></textarea>
        </div>
        <div class="input-wrapper">
          <label>Phone</label>
          <input type="text" name="phone" value="<?= sanitize($selectedBranch['phone'] ?? ''); ?>">
        </div>
        <div class="input-wrapper">
          <label>Email</label>
          <input type="email" name="email" value="<?= sanitize($selectedBranch['email'] ?? ''); ?>">
        </div>
        <div class="input-wrapper">
          <label>Manager</label>
          <input type="text" name="manager" value="<?= sanitize($selectedBranch['manager'] ?? ''); ?>">
        </div>
        <button class="btn" type="submit">Save changes</button>
      </form>

      <div class="admin-card">
        <h3 class="admin-section-title">Recent activity</h3>
        <?php if (!$branchSchedules): ?>
          <p>No lessons recorded yet.</p>
        <?php else: ?>
          <ul>
            <?php foreach ($branchSchedules as $lesson): ?>
              <li style="margin-bottom:12px;">
                <?= date('M d, H:i', strtotime($lesson['start_time'])); ?> — <?= sanitize($lesson['student_name']); ?> with <?= sanitize($lesson['instructor_name']); ?> (<?= ucfirst($lesson['status']); ?>)
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>