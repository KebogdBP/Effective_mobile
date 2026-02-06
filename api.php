<?php

require_once 'config.php';
require_once 'Database.php';
require_once 'Task.php';

// CORS заголовки
header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGINS);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Получение метода и действия
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

// Получение JSON данных
$inputData = json_decode(file_get_contents('php://input'), true) ?? [];

// Инициализация модели
$task = new Task();

// Обработка запросов
try {
    switch ($action) {
        case 'list':
            // GET /api.php?action=list - Получить все задачи
            if ($method !== 'GET') {
                sendError('Method not allowed', 405);
            }
            $tasks = $task->getAll();
            sendResponse($tasks);
            break;

        case 'get':
            // GET /api.php?action=get&id=1 - Получить задачу по ID
            if ($method !== 'GET') {
                sendError('Method not allowed', 405);
            }
            if (!$id) {
                sendError('ID is required', 400);
            }
            $result = $task->getById($id);
            if (!$result) {
                sendError('Task not found', 404);
            }
            sendResponse($result);
            break;

        case 'create':
            // POST /api.php?action=create - Создать задачу
            if ($method !== 'POST') {
                sendError('Method not allowed', 405);
            }
            $result = $task->create($inputData);
            if (!$result['success']) {
                sendError($result['errors'], 422);
            }
            sendResponse($result['data'], 201);
            break;

        case 'update':
            // PUT /api.php?action=update&id=1 - Обновить задачу
            if ($method !== 'PUT') {
                sendError('Method not allowed', 405);
            }
            if (!$id) {
                sendError('ID is required', 400);
            }
            $result = $task->update($id, $inputData);
            if (!$result['success']) {
                $code = isset($result['errors']['id']) ? 404 : 422;
                sendError($result['errors'], $code);
            }
            sendResponse($result['data']);
            break;

        case 'delete':
            // DELETE /api.php?action=delete&id=1 - Удалить задачу
            if ($method !== 'DELETE') {
                sendError('Method not allowed', 405);
            }
            if (!$id) {
                sendError('ID is required', 400);
            }
            $result = $task->delete($id);
            if (!$result['success']) {
                sendError($result['errors'], 404);
            }
            http_response_code(204);
            exit();

        default:
            sendError('Invalid action. Available actions: list, get, create, update, delete', 400);
    }
} catch (Exception $e) {
    if (APP_DEBUG) {
        sendError([
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    } else {
        sendError('Internal server error', 500);
    }
}

/**
 * Отправить успешный ответ
 */
function sendResponse($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

/**
 * Отправить ответ с ошибкой
 */
function sendError($message, $code = 400)
{
    http_response_code($code);
    echo json_encode([
        'error' => true,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}
