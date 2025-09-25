<?php
require_once __DIR__ . "/../config/db.php";  // DB connection

try {
    // Fetch all invoices with their items
    $stmt = $pdo->query("
        SELECT i.id AS invoice_id, i.student_id, i.issue_date, i.due_date, 
               i.total, i.status, s.id AS sid, u.name AS student_name
        FROM invoices i
        JOIN students s ON i.student_id = s.id
        JOIN users u ON s.user_id = u.id
        ORDER BY i.due_date ASC
    ");
    $invoices = $stmt->fetchAll();

    // Fetch invoice items grouped by invoice_id
    $itemStmt = $pdo->query("
        SELECT invoice_id, description, qty, unit_price
        FROM invoice_items
    ");
    $itemsRaw = $itemStmt->fetchAll();

    $invoiceItems = [];
    foreach ($itemsRaw as $item) {
        $invoiceItems[$item['invoice_id']][] = $item;
    }
} catch (PDOException $e) {
    die("❌ Error fetching invoices: " . $e->getMessage());
}
