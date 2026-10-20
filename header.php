<?php
require_once __DIR__.'/../config/config.php';
if(session_status() === PHP_SESSION_NONE) session_start();

// Определяем текущую страницу
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>ИС МТО</title>
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <!-- PRELOADER -->
<div id="preloader">
    <div class="loader-logo">ИС МТО</div>
</div>

<!-- PAGE TRANSITION -->
<div id="page-transition"></div>

<header>
    <a href="../pages/dashboard.php" class="logo">
        ИС МТО
    </a>
    <button id="theme-toggle" class="theme-btn" title="Сменить тему">
        🌙
    </button>


    <nav>
        <!-- Кнопка Главная (не показываем на dashboard) -->
        <?php if($current_page !== 'dashboard.php'): ?>
            <a href="dashboard.php"><i class="fa-solid fa-house"></i> Главная</a>
        <?php endif; ?>

        <?php if(isset($_SESSION['user_id'])): ?>
            <!-- Меню для авторизованных пользователей -->
            <?php if($_SESSION['role'] === 'Администратор'): ?>
                <a href="materials.php"><i class="fa-solid fa-box"></i> Материалы</a>
                <a href="income.php"><i class="fa-solid fa-arrow-down"></i> Приход</a>
                <a href="expense.php"><i class="fa-solid fa-arrow-up"></i> Расход</a>
                <a href="stock.php"><i class="fa-solid fa-warehouse"></i> Склад</a>
                <a href="requests_manage.php"><i class="fa-solid fa-list-check"></i> Заявки</a>
            <?php else: ?>
                <a href="requests.php"><i class="fa-solid fa-file-circle-plus"></i> Заявка</a>
            <?php endif; ?>

            <!-- Личный кабинет -->
            <a href="profile.php"><i class="fa-solid fa-user"></i> Личный кабинет</a>

            <!-- Кнопка Выход убрана из меню -->
        <?php else: ?>
            <!-- Меню для гостей: Вход/Регистрация -->
            <a href="../auth/login.php"><i class="fa-solid fa-right-to-bracket"></i> Вход/Регистрация</a>
        <?php endif; ?>
    </nav>
</header>

<main>
