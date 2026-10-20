<?php
require "../config/db.php";

$q = $_GET['q'] ?? '';

$stmt = $pdo->prepare("
    SELECT * FROM materials
    WHERE name LIKE ?
");

$stmt->execute(["%$q%"]);

while($m = $stmt->fetch()){
    echo "
      <tr>
        <td>{$m['name']}</td>
        <td>{$m['unit']}</td>
        <td>{$m['qty']}</td>
      </tr>
    ";
}
