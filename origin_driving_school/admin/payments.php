<?php
$pageTitle = 'Payments';
include __DIR__ . '/includes/header.php';

function syncInvoiceStatus(PDO $pdo, int $invoiceId): void {
    $invoiceStmt = $pdo->prepare('SELECT total FROM invoices WHERE id = ?');
    $invoiceStmt->execute([$invoiceId]);
    $total = (float)$invoiceStmt->fetchColumn();

    $paidStmt = $pdo->prepare('SELECT IFNULL(SUM(amount),0) FROM payments WHERE invoice_id = ?');
    $paidStmt->execute([$invoiceId]);
    $paid = (float)$paidStmt->fetchColumn();

    $status = 'sent';
    if ($paid >= $total && $total > 0) {
        $status = 'paid';
    } elseif ($paid > 0 && $paid < $total) {
        $status = 'partial';
    } elseif ($total > 0 && $paid === 0) {
        $status = 'sent';
    }

    $update = $pdo->prepare('UPDATE invoices SET status = ? WHERE id = ?');
    $update->execute([$status, $invoiceId]);
}

$invoiceOptions = $pdo->query("SELECT i.id, u.name AS student_name, i.total, i.status,
                                      IFNULL((SELECT SUM(amount) FROM payments WHERE invoice_id = i.id),0) AS paid
                               FROM invoices i
                               JOIN students st ON i.student_id = st.id
                               JOIN users u ON st.user_id = u.id
                               ORDER BY i.due_date ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoiceId = (int)($_POST['invoice_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $method = $_POST['method'] ?? 'card';
    $paidAt = $_POST['paid_at'] ?? date('Y-m-d\TH:i');
    $reference = trim($_POST['reference'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($invoiceId && $amount > 0) {
        $stmt = $pdo->prepare('INSERT INTO payments (invoice_id, amount, method, reference, paid_at, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$invoiceId, $amount, $method, $reference ?: null, date('Y-m-d H:i:s', strtotime($paidAt)), $notes ?: null, $authUser['id']]);
        syncInvoiceStatus($pdo, $invoiceId);
        header('Location: payments.php?success=Payment+recorded');
        exit;
    }

    header('Location: payments.php?error=Invalid+payment+details');
    exit;
}

$paymentsStmt = $pdo->query("SELECT p.id, p.invoice_id, p.amount, p.method, p.reference, p.paid_at, p.created_at,
                                    i.total, i.status, u.name AS student_name
                             FROM payments p
                             JOIN invoices i ON p.invoice_id = i.id
                             JOIN students st ON i.student_id = st.id
                             JOIN users u ON st.user_id = u.id
                             ORDER BY p.paid_at DESC");
$payments = $paymentsStmt->fetchAll();

$totalCollected = 0;
foreach ($payments as $payment) {
    $totalCollected += $payment['amount'];
}
?>

<?php if (isset($_GET['success'])): ?>
  <div class="admin-card" style="background:#e3f9e5; color:#1c6b3c;">✅ <?= sanitize($_GET['success']); ?></div>
<?php elseif (isset($_GET['error'])): ?>
  <div class="admin-card" style="background:#fde7e7; color:#a21d1d;">⚠️ <?= sanitize($_GET['error']); ?></div>
<?php endif; ?>

<div class="admin-grid" style="grid-template-columns: 2fr 1fr; gap:24px;">
  <section class="admin-card">
    <h2 class="admin-section-title">Payment ledger</h2>
    <p>Total collected: <strong>$<?= number_format($totalCollected, 2); ?></strong></p>
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Student</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Reference</th>
            <th>Invoice #</th>
            <th>Status after payment</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $payment): ?>
            <tr>
              <td><?= date('M d, Y H:i', strtotime($payment['paid_at'])); ?></td>
              <td><?= sanitize($payment['student_name']); ?></td>
              <td>$<?= number_format($payment['amount'], 2); ?></td>
              <td><?= strtoupper($payment['method']); ?></td>
              <td><?= sanitize($payment['reference'] ?? '—'); ?></td>
              <td>#<?= (int)$payment['invoice_id']; ?></td>
              <td><?= ucfirst($payment['status']); ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$payments): ?>
            <tr><td colspan="7">No payments recorded yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <aside class="admin-card">
    <h2 class="admin-section-title">Record payment</h2>
    <form method="post" class="admin-form">
      <div class="input-wrapper">
        <label>Invoice *</label>
        <select name="invoice_id" required>
          <option value="">Select invoice</option>
          <?php foreach ($invoiceOptions as $invoice): ?>
            <?php $balance = max(0, $invoice['total'] - $invoice['paid']); ?>
            <option value="<?= (int)$invoice['id']; ?>">#<?= (int)$invoice['id']; ?> — <?= sanitize($invoice['student_name']); ?> (Balance $<?= number_format($balance, 2); ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Amount *</label>
        <input type="number" name="amount" min="0" step="0.01" required>
      </div>
      <div class="input-wrapper">
        <label>Method</label>
        <select name="method">
          <option value="card">Card</option>
          <option value="cash">Cash</option>
          <option value="bank_transfer">Bank transfer</option>
          <option value="online">Online</option>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Paid at</label>
        <input type="datetime-local" name="paid_at" value="<?= date('Y-m-d\TH:i'); ?>">
      </div>
      <div class="input-wrapper">
        <label>Reference</label>
        <input type="text" name="reference" placeholder="Receipt number or transaction ID">
      </div>
      <div class="input-wrapper">
        <label>Notes</label>
        <textarea name="notes" rows="3" placeholder="Optional details"></textarea>
      </div>
      <button class="btn" type="submit">Save payment</button>
    </form>
  </aside>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>