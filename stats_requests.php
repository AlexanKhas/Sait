<?php
require_once '../config/db.php';

header("Content-Type: application/json");

// Считаем ВСЕ заявки по дням
$q = $pdo->query("
    SELECT DATE(created_at) AS d, COUNT(*) AS c
    FROM requests
    GROUP BY d
    ORDER BY d ASC
");

echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));