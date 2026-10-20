<?php
session_start();
require "../config/db.php";
require_once __DIR__.'/../config/config.php';

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error'=>'not_logged_in']);
    exit;
}

$id = intval($_GET['id']);
$user = intval($_SESSION['user_id']);

$stmt = $pdo->prepare("
    SELECT m.name, ri.quantity
    FROM request_items ri
    JOIN materials m ON m.id = ri.material_id
    JOIN requests r ON r.id = ri.request_id
    WHERE r.id = ? AND r.user_id = ?
");
$stmt->execute([$id, $user]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
