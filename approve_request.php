<?php
require "../config/db.php";
require_once __DIR__.'/../config/config.php';

$id = $_GET['id'];

$req = $pdo->query("
    SELECT * FROM requests WHERE id = $id
")->fetch();

$stock = $pdo->query("
    SELECT quantity FROM stock WHERE material_id = {$req['material_id']}
")->fetchColumn();

if ($stock >= $req['quantity']) {
    $pdo->exec("
        UPDATE stock SET quantity = quantity - {$req['quantity']}
        WHERE material_id = {$req['material_id']}
    ");

    $pdo->exec("
        UPDATE requests SET status='Одобрена' WHERE id=$id
    ");
} else {
    $pdo->exec("
        UPDATE requests SET status='Отклонена' WHERE id=$id
    ");
}

header("Location: ../pages/requests_manage.php");
