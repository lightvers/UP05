<?php
class Equipment {
    private $conn;
    private $table = 'equipment';

    public $id;
    public $name;
    public $photo_path;
    public $inventory_number;
    public $current_classroom_id;
    public $responsible_user_id;
    public $temp_responsible_user_id;
    public $cost;
    public $direction_id;
    public $status_id;
    public $model_id;
    public $comments;
    
    // Дополнительные поля для JOIN запросов
    public $classroom_name;
    public $responsible_last_name;
    public $responsible_first_name;
    public $direction_name;
    public $cost_name;
    public $status_name;
    public $model_name;

    public function __construct($db) {
        $this->conn = $db;
    }

public function getAll($search = '', $status_id = null, $classroom_id = null) {
    // Базовый запрос
    $query = "SELECT 
                e.*, 
                c.name as classroom_name,
                u.last_name as responsible_last_name,
                u.first_name as responsible_first_name,
                d.name as direction_name,
                e.cost as cost_name,
                s.name as status_name,
                m.name as model_name
              FROM {$this->table} e
              LEFT JOIN classrooms c ON e.current_classroom_id = c.id
              LEFT JOIN users u ON e.responsible_user_id = u.id
              LEFT JOIN reference_items d ON e.direction_id = d.id AND d.type = 'direction'
              LEFT JOIN reference_items s ON e.status_id = s.id AND s.type = 'status'
              LEFT JOIN equipment_models m ON e.model_id = m.id
              WHERE 1=1";
    
    $params = [];
    $types = [];
    
    // Обработка поиска
    if (!empty($search)) {
        $query .= " AND (e.name LIKE ? OR e.inventory_number LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types[] = PDO::PARAM_STR;
        $types[] = PDO::PARAM_STR;
    }
    
    // Обработка статуса
    if ($status_id !== null && $status_id !== '') {
        $query .= " AND e.status_id = ?";
        $params[] = $status_id;
        $types[] = PDO::PARAM_INT;
    }
    
    // Обработка аудитории
    if ($classroom_id !== null && $classroom_id !== '') {
        $query .= " AND e.current_classroom_id = ?";
        $params[] = $classroom_id;
        $types[] = PDO::PARAM_INT;
    }
    
    $query .= " ORDER BY e.name";
    
    try {
        $stmt = $this->conn->prepare($query);
        
        // Привязываем параметры с указанием типов
        foreach ($params as $i => $value) {
            $stmt->bindValue($i + 1, $value, $types[$i] ?? PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt;
    } catch (PDOException $e) {
        error_log("PDO Error in Equipment::getAll(): " . $e->getMessage());
        error_log("Query: " . $query);
        error_log("Params: " . print_r($params, true));
        throw new Exception("Database error occurred");
    }
}
    public function getById($id) {
        $query = "SELECT 
                    e.*, 
                    c.name as classroom_name,
                    u.last_name as responsible_last_name,
                    u.first_name as responsible_first_name,
                    d.name as direction_name,
                    s.name as status_name,
                    e.cost as cost_name,
                    m.name as model_name
                  FROM {$this->table} e
                  LEFT JOIN classrooms c ON e.current_classroom_id = c.id
                  LEFT JOIN users u ON e.responsible_user_id = u.id
                  LEFT JOIN reference_items d ON e.direction_id = d.id AND d.type = 'direction'
                  LEFT JOIN reference_items s ON e.status_id = s.id AND s.type = 'status'
                  LEFT JOIN equipment_models m ON e.model_id = m.id
                  WHERE e.id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt->execute([$id])) {
            error_log("Ошибка выполнения запроса: " . implode(", ", $stmt->errorInfo()));
            return null;
        }

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            return null;
        }

        // Заполняем свойства объекта
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        return $data;
    }

    public function create() {
        $query = "INSERT INTO {$this->table} 
                  SET name = :name,
                      inventory_number = :inventory_number,
                      current_classroom_id = :current_classroom_id,
                      responsible_user_id = :responsible_user_id,
                      cost = :cost,
                      direction_id = :direction_id,
                      status_id = :status_id,
                      model_id = :model_id,
                      comments = :comments";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':inventory_number', $this->inventory_number);
        $stmt->bindParam(':current_classroom_id', $this->current_classroom_id);
        $stmt->bindParam(':responsible_user_id', $this->responsible_user_id);
        $stmt->bindParam(':cost', $this->cost);
        $stmt->bindParam(':direction_id', $this->direction_id);
        $stmt->bindParam(':status_id', $this->status_id);
        $stmt->bindParam(':model_id', $this->model_id);
        $stmt->bindParam(':comments', $this->comments);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE {$this->table} 
                  SET name = :name,
                      inventory_number = :inventory_number,
                      current_classroom_id = :current_classroom_id,
                      responsible_user_id = :responsible_user_id,
                      cost = :cost,
                      direction_id = :direction_id,
                      status_id = :status_id,
                      model_id = :model_id,
                      comments = :comments
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':inventory_number', $this->inventory_number);
        $stmt->bindParam(':current_classroom_id', $this->current_classroom_id);
        $stmt->bindParam(':responsible_user_id', $this->responsible_user_id);
        $stmt->bindParam(':cost', $this->cost);
        $stmt->bindParam(':direction_id', $this->direction_id);
        $stmt->bindParam(':status_id', $this->status_id);
        $stmt->bindParam(':model_id', $this->model_id);
        $stmt->bindParam(':comments', $this->comments);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function count() {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    public function getByStatus($status, $limit = 5) {
        $query = "SELECT e.id, e.name, e.inventory_number, c.name as classroom_name
                  FROM {$this->table} e
                  LEFT JOIN classrooms c ON e.current_classroom_id = c.id
                  JOIN reference_items s ON e.status_id = s.id AND s.type = 'status' AND s.name = ?
                  ORDER BY e.name
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(1, $status);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function getMovementHistory() {
        $query = "SELECT 
                    h.from_id, 
                    h.to_id, 
                    h.changed_at, 
                    h.comments,
                    fc.name as from_classroom_name,
                    tc.name as to_classroom_name,
                    u.last_name as user_last_name,
                    u.first_name as user_first_name
                  FROM change_history h
                  LEFT JOIN classrooms fc ON h.from_id = fc.id
                  JOIN classrooms tc ON h.to_id = tc.id
                  JOIN users u ON h.changed_by_user_id = u.id
                  WHERE h.entity_type = 'equipment_movement' AND h.equipment_id = ?
                  ORDER BY h.changed_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        return $stmt;
    }

    public function getNetworkSettings() {
        $query = "SELECT * FROM network_settings WHERE equipment_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        return $stmt;
    }

 public function getConsumables() {
    $query = "SELECT c.id, c.name, t.name as type_name
              FROM consumables c
              LEFT JOIN reference_items t ON c.type_id = t.id AND t.type = 'consumable_type'
              WHERE c.equipment_id = ?
              ORDER BY c.name";
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute([$this->id]);
    return $stmt;
}

    public function getByResponsibleUser($user_id) {
        $query = "SELECT e.id, e.name, e.inventory_number, s.name as status_name
                  FROM {$this->table} e
                  JOIN reference_items s ON e.status_id = s.id AND s.type = 'status'
                  WHERE e.responsible_user_id = :user_id
                  ORDER BY e.name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt;
    }

    public function countByResponsibleUser($user_id) {
        $query = "SELECT COUNT(*) FROM equipment WHERE responsible_user_id = :user_id OR temp_responsible_user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }

    public function clearResponsibleUser($user_id) {
        $query = "UPDATE equipment SET 
                  responsible_user_id = NULL, 
                  temp_responsible_user_id = NULL 
                  WHERE responsible_user_id = :user_id OR temp_responsible_user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':user_id' => $user_id]);
    }

    public function inventoryNumberExists($inventory_number) {
        $query = "SELECT COUNT(*) FROM equipment WHERE inventory_number = :inventory_number";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':inventory_number', $inventory_number);
        $stmt->execute();
        
        $count = $stmt->fetchColumn();
        return $count > 0;
    }
    public function getByClassroom($classroom_id) {
    $query = "SELECT * FROM equipment WHERE current_classroom_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([$classroom_id]);
    return $stmt;
}
public function getCountByClassroom($classroom_id) {
    $query = "SELECT COUNT(*) as count FROM equipment WHERE current_classroom_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([$classroom_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)$row['count'];
}

public function moveAllFromClassroom($classroom_id, $new_classroom_id = null) {
    $query = "UPDATE equipment 
             SET current_classroom_id = :new_classroom_id
             WHERE current_classroom_id = :classroom_id";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':new_classroom_id', $new_classroom_id, PDO::PARAM_INT);
    $stmt->bindParam(':classroom_id', $classroom_id, PDO::PARAM_INT);
    
    try {
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Ошибка перемещения оборудования: " . $e->getMessage());
        return false;
    }
}
    
}