<?php
$pageTitle = 'Communications';
include __DIR__ . '/includes/header.php';

$students = $pdo->query("SELECT users.id, users.name FROM users WHERE role = 'student' ORDER BY users.name")->fetchAll();
$instructors = $pdo->query("SELECT users.id, users.name FROM users WHERE role = 'instructor' ORDER BY users.name")->fetchAll();
$staff = $pdo->query("SELECT users.id, users.name FROM users WHERE role = 'admin' ORDER BY users.name")->fetchAll();
$invoices = $pdo->query('SELECT invoices.id, users.name AS student_name FROM invoices JOIN students ON invoices.student_id = students.id JOIN users ON students.user_id = users.id ORDER BY invoices.due_date DESC')->fetchAll();
$schedules = $pdo->query('SELECT schedule.id, users.name AS student_name, schedule.start_time FROM schedule JOIN students ON schedule.student_id = students.id JOIN users ON students.user_id = users.id ORDER BY schedule.start_time DESC LIMIT 50')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'send_message') {
        $audience = $_POST['audience'] ?? 'all';
        $channel = $_POST['channel'] ?? 'in_app';
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $targetUserId = isset($_POST['user_id']) && $_POST['user_id'] !== '' ? (int)$_POST['user_id'] : null;

        if ($body === '') {
            header('Location: communications.php?error=Message+body+is+required');
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO communications (sender_id, audience_type, target_user_id, channel, subject, body) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$authUser['id'], $audience, $targetUserId, $channel, $subject ?: null, $body]);

        // Determine recipients
        $recipientIds = [];
        if ($targetUserId) {
            $recipientIds[] = $targetUserId;
        } else {
            if ($audience === 'student') {
                $recipientIds = array_column($students, 'id');
            } elseif ($audience === 'instructor') {
                $recipientIds = array_column($instructors, 'id');
            } elseif ($audience === 'staff') {
                $recipientIds = array_column($staff, 'id');
            } else {
                $allUsers = $pdo->query('SELECT id FROM users')->fetchAll(PDO::FETCH_COLUMN);
                $recipientIds = $allUsers;
            }
        }

        if ($recipientIds) {
            $notificationStmt = $pdo->prepare('INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)');
            foreach ($recipientIds as $recipientId) {
                $notificationStmt->execute([$recipientId, $subject ?: 'Driving school update', $body, '/origin_driving_school/notifications.php']);
            }
        }

        header('Location: communications.php?success=Message+queued');
        exit;
    }

    if ($action === 'create_reminder') {
        $reminderType = $_POST['reminder_type'] ?? 'custom';
        $userId = isset($_POST['user_id']) && $_POST['user_id'] !== '' ? (int)$_POST['user_id'] : null;
        $invoiceId = isset($_POST['invoice_id']) && $_POST['invoice_id'] !== '' ? (int)$_POST['invoice_id'] : null;
        $scheduleId = isset($_POST['schedule_id']) && $_POST['schedule_id'] !== '' ? (int)$_POST['schedule_id'] : null;
        $reminderAt = $_POST['reminder_at'] ?? '';
        $message = trim($_POST['message'] ?? '');

        if ($reminderAt && $message) {
            $stmt = $pdo->prepare('INSERT INTO reminders (user_id, invoice_id, schedule_id, reminder_type, reminder_at, message, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$userId, $invoiceId, $scheduleId, $reminderType, date('Y-m-d H:i:s', strtotime($reminderAt)), $message, $authUser['id']]);
            header('Location: communications.php?success=Reminder+scheduled');
            exit;
        }
        header('Location: communications.php?error=Reminder+details+missing');
        exit;
    }
}

$communications = $pdo->query('SELECT c.subject, c.body, c.audience_type, c.channel, c.created_at, u.name AS sender
                                FROM communications c
                                LEFT JOIN users u ON c.sender_id = u.id
                                ORDER BY c.created_at DESC
                                LIMIT 20')->fetchAll();

$upcomingReminders = $pdo->query('SELECT r.id, r.reminder_type, r.reminder_at, r.message, u.name AS recipient
                                  FROM reminders r
                                  LEFT JOIN users u ON r.user_id = u.id
                                  WHERE r.reminder_at >= NOW()
                                  ORDER BY r.reminder_at ASC')->fetchAll();
?>

<?php if (isset($_GET['success'])): ?>
  <div class="admin-card" style="background:#e3f9e5; color:#1c6b3c;">✅ <?= sanitize($_GET['success']); ?></div>
<?php elseif (isset($_GET['error'])): ?>
  <div class="admin-card" style="background:#fde7e7; color:#a21d1d;">⚠️ <?= sanitize($_GET['error']); ?></div>
<?php endif; ?>

<div class="admin-grid" style="grid-template-columns: 2fr 1fr; gap:24px;">
  <section class="admin-card">
    <h2 class="admin-section-title">Send communication</h2>
    <form method="post" class="admin-form">
      <input type="hidden" name="action" value="send_message">
      <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="input-wrapper">
          <label>Audience</label>
          <select name="audience" id="audience-select">
            <option value="all">All users</option>
            <option value="student">Students</option>
            <option value="instructor">Instructors</option>
            <option value="staff">Admins</option>
          </select>
        </div>
        <div class="input-wrapper">
          <label>Channel</label>
          <select name="channel">
            <option value="in_app">In-app</option>
            <option value="email">Email</option>
            <option value="sms">SMS</option>
          </select>
        </div>
        <div class="input-wrapper">
          <label>Specific recipient (optional)</label>
          <select name="user_id">
            <option value="">Broadcast</option>
            <?php foreach ([$students, $instructors, $staff] as $group): ?>
              <?php foreach ($group as $user): ?>
                <option value="<?= (int)$user['id']; ?>"><?= sanitize($user['name']); ?></option>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="input-wrapper">
        <label>Subject</label>
        <input type="text" name="subject" placeholder="Optional subject line">
      </div>
      <div class="input-wrapper">
        <label>Message *</label>
        <textarea name="body" rows="5" required></textarea>
      </div>
      <button class="btn" type="submit">Send message</button>
    </form>

    <h3 class="admin-section-title" style="margin-top:32px;">Recent messages</h3>
    <?php if (!$communications): ?>
      <p>No messages sent yet.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($communications as $comm): ?>
          <li style="margin-bottom:16px;">
            <strong><?= sanitize($comm['subject'] ?? '(No subject)'); ?></strong>
            <span style="display:block; color:#6f7a90;">To <?= ucfirst($comm['audience_type']); ?> via <?= strtoupper($comm['channel']); ?> on <?= date('M d, Y H:i', strtotime($comm['created_at'])); ?> — by <?= sanitize($comm['sender'] ?? 'System'); ?></span>
            <p><?= nl2br(sanitize($comm['body'])); ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

  <aside class="admin-card">
    <h2 class="admin-section-title">Schedule reminder</h2>
    <form method="post" class="admin-form">
      <input type="hidden" name="action" value="create_reminder">
      <div class="input-wrapper">
        <label>Reminder type</label>
        <select name="reminder_type">
          <option value="custom">Custom</option>
          <option value="lesson">Lesson</option>
          <option value="payment">Payment</option>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Recipient</label>
        <select name="user_id">
          <option value="">General</option>
          <?php foreach (array_merge($students, $instructors, $staff) as $user): ?>
            <option value="<?= (int)$user['id']; ?>"><?= sanitize($user['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Invoice (optional)</label>
        <select name="invoice_id">
          <option value="">None</option>
          <?php foreach ($invoices as $invoice): ?>
            <option value="<?= (int)$invoice['id']; ?>">Invoice #<?= (int)$invoice['id']; ?> — <?= sanitize($invoice['student_name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Lesson booking (optional)</label>
        <select name="schedule_id">
          <option value="">None</option>
          <?php foreach ($schedules as $schedule): ?>
            <option value="<?= (int)$schedule['id']; ?>">Lesson #<?= (int)$schedule['id']; ?> — <?= sanitize($schedule['student_name']); ?> at <?= date('M d, H:i', strtotime($schedule['start_time'])); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="input-wrapper">
        <label>Remind at *</label>
        <input type="datetime-local" name="reminder_at" value="<?= date('Y-m-d\TH:i', strtotime('+1 day')); ?>" required>
      </div>
      <div class="input-wrapper">
        <label>Message *</label>
        <textarea name="message" rows="4" required></textarea>
      </div>
      <button class="btn" type="submit">Schedule reminder</button>
    </form>

    <h3 class="admin-section-title" style="margin-top:32px;">Upcoming reminders</h3>
    <?php if (!$upcomingReminders): ?>
      <p>No reminders scheduled.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($upcomingReminders as $reminder): ?>
          <li style="margin-bottom:12px;">
            <?= date('M d, H:i', strtotime($reminder['reminder_at'])); ?> — <?= ucfirst($reminder['reminder_type']); ?> for <?= sanitize($reminder['recipient'] ?? 'General'); ?><br>
            <em><?= nl2br(sanitize($reminder['message'])); ?></em>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </aside>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>