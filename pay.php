<?php
require_once '../config/config.php';
require_once '../config/db.php';
session_start();

// Проверка авторизации
if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit;
}

// Обработка POST
if($_POST){
    // CSRF
    if(!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']){
        die('CSRF');
    }

    $amount = (int)$_POST['amount'];
    $status = rand(1,10) > 2 ? 'success' : 'fail'; // Sandbox

    $stmt = $pdo->prepare("INSERT INTO payments(user_id, amount, status) VALUES(?,?,?)");
    $stmt->execute([$_SESSION['user_id'], $amount, $status]);

    header("Location: dashboard.php");
    exit;
}

// Генерация токена CSRF
if(empty($_SESSION['token'])){
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оплата</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<section class="section">
    <h2>Оплата</h2>
    <form method="post" action="pay.php">
        <input type="number" name="amount" placeholder="Сумма" required>
        <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
        <button type="submit">Оплатить</button>
    </form>
</section>

<?php include '../includes/footer.php'; ?>
</body>
</html>
