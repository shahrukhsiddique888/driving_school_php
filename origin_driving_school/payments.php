<?php
include "includes/header.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total = $_POST['total'] ?? 0;
}
?>

<main>
  <section class="section get-start">
    <div class="container">
      <h2 class="h2 section-title">Checkout</h2>
      <p>You are about to pay: <strong>$<?= number_format($total,2) ?></strong></p>

      <!-- Dummy payment form -->
      <form method="POST" action="controllers/pay.php">
        <input type="hidden" name="amount" value="<?= $total ?>">
        <div class="input-wrapper">
          <label>Card Number</label>
          <input type="text" class="input-field" placeholder="1111 2222 3333 4444" required>
        </div>
        <div class="input-wrapper">
          <label>Expiry</label>
          <input type="text" class="input-field" placeholder="MM/YY" required>
        </div>
        <div class="input-wrapper">
          <label>CVV</label>
          <input type="text" class="input-field" placeholder="123" required>
        </div>
        <button type="submit" class="btn">Confirm Payment</button>
      </form>
    </div>
  </section>
</main>

<?php include "includes/footer.php"; ?>
