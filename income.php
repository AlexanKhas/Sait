<?php
require "../includes/auth.php";
require_once __DIR__.'/../config/config.php';

if ($_SESSION['role'] !== 'Администратор') exit();

require "../includes/header.php";
require "../config/db.php";

$msg=""; $type="";

if($_POST){

    $pdo->prepare("
        INSERT INTO income(material_id,quantity,date)
        VALUES(?,?,CURDATE())
    ")->execute([$_POST['material'],$_POST['qty']]);

    $pdo->prepare("
        INSERT INTO stock(material_id,quantity)
        VALUES(?,?)
        ON DUPLICATE KEY UPDATE quantity=quantity+?
    ")->execute([$_POST['material'],$_POST['qty'],$_POST['qty']]);

    $msg="Приход зарегистрирован";
    $type="success";
}

$materials_stmt = $pdo->prepare("
    SELECT * FROM materials
    WHERE is_deleted = 0
    ORDER BY name ASC
");
$materials_stmt->execute();
$materials = $materials_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="section">

<h2><i class="fa-solid fa-arrow-down"></i> Приход</h2>

<?php if($msg): ?>
<div class="alert <?=$type?>"><?=$msg?></div>
<?php endif; ?>

<div class="form-card">

<form method="post" class="main-form">

<label>Материал</label>

<select name="material" required>
<option value="">Выберите</option>

<?php foreach($materials as $m): ?>
<option value="<?=$m['id']?>"><?=$m['name']?></option>
<?php endforeach; ?>

</select>

<label>Количество</label>
<input type="number" name="qty" min="1" required>

<button class="btn-main">Принять</button>

</form>
</div>

</section>

<?php require "../includes/footer.php"; ?>
