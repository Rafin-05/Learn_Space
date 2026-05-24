<?php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'learnspace');
define('DB_PORT', getenv('DB_PORT') ?: 3306);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
// ---- CUSTOM VERCEL SESSION HANDLER ----
class DatabaseSessionHandler implements SessionHandlerInterface {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }
    public function open($path, $name): bool { return true; }
    public function close(): bool { return true; }
    public function read($id): string|false {
        $stmt = $this->conn->prepare("SELECT data FROM sessions WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            return $res->fetch_assoc()['data'];
        }
        return '';
    }
    public function write($id, $data): bool {
        $stmt = $this->conn->prepare("REPLACE INTO sessions (id, data, last_accessed) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $id, $data);
        return $stmt->execute();
    }
    public function destroy($id): bool {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE id = ?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }
    public function gc($max_lifetime): int|false {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE last_accessed < DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->bind_param("i", $max_lifetime);
        $stmt->execute();
        return $stmt->affected_rows;
    }
}
$handler = new DatabaseSessionHandler($conn);
session_set_save_handler($handler, true);
?>
