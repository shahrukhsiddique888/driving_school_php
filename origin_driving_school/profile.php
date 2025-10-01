<?php
// profile.php
session_start();
require_once __DIR__ . "/config/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php?error=Please+login+first");
    exit;
}

$user = $_SESSION['user'];

// Handle delete request
if (isset($_GET['delete'])) {
    $file_id = (int) $_GET['delete'];

    $stmt = $pdo->prepare("SELECT file_path FROM user_files WHERE id = ? AND user_id = ?");
    $stmt->execute([$file_id, $user['id']]);
    $file = $stmt->fetch();

    if ($file) {
        // Delete file from server
        $filePath = __DIR__ . "/" . $file['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Remove from DB
        $pdo->prepare("DELETE FROM user_files WHERE id = ?")->execute([$file_id]);
        header("Location: profile.php?deleted=1");
        exit;
    }
}

// Fetch uploaded files
$stmt = $pdo->prepare("SELECT id, file_path, uploaded_at FROM user_files WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$user['id']]);
$files = $stmt->fetchAll();

include "includes/header.php";
?>

<main>
  <section class="section get-start">
    <div class="container">
      <h2 class="h2 section-title">My Profile</h2>
      <p>Welcome, <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['role']) ?>)</p>

      <?php if (isset($_GET['uploaded'])): ?>
        <p style="color:green;">File uploaded successfully!</p>
      <?php endif; ?>
      <?php if (isset($_GET['deleted'])): ?>
        <p style="color:red;">File deleted successfully!</p>
      <?php endif; ?>

      <!-- Upload Form -->
      <div class="get-start-card">
        <h3 class="card-title">Upload Document or Image</h3>
        <form method="POST" action="controllers/upload.php" enctype="multipart/form-data">
          <div class="input-wrapper">
            <input type="file" name="file" required>
          </div>
          <button type="submit" class="btn">Upload</button>
        </form>
      </div>

      <!-- Uploaded Files -->
      <h3 class="h3" style="margin-top:20px;">Your Uploads</h3>
      <ul class="featured-car-list">
        <?php foreach ($files as $f): ?>
          <li>
            <div class="featured-car-card">
              <div class="card-content">
                <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $f['file_path'])): ?>
                  <img src="<?= htmlspecialchars($f['file_path']) ?>" alt="Uploaded Image" style="max-width:200px; border-radius:8px;">
                <?php else: ?>
                  <p><?= basename($f['file_path']) ?></p>
                <?php endif; ?>
                <p>Uploaded: <?= $f['uploaded_at'] ?></p>
                <a href="profile.php?delete=<?= $f['id'] ?>" class="btn" style="background:red;">Delete</a>
              </div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
</main>

<?php include "includes/footer.php"; ?>
