<?php
require_once '../../config/database.php';
require_once '../../models/Equipment.php';
require_once '../../models/Classroom.php';
require_once '../../models/ReferenceItem.php';

class EquipmentController {
    private $db;
    private $equipment;
    private $classroom;
    private $referenceItem;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->equipment = new Equipment($this->db);
        $this->classroom = new Classroom($this->db);
        $this->referenceItem = new ReferenceItem($this->db);
    }

    public function index() {
        // Получаем и очищаем параметры
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status_id = isset($_GET['status_id']) && $_GET['status_id'] !== '' ? $_GET['status_id'] : null;
        $classroom_id = isset($_GET['classroom_id']) && $_GET['classroom_id'] !== '' ? $_GET['classroom_id'] : null;

        // Получаем данные
        $equipment = $this->equipment->getAll($search, $status_id, $classroom_id);
        $statuses = $this->referenceItem->getByType('status');
        $classrooms = $this->classroom->getAll();

        return [
            'equipment' => $equipment->fetchAll(PDO::FETCH_ASSOC),
            'statuses' => $statuses->fetchAll(PDO::FETCH_ASSOC),
            'classrooms' => $classrooms->fetchAll(PDO::FETCH_ASSOC),
            'filters' => [
                'search' => $search,
                'status_id' => $status_id,
                'classroom_id' => $classroom_id
            ]
        ];
    }
}