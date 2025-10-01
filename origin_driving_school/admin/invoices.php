<?php
$pageTitle = 'Invoices';
include __DIR__ . '/includes/header.php';

function recalcInvoiceTotal(PDO $pdo, int $invoiceId): void {
    $sumStmt = $pdo->prepare('SELECT IFNULL(SUM(qty * unit_price),0) FROM invoice_items WHERE invoice_id = ?');
    $sumStmt->execute([$invoiceId]);
    $total = (float)$sumStmt->fetchColumn();
    $updateStmt = $pdo->prepare('UPDATE invoices SET total = ? WHERE id = ?');
    $updateStmt->execute([$total, $invoiceId]);

    $paidStmt = $pdo->prepare('SELECT IFNULL(SUM(amount),0) FROM payments WHERE invoice_id = ?');
    $paidStmt->execute([$invoiceId]);
    $paid = (float)$paidStmt->fetchColumn();

    $status = 'sent';
    if ($paid >= $total && $total > 0) {
        $status = 'paid';
    } elseif ($paid > 0 && $paid < $total) {
        $status = 'partial';
    }
    $statusStmt = $pdo->prepare('UPDATE invoices SET status = ? WHERE id = ?');
    $statusStmt->execute([$status, $invoiceId]);
}

$students = $pdo->query("SELECT students.id, users.name FROM students JOIN users ON students.user_id = users.id ORDER BY users.name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_invoice') {
        $studentId = (int)($_POST['student_id'] ?? 0);
        $issueDate = $_POST['issue_date'] ?? '';
        $dueDate = $_POST['due_date'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $qty = (int)($_POST['qty'] ?? 1);
        $unitPrice = (float)($_POST['unit_price'] ?? 0);

        if (!$studentId || !$issueDate || !$dueDate || !$description || $qty <= 0 || $unitPrice <= 0) {
            header('Location: invoices.php?error=Invalid+invoice+details');
            exit;
        }

        $total = $qty * $unitPrice;
        $invoiceStmt = $pdo->prepare('INSERT INTO invoices (student_id, issue_date, due_date, total, status) VALUES (?, ?, ?, ?, "sent")');
        $invoiceStmt->execute([$studentId, $issueDate, $dueDate, $total]);
        $invoiceId = (int)$pdo->lastInsertId();

        $itemStmt = $pdo->prepare('INSERT INTO invoice_items (invoice_id, description, qty, unit_price) VALUES (?, ?, ?, ?)');
        $itemStmt->execute([$invoiceId, $description, $qty, $unitPrice]);

        recalcInvoiceTotal($pdo, $invoiceId);

        header('Location: invoices.php?success=Invoice+created');
        exit;
    }

    if ($action === 'add_item') {
        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $qty = (int)($_POST['qty'] ?? 1);
        $unitPrice = (float)($_POST['unit_price'] ?? 0);

        if ($invoiceId && $description && $qty > 0 && $unitPrice > 0) {
            $itemStmt = $pdo->prepare('INSERT INTO invoice_items (invoice_id, description, qty, unit_price) VALUES (?, ?, ?, ?)');
            $itemStmt->execute([$invoiceId, $description, $qty, $unitPrice]);
            recalcInvoiceTotal($pdo, $invoiceId);
            header('Location: invoices.php?success=Line+item+added');
            exit;
        }
    }

    if ($action === 'update_status') {
        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $status = $_POST['status'] ?? 'sent';
        $allowed = ['draft','sent','partial','paid','overdue'];
        if ($invoiceId && in_array($status, $allowed, true)) {
            $stmt = $pdo->prepare('UPDATE invoices SET status = ? WHERE id = ?');
            $stmt->execute([$status, $invoiceId]);
            header('Location: invoices.php?success=Invoice+updated');
            exit;
        }
    }
}

$invoiceStmt = $pdo->query("SELECT i.id, i.student_id, u.name AS student_name, i.issue_date, i.due_date, i.total, i.status,
                                   IFNULL((SELECT SUM(amount) FROM payments WHERE invoice_id = i.id),0) AS paid
                            FROM invoices i
                            JOIN students st ON i.student_id = st.id
                            JOIN users u ON st.user_id = u.id
                            ORDER BY i.issue_date DESC");
$invoices = $invoiceStmt->fetchAll();

$itemsStmt = $pdo->query('SELECT invoice_id, description, qty, unit_price FROM invoice_items');
$items = [];
foreach ($itemsStmt->fetchAll() as $item) {
    $items[$item['invoice_id']][] = $item;
}

$paymentsStmt = $pdo->query('SELECT invoice_id, amount, method, paid_at FROM payments ORDER BY paid_at DESC');
$paymentsByInvoice = [];
foreach ($paymentsStmt->fetchAll() as $payment) {
    $paymentsByInvoice[$payment['invoice_id']][] = $payment;
}
?>

<?php if (isset($_GET['success'])): ?>
  <div class="admin-card" style="background:#e3f9e5; color:#1c6b3c;">✅ <?= sanitize($_GET['success']); ?></div>
<?php elseif (isset($_GET['error'])): ?>
  <div class="admin-card" style="background:#fde7e7; color:#a21d1d;">⚠️ <?= sanitize($_GET['error']); ?></div>
<?php endif; ?>

<div class="admin-grid" style="grid-template-columns: 2fr 1fr; gap:24px;">
  <section class="admin-card">
    <h2 class="admin-section-title">Outstanding invoices</h2>
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Student</th>
            <th>Issued</th>
            <th>Due</th>
            <th>Total</th>
            <th>Paid</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($invoices as $invoice): ?>
            <?php $balance = max(0, $invoice['total'] - $invoice['paid']); ?>
            <tr>
              <td>#<?= (int)$invoice['id']; ?></td>
              <td><?= sanitize($invoice['student_name']); ?></td>
              <td><?= date('M d, Y', strtotime($invoice['issue_date'])); ?></td>
              <td><?= date('M d, Y', strtotime($invoice['due_date'])); ?></td>
              <td>$<?= number_format($invoice['total'], 2); ?></td>
              <td>$<?= number_format($invoice['paid'], 2); ?></td>
              <td><?= ucfirst($invoice['status']); ?><?= $balance ? ' ($' . number_format($balance, 2) . ')' : ''; ?></td>
              <td>
                <form method="post" class="admin-actions">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="invoice_id" value="<?= (int)$invoice['id']; ?>">
                  <select name="status">
                    <option value="draft" <?= $invoice['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="sent" <?= $invoice['status'] === 'sent' ? 'selected' : ''; ?>>Sent</option>
                    <option value="partial" <?= $invoice['status'] === 'partial' ? 'selected' : ''; ?>>Partial</option>
                    <option value="paid" <?= $invoice['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                    <option value="overdue" <?= $invoice['status'] === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                  </select>
                  <button class="btn" type="submit">Update</button>
                </form>
              </td>
            </tr>
            <tr>
              <td colspan="8">
                <strong>Line items</strong>
                <ul>
                  <?php foreach ($items[$invoice['id']] ?? [] as $line): ?>
                    <li><?= sanitize($line['description']); ?> — <?= (int)$line['qty']; ?> × $<?= number_format($line['unit_price'], 2); ?></li>
                  <?php endforeach; ?>
                  <?php if (empty($items[$invoice['id']] ?? [])): ?>
                    <li>No items yet.</li>
                  <?php endif; ?>
                </ul>

                <strong>Payments</strong>
                <ul>
                  <?php foreach ($paymentsByInvoice[$invoice['id']] ?? [] as $payment): ?>
                    <li>$<?= number_format($payment['amount'], 2); ?> via <?= strtoupper($payment['method']); ?> on <?= date('M d, Y', strtotime($payment['paid_at'])); ?></li>
                  <?php endforeach; ?>
                  <?php if (empty($paymentsByInvoice[$invoice['id']] ?? [])): ?>
                    <li>No payments yet.</li>
                  <?php endif; ?>
                </ul>

                <form method="post" class="admin-form" style="margin-top:12px;">
                  <input type="hidden" name="action" value="add_item">
                  <input type="hidden" name="invoice_id" value="<?= (int)$invoice['id']; ?>">
                  <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                    <div class="input-wrapper">
                      <label>Description</label>
                      <input type="text" name="description" required>
                    </div>
                    <div class="input-wrapper">
                      <label>Quantity</label>
                      <input type="number" name="qty" min="1" value="1" required>
                    </div>
                    <div class="input-wrapper">
                      <label>Unit price</label>
                      <input type="number" step="0.01" min="0" name="unit_price" required>
                    </div>
                  </div>
                  <button class="btn" type="submit">Add line item</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <aside class="admin-card">
    <h2 class="admin-section-title">Create invoice</h2>
    <form method="post" class="admin-form">
      <input type="hidden" name="action" value="create_invoice">
      <div class="input-wrapper">
        <label>Student *</label>
        <select name="student_id" required>
          <option value="">Select learner</option>
          <?php foreach ($students as $student): ?>
            <option value="<?= (int)$student['id']; ?>"><?= sanitize($student['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Issue date *</label>
        <input type="date" name="issue_date" required>
      </div>
      <div class="input-wrapper">
        <label>Due date *</label>
        <input type="date" name="due_date" required>
      </div>
      <div class="input-wrapper">
        <label>Line description *</label>
        <input type="text" name="description" required>
      </div>
      <div class="input-wrapper">
        <label>Quantity *</label>
        <input type="number" name="qty" min="1" value="1" required>
      </div>
      <div class="input-wrapper">
        <label>Unit price *</label>
        <input type="number" name="unit_price" min="0" step="0.01" required>
      </div>
      <button class="btn" type="submit">Create invoice</button>
    </form>
  </aside>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>