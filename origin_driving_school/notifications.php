<?php
include "includes/header.php";
require_once __DIR__ . "/config/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php?error=Please+login+to+view+notifications");
    exit;
}

$user = $_SESSION['user'];

if (isset($_GET['read'])) {
    $notificationId = (int) $_GET['read'];
    $markStmt = $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?");
    $markStmt->execute([$notificationId, $user['id']]);
    header("Location: notifications.php");
    exit;
}

$notifStmt = $pdo->prepare("SELECT id, title, message, link, created_at, read_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$notifStmt->execute([$user['id']]);
$notifications = $notifStmt->fetchAll();
?>

<main>
  <section class="section get-start">
    <div class="container">
      <h2 class="h2 section-title">Notifications</h2>
      <p class="section-text">Stay up to date with reminders, bookings, and account changes.</p>

      <?php if (!$notifications): ?>
        <p>You do not have any notifications yet.</p>
      <?php else: ?>
        <ul class="featured-car-list">
          <?php foreach ($notifications as $notification): ?>
            <li>
              <div class="featured-car-card" style="<?= $notification['read_at'] ? '' : 'border: 2px solid var(--carolina-blue);' ?>">
                <div class="card-content">
                  <div class="card-title-wrapper">
                    <h3 class="h3 card-title"><?= htmlspecialchars($notification['title']) ?></h3>
                    <data class="year"><?= date('M d, Y H:i', strtotime($notification['created_at'])) ?></data>
                  </div>
                  <p><?= nl2br(htmlspecialchars($notification['message'])) ?></p>
                  <div style="margin-top:10px; display:flex; gap:10px;">
                    <?php if ($notification['link']): ?>
                      <a class="btn" href="<?= htmlspecialchars($notification['link']) ?>">View details</a>
                    <?php endif; ?>
                    <?php if (!$notification['read_at']): ?>
                      <a class="btn" style="background:var(--cadet-blue-crayola);" href="?read=<?= (int)$notification['id'] ?>">Mark as read</a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php include "includes/footer.php"; ?>