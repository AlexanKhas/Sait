<?php
require "../includes/header.php";
require "../config/db.php";
require_once __DIR__.'/../config/config.php';

$message = '';

// Списание материалов
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $material_id = intval($_POST['material']);
    $quantity = intval($_POST['quantity']);
    $request_id = $_POST['request_id'] ?: null;

    $stmt = $pdo->prepare("SELECT quantity FROM stock WHERE material_id=?");
    $stmt->execute([$material_id]);
    $available = $stmt->fetchColumn() ?? 0;

    if($available >= $quantity){
        $pdo->prepare("INSERT INTO expense (material_id, quantity, request_id, date)
            VALUES (?, ?, ?, CURDATE())")->execute([$material_id, $quantity, $request_id]);

        $pdo->prepare("UPDATE stock SET quantity = quantity - ? WHERE material_id=?")
            ->execute([$quantity, $material_id]);

        $message = "Расход оформлен.";
    } else {
        $message = "Недостаточно на складе.";
    }
}

// Список материалов для формы
$materials_stmt = $pdo->prepare("
    SELECT m.id, m.name, COALESCE(s.quantity,0) as quantity
    FROM materials m
    LEFT JOIN stock s ON m.id = s.material_id
    WHERE m.is_deleted = 0
    ORDER BY m.name ASC
");
$materials_stmt->execute();
$materials = $materials_stmt->fetchAll(PDO::FETCH_ASSOC);

// История расходов
$expenses = $pdo->query("
    SELECT e.id, m.name AS material_name, e.quantity, e.date, e.request_id
    FROM expense e
    LEFT JOIN materials m ON e.material_id = m.id
    ORDER BY e.date DESC, e.id DESC
")->fetchAll();
?>

<section class="section">
    <h2>Расход материалов</h2>

    <?php if($message): ?>
        <p class="alert <?= strpos($message,'Недостаточно')!==false?'error':'success' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" class="main-form">
        <label>Материал</label>
        <select name="material" required>
            <?php foreach($materials as $m): ?>
                <option value="<?= $m['id'] ?>">
                    <?= htmlspecialchars($m['name']) ?> (на складе: <?= $m['quantity'] ?? 0 ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label>Количество</label>
        <input type="number" name="quantity" min="1" required>

        <label>Номер заявки (если списывается по заявке)</label>
        <input type="number" name="request_id">

        <button type="submit" class="btn">Списать</button>
    </form>

    <h3>История расходов</h3>
    <table class="data-table">
        <tr>
            <th>Дата</th>
            <th>Материал</th>
            <th>Количество</th>
            <th>По заявке</th>
        </tr>
        <?php foreach($expenses as $e): ?>
        <tr>
            <td><?= htmlspecialchars($e['date']) ?></td>
            <td><?= htmlspecialchars($e['material_name']) ?></td>
            <td><?= htmlspecialchars($e['quantity']) ?></td>
            <td><?= $e['request_id'] ? '№'.$e['request_id'] : '-' ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</section>

<?php require "../includes/footer.php"; ?>
