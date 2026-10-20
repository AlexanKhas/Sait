<?php
session_start();
require "../config/db.php";
require_once __DIR__.'/../config/config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "
        SELECT users.*, roles.name AS role_name
        FROM users
        JOIN roles ON users.role_id = roles.id
        WHERE username=?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $login_success = false;

        if ($user['password'] === md5($password)) {
            $login_success = true;
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
            $update->execute([$new_hash, $user['id']]);
        } elseif (password_verify($password, $user['password'])) {
            $login_success = true;
        }

        if ($login_success) {
            $_SESSION['user'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role_name'];

            app_log("User {$user['id']} logged in");

            header("Location: ../pages/dashboard.php");
            exit();
        } else {
            $error = "Неверный логин или пароль";
        }
    } else {
        $error = "Неверный логин или пароль";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Вход</title>

<style>
:root {

    --bg-main: #fbfaf9;
    --bg-soft: #f2efed;
    --card-bg: #ffffff;

    --text-main: #1a1a1a;
    --text-muted: #6f6a67;

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

.form-card.login-box {
    width: 100%;
    max-width: 440px;

    background: var(--card-bg);
    border-radius: 22px;

    padding: 50px 45px;

    box-shadow: 0 20px 60px rgba(0,0,0,0.08);
    border: 1px solid var(--border-soft);

    text-align: center;
}

/* ===== TITLE ===== */

.form-card h2 {
    font-family: "Playfair Display", serif;
    font-size: 28px;
    margin-bottom: 10px;

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
    background: #fff;
    border-radius: 999px;
    padding: 14px 22px;
    border: 1px solid var(--border-soft);
    font-size: 15px;
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

.form-card p {
    margin-top: 22px;
    font-size: 14px;
    color: var(--text-muted);
}

.form-card a {
    color: var(--accent-main);
    font-weight: 600;
    text-decoration: none;
}

.form-card a:hover {
    text-decoration: underline;
}

/* ===== ERROR ===== */

.alert.error {
    margin-top: 18px;
    padding: 12px 16px;

    border-radius: 14px;

    background: rgba(229,57,53,0.08);
    color: #e53935;

    font-size: 14px;
}
</style>

</head>
<body class="login-page">

<div class="form-card login-box">
    <h2>Вход в систему</h2>

    <form method="post" class="main-form">
        <input type="text" name="username" placeholder="Логин" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit">Войти</button>
    </form>

    <p>Нет аккаунта? <a href="register.php">Зарегистрируйтесь</a></p>

    <?php if($error): ?>
        <p class="alert error"><?= $error ?></p>
    <?php endif; ?>
</div>

</body>
</html>