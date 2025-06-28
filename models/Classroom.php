<?php
class Classroom {
    private $conn;
    private $table = 'classrooms';

    public $id;
    public $name;
    public $short_name;
    public $responsible_user_id;
    public $temp_responsible_user_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        try {
            $query = "SELECT c.*,
                      u.last_name as responsible_last_name,
                      u.first_name as responsible_first_name
                      FROM {$this->table} c
                      LEFT JOIN users u ON c.responsible_user_id = u.id
                      ORDER BY c.name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Ошибка в запросе Classroom::getAll - " . $e->getMessage());
            // Создаем пустой результат в случае ошибки
            return $this->conn->query("SELECT 1 FROM {$this->table} LIMIT 0");
        }
    }

    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->short_name = $row['short_name'];
            $this->responsible_user_id = $row['responsible_user_id'];
            $this->temp_responsible_user_id = $row['temp_responsible_user_id'];
            return true;
        }
        return false;
    }
public function getByResponsibleUser($user_id) {
    try {
        $query = "SELECT c.*, 
                  u.last_name as responsible_last_name,
                  u.first_name as responsible_first_name
                  FROM {$this->table} c
                  LEFT JOIN users u ON c.responsible_user_id = u.id
                  WHERE c.responsible_user_id = ? OR c.temp_responsible_user_id = ?
                  ORDER BY c.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $user_id]);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Ошибка в методе Classroom::getByResponsibleUser - " . $e->getMessage());
        // Возвращаем пустой результат в случае ошибки
        return $this->conn->query("SELECT 1 FROM {$this->table} LIMIT 0");
    }
}
    public function create() {
        $query = "INSERT INTO classrooms
                  (name, short_name, responsible_user_id, temp_responsible_user_id, created_at, updated_at)
                  VALUES
                  (:name, :short_name, :responsible_user_id, :temp_responsible_user_id, NOW(), NOW())";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':name' => $this->name,
            ':short_name' => $this->short_name,
            ':responsible_user_id' => $this->responsible_user_id,
            ':temp_responsible_user_id' => $this->temp_responsible_user_id
        ]);
    }

    public function update() {
        $query = "UPDATE {$this->table}
                  SET name = :name,
                      short_name = :short_name,
                      responsible_user_id = :responsible_user_id,
                      temp_responsible_user_id = :temp_responsible_user_id
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':short_name', $this->short_name);
        $stmt->bindParam(':responsible_user_id', $this->responsible_user_id);
        $stmt->bindParam(':temp_responsible_user_id', $this->temp_responsible_user_id);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM classrooms WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function count() {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    public function getWithEquipmentCount() {
        $query = "SELECT c.*,
                  COUNT(e.id) as equipment_count,
                  u.last_name as responsible_last_name,
                  u.first_name as responsible_first_name
                  FROM {$this->table} c
                  LEFT JOIN equipment e ON c.id = e.current_classroom_id
                  LEFT JOIN users u ON c.responsible_user_id = u.id
                  GROUP BY c.id
                  ORDER BY c.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Получает список аудиторий с фильтрацией
     * @param string $search строка для поиска по названию или краткому названию
     * @param string|int $responsible_id ID ответственного пользователя
     * @param int $min_equipment минимальное количество оборудования
     * @return PDOStatement
     */
    public function getFilteredWithEquipmentCount($search = '', $responsible_id = '', $min_equipment = 0) {
        $query = "SELECT c.*,
                  COUNT(e.id) as equipment_count,
                  u.last_name as responsible_last_name,
                  u.first_name as responsible_first_name
                  FROM {$this->table} c
                  LEFT JOIN equipment e ON c.id = e.current_classroom_id
                  LEFT JOIN users u ON c.responsible_user_id = u.id
                  WHERE 1=1";

        $params = [];

        if (!empty($search)) {
            $query .= " AND (c.name LIKE :search OR c.short_name LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if (!empty($responsible_id)) {
            $query .= " AND c.responsible_user_id = :responsible_id";
            $params[':responsible_id'] = $responsible_id;
        }

        $query .= " GROUP BY c.id";

        if ($min_equipment > 0) {
            $query .= " HAVING equipment_count >= :min_equipment";
            $params[':min_equipment'] = $min_equipment;
        }

        $query .= " ORDER BY c.name";

        try {
            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Ошибка в запросе Classroom::getFilteredWithEquipmentCount - " . $e->getMessage());
            // Создаем пустой результат в случае ошибки
            return $this->conn->query("SELECT 1 FROM {$this->table} LIMIT 0");
        }
    }

    public function getEquipment($classroom_id) {
        $query = "SELECT e.id, e.name, e.inventory_number, s.name as status_name
                  FROM equipment e
                  JOIN reference_items s ON e.status_id = s.id AND s.type = 'status'
                  WHERE e.current_classroom_id = ?
                  ORDER BY e.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$classroom_id]);
        return $stmt;
    }

    /**
     * Подсчитывает количество аудиторий, закрепленных за пользователем
     * @param int $user_id ID пользователя
     * @return int Количество аудиторий
     */
    public function countByResponsibleUser($user_id) {
        try {
            $query = "SELECT COUNT(*) AS count FROM {$this->table} WHERE responsible_user_id = ? OR temp_responsible_user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user_id, $user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['count'] ?? 0);
        } catch (PDOException $e) {
            error_log("Ошибка в методе Classroom::countByResponsibleUser - " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Очищает ответственного пользователя для всех аудиторий
     * @param int $user_id ID пользователя
     * @return bool Результат операции
     */
    public function clearResponsibleUser($user_id) {
        try {
            $query = "UPDATE {$this->table} SET
                     responsible_user_id = CASE WHEN responsible_user_id = ? THEN NULL ELSE responsible_user_id END,
                     temp_responsible_user_id = CASE WHEN temp_responsible_user_id = ? THEN NULL ELSE temp_responsible_user_id END
                     WHERE responsible_user_id = ? OR temp_responsible_user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user_id, $user_id, $user_id, $user_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Ошибка в методе Classroom::clearResponsibleUser - " . $e->getMessage());
            return false;
        }
    }
    // Добавьте этот метод в класс Classroom
public function deleteWithEquipment($id) {
    try {
        $this->conn->beginTransaction();
        
        // 1. Перемещаем оборудование в null (не назначено)
        $equipment = new Equipment($this->conn);
        $equipment->moveAllFromClassroom($id, null);
        
        // 2. Удаляем аудиторию
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        $this->conn->commit();
        return true;
    } catch (Exception $e) {
        $this->conn->rollBack();
        error_log("Ошибка при удалении аудитории: " . $e->getMessage());
        return false;
    }
}

}
?>
