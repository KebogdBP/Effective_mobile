<?php

// Конфигурация базы данных
define('DB_TYPE', 'sqlite'); // sqlite или mysql
define('DB_PATH', __DIR__ . '/tasks.db'); // для SQLite

// Для MySQL раскомментируйте:
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'todo_api');
// define('DB_USER', 'root');
// define('DB_PASS', '');

// Настройки приложения
define('APP_DEBUG', true);

// CORS настройки
define('ALLOWED_ORIGINS', '*');

// Часовой пояс
date_default_timezone_set('Europe/Moscow');

// Кодировка
header('Content-Type: application/json; charset=utf-8');
