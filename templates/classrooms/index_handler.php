<?php
require_once '../../config/database.php';
require_once '../../models/Classroom.php';
require_once '../../models/User.php';

class ClassroomController {
    private $db;
    private $classroom;
    private $user;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->classroom = new Classroom($this->db);
        $this->user = new User($this->db);
    }

    public function index() {
        try {
            // Получаем данные из GET запроса для фильтрации
            $search = $_GET['search'] ?? '';
            $responsible_id = $_GET['responsible_id'] ?? '';
            $min_equipment = intval($_GET['min_equipment'] ?? 0);

            // Получаем отфильтрованные аудитории
            if (!empty($search) || !empty($responsible_id) || $min_equipment > 0) {
                return $this->classroom->getFilteredWithEquipmentCount($search, $responsible_id, $min_equipment);
            } else {
                return $this->classroom->getWithEquipmentCount();
            }
        } catch (PDOException $e) {
            // Логирование ошибки
            error_log("Database error: " . $e->getMessage());

            // Можно вернуть пустой результат или бросить исключение дальше
            $stmt = $this->db->prepare("SELECT 0");
            $stmt->execute();
            return $stmt;
        }
    }

    /**
     * Получает список всех ответственных пользователей для фильтрации
     */
    public function getResponsibleUsers() {
        try {
            return $this->user->getAll();
        } catch (Exception $e) {
            error_log("Ошибка при получении пользователей: " . $e->getMessage());
            $stmt = $this->db->prepare("SELECT 0");
            $stmt->execute();
            return $stmt;
        }
    }

    // Другие методы контроллера (create, view, update, delete) могут быть добавлены здесь
}
