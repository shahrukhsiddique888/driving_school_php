<?php include "includes/header.php"; ?>
<main>
  <section class="hero">
    <div class="container">
      <div class="hero-content">
        <h1 class="h1 hero-title">Learn to Drive with Confidence</h1>
        <p class="hero-text">Book lessons with professional instructors today.</p>
        <form action="reservation.php" method="post" class="hero-form">
          <div class="input-wrapper">
            <label class="input-label">Pick-up</label>
            <input type="text" name="pickup" class="input-field" required>
          </div>
          <div class="input-wrapper">
            <label class="input-label">Drop-off</label>
            <input type="text" name="dropoff" class="input-field" required>
          </div>
          <div class="input-wrapper">
            <label class="input-label">Date</label>
            <input type="date" name="date" class="input-field" required>
          </div>
          <button type="submit" class="btn">Book Now</button>
        </form>
      </div>
      <figure class="hero-banner">
        <img src="assets/images/7.jpg" alt="Driving Lesson" width="800">
      </figure>
    </div>
  </section>
</main>
<?php include "includes/footer.php"; ?>
