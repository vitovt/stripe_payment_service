<?php

use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php'; // Adjust the path as necessary

class Database
{
    protected $pdo;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $host = $_ENV['DB_HOST'];
        $db = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
            $this->checkAndCreateTable();
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    protected function checkAndCreateTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS orders (
                    id INT(16) AUTO_INCREMENT, 
                    token VARCHAR(96),
                    created DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    payment_status VARCHAR(32) DEFAULT NULL,
                    PRIMARY KEY (id)
                  ) AUTO_INCREMENT=12700001;";

        $this->pdo->exec($query);
    }

    public function addRecord()
    {
        $query = "INSERT INTO orders (token) VALUES (?);"; // Let MySQL handle `created` and `updated`
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['']);
        return $this->pdo->lastInsertId();
    }

    public function addToken($token, $id)
    {
        $query = "UPDATE orders SET token = ? WHERE id = ?;"; // `updated` will be auto-updated by MySQL
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$token, $id]);
    }

    public function updateRecord($id, $status)
    {
        $query = "UPDATE orders SET payment_status = ? WHERE id = ?;"; // `updated` will be auto-updated by MySQL
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$status, $id]);
    }

    public function getId($token)
    {
        $query = "SELECT id FROM orders WHERE token = ?;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$token]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : null;
    }

    public function getToken($id)
    {
        $query = "SELECT token FROM orders WHERE id = ?;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ? $result['token'] : null;
    }
}

// Usage
// $db = new Database();
// $lastId = $db->addRecord('your_token_here');
// $db->addToken('new_token', $lastId);
// $db->updateRecord($lastId, 'Paid');
// $id = $db->getId('your_token_here');
// $token = $db->getToken($id);

?>

