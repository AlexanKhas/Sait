<?php
require "../includes/auth.php";
require_once __DIR__.'/../config/config.php';

if ($_SESSION['role'] !== 'Администратор') exit();

require "../includes/header.php";
require "../config/db.php";

$msg="";
$type="";

/* ================= ДОБАВЛЕНИЕ ================= */
if(isset($_POST['add'])){

    $pdo->prepare("
        INSERT INTO materials(name,unit)
        VALUES(?,?)
    ")->execute([$_POST['name'],$_POST['unit']]);

    $msg="Материал добавлен";
    $type="success";
}

/* ================= УДАЛЕНИЕ ================= */
if(isset($_POST['delete_id'])){

    $pdo->prepare("
        UPDATE materials 
        SET is_deleted=1 
        WHERE id=?
    ")->execute([$_POST['delete_id']]);

    $msg="Материал скрыт";
    $type="success";
}

/* ================= СПИСОК ================= */
$rows=$pdo->query("
    SELECT * FROM materials 
    WHERE is_deleted=0 
    ORDER BY id DESC
")->fetchAll();
?>

<section class="section">

<h2><i class="fa-solid fa-box"></i> Материалы</h2>

<?php if($msg): ?>
<div class="alert <?=$type?>"><?=$msg?></div>
<?php endif; ?>

<div class="form-card">

<form method="post" class="main-form">

<input type="hidden" name="add" value="1">

<label>Название</label>
<input name="name" required>

<label>Ед. измерения</label>
<input name="unit" required>

<button class="btn-main">Добавить</button>

<a href="materials_archive.php" class="btn-main archive-btn">
🗂 Открыть архив
</a>

</form>
</div>

<input
  type="text"
  id="liveSearch"
  placeholder="Поиск по ID, названию или ед. изм..."
  class="search-input"
>

<table class="data-table" id="materialsTable">

<tr>
<th>ID</th>
<th>Название</th>
<th>Ед.</th>
<th>Действия</th>
</tr>

<?php foreach($rows as $r): ?>

<tr>
<td><?=$r['id']?></td>
<td><?=$r['name']?></td>
<td><?=$r['unit']?></td>
<td>

<form method="post" style="display:inline;"
      onsubmit="return confirm('Удалить материал?')">

<input type="hidden" name="delete_id" value="<?=$r['id']?>">
<button class="btn-delete">
<i class="fa-solid fa-trash"></i>
</button>

</form>

</td>
</tr>

<?php endforeach; ?>

</table>

</section>

<style>
.search-input{
    margin:20px 0;
    padding:10px 15px;
    width:300px;
    border-radius:10px;
    border:1px solid #ccc;
}

.btn-delete{
    background:#e74c3c;
    border:none;
    color:#fff;
    padding:6px 10px;
    border-radius:8px;
    cursor:pointer;
}

.btn-delete:hover{
    background:#c0392b;
}

.archive-btn{
    text-decoration:none;
    display:block;
    text-align:center;
    width:200px;
    margin:10px auto; /* центрирование блока */
}
</style>

<script>
/* ================= ЖИВОЙ ПОИСК ================= */

document.getElementById("liveSearch")
.addEventListener("keyup", function() {

    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#materialsTable tr");

    rows.forEach((row, index) => {

        if(index === 0) return; // пропускаем заголовок

        let text = row.innerText.toLowerCase();

        if(text.includes(filter)){
            row.style.display="";
        } else {
            row.style.display="none";
        }

    });
});
</script>

<?php require "../includes/footer.php"; ?>