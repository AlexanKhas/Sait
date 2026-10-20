<?php
require_once '../config/db.php';
header("Content-Type: application/json");

$q = $pdo->query("
    SELECT DATE(created_at) AS d, SUM(amount) AS s
    FROM payments
    WHERE status='success'
    GROUP BY d
");
echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
