<?php
session_start();
require_once __DIR__.'/../config/config.php';

if(isset($_SESSION['user_id'])){
    // ✅ Логирование выхода
    app_log("User {$_SESSION['user_id']} logged out");
}

session_destroy();
header("Location: ../pages/dashboard.php");
exit();
