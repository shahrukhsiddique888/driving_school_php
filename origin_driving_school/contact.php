<?php
include "includes/header.php";
?>

<main>
  <section class="section get-start">
    <div class="container">
      <h2 class="h2 section-title">Contact Us</h2>
      <p class="section-text">
        Have questions or want to book your driving lessons? Get in touch with us using the form below or find our contact details.
      </p>

      <div class="grid-list">
        <!-- Contact Details -->
        <div class="get-start-card">
          <div class="card-icon icon-2">
            <ion-icon name="call-outline"></ion-icon>
          </div>
          <h3 class="card-title">Call Us</h3>
          <p class="card-text">+61 400 123 456</p>
        </div>

        <div class="get-start-card">
          <div class="card-icon icon-3">
            <ion-icon name="mail-outline"></ion-icon>
          </div>
          <h3 class="card-title">Email Us</h3>
          <p class="card-text">info@origindrivingschool.com</p>
        </div>

        <div class="get-start-card">
          <div class="card-icon icon-1">
            <ion-icon name="location-outline"></ion-icon>
          </div>
          <h3 class="card-title">Visit Us</h3>
          <p class="card-text">123 Main Street, Sydney, NSW, Australia</p>
        </div>
      </div>

      <!-- Contact Form -->
      <div class="get-start-card" style="margin-top:30px;">
        <h3 class="card-title">Send Us a Message</h3>
        <form method="POST" action="controllers/contact.php">
          <div class="input-wrapper">
            <label for="name">Your Name</label>
            <input type="text" id="name" name="name" required class="input-field">
          </div>
          <div class="input-wrapper">
            <label for="email">Your Email</label>
            <input type="email" id="email" name="email" required class="input-field">
          </div>
          <div class="input-wrapper">
            <label for="message">Message</label>
            <textarea id="message" name="message" required class="input-field"></textarea>
          </div>
          <button type="submit" class="btn">Send Message</button>
        </form>
      </div>
    </div>
  </section>
</main>

<?php
include "includes/footer.php";
?>
