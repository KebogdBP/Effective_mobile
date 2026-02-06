<?php

require_once 'config.php';
require_once 'Database.php';

echo "Инициализация базы данных...\n";

try {
    $db = Database::getInstance();
    
    if ($db->createTasksTable()) {
        echo "✓ Таблица 'tasks' успешно создана!\n";
        echo "✓ База данных готова к использованию.\n";
        echo "\nЗапустите сервер командой:\n";
        echo "php -S localhost:8000\n";
    } else {
        echo "✗ Ошибка при создании таблицы.\n";
    }
} catch (Exception $e) {
    echo "✗ Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}
