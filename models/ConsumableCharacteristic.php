<?php
class ConsumableCharacteristic {
    private $conn;
    private $table = 'consumable_characteristics';

    public $id;
    public $consumable_id;
    public $name;
    public $value;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Получить характеристики для расходника
    public function getByConsumable($consumable_id) {
        $query = "SELECT * FROM {$this->table} 
                 WHERE consumable_id = :consumable_id
                 ORDER BY name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':consumable_id', $consumable_id);
        $stmt->execute();
        return $stmt;
    }

    // Добавить характеристику
    public function create() {
        $query = "INSERT INTO {$this->table} 
                 SET consumable_id = :consumable_id,
                     name = :name,
                     value = :value";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':consumable_id', $this->consumable_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':value', $this->value);
        
        return $stmt->execute();
    }

    public function update() {
    $query = "UPDATE {$this->table} 
             SET name = :name,
                 value = :value
             WHERE id = :id";
    
    $stmt = $this->conn->prepare($query);
    
    $stmt->bindParam(':name', $this->name);
    $stmt->bindParam(':value', $this->value);
    $stmt->bindParam(':id', $this->id);
    
    return $stmt->execute();
}
    // Удалить характеристику
    public function delete() {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
}
?>