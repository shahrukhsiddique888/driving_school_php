<?php
include "includes/header.php";
require "./controllers/payments.php";
?>

<main>
  <section class="section get-start">
    <div class="container">
      <h2 class="h2 section-title">Payments & Invoices</h2>
      <p class="section-text">
        View and manage your driving lesson payments and invoices below.
      </p>

      <ul class="featured-car-list">
        <?php if (!empty($invoices)): ?>
          <?php foreach ($invoices as $inv): ?>
            <li>
              <div class="featured-car-card">
                <div class="card-content">
                  <div class="card-title-wrapper">
                    <h3 class="h3 card-title">
                      Invoice #<?= htmlspecialchars($inv['invoice_id']) ?>
                    </h3>
                    <data class="year">
                      Due: <?= htmlspecialchars($inv['due_date']) ?>
                    </data>
                  </div>
                  <p class="card-text">
                    <strong>Student:</strong> <?= htmlspecialchars($inv['student_name']) ?><br>
                    <strong>Issued:</strong> <?= htmlspecialchars($inv['issue_date']) ?>
                  </p>

                  <!-- Items -->
                  <ul class="card-list">
                    <?php if (isset($invoiceItems[$inv['invoice_id']])): ?>
                      <?php foreach ($invoiceItems[$inv['invoice_id']] as $item): ?>
                        <li class="card-list-item">
                          <span class="card-item-text">
                            <?= htmlspecialchars($item['description']) ?> 
                            (x<?= $item['qty'] ?>) 
                            - $<?= number_format($item['unit_price'], 2) ?>
                          </span>
                        </li>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </ul>

                  <p class="card-price">
                    <strong>$<?= number_format($inv['total'], 2) ?></strong>
                  </p>

                  <?php if ($inv['status'] === 'paid'): ?>
                    <button class="btn" style="background: green;">Paid</button>
                  <?php else: ?>
                    <button class="btn">Pay Now</button>
                  <?php endif; ?>
                </div>
              </div>
            </li>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No invoices available.</p>
        <?php endif; ?>
      </ul>
    </div>
  </section>
</main>

<?php include "includes/footer.php"; ?>
