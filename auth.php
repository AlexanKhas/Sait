<?php
require_once __DIR__.'/../config/config.php';
if(session_status() === PHP_SESSION_NONE) session_start();

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
