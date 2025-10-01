<?php
// controllers/remove_cart.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php?error=Please+login+to+remove+courses");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = $_POST['cart_id'] ?? null;
    $user_id = $_SESSION['user']['id'];

    if ($cart_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$cart_id, $user_id]);

            header("Location: ../cart.php?removed=1");
            exit;
        } catch (Exception $e) {
            header("Location: ../cart.php?error=Could+not+remove+course");
            exit;
        }
    }
}
