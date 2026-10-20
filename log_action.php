<?php
session_start();
require "../config/db.php";
require_once __DIR__.'/../config/config.php';

header("Content-Type: application/json");

if(!isset($_SESSION['user_id'])){
    echo json_encode(['error'=>'not_logged_in']);
    exit;
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

if($action){
    $logLine = "[".date('Y-m-d H:i:s')."] [User $user_id] $action\n";
    file_put_contents(__DIR__.'/../logs/app.log', $logLine, FILE_APPEND);
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['error'=>'empty_action']);
}
