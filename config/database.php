<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'DATABASE';
    private $username = 'root';
    private $password = '';
    private $conn;

    private $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    public function connect() {
        $this->conn = null;

        try {
            // Пытаемся подключиться сразу к конкретной базе данных
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password,
                $this->options
            );
            $this->conn->exec("set names utf8mb4");
            
            // Проверяем существование таблиц
            $this->checkDatabaseStructure();
            
            error_log("Подключение к базе данных успешно установлено.");
            return $this->conn;
            
        } catch(PDOException $e) {
            // Если базы данных не существует, пытаемся создать
            if ($e->getCode() == 1049) { // Код ошибки "Unknown database"
                return $this->createDatabase();
            }
            
            error_log("Ошибка подключения: " . $e->getMessage());
            echo '<div class="alert alert-danger mt-3">Ошибка подключения к базе данных: ' . $e->getMessage() . '</div>';
            return false;
        }
    }

    private function createDatabase() {
        try {
            // Подключаемся без указания базы данных
            $pdo = new PDO(
                "mysql:host={$this->host}",
                $this->username,
                $this->password,
                $this->options
            );

            // Создаем базу данных
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            error_log("Создана база данных: {$this->db_name}");

            // Подключаемся к новой базе данных
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password,
                $this->options
            );
            $this->conn->exec("set names utf8mb4");

            // Импортируем структуру
            $this->importDatabaseSchema();
            return $this->conn;

        } catch(PDOException $e) {
            error_log("Ошибка при создании базы данных: " . $e->getMessage());
            echo '<div class="alert alert-danger mt-3">Ошибка создания базы данных: ' . $e->getMessage() . '</div>';
            return false;
        }
    }

    private function checkDatabaseStructure() {
        try {
            $tables = $this->conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($tables)) {
                $this->importDatabaseSchema();
            }
        } catch (PDOException $e) {
            error_log("Ошибка проверки структуры БД: " . $e->getMessage());
        }
    }

    private function importDatabaseSchema() {
        try {
            $sqlFile = __DIR__ . '/../DATABASE_(4).sql';

            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                $this->conn->exec($sql);
                error_log("SQL-структура успешно импортирована из файла: {$sqlFile}");
                $this->createDefaultAdmin();
            } else {
                error_log("ОШИБКА: SQL-файл не найден: {$sqlFile}");
            }
        } catch (PDOException $e) {
            error_log("Ошибка при импорте структуры базы данных: " . $e->getMessage());
        }
    }

    private function createDefaultAdmin() {
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch();

            if ($result['count'] == 0) {
                $username = 'admin';
                $password = password_hash('admin123', PASSWORD_DEFAULT);
                $role = 'admin';
                $email = 'admin@example.com';
                $last_name = 'Администратор';
                $first_name = 'Системы';

                $stmt = $this->conn->prepare("INSERT INTO users (username, password, role, email, last_name, first_name)
                                       VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $password, $role, $email, $last_name, $first_name]);

                error_log("Создан администратор по умолчанию: {$username}");
            }
        } catch (PDOException $e) {
            error_log("Ошибка при создании администратора: " . $e->getMessage());
        }
    }
}
?>