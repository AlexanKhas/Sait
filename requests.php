<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require "../includes/header.php";
require "../config/db.php";
require_once __DIR__.'/../config/config.php';

$user_id = $_SESSION['user_id'] ?? 0;
$materials_stmt = $pdo->prepare("
    SELECT * FROM materials
    WHERE is_deleted = 0
    ORDER BY name ASC
");
$materials_stmt->execute();
$materials = $materials_stmt->fetchAll(PDO::FETCH_ASSOC);
$message = "";

// Безопасный вывод
function e($v){
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

// ================== ОБРАБОТКА POST ==================
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    // CSRF защита
    if(!isset($_POST['token']) || $_POST['token'] !== ($_SESSION['token'] ?? '')){
        die("CSRF detected");
    }

    // ===== ОПЛАТА ЗАЯВКИ =====
    if(isset($_POST['request_id'], $_POST['amount'])){
        $request_id = intval($_POST['request_id']);
        $amount = intval($_POST['amount']);
        $status = rand(1,10) > 2 ? 'success' : 'fail'; // sandbox

        $stmt_pay = $pdo->prepare("INSERT INTO payments(user_id, request_id, amount, status, created_at) VALUES (?,?,?,?,NOW())");
        $stmt_pay->execute([$user_id, $request_id, $amount, $status]);

        $msg = $status === 'success' ? "Оплата прошла успешно" : "Ошибка оплаты";
        app_log("User $user_id paid request #$request_id: $status, amount=$amount");

        $message = $msg;
    }
    // ===== СОЗДАНИЕ НОВОЙ ЗАЯВКИ =====
    else {
        $pdo->beginTransaction();
        try{
            $stmt = $pdo->prepare("INSERT INTO requests(user_id, status, created_at) VALUES (?, 'Новая', NOW())");
            $stmt->execute([$user_id]);
            $request_id = $pdo->lastInsertId();

            if(!empty($_POST['material_id']) && !empty($_POST['quantity'])){

                $checkMaterial = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM materials 
                    WHERE id = ? AND is_deleted = 0
                ");

                $stmt_item = $pdo->prepare("
                    INSERT INTO request_items(request_id, material_id, quantity)
                    VALUES (?,?,?)
                ");

                foreach($_POST['material_id'] as $idx => $mid){

                    $mid = intval($mid);
                    $qty = intval($_POST['quantity'][$idx]);

                    if($qty > 0){

                        // 🔐 Проверяем — материал активен?
                        $checkMaterial->execute([$mid]);
                        $exists = $checkMaterial->fetchColumn();

                        if($exists > 0){
                            $stmt_item->execute([$request_id, $mid, $qty]);
                        }
                    }
                }
            }

            $pdo->commit();
            $message = "Заявка успешно создана!";
            app_log("User $user_id created request #$request_id");
        } catch(Exception $e){
            $pdo->rollBack();
            $message = "Ошибка создания заявки: ".$e->getMessage();
        }
    }
}

// ================== ПОЛУЧЕНИЕ ЗАЯВОК ПОЛЬЗОВАТЕЛЯ ==================
$my_requests_stmt = $pdo->prepare("
    SELECT r.id, r.status, r.created_at,
           GROUP_CONCAT(
               CONCAT(
                   COALESCE(m.name,'[Удалённый материал]'),
                   ' (', ri.quantity, ')'
               ) SEPARATOR '<br>'
           ) AS items
    FROM requests r
    LEFT JOIN request_items ri ON r.id = ri.request_id
    LEFT JOIN materials m 
        ON ri.material_id = m.id 
        AND m.is_deleted = 0
    WHERE r.user_id = ?
    GROUP BY r.id
    ORDER BY r.created_at DESC
");
$my_requests_stmt->execute([$user_id]);
$my_requests = $my_requests_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="section">
<h2>Создать новую заявку</h2>
<?php if($message): ?>
<p class="alert <?= strpos($message,'успешно')!==false ? 'success':'error' ?>"><?= e($message) ?></p>
<?php endif; ?>

<form method="post">
<input type="hidden" name="token" value="<?= e($_SESSION['token'] ?? '') ?>">
<table class="data-table">
<tr>
<th>Материал</th>
<th>Количество</th>
</tr>
<?php foreach($materials as $m): ?>
<tr>
<td><?= e($m['name']) ?></td>
<td>
<input type="number" name="quantity[]" min="0" value="0">
<input type="hidden" name="material_id[]" value="<?= e($m['id']) ?>">
</td>
</tr>
<?php endforeach; ?>
</table>
<button type="submit" class="btn">Создать заявку</button>
</form>
</section>

<section class="section">
<h2>Мои заявки</h2>
<table class="data-table">
<tr>
<th>ID</th>
<th>Статус</th>
<th>Дата</th>
<th>Материалы</th>
<th>Оплата</th>
</tr>
<?php foreach($my_requests as $r): ?>
<tr>
<td><?= e($r['id']) ?></td>
<td><?= e($r['status']) ?></td>
<td><?= e($r['created_at']) ?></td>
<td><?= $r['items'] ?? '-' ?></td>
<td>

<?php
$stmt_pay = $pdo->prepare("
    SELECT status 
    FROM payments 
    WHERE request_id=? AND user_id=? 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt_pay->execute([$r['id'], $user_id]);
$payment = $stmt_pay->fetchColumn();
?>

<?php if($r['status'] === 'Одобрено'): ?>

    <?php if($payment === 'success'): ?>
        <span style="color:green;font-weight:600;">✔ Оплачено</span>

    <?php elseif($payment === 'fail'): ?>
        <span style="color:red;font-weight:600;">✖ Ошибка оплаты</span>

    <?php else: ?>
        <button class="btn-pay"
                onclick="openPaymentModal(<?= $r['id'] ?>)">
            💳 Оплатить
        </button>
    <?php endif; ?>

<?php else: ?>
    <span style="color:#888;font-size:13px;">
        Оплата доступна только после одобрения
    </span>
<?php endif; ?>

</td>

<style>

.btn-pay{
    background:#2ecc71;
    color:white;
    border:none;
    padding:8px 14px;
    border-radius:10px;
    cursor:pointer;
    font-weight:600;
    transition:.3s;
}

.btn-pay:hover{
    background:#27ae60;
}

/* ===== MODAL ===== */

.payment-modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.6);
    justify-content:center;
    align-items:center;
}

.payment-box{
    background:white;
    padding:30px;
    border-radius:20px;
    width:350px;
    text-align:center;
    box-shadow:0 20px 50px rgba(0,0,0,0.2);
}

.payment-box input{
    width:100%;
    padding:10px;
    margin:15px 0;
    border-radius:10px;
    border:1px solid #ddd;
}

.payment-box button{
    width:100%;
}

.close-modal{
    background:#ccc;
    margin-top:10px;
}

</style>


<!-- PAYMENT MODAL -->

<div class="payment-modal" id="paymentModal">
    <div class="payment-box">

        <h3>Оплата заявки</h3>

        <form method="post">
            <input type="hidden" name="token" value="<?= e($_SESSION['token'] ?? '') ?>">
            <input type="hidden" name="request_id" id="modalRequestId">

            <label>Сумма</label>
            <input type="number" name="amount" required min="1">

            <button type="submit" class="btn-pay">
                💳 Подтвердить оплату
            </button>

        </form>

        <button class="close-modal" onclick="closePaymentModal()">
            Закрыть
        </button>

    </div>
</div>

</tr>
<?php endforeach; ?>
</table>
</section>

<script>

function openPaymentModal(requestId){
    document.getElementById("modalRequestId").value = requestId;
    document.getElementById("paymentModal").style.display = "flex";
}

function closePaymentModal(){
    document.getElementById("paymentModal").style.display = "none";
}

// Закрытие при клике вне окна
document.getElementById("paymentModal").addEventListener("click", function(e){
    if(e.target === this){
        closePaymentModal();
    }
});

</script>

<?php require "../includes/footer.php"; ?>
