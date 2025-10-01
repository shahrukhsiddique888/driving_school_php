<?php
include "./includes/header.php";
require "./controllers/courses.php";

$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? 'guest';
?>


<main>
  <section class="section get-start">
    <div class="container">
      <h2 class="h2 section-title">Available Courses</h2>

      <?php if (isset($_GET['success'])): ?>
        <p style="color:green;">Course added successfully!</p>
      <?php endif; ?>
      <?php if (isset($_GET['deleted'])): ?>
        <p style="color:red;">Course deleted successfully!</p>
      <?php endif; ?>
      <?php if (isset($_GET['added'])): ?>
        <p style="color:blue;">Course added to cart!</p>
      <?php endif; ?>

      <!-- ADMIN: Add Course Form -->
      <?php if ($role === 'admin'): ?>
      <div class="get-start-card">
        <h3 class="card-title">Add New Course</h3>
        <form method="POST" action="../controllers/courses.php">
          <div class="input-wrapper">
            <label>Title</label>
            <input type="text" name="title" required class="input-field">
          </div>
          <div class="input-wrapper">
            <label>Description</label>
            <textarea name="description" required class="input-field"></textarea>
          </div>
          <div class="input-wrapper">
            <label>Duration</label>
            <input type="text" name="duration" placeholder="e.g., 5 Lessons" class="input-field">
          </div>
          <div class="input-wrapper">
            <label>Price ($)</label>
            <input type="number" step="0.01" name="price" required class="input-field">
          </div>
          <button type="submit" class="btn">Save</button>
        </form>
      </div>
      <?php endif; ?>

      <!-- Course List -->
      <ul class="featured-car-list">
        <?php foreach ($courses as $c): ?>
          <li>
            <div class="featured-car-card">
              <div class="card-content">
                <div class="card-title-wrapper">
                  <h3 class="h3 card-title"><?= htmlspecialchars($c['title']) ?></h3>
                  <data class="year"><?= htmlspecialchars($c['duration']) ?></data>
                </div>
                <p><?= htmlspecialchars($c['description']) ?></p>
                <p class="card-price">
                  <strong>$<?= number_format($c['price'],2) ?></strong>
                </p>

                <?php if ($role === 'admin'): ?>
                  <a href="courses.php?delete=<?= $c['id'] ?>" class="btn" style="background:red;">Delete</a>
                <?php elseif ($role === 'student'): ?>
                  <form method="POST" action="controllers/cart.php" style="margin-top:10px;">
                    <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                    <button type="submit" class="btn" style="background:var(--carolina-blue);color:white;">Add to Cart</button>
                  </form>
                <?php else: ?>
                  <p><a href="login.php" class="btn">Login to Enroll</a></p>
                <?php endif; ?>
              </div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
</main>

<?php include "./includes/footer.php"; ?>
