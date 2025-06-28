<?php
class ReferenceItem {
    private $conn;
    private $table = 'reference_items';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByType($type) {
        $query = "SELECT * FROM {$this->table} WHERE type = :type ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':type', $type);
        
        try {
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error getting items by type: " . $e->getMessage());
            return false;
        }
    }

    public function create($type, $name, $description = null) {
        $query = "INSERT INTO {$this->table} (type, name, description) 
                  VALUES (:type, :name, :description)";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        
        try {
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error creating reference item: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $type, $name, $description = null) {
        $query = "UPDATE {$this->table} 
                  SET name = :name, description = :description 
                  WHERE id = :id AND type = :type";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':type', $type);
        
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating reference item: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id, $type) {
        $query = "DELETE FROM {$this->table} WHERE id = :id AND type = :type";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':type', $type);
        
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting reference item: " . $e->getMessage());
            return false;
        }
    }

    public function nameExists($type, $name, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM {$this->table} 
                  WHERE type = :type AND name = :name";
        
        if ($excludeId) {
            $query .= " AND id != :excludeId";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':name', $name);
        
        if ($excludeId) {
            $stmt->bindParam(':excludeId', $excludeId);
        }
        
        try {
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking name existence: " . $e->getMessage());
            return false;
        }
    }
}
?>