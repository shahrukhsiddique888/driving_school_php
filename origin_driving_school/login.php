<?php
include "includes/header.php";
?>

<main>
  <section class="section get-start">
    <div class="container">
      <h2 class="h2 section-title">Login</h2>
      <p class="section-text">Enter your credentials to access your account.</p>

      <!-- Login Form -->
      <div class="get-start-card">
        <h3 class="card-title">Sign In</h3>
        <?php if (isset($_GET['error'])): ?>
          <p style="color:red;"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>

        <form method="POST" action="controllers/login.php">
          <div class="input-wrapper">
            <label for="email">Email</label>
            <input type="email" name="email" required class="input-field" placeholder="Enter your email">
          </div>

          <div class="input-wrapper">
            <label for="password">Password</label>
            <input type="password" name="password" required class="input-field" placeholder="Enter your password">
          </div>

          <button type="submit" class="btn">Login</button>
        </form>
      </div>

      <p class="section-text" style="margin-top:20px;">
        Don’t have an account? <a href="register.php" style="color: var(--carolina-blue);">Register here</a>.
      </p>
    </div>
  </section>
</main>

<?php
include "includes/footer.php";
?>
