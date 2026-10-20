<?php
require "../config/db.php";
require_once __DIR__.'/../config/config.php';
$pdo->exec("UPDATE requests SET status='Отклонена' WHERE id={$_GET['id']}");
header("Location: ../pages/requests_manage.php");
