<?php
require "../includes/auth.php";
require_once __DIR__.'/../config/config.php';

if($_SESSION['role'] !== 'Администратор') exit();

require "../includes/header.php";
require "../config/db.php";

$message = "";

/* ========== ВОССТАНОВЛЕНИЕ ========== */
if(isset($_GET['restore'])){
    $id = intval($_GET['restore']);
    $pdo->prepare("UPDATE materials SET is_deleted=0 WHERE id=?")
        ->execute([$id]);
    $message = "Материал восстановлен";
}

/* ========== УДАЛЕНИЕ НАВСЕГДА ========== */
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);

    // Проверка — используется ли материал
    $check = $pdo->prepare("SELECT COUNT(*) FROM request_items WHERE material_id=?");
    $check->execute([$id]);

    if($check->fetchColumn() > 0){
        $message = "Нельзя удалить — материал используется в заявках";
    } else {
        $pdo->prepare("DELETE FROM materials WHERE id=?")
            ->execute([$id]);
        $message = "Материал удалён полностью";
    }
}

/* ========== Получаем архив ========== */
$stmt = $pdo->query("
    SELECT m.*, COALESCE(s.quantity,0) as stock
    FROM materials m
    LEFT JOIN stock s ON m.id = s.material_id
    WHERE m.is_deleted = 1
    ORDER BY m.name ASC
");

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="section">

<h2>Архив материалов</h2>

<?php if($message): ?>
<div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<table class="data-table">
<tr>
<th>ID</th>
<th>Название</th>
<th>Склад</th>
<th>Действия</th>
</tr>

<?php foreach($rows as $r): ?>
<tr>
<td><?= $r['id'] ?></td>
<td><?= htmlspecialchars($r['name']) ?></td>
<td><?= $r['stock'] ?></td>
<td class="archive-actions">

<a href="?restore=<?= $r['id'] ?>"
   class="archive-btn restore"
   onclick="return confirm('Восстановить материал?')">
   <i class="fa-solid fa-rotate"></i>
   Восстановить
</a>

<a href="?delete=<?= $r['id'] ?>"
   class="archive-btn delete"
   onclick="return confirm('Удалить материал навсегда?')">
   <i class="fa-solid fa-trash"></i>
   Удалить
</a>

</td>
</tr>
<?php endforeach; ?>

</table>

</section>
<style>

.archive-actions{
    display:flex;
    gap:15px;
    align-items:center;
}

.archive-btn{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:8px 16px;
    border-radius:12px;
    font-weight:600;
    font-size:14px;
    text-decoration:none;
    transition:0.2s ease;
}

/* Восстановить */
.archive-btn.restore{
    background:#7a1f2f;
    color:white;
}

.archive-btn.restore:hover{
    background:#5e1824;
    transform:translateY(-2px);
}

/* Удалить */
.archive-btn.delete{
    background:transparent;
    color:#c0392b;
    border:2px solid #c0392b;
}

.archive-btn.delete:hover{
    background:#c0392b;
    color:white;
    transform:translateY(-2px);
}

</style>
<?php require "../includes/footer.php"; ?>