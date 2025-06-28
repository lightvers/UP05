<?php
class User {
    private $conn;
    private $table = 'users';

    // Свойства для отладки SQL
    public $last_sql;
    public $last_params;

    public $id;
    public $username;
    public $password;
    public $role;
    public $email;
    public $last_name;
    public $first_name;
    public $middle_name;
    public $phone;
    public $classroom;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username, $password) {
        $query = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM {$this->table} ORDER BY last_name, first_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Ошибка в запросе User::getAll - " . $e->getMessage());
            // Возвращаем пустой результат в случае ошибки
            return $this->conn->query("SELECT 1 FROM {$this->table} LIMIT 0");
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if($row) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->role = $row['role'];
                $this->email = $row['email'];
                $this->last_name = $row['last_name'];
                $this->first_name = $row['first_name'];
                $this->middle_name = $row['middle_name'];
                $this->phone = $row['phone'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Ошибка в запросе User::getById - " . $e->getMessage());
            return false;
        }
    }

    public function create() {
        try {
            $query = "INSERT INTO {$this->table}
                      SET username = :username,
                          password = :password,
                          role = :role,
                          email = :email,
                          last_name = :last_name,
                          first_name = :first_name,
                          middle_name = :middle_name,
                          phone = :phone";

            $stmt = $this->conn->prepare($query);

            $this->password = password_hash($this->password, PASSWORD_DEFAULT);

            $stmt->bindParam(':username', $this->username);
            $stmt->bindParam(':password', $this->password);
            $stmt->bindParam(':role', $this->role);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':last_name', $this->last_name);
            $stmt->bindParam(':first_name', $this->first_name);
            $stmt->bindParam(':middle_name', $this->middle_name);
            $stmt->bindParam(':phone', $this->phone);

            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Ошибка в запросе User::create - " . $e->getMessage());
            return false;
        }
    }

    public function update() {
        try {
            $query = "UPDATE {$this->table}
                      SET username = :username,
                          role = :role,
                          email = :email,
                          last_name = :last_name,
                          first_name = :first_name,
                          middle_name = :middle_name,
                          phone = :phone
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':username', $this->username);
            $stmt->bindParam(':role', $this->role);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':last_name', $this->last_name);
            $stmt->bindParam(':first_name', $this->first_name);
            $stmt->bindParam(':middle_name', $this->middle_name);
            $stmt->bindParam(':phone', $this->phone);
            $stmt->bindParam(':id', $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Ошибка в запросе User::update - " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword() {
        try {
            $query = "UPDATE {$this->table} SET password = :password WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $this->password);
            $stmt->bindParam(':id', $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Ошибка в запросе User::updatePassword - " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
    try {
        if (empty($this->id)) {
            throw new Exception("ID пользователя не указан");
        }

        // Начинаем транзакцию
        $this->conn->beginTransaction();

        // 1. Удаляем пользователя
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);

        // Проверяем, была ли удалена запись
        if ($stmt->rowCount() === 0) {
            throw new Exception("Пользователь не найден");
        }

        // Подтверждаем транзакцию
        $this->conn->commit();
        return true;
    } catch (Exception $e) {
        // Откатываем транзакцию при ошибке
        if ($this->conn->inTransaction()) {
            $this->conn->rollBack();
        }
        error_log("Ошибка при удалении пользователя: " . $e->getMessage());
        throw $e; // Пробрасываем исключение дальше
    }
}

    public function count() {
        try {
            $query = "SELECT COUNT(*) as count FROM {$this->table}";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['count'];
        } catch (PDOException $e) {
            error_log("Ошибка в запросе User::count - " . $e->getMessage());
            return 0;
        }
    }

    public function getAssignedEquipment() {
        try {
            $query = "SELECT e.id, e.name, e.inventory_number, s.name as status_name
                      FROM equipment e
                      JOIN reference_items s ON e.status_id = s.id AND s.type = 'status'
                      WHERE e.responsible_user_id = :user_id
                      ORDER BY e.name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $this->id);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Ошибка в запросе User::getAssignedEquipment - " . $e->getMessage());
            // Возвращаем пустой результат в случае ошибки
            return $this->conn->query("SELECT 1 LIMIT 0");
        }
    }

    /**
     * Версия метода getPaginated с возвратом отладочной информации
     */
    public function getPaginatedDebug($page = 1, $perPage = 10, $search = '', $role = '') {
        try {
            // Базовый запрос
            $sql = "SELECT * FROM {$this->table}";
            $params = [];
            $conditions = [];

            // Простой поиск по всем текстовым полям
            if (!empty($search)) {
                $conditions[] = "(
                    username LIKE ? OR
                    last_name LIKE ? OR
                    first_name LIKE ? OR
                    middle_name LIKE ? OR
                    CONCAT(last_name, ' ', first_name) LIKE ? OR
                    CONCAT(last_name, ' ', first_name, ' ', middle_name) LIKE ?
                )";

                $searchParam = "%{$search}%";
                // Добавляем параметр 6 раз - для каждого поля
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            // Фильтр по роли - простое равенство
            if (!empty($role)) {
                $conditions[] = "role = ?";
                $params[] = $role;
            }

            // Добавляем условия
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            // Добавляем сортировку и пагинацию
            $sql .= " ORDER BY last_name, first_name LIMIT ? OFFSET ?";

            // Добавляем параметры для пагинации
            $params[] = (int)$perPage;
            $params[] = (int)(($page - 1) * $perPage);

            // Сохраняем для отладки
            $this->last_sql = $sql;
            $this->last_params = $params;

            // Подготавливаем и выполняем запрос
            $stmt = $this->conn->prepare($sql);

            // Привязываем все параметры по индексу (начиная с 1)
            foreach ($params as $index => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($index + 1, $value, $paramType);
            }

            $stmt->execute();

            return [
                'stmt' => $stmt,
                'sql' => $sql,
                'params' => $params
            ];
        } catch (PDOException $e) {
            error_log("Ошибка в отладке поиска пользователей: " . $e->getMessage());
            // Возвращаем пустой результат в случае ошибки
            return [
                'stmt' => $this->conn->query("SELECT * FROM {$this->table} WHERE 1=0"),
                'sql' => $sql ?? 'ERROR',
                'params' => $params ?? []
            ];
        }
    }

    /**
     * Получает список пользователей с пагинацией и фильтрацией
     */
    public function getPaginated($page = 1, $perPage = 10, $search = '', $role = '') {
        try {
            // Базовый запрос
            $sql = "SELECT * FROM {$this->table}";
            $params = [];
            $conditions = [];

            // Простой поиск по всем текстовым полям
            if (!empty($search)) {
                $conditions[] = "(
                    username LIKE ? OR
                    last_name LIKE ? OR
                    first_name LIKE ? OR
                    middle_name LIKE ? OR
                    CONCAT(last_name, ' ', first_name) LIKE ? OR
                    CONCAT(last_name, ' ', first_name, ' ', middle_name) LIKE ?
                )";

                $searchParam = "%{$search}%";
                // Добавляем параметр 6 раз - для каждого поля
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            // Фильтр по роли - простое равенство
            if (!empty($role)) {
                $conditions[] = "role = ?";
                $params[] = $role;
            }

            // Добавляем условия
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            // Добавляем сортировку и пагинацию
            $sql .= " ORDER BY last_name, first_name LIMIT ? OFFSET ?";

            // Добавляем параметры для пагинации
            $params[] = (int)$perPage;
            $params[] = (int)(($page - 1) * $perPage);

            // Сохраняем для отладки
            $this->last_sql = $sql;
            $this->last_params = $params;

            // Подготавливаем и выполняем запрос
            $stmt = $this->conn->prepare($sql);

            // Привязываем все параметры по индексу (начиная с 1)
            foreach ($params as $index => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($index + 1, $value, $paramType);
            }

            $stmt->execute();

            // Логирование результатов
            if ($stmt->rowCount() === 0) {
                error_log("Поиск пользователей (getPaginated) не вернул результатов. SQL: {$sql}, Параметры: " . json_encode($params));
            } else {
                error_log("Поиск пользователей (getPaginated) вернул {$stmt->rowCount()} результатов");
            }

            return $stmt;
        } catch (PDOException $e) {
            error_log("Ошибка в поиске пользователей (getPaginated): " . $e->getMessage());
            // Возвращаем пустой результат в случае ошибки
            return $this->conn->query("SELECT * FROM {$this->table} WHERE 1=0");
        }
    }

    /**
     * Подсчитывает общее количество пользователей с учетом фильтров
     */
    public function countAll($search = '', $role = '') {
        try {
            // Базовый запрос
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $params = [];
            $conditions = [];

            // Простой поиск по всем текстовым полям
            if (!empty($search)) {
                $conditions[] = "(
                    username LIKE ? OR
                    last_name LIKE ? OR
                    first_name LIKE ? OR
                    middle_name LIKE ? OR
                    CONCAT(last_name, ' ', first_name) LIKE ? OR
                    CONCAT(last_name, ' ', first_name, ' ', middle_name) LIKE ?
                )";

                $searchParam = "%{$search}%";
                // Добавляем параметр 6 раз - для каждого поля
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            // Фильтр по роли - простое равенство
            if (!empty($role)) {
                $conditions[] = "role = ?";
                $params[] = $role;
            }

            // Добавляем условия
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            // Сохраняем для отладки
            $this->last_sql = $sql;
            $this->last_params = $params;

            // Подготавливаем и выполняем запрос
            $stmt = $this->conn->prepare($sql);

            // Привязываем все параметры по индексу (начиная с 1)
            foreach ($params as $index => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($index + 1, $value, $paramType);
            }

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Логирование результатов
            if (!isset($result['total'])) {
                error_log("Подсчет пользователей (countAll) вернул некорректный результат. SQL: {$sql}");
                return 0;
            }

            error_log("Подсчет пользователей (countAll) вернул: {$result['total']}");
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Ошибка в подсчете пользователей (countAll): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Получает список всех доступных ролей пользователей
     * @return array Массив с доступными ролями
     */
    public function getAllRoles() {
        return [
            'admin' => 'Администратор',
            'teacher' => 'Преподаватель',
            'employee' => 'Сотрудник'
        ];
    }

    public function updateProfile() {
        try {
            $query = "UPDATE {$this->table}
                      SET email = :email,
                          phone = :phone
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':phone', $this->phone);
            $stmt->bindParam(':id', $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Ошибка в запросе User::updateProfile - " . $e->getMessage());
            return false;
        }
    }
}
?>
