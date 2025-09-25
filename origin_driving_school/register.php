<?php
include "includes/header.php";
?>

<main>
  <section class="section get-start">
    <div class="container">
      <h2 class="h2 section-title">Create an Account</h2>
      <p class="section-text">Fill out the form below to sign up.</p>

      <div class="get-start-card">
        <h3 class="card-title">Sign Up</h3>
        <?php if (isset($_GET['error'])): ?>
          <p style="color:red;"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
          <p style="color:green;"><?= htmlspecialchars($_GET['success']) ?></p>
        <?php endif; ?>

        <form method="POST" action="controllers/register.php">
          <div class="input-wrapper">
            <label for="name">Full Name</label>
            <input type="text" name="name" required class="input-field" placeholder="Enter your full name">
          </div>

          <div class="input-wrapper">
            <label for="email">Email</label>
            <input type="email" name="email" required class="input-field" placeholder="Enter your email">
          </div>

          <div class="input-wrapper">
            <label for="phone">Phone</label>
            <input type="text" name="phone" class="input-field" placeholder="Optional phone number">
          </div>

          <div class="input-wrapper">
            <label for="password">Password</label>
            <input type="password" name="password" required class="input-field" placeholder="Choose a password">
          </div>

          <div class="input-wrapper">
            <label for="role">Register as</label>
            <select name="role" class="input-field" required>
              <option value="student">Student</option>
              <option value="instructor">Instructor</option>
            </select>
          </div>

          <button type="submit" class="btn">Register</button>
        </form>
      </div>

      <p class="section-text" style="margin-top:20px;">
        Already have an account? <a href="login.php" style="color: var(--carolina-blue);">Login here</a>.
      </p>
    </div>
  </section>
</main>

<?php
include "includes/footer.php";
?>
