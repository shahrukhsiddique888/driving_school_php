

<?php
if (session_status() === PHP_SESSION_NONE) { // Start the session
    session_start();
}
session_unset(); // Remove all session variables
session_destroy();
header("Location: index.php"); // Redirect back to home or login page
exit;
