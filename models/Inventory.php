<?php
class Inventory {
    private $conn;
    private $table = 'inventories';

    public $id;
    public $name;
    public $start_date;
    public $end_date;
    public $created_by_user_id;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Получить последние записи с информацией о пользователе
    public function getRecent($limit = 5) {
        $query = "SELECT i.*,
                  u.last_name as user_last_name,
                  u.first_name as user_first_name
                  FROM {$this->table} i
                  LEFT JOIN users u ON i.created_by_user_id = u.id
                  ORDER BY i.start_date DESC
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Получить все записи
    public function getAll($search = '', $status = '', $classroom_id = '', $sort = 'start_date', $order = 'desc') {
        $query = "SELECT DISTINCT i.*,
                  u.last_name as user_last_name,
                  u.first_name as user_first_name
                  FROM {$this->table} i
                  LEFT JOIN users u ON i.created_by_user_id = u.id
                  LEFT JOIN inventory_results ir ON i.id = ir.inventory_id
                  LEFT JOIN equipment e ON ir.equipment_id = e.id
                  WHERE 1=1";

        $params = [];

        // Добавляем поиск по имени
        if (!empty($search)) {
            $query .= " AND i.name LIKE :search";
            $params[':search'] = "%$search%";
        }

        // Добавляем фильтр по статусу
        if (!empty($status)) {
            $query .= " AND i.status = :status";
            $params[':status'] = $status;
        }

        // Добавляем фильтр по аудитории
        if (!empty($classroom_id)) {
            $query .= " AND e.current_classroom_id = :classroom_id";
            $params[':classroom_id'] = $classroom_id;
        }

        // Добавляем сортировку
        $allowedSortFields = ['name', 'start_date', 'end_date', 'status'];
        $sort = in_array($sort, $allowedSortFields) ? $sort : 'start_date';
        $order = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

        $query .= " ORDER BY i.$sort $order";

        try {
            $stmt = $this->conn->prepare($query);

            // Привязываем параметры
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Ошибка в запросе Inventory::getAll - " . $e->getMessage());
            return $this->conn->query("SELECT 1 LIMIT 0");
        }
    }

    // Получить с пагинацией и фильтрацией
    public function getPaginated($page = 1, $perPage = 10, $search = '', $status = '', $classroom_id = '', $sort = 'start_date', $order = 'desc') {
        // Вычисляем смещение для запроса
        $offset = ($page - 1) * $perPage;

        $query = "SELECT DISTINCT i.*,
                  u.last_name as user_last_name,
                  u.first_name as user_first_name
                  FROM {$this->table} i
                  LEFT JOIN users u ON i.created_by_user_id = u.id
                  LEFT JOIN inventory_results ir ON i.id = ir.inventory_id
                  LEFT JOIN equipment e ON ir.equipment_id = e.id
                  WHERE 1=1";

        $params = [];

        // Добавляем поиск по имени
        if (!empty($search)) {
            $query .= " AND i.name LIKE :search";
            $params[':search'] = "%$search%";
        }

        // Добавляем фильтр по статусу
        if (!empty($status)) {
            $query .= " AND i.status = :status";
            $params[':status'] = $status;
        }

        // Добавляем фильтр по аудитории
        if (!empty($classroom_id)) {
            $query .= " AND e.current_classroom_id = :classroom_id";
            $params[':classroom_id'] = $classroom_id;
        }

        // Добавляем сортировку
        $allowedSortFields = ['name', 'start_date', 'end_date', 'status'];
        $sort = in_array($sort, $allowedSortFields) ? $sort : 'start_date';
        $order = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

        $query .= " ORDER BY i.$sort $order LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->conn->prepare($query);

            // Привязываем параметры
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Ошибка в запросе Inventory::getPaginated - " . $e->getMessage());
            return $this->conn->query("SELECT 1 LIMIT 0");
        }
    }

    // Подсчет общего количества записей с учетом фильтров
    public function countAll($search = '', $status = '', $classroom_id = '') {
        $query = "SELECT COUNT(DISTINCT i.id) as total
                 FROM {$this->table} i
                 LEFT JOIN inventory_results ir ON i.id = ir.inventory_id
                 LEFT JOIN equipment e ON ir.equipment_id = e.id
                 WHERE 1=1";

        $params = [];

        // Добавляем поиск по имени
        if (!empty($search)) {
            $query .= " AND i.name LIKE :search";
            $params[':search'] = "%$search%";
        }

        // Добавляем фильтр по статусу
        if (!empty($status)) {
            $query .= " AND i.status = :status";
            $params[':status'] = $status;
        }

        // Добавляем фильтр по аудитории
        if (!empty($classroom_id)) {
            $query .= " AND e.current_classroom_id = :classroom_id";
            $params[':classroom_id'] = $classroom_id;
        }

        try {
            $stmt = $this->conn->prepare($query);

            // Привязываем параметры
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return isset($row['total']) ? $row['total'] : 0;
        } catch (PDOException $e) {
            error_log("Ошибка в запросе Inventory::countAll - " . $e->getMessage());
            return 0;
        }
    }

    // Получить по ID
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->created_by_user_id = $row['created_by_user_id'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    // Создать новую запись
    public function create() {
        $query = "INSERT INTO {$this->table} (name, start_date, end_date, created_by_user_id, status)
                  VALUES (:name, :start_date, :end_date, :created_by_user_id, :status)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':created_by_user_id', $this->created_by_user_id);
        $stmt->bindParam(':status', $this->status);
        return $stmt->execute();
    }

    // Обновить существующую запись
    public function update() {
        $query = "UPDATE {$this->table}
                  SET name = :name,
                      start_date = :start_date,
                      end_date = :end_date,
                      created_by_user_id = :created_by_user_id,
                      status = :status
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':created_by_user_id', $this->created_by_user_id);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    // Удалить запись
    public function delete() {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    // Получить список аудиторий, связанных с инвентаризациями
    public function getClassrooms() {
        $query = "SELECT DISTINCT c.id, c.name
                 FROM classrooms c
                 JOIN equipment e ON c.id = e.current_classroom_id
                 JOIN inventory_results ir ON e.id = ir.equipment_id
                 JOIN {$this->table} i ON ir.inventory_id = i.id
                 ORDER BY c.name";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Ошибка в запросе Inventory::getClassrooms - " . $e->getMessage());
            return $this->conn->query("SELECT 1 FROM classrooms LIMIT 0");
        }
    }
}
?>
