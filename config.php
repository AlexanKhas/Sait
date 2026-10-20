<?php
// config/config.php
define("BASE_URL", "/mto-system");


if(session_status() === PHP_SESSION_NONE) session_start();

date_default_timezone_set('Europe/Moscow'); // Настройка времени

define('LOG_FILE', __DIR__ . '/../logs/app.log');

// Функция логирования
function app_log($message) {
    $user_id = $_SESSION['user_id'] ?? 0;
    $username = $_SESSION['user'] ?? 'guest';
    $time = date('Y-m-d H:i:s');
    $entry = "[$time] [User $user_id - $username] $message\n";
    
    if(!file_exists(dirname(LOG_FILE))){
        mkdir(dirname(LOG_FILE), 0777, true);
    }

    file_put_contents(LOG_FILE, $entry, FILE_APPEND | LOCK_EX);
}
