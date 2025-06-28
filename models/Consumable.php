<?php
class Consumable {
    private $conn;
    private $table = 'consumables';

    public $id;
    public $name;
    public $receipt_date;
    public $photo_path;
    public $responsible_user_id;
    public $temp_responsible_user_id;
    public $type_id;
    public $equipment_id;
    
    // Дополнительные поля для JOIN
    public $type_name;
    public $equipment_name;
    public $responsible_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($search = '') {
        $query = "SELECT c.*, 
                  t.name as type_name, 
                  e.name as equipment_name,
                  CONCAT(u.last_name, ' ', u.first_name) as responsible_name
                 FROM {$this->table} c
                 LEFT JOIN reference_items t ON c.type_id = t.id AND t.type = 'consumable_type'
                 LEFT JOIN equipment e ON c.equipment_id = e.id
                 LEFT JOIN users u ON c.responsible_user_id = u.id";
        
        $params = [];
        
        if (!empty($search)) {
            $query .= " WHERE c.name LIKE :search 
                        OR t.name LIKE :search
                        OR e.name LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        $query .= " ORDER BY c.name";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT c.*, 
                  t.name as type_name, 
                  e.name as equipment_name,
                  CONCAT(u.last_name, ' ', u.first_name) as responsible_name
                 FROM {$this->table} c
                 LEFT JOIN reference_items t ON c.type_id = t.id AND t.type = 'consumable_type'
                 LEFT JOIN equipment e ON c.equipment_id = e.id
                 LEFT JOIN users u ON c.responsible_user_id = u.id
                 WHERE c.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO {$this->table} 
                 SET name = :name,
                     receipt_date = :receipt_date,
                     responsible_user_id = :responsible_user_id,
                     type_id = :type_id,
                     equipment_id = :equipment_id,
                     created_at = NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':receipt_date', $this->receipt_date);
        $stmt->bindParam(':responsible_user_id', $this->responsible_user_id, PDO::PARAM_INT);
        $stmt->bindParam(':type_id', $this->type_id, PDO::PARAM_INT);
        $stmt->bindParam(':equipment_id', $this->equipment_id, PDO::PARAM_INT);
        
        try {
            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return $this->id;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error creating consumable: " . $e->getMessage());
            return false;
        }
    }

    public function update() {
        $query = "UPDATE {$this->table} 
                 SET name = :name,
                     receipt_date = :receipt_date,
                     responsible_user_id = :responsible_user_id,
                     type_id = :type_id,
                     equipment_id = :equipment_id,
                     updated_at = NOW()
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':receipt_date', $this->receipt_date);
        $stmt->bindParam(':responsible_user_id', $this->responsible_user_id, PDO::PARAM_INT);
        $stmt->bindParam(':type_id', $this->type_id, PDO::PARAM_INT);
        $stmt->bindParam(':equipment_id', $this->equipment_id, PDO::PARAM_INT);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating consumable: " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting consumable: " . $e->getMessage());
            return false;
        }
    }
}
?>