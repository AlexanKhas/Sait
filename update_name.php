<?php
require "../config/db.php";
require_once __DIR__.'/../config/config.php';
session_start();

if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false,'message'=>'Не авторизован']);
    exit;
}

$user_id = $_SESSION['user_id'];

if($_SERVER['REQUEST_METHOD']==='POST'){
    $first  = trim($_POST['first_name'] ?? '');
    $last   = trim($_POST['last_name'] ?? '');
    $middle = trim($_POST['middle_name'] ?? '');

    $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, middle_name=? WHERE id=?");
    $stmt->execute([$first,$last,$middle,$user_id]);

    echo json_encode([
        'success'=>true,
        'name'=>trim("$last $first $middle")
    ]);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Неверный запрос']);
