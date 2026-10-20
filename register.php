<?php
require "../config/db.php";
require_once __DIR__.'/../config/config.php';
session_start();

if($_POST){
    $username = trim($_POST['login']);
    $password = trim($_POST['password']);
    $fullname = trim($_POST['name']);
    $company = trim($_POST['company']);

    if(!$username || !$password){
        die("Логин и пароль обязательны");
    }

    $pass = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users(username,password,role_id,fullname,company)
        VALUES(?,?,?,?,?)
    ");

    $stmt->execute([
        $username,
        $pass,
        3,
        $fullname,
        $company
    ]);

    $user_id = $pdo->lastInsertId();

    $role_stmt = $pdo->prepare("SELECT name FROM roles WHERE id=3");
    $role_stmt->execute();
    $role = $role_stmt->fetchColumn() ?: 'Пользователь';

    $_SESSION['user'] = $username;
    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = $role;

    header("Location: ../pages/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Регистрация</title>

<style>

/* ===== VARIABLES (как у login) ===== */

:root {
    --bg-main: #fbfaf9;
    --card-bg: #ffffff;
    --text-main: #1a1a1a;

    --accent-main: #5c1522;
    --accent-light: #8e2c3c;

    --border-soft: rgba(0,0,0,0.06);
}

/* ===== BASE ===== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: var(--bg-main);
    color: var(--text-main);
    font-family: "Inter", "Poppins", sans-serif;

    min-height: 100vh;

    display: flex;
    align-items: center;
    justify-content: center;
}

/* ===== CARD ===== */

.section.form-card {
    width: 100%;
    max-width: 480px;

    background: var(--card-bg);
    border-radius: 22px;

    padding: 50px 45px;

    box-shadow: 0 20px 60px rgba(0,0,0,0.08);
    border: 1px solid var(--border-soft);

    text-align: center;
}

/* ===== TITLE (как login) ===== */

.section h2 {
    font-family: "Playfair Display", serif;
    font-size: 28px;
    margin-bottom: 30px;

    background: linear-gradient(
        135deg,
        var(--accent-main),
        var(--accent-light)
    );

    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;

    font-weight: 700;
}

/* ===== FORM ===== */

.main-form {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.main-form input {
    padding: 14px 22px;

    border-radius: 999px;
    border: 1px solid var(--border-soft);

    font-size: 15px;

    background: #fff;
}

.main-form input:focus {
    outline: none;
    border-color: var(--accent-main);
    box-shadow: 0 0 0 3px rgba(92,21,34,0.08);
}

/* ===== BUTTON ===== */

.main-form button {

    background: linear-gradient(
        135deg,
        var(--accent-main),
        var(--accent-light)
    );

    color: #fff;

    border: none;

    padding: 14px;
    border-radius: 999px;

    font-weight: 600;
    letter-spacing: .05em;
    text-transform: uppercase;
    font-size: 13px;

    cursor: pointer;

    box-shadow: 0 12px 30px rgba(92,21,34,.25);

    transition: .4s;
}

.main-form button:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 40px rgba(92,21,34,.35);
}

/* ===== LINK ===== */

.section p {
    margin-top: 22px;
    font-size: 14px;
}

.section a {
    color: var(--accent-main);
    font-weight: 600;
    text-decoration: none;
}

.section a:hover {
    text-decoration: underline;
}

</style>

</head>
<body>

<section class="section form-card">

    <h2>Регистрация</h2>

    <form method="post" class="main-form">
        <input name="login" placeholder="Логин" required>
        <input name="password" type="password" placeholder="Пароль" required>
        <input name="name" placeholder="ФИО">
        <input name="company" placeholder="Организация">
        <button type="submit">Зарегистрироваться</button>
    </form>

    <p>
        Уже есть аккаунт?
        <a href="login.php">Войдите</a>
    </p>

</section>

</body>
</html>