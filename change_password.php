<?php
session_start();
require_once __DIR__.'/../config/config.php';
require "../config/db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: profile.php");
    exit;
}


$old = $_POST['old_password'] ?? '';
$new = $_POST['new_password'] ?? '';

if($old=='' || $new==''){
    $_SESSION['pass_error'] = "Заполните все поля";
    header("Location: profile.php");
    exit;
}


/* Берём пароль */
$stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");
$stmt->execute([$user_id]);

$dbPass = $stmt->fetchColumn();


/* Проверка старого */
if(!password_verify($old,$dbPass)){
    $_SESSION['pass_error'] = "Неверный старый пароль";
    header("Location: profile.php");
    exit;
}


/* Новый хэш */
$newHash = password_hash($new,PASSWORD_DEFAULT);


/* Обновляем */
$stmt = $pdo->prepare("
    UPDATE users SET password=? WHERE id=?
");

$stmt->execute([$newHash,$user_id]);


/* Сообщение успеха */
$_SESSION['pass_success'] = "Пароль успешно изменён";

header("Location: profile.php");
exit;
