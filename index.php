<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['usuario_id'])) {
    header("Location: /dashboard.php");
} else {
    header("Location: /login.php");
}
exit;
