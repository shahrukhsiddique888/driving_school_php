<?php
include "includes/header.php";
require "config/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: login.php?error=Please login to view cart");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Fetch cart items
$stmt = $pdo->prepare("
    SELECT cart.id AS cart_id, c.id, c.title, c.description, c.duration, c.price
    FROM cart
    JOIN courses c ON cart.course_id = c.id
    WHERE cart.user_id = ?
");
$stmt->execute([$user_id]);
$courses = $stmt->fetchAll();
?>

<main>
  <section class="section get-start">
    <div class="container">
      <h2 class="h2 section-title">My Cart</h2>

      <?php if (isset($_GET['added'])): ?>
        <p style="color:green;">Course added to your cart!</p>
      <?php endif; ?>
      <?php if (isset($_GET['removed'])): ?>
        <p style="color:red;">Course removed from your cart!</p>
      <?php endif; ?>

      <?php if (!$courses): ?>
        <p>Your cart is empty. <a href="courses.php" style="color:var(--carolina-blue);">Browse courses</a>.</p>
      <?php else: ?>
        <ul class="featured-car-list">
          <?php $total = 0; ?>
          <?php foreach ($courses as $c): ?>
            <?php $total += $c['price']; ?>
            <li>
              <div class="featured-car-card">
                <div class="card-content">
                  <div class="card-title-wrapper">
                    <h3 class="h3 card-title"><?= htmlspecialchars($c['title']) ?></h3>
                    <data class="year"><?= htmlspecialchars($c['duration']) ?></data>
                  </div>
                  <p><?= htmlspecialchars($c['description']) ?></p>
                  <p class="card-price"><strong>$<?= number_format($c['price'],2) ?></strong></p>

                  <!-- Remove from cart -->
                  <form method="POST" action="controllers/remove_cart.php" style="margin-top:10px;">
                    <input type="hidden" name="cart_id" value="<?= $c['cart_id'] ?>">
                    <button type="submit" class="btn" style="background:red;">Remove</button>
                  </form>
                </div>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>

        <h3>Total: $<?= number_format($total, 2) ?></h3>
        <form action="payments.php" method="post">
          <input type="hidden" name="total" value="<?= $total ?>">
          <button type="submit" class="btn">Proceed to Payment</button>
        </form>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php include "includes/footer.php"; ?>
