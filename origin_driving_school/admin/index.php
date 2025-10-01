<?php
$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';

$totalStudents = (int)$pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$totalInstructors = (int)$pdo->query('SELECT COUNT(*) FROM instructors')->fetchColumn();
$totalVehicles = (int)$pdo->query('SELECT COUNT(*) FROM vehicles')->fetchColumn();
$upcomingLessonsCount = (int)$pdo->query("SELECT COUNT(*) FROM schedule WHERE start_time >= NOW() AND status = 'booked'")->fetchColumn();

$monthlyRevenueStmt = $pdo->prepare('SELECT IFNULL(SUM(amount),0) FROM payments WHERE paid_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
$monthlyRevenueStmt->execute();
$monthlyRevenue = (float)$monthlyRevenueStmt->fetchColumn();

$outstandingStmt = $pdo->prepare("SELECT IFNULL(SUM(total - IFNULL((SELECT SUM(amount) FROM payments p WHERE p.invoice_id = invoices.id),0)),0)
                                   FROM invoices WHERE status IN ('sent','partial','overdue')");
$outstandingStmt->execute();
$outstandingBalance = (float)$outstandingStmt->fetchColumn();

$upcomingLessons = $pdo->query("SELECT s.start_time, s.end_time, u.name AS student_name, ins.specialty, b.name AS branch_name
                                 FROM schedule s
                                 JOIN students st ON s.student_id = st.id
                                 JOIN users u ON st.user_id = u.id
                                 JOIN instructors ins ON s.instructor_id = ins.id
                                 LEFT JOIN branches b ON s.branch_id = b.id
                                 WHERE s.start_time >= NOW()
                                 ORDER BY s.start_time ASC
                                 LIMIT 5")->fetchAll();

$overdueInvoices = $pdo->query("SELECT i.id, u.name AS student_name, i.due_date, i.total,
                                      IFNULL((SELECT SUM(amount) FROM payments WHERE invoice_id = i.id),0) AS paid
                               FROM invoices i
                               JOIN students st ON i.student_id = st.id
                               JOIN users u ON st.user_id = u.id
                               WHERE i.due_date < CURDATE() AND i.status != 'paid'
                               ORDER BY i.due_date ASC
                               LIMIT 5")->fetchAll();

$reminders = $pdo->query("SELECT r.id, r.reminder_at, r.message, r.reminder_type, u.name AS user_name
                           FROM reminders r
                           LEFT JOIN users u ON r.user_id = u.id
                           WHERE r.reminder_at >= NOW()
                           ORDER BY r.reminder_at ASC
                           LIMIT 5")->fetchAll();

$recentComms = $pdo->query("SELECT c.subject, c.audience_type, c.channel, c.created_at
                             FROM communications c
                             ORDER BY c.created_at DESC
                             LIMIT 5")->fetchAll();
?>

<div class="admin-grid admin-grid-3 admin-card admin-metrics">
  <div>
    <span><?= number_format($totalStudents); ?></span>
    Learners registered
  </div>
  <div>
    <span><?= number_format($totalInstructors); ?></span>
    Instructors on roster
  </div>
  <div>
    <span><?= number_format($totalVehicles); ?></span>
    Training vehicles
  </div>
  <div>
    <span><?= number_format($upcomingLessonsCount); ?></span>
    Upcoming lessons
  </div>
  <div>
    <span>$<?= number_format($monthlyRevenue, 2); ?></span>
    Payments (last 30 days)
  </div>
  <div>
    <span>$<?= number_format($outstandingBalance, 2); ?></span>
    Outstanding balance
  </div>
</div>

<div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
  <div class="admin-card">
    <h3 class="admin-section-title">Upcoming lessons</h3>
    <?php if (!$upcomingLessons): ?>
      <p>No lessons scheduled.</p>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>Time</th>
            <th>Student</th>
            <th>Instructor</th>
            <th>Branch</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($upcomingLessons as $lesson): ?>
            <tr>
              <td><?= date('M d, H:i', strtotime($lesson['start_time'])); ?></td>
              <td><?= sanitize($lesson['student_name']); ?></td>
              <td><?= sanitize($lesson['specialty']); ?></td>
              <td><?= sanitize($lesson['branch_name'] ?? 'N/A'); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="admin-card">
    <h3 class="admin-section-title">Overdue invoices</h3>
    <?php if (!$overdueInvoices): ?>
      <p>Great! There are no overdue invoices.</p>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>Invoice #</th>
            <th>Student</th>
            <th>Due date</th>
            <th>Balance</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($overdueInvoices as $invoice): ?>
            <?php $balance = max(0, $invoice['total'] - $invoice['paid']); ?>
            <tr>
              <td>#<?= (int)$invoice['id']; ?></td>
              <td><?= sanitize($invoice['student_name']); ?></td>
              <td><?= date('M d, Y', strtotime($invoice['due_date'])); ?></td>
              <td>$<?= number_format($balance, 2); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="admin-card">
    <h3 class="admin-section-title">Upcoming reminders</h3>
    <?php if (!$reminders): ?>
      <p>No reminders scheduled.</p>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>When</th>
            <th>Type</th>
            <th>Recipient</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reminders as $reminder): ?>
            <tr>
              <td><?= date('M d, H:i', strtotime($reminder['reminder_at'])); ?></td>
              <td><?= ucfirst($reminder['reminder_type']); ?></td>
              <td><?= sanitize($reminder['user_name'] ?? 'General'); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="admin-card">
    <h3 class="admin-section-title">Recent communications</h3>
    <?php if (!$recentComms): ?>
      <p>No communications sent recently.</p>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>Subject</th>
            <th>Audience</th>
            <th>Channel</th>
            <th>Sent</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentComms as $comm): ?>
            <tr>
              <td><?= sanitize($comm['subject'] ?? '(No subject)'); ?></td>
              <td><?= ucfirst($comm['audience_type']); ?></td>
              <td><?= strtoupper($comm['channel']); ?></td>
              <td><?= date('M d, H:i', strtotime($comm['created_at'])); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>