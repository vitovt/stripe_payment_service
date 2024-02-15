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
            $this->ensureColumnExists('orders', 'description', 'VARCHAR(255) DEFAULT NULL');
            $this->ensureColumnExists('orders', 'amount2', 'SMALLINT UNSIGNED NOT NULL DEFAULT 0'); //euros
            $this->ensureColumnExists('orders', 'amount1', 'TINYINT UNSIGNED NOT NULL DEFAULT 0'); //cents
            //$this->ensureColumnExists('orders', 'init_ip', 'VARCHAR(64) DEFAULT NULL');
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

    /**
     * Ensures a specified column exists in a table, adding it if not.
     *
     * @param string $tableName The name of the table to check.
     * @param string $columnName The name of the column to check for.
     * @param string $columnDefinition The SQL column definition to use if adding.
     */
    protected function ensureColumnExists($tableName, $columnName, $columnDefinition)
    {
        $checkQuery = "SELECT COLUMN_NAME
                       FROM INFORMATION_SCHEMA.COLUMNS
                       WHERE TABLE_SCHEMA = :databaseName
                       AND TABLE_NAME = :tableName
                       AND COLUMN_NAME = :columnName LIMIT 1;";

        $stmt = $this->pdo->prepare($checkQuery);
        $stmt->execute([
            ':databaseName' => $_ENV['DB_NAME'],
            ':tableName' => $tableName,
            ':columnName' => $columnName,
        ]);

        if (!$stmt->fetch()) {
            $alterQuery = "ALTER TABLE `{$tableName}` ADD COLUMN `{$columnName}` {$columnDefinition};";
            $this->pdo->exec($alterQuery);
        }
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

    public function updateRecordStatus($status, $id)
    {
        $query = "UPDATE orders SET payment_status = ? WHERE id = ?;"; // `updated` will be auto-updated by MySQL
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$status, $id]);
    }

    public function updateRecordDescription($Description, $id)
    {
        $Description = mb_substr($Description, 0, 255, "UTF-8");
        $query = "UPDATE orders SET description = ? WHERE id = ?;"; // `updated` will be auto-updated by MySQL
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$Description, $id]);
    }

    public function updateRecordAmount($Amount1, $Amount2, $id)
    {
        $Amount1 = (int)$Amount1;
        $Amount2 = (int)$Amount2;
        $query = "UPDATE orders SET amount1 = ?, amount2 = ? WHERE id = ?;"; // `updated` will be auto-updated by MySQL
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$Amount1, $Amount2, $id]);
    }

    public function getRecordDescription($id)
    {
        $query = "SELECT description FROM orders WHERE id = ?;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ? $result['description'] : null;
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

	public function getRecords($fromDate = '1970-01-01', $toDate = '9999-12-31', $recordsPerPage = 0, $currentPage = 0, $sortBy = 'updated', $sortOrder = 'ASC') {
		$offset = ($currentPage - 1) * $recordsPerPage;
		$sortBy = in_array($sortBy, ['updated', 'id']) ? $sortBy : 'updated';
		$sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

		// Prepare the SQL query with placeholders
		$sql = "SELECT id, created, updated, payment_status, description, (amount1 + amount2 * 100) AS amount FROM orders WHERE updated >= :fromDate AND updated <= :toDate ORDER BY $sortBy $sortOrder";
        if($recordsPerPage && $currentPage) {
            $sql .=  "LIMIT :offset, :recordsPerPage";
        }
		$stmt = $this->pdo->prepare($sql);

		//When I specify a date without a time component (like 2024-02-10),
		//it defaults to the beginning of that day (2024-02-10 00:00:00). 
		//So, lets add one day after
		$toDatePlusOne = new DateTime($toDate);
		$toDatePlusOne->modify('+1 day');
		$toDatePlusOne = $toDatePlusOne->format('Y-m-d');

		// Bind values to the placeholders
		$stmt->bindValue(':fromDate', $fromDate);
		$stmt->bindValue(':toDate', $toDatePlusOne);
        if($recordsPerPage && $currentPage) {
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':recordsPerPage', $recordsPerPage, PDO::PARAM_INT);
        }

		$stmt->execute();

		// Fetch the results
		$results = $stmt->fetchAll();

        return $results;
	}

}

// Usage
// $db = new Database();
// $lastId = $db->addRecord();
// $db->addToken('token', $lastId);
// $db->updateRecordStatus('Paid', $lastId);
// $id = $db->getId('token');
// $token = $db->getToken($id);

?>

