<?php
require "../includes/auth.php";
require_once __DIR__.'/../config/config.php';

if ($_SESSION['role'] !== 'Администратор') exit();

require "../includes/header.php";
require "../config/db.php";

$rows=$pdo->query("
SELECT m.name,s.quantity,m.unit
FROM stock s
JOIN materials m ON s.material_id=m.id
")->fetchAll();
?>

<section class="section">

<h2><i class="fa-solid fa-warehouse"></i> Остатки</h2>

<table class="data-table">

<tr>
<th>Материал</th>
<th>Количество</th>
<th>Ед.</th>
</tr>

<?php foreach($rows as $r): ?>

<tr>
<td><?=$r['name']?></td>
<td><?=$r['quantity']?></td>
<td><?=$r['unit']?></td>
</tr>

<?php endforeach; ?>

</table>

</section>

<?php require "../includes/footer.php"; ?>
