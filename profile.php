<?php
require "../includes/header.php";
require "../config/db.php";
require_once __DIR__.'/../config/config.php';
require "../includes/auth.php";

$user_id = $_SESSION['user_id'];

/* ===== Загрузка аватара ===== */
if(isset($_FILES['avatar'])){
    $file = $_FILES['avatar'];
    if($file['error']===0){
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','webp'];
        if(in_array(strtolower($ext), $allowed)){
            $name = 'user_'.$user_id.'_'.time().'.'.$ext;
            move_uploaded_file($file['tmp_name'], "../uploads/".$name);

            $stmt = $pdo->prepare("UPDATE users SET avatar=? WHERE id=?");
            $stmt->execute([$name,$user_id]);

            app_log("Updated avatar");

            header("Location: profile.php");
            exit;
        }
    }
}

/* ===== Получаем пользователя ===== */
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

/* ===== ФИО ===== */
$fullName = trim(
    ($user['last_name'] ?? '').' '.
    ($user['first_name'] ?? '').' '.
    ($user['middle_name'] ?? '')
);

/* ===== Мои заявки ===== */
$stmt = $pdo->prepare("
    SELECT 
        r.id,
        r.status,
        r.created_at,
        GROUP_CONCAT(
            CONCAT(m.name,' (',ri.quantity,')')
            SEPARATOR '<br>'
        ) AS materials
    FROM requests r
    LEFT JOIN request_items ri ON r.id = ri.request_id
    LEFT JOIN materials m ON ri.material_id = m.id
    WHERE r.user_id = ?
    GROUP BY r.id, r.status, r.created_at
    ORDER BY r.id DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

/* ===== AJAX: обновление ФИО ===== */
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['first_name'])){
    if(!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']){
        die('CSRF detected');
    }

    $first  = trim($_POST['first_name']);
    $last   = trim($_POST['last_name']);
    $middle = trim($_POST['middle_name']);

    $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, middle_name=? WHERE id=?");
    $stmt->execute([$first,$last,$middle,$user_id]);

    app_log("Updated full name");

    echo json_encode([
        'success'=>true,
        'name'=>trim("$last $first $middle")
    ]);
    exit;
}
?>

<section class="section profile-section">

<!-- ===== АВАТАР ===== -->
<div class="avatar-container">
<form method="post" enctype="multipart/form-data" class="avatar-form">
<label class="avatar-label">
<?php if(!empty($user['avatar'])): ?>
<img src="../uploads/<?= htmlspecialchars($user['avatar']) ?>" class="avatar-img">
<?php else: ?>
<div class="avatar-img"><?= strtoupper(substr($user['first_name'] ?? $user['username'],0,1)) ?></div>
<?php endif; ?>
<div class="avatar-overlay"><i class="fa-solid fa-camera"></i></div>
<input type="file" name="avatar" hidden onchange="this.form.submit()">
</label>
</form>
</div>

<!-- ===== ИНФО ===== -->
<div class="profile-info">
<div class="editable-name">
<h2 id="username"><?= htmlspecialchars($fullName ?: $user['username']) ?></h2>
<i class="fa-solid fa-pencil" id="edit-name"></i>
</div>

<p><b>Логин:</b> <?= htmlspecialchars($user['username']) ?></p>
<p><b>Организация:</b> <?= htmlspecialchars($user['company'] ?? 'Не указано') ?></p>
</div>

<!-- ===== МОИ ЗАЯВКИ ===== -->
<h3>Мои заявки</h3>
<table class="data-table">
<tr>
<th>ID</th>
<th>Статус</th>
<th></th>
</tr>
<?php foreach($orders as $o): ?>
<tr>
<td><?= $o['id'] ?></td>
<td><?= htmlspecialchars($o['status']) ?></td>
<td><button class="btn-view" data-id="<?= $o['id'] ?>">Подробнее</button></td>
</tr>
<tr class="details-row" id="d<?= $o['id'] ?>"><td colspan="4"><div class="details-box"></div></td></tr>
<?php endforeach; ?>
</table>

<!-- ===== СМЕНА ПАРОЛЯ ===== -->
<section class="change-password">
<h3>Изменить пароль</h3>
<form method="post" action="change_password.php">
<input type="password" name="old_password" placeholder="Старый пароль" required>
<input type="password" name="new_password" placeholder="Новый пароль" required>
<input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
<button type="submit" class="btn">Сменить пароль</button>
</form>
</section>

<!-- ===== ВЫХОД ===== -->
<div class="logout-btn">
<a href="../auth/logout.php" class="logout-link" style="color:red;text-decoration:none;">Выйти из профиля</a>
</div>

<!-- ===== МОДАЛКА ФИО ===== -->
<div class="modal" id="nameModal">
    <div class="modal-box">
        <h3>Изменить ФИО</h3>
        <input id="lastName" placeholder="Фамилия">
        <input id="firstName" placeholder="Имя">
        <input id="middleName" placeholder="Отчество">
        <input type="hidden" id="csrfToken" value="<?= $_SESSION['token'] ?>">
        <button class="btn" id="saveName">Сохранить</button>
    </div>
</div>

<?php require "../includes/footer.php"; ?>

<script>
document.addEventListener("click", async (e) => {

    /* ===== РЕДАКТИРОВАНИЕ ИМЕНИ ===== */
    if (e.target.id === "edit-name") {
        document.getElementById("nameModal").classList.add("show");
        return;
    }

    if (e.target.id === "saveName") {

        e.preventDefault();

        const first  = document.getElementById("firstName").value.trim();
        const last   = document.getElementById("lastName").value.trim();
        const middle = document.getElementById("middleName").value.trim();
        const token  = document.getElementById("csrfToken").value;

        fetch("profile.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body:
                "first_name=" + encodeURIComponent(first) +
                "&last_name=" + encodeURIComponent(last) +
                "&middle_name=" + encodeURIComponent(middle) +
                "&token=" + token
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                document.getElementById("username").innerText = res.name;
                document.getElementById("nameModal").classList.remove("show");
            }
        });

        return;
    }


    /* ===== КНОПКА ПОДРОБНЕЕ ===== */
    if (e.target.classList.contains("btn-view")) {

        const btn = e.target;
        const id = btn.dataset.id;

        const row = document.getElementById("d" + id);
        const box = row.querySelector(".details-box");

        if (row.classList.contains("open")) {
            row.classList.remove("open");
            return;
        }

        try {

            const res = await fetch("../api/request_items.php?id=" + id);
            const data = await res.json();

            if (data.length) {

                let html =
                    "<table class='inner-table'>" +
                    "<tr><th>Материал</th><th>Количество</th></tr>";

                data.forEach(i => {
                    html += `<tr><td>${i.name}</td><td>${i.quantity}</td></tr>`;
                });

                html += "</table>";

                box.innerHTML = html;

            } else {

                box.innerHTML = "<p>Нет данных</p>";

            }

            document
                .querySelectorAll(".details-row")
                .forEach(r => r.classList.remove("open"));

            row.classList.add("open");

        } catch (err) {

            console.error(err);
            box.innerHTML = "<p>Ошибка загрузки</p>";

        }
    }

});
</script>
