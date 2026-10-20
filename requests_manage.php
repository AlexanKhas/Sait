<?php
require "../includes/auth.php";
require_once __DIR__.'/../config/config.php';
if($_SESSION['role'] !== 'Администратор') exit;

require "../includes/header.php";
require "../config/db.php";

$message = '';

// Обработка одобрения заявки
if(isset($_GET['ok'])) {
    $request_id = intval($_GET['ok']);

    $items = $pdo->prepare("SELECT * FROM request_items WHERE request_id=?");
    $items->execute([$request_id]);
    $items = $items->fetchAll(PDO::FETCH_ASSOC);

    $enough = true;
    foreach($items as $item){
        $stmt = $pdo->prepare("SELECT quantity FROM stock WHERE material_id=?");
        $stmt->execute([$item['material_id']]);
        $available = $stmt->fetchColumn() ?? 0;

        if($available < $item['quantity']){
            $enough = false;
        }
    }

    if($enough){
        $pdo->prepare("UPDATE requests SET status='Одобрено' WHERE id=?")->execute([$request_id]);

        foreach($items as $item){
            $pdo->prepare("UPDATE stock SET quantity = quantity - ? WHERE material_id=?")
                ->execute([$item['quantity'], $item['material_id']]);

            $pdo->prepare("INSERT INTO expense (material_id, quantity, request_id, date)
                VALUES (?, ?, ?, CURDATE())")
                ->execute([$item['material_id'], $item['quantity'], $request_id]);
        }

        $message = "Заявка №$request_id одобрена и материалы списаны.";
    } else {
        $message = "Недостаточно товара на складе для заявки №$request_id.";
    }
}

// Обработка отклонения заявки
if(isset($_GET['no'])){
    $request_id = intval($_GET['no']);
    $pdo->prepare("UPDATE requests SET status='Отклонено' WHERE id=?")->execute([$request_id]);
    $message = "Заявка №$request_id отклонена.";
}

// Получаем заявки с пользователями
$rows_stmt = $pdo->prepare("
    SELECT 
        r.id AS request_id,
        r.status,
        r.created_at,
        u.username,
        u.company,
        GROUP_CONCAT(
            CONCAT(m.name,' (',ri.quantity,')')
            SEPARATOR '<br>'
        ) AS materials
    FROM requests r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN request_items ri ON r.id = ri.request_id
    LEFT JOIN materials m ON ri.material_id = m.id
    GROUP BY r.id, r.status, r.created_at, u.username, u.company
    ORDER BY r.id DESC
");
$rows_stmt->execute();
$rows = $rows_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="section">
    <h2>Управление заявками</h2>

    <?php if($message): ?>
        <p class="alert <?= strpos($message,'Недостаточно')!==false?'error':'success' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <table class="data-table">
        <tr>
            <th>Заявка</th>
            <th>Пользователь</th>
            <th>Организация</th>
            <th>Материалы</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>

        <?php foreach($rows as $r): ?>
        <tr>
            <td><?= $r['request_id'] ?></td>
            <td><?= htmlspecialchars($r['username']) ?></td>
            <td><?= htmlspecialchars($r['company'] ?? '-') ?></td>
            <td><?= $r['materials'] ?? '-' ?></td>
            <td><?= htmlspecialchars($r['status']) ?></td>
            <td>
                <?php if($r['status'] === 'Новая'): ?>
                    <a href="?ok=<?= $r['request_id'] ?>" class="btn-main" style="margin-right:15px; font-size:22px;">✔</a>
                    <a href="?no=<?= $r['request_id'] ?>" class="btn-main" style="font-size:22px;">✖</a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</section>

<?php require "../includes/footer.php"; ?>
