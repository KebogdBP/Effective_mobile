<?php

class Task
{
    private $db;
    private $table = 'tasks';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Получить все задачи
     */
    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Получить задачу по ID
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Создать новую задачу
     */
    public function create($data)
    {
        // Валидация
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $sql = "INSERT INTO {$this->table} (title, description, status) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $status = $data['status'] ?? 'pending';
        $description = $data['description'] ?? null;
        
        try {
            $stmt->execute([
                $data['title'],
                $description,
                $status
            ]);
            
            $id = $this->db->lastInsertId();
            return ['success' => true, 'data' => $this->getById($id)];
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['database' => $e->getMessage()]];
        }
    }

    /**
     * Обновить задачу
     */
    public function update($id, $data)
    {
        // Проверяем существование задачи
        $task = $this->getById($id);
        if (!$task) {
            return ['success' => false, 'errors' => ['id' => 'Task not found']];
        }

        // Валидация
        $errors = $this->validate($data, true);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $fields = [];
        $values = [];

        if (isset($data['title'])) {
            $fields[] = 'title = ?';
            $values[] = $data['title'];
        }
        if (isset($data['description'])) {
            $fields[] = 'description = ?';
            $values[] = $data['description'];
        }
        if (isset($data['status'])) {
            $fields[] = 'status = ?';
            $values[] = $data['status'];
        }

        if (empty($fields)) {
            return ['success' => true, 'data' => $task];
        }

        $fields[] = 'updated_at = CURRENT_TIMESTAMP';
        $values[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        try {
            $stmt->execute($values);
            return ['success' => true, 'data' => $this->getById($id)];
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['database' => $e->getMessage()]];
        }
    }

    /**
     * Удалить задачу
     */
    public function delete($id)
    {
        // Проверяем существование задачи
        $task = $this->getById($id);
        if (!$task) {
            return ['success' => false, 'errors' => ['id' => 'Task not found']];
        }

        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        
        try {
            $stmt->execute([$id]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['database' => $e->getMessage()]];
        }
    }

    /**
     * Валидация данных
     */
    private function validate($data, $isUpdate = false)
    {
        $errors = [];

        // Title валидация
        if (!$isUpdate || isset($data['title'])) {
            if (empty($data['title'])) {
                $errors['title'] = 'Title field is required';
            } elseif (strlen($data['title']) > 255) {
                $errors['title'] = 'Title must not exceed 255 characters';
            }
        }

        // Status валидация
        if (isset($data['status'])) {
            $validStatuses = ['pending', 'in_progress', 'completed'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors['status'] = 'Status must be one of: pending, in_progress, completed';
            }
        }

        return $errors;
    }
}
