<?php
$pageTitle = 'Fleet';
include __DIR__ . '/includes/header.php';

$branches = $pdo->query('SELECT id, name FROM branches ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_vehicle') {
        $make = trim($_POST['make'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $year = (int)($_POST['year'] ?? date('Y'));
        $transmission = $_POST['transmission'] ?? 'automatic';
        $rego = trim($_POST['rego'] ?? '');
        $branchId = isset($_POST['branch_id']) && $_POST['branch_id'] !== '' ? (int)$_POST['branch_id'] : null;

        if ($make && $model && $rego) {
            $stmt = $pdo->prepare('INSERT INTO vehicles (make, model, year, transmission, rego, branch_id) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$make, $model, $year, $transmission, $rego, $branchId]);
            header('Location: fleet.php?success=Vehicle+added');
            exit;
        }
        header('Location: fleet.php?error=Please+fill+make,+model+and+rego');
        exit;
    }

    if ($action === 'update_vehicle') {
        $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
        $make = trim($_POST['make'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $year = (int)($_POST['year'] ?? date('Y'));
        $transmission = $_POST['transmission'] ?? 'automatic';
        $rego = trim($_POST['rego'] ?? '');
        $branchId = isset($_POST['branch_id']) && $_POST['branch_id'] !== '' ? (int)$_POST['branch_id'] : null;

        if ($vehicleId && $make && $model && $rego) {
            $stmt = $pdo->prepare('UPDATE vehicles SET make = ?, model = ?, year = ?, transmission = ?, rego = ?, branch_id = ? WHERE id = ?');
            $stmt->execute([$make, $model, $year, $transmission, $rego, $branchId, $vehicleId]);
            header('Location: fleet.php?success=Vehicle+updated');
            exit;
        }
        header('Location: fleet.php?error=Unable+to+update+vehicle');
        exit;
    }
}

$vehicles = $pdo->query("SELECT v.*, b.name AS branch_name,
                                (SELECT COUNT(*) FROM schedule s WHERE s.vehicle_id = v.id AND s.start_time >= NOW()) AS upcoming
                         FROM vehicles v
                         LEFT JOIN branches b ON v.branch_id = b.id
                         ORDER BY v.created_at DESC")->fetchAll();
?>

<?php if (isset($_GET['success'])): ?>
  <div class="admin-card" style="background:#e3f9e5; color:#1c6b3c;">✅ <?= sanitize($_GET['success']); ?></div>
<?php elseif (isset($_GET['error'])): ?>
  <div class="admin-card" style="background:#fde7e7; color:#a21d1d;">⚠️ <?= sanitize($_GET['error']); ?></div>
<?php endif; ?>

<div class="admin-grid" style="grid-template-columns: 2fr 1fr; gap:24px;">
  <section class="admin-card">
    <h2 class="admin-section-title">Fleet inventory</h2>
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Vehicle</th>
            <th>Rego</th>
            <th>Transmission</th>
            <th>Branch</th>
            <th>Upcoming lessons</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($vehicles as $vehicle): ?>
            <tr>
              <td><?= sanitize($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?></td>
              <td><?= sanitize($vehicle['rego']); ?></td>
              <td><?= ucfirst($vehicle['transmission']); ?></td>
              <td><?= sanitize($vehicle['branch_name'] ?? 'Unassigned'); ?></td>
              <td><?= (int)$vehicle['upcoming']; ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$vehicles): ?>
            <tr><td colspan="5">No vehicles registered yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <aside class="admin-card">
    <h2 class="admin-section-title">Add vehicle</h2>
    <form method="post" class="admin-form">
      <input type="hidden" name="action" value="create_vehicle">
      <div class="input-wrapper">
        <label>Make *</label>
        <input type="text" name="make" required>
      </div>
      <div class="input-wrapper">
        <label>Model *</label>
        <input type="text" name="model" required>
      </div>
      <div class="input-wrapper">
        <label>Year *</label>
        <input type="number" name="year" min="2000" max="<?= date('Y') + 1; ?>" value="<?= date('Y'); ?>" required>
      </div>
      <div class="input-wrapper">
        <label>Transmission</label>
        <select name="transmission">
          <option value="automatic">Automatic</option>
          <option value="manual">Manual</option>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Rego *</label>
        <input type="text" name="rego" required>
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
      <button class="btn" type="submit">Save vehicle</button>
    </form>
  </aside>
</div>

<section class="admin-card">
  <h2 class="admin-section-title">Update vehicle</h2>
  <form method="post" class="admin-form">
    <input type="hidden" name="action" value="update_vehicle">
    <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
      <div class="input-wrapper">
        <label>Select vehicle</label>
        <select name="vehicle_id" required>
          <option value="">Choose vehicle</option>
          <?php foreach ($vehicles as $vehicle): ?>
            <option value="<?= (int)$vehicle['id']; ?>">#<?= (int)$vehicle['id']; ?> — <?= sanitize($vehicle['make'] . ' ' . $vehicle['model'] . ' ' . $vehicle['rego']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Make *</label>
        <input type="text" name="make" required>
      </div>
      <div class="input-wrapper">
        <label>Model *</label>
        <input type="text" name="model" required>
      </div>
      <div class="input-wrapper">
        <label>Year *</label>
        <input type="number" name="year" min="2000" max="<?= date('Y') + 1; ?>" required>
      </div>
      <div class="input-wrapper">
        <label>Transmission</label>
        <select name="transmission">
          <option value="automatic">Automatic</option>
          <option value="manual">Manual</option>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Rego *</label>
        <input type="text" name="rego" required>
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
    </div>
    <button class="btn" type="submit">Update vehicle</button>
  </form>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>