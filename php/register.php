<?php
declare(strict_types=1);

function json_response(int $status, array $payload): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}
function ok(array $data = []): void { json_response(200, ['success' => true] + $data); }
function fail(string $message, int $status = 400): void { json_response($status, ['success' => false, 'error' => $message]); }

function read_dotenv(string $path): array {
    if (!is_readable($path)) return [];
    $vals = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $pos = strpos($line, '=');
        if ($pos === false) continue;
        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));
        if ($key === '') continue;
        if ((str_starts_with($val, '"') && str_ends_with($val, '"')) || (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
            $val = substr($val, 1, -1);
        }
        $vals[$key] = $val;
    }
    return $vals;
}

function env(string $key, $default = null) {
    $v = getenv($key);
    if ($v !== false && $v !== '') return $v;
    static $dotenv = null;
    if ($dotenv === null) {
        $root = dirname(__DIR__);
        $dotenv = read_dotenv($root . DIRECTORY_SEPARATOR . '.env');
    }
    if (is_array($dotenv) && array_key_exists($key, $dotenv) && $dotenv[$key] !== '') return $dotenv[$key];
    return $default;
}

function pdo_mysql(): PDO {
    $host = (string)env('MYSQL_HOST', '127.0.0.1');
    $port = (int)env('MYSQL_PORT', 3306);
    $db   = (string)env('MYSQL_DB', 'guvi_app');
    $user = (string)env('MYSQL_USER', 'root');
    $pass = (string)env('MYSQL_PASSWORD', '');

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $db);
    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function valid_username(string $u): bool {
    $len = strlen($u);
    if ($len < 3 || $len > 32) return false;
    return (bool)preg_match('/^[a-zA-Z0-9_\.\-]+$/', $u);
}
function valid_email(string $e): bool { return filter_var($e, FILTER_VALIDATE_EMAIL) !== false; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed', 405);
}

$username = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
$email    = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
$password = isset($_POST['password']) ? (string)$_POST['password'] : '';

if (!valid_username($username)) fail('Invalid username. Use 3-32 letters, numbers, dot, dash or underscore.');
if (!valid_email($email)) fail('Invalid email address.');
if (strlen($password) < 8) fail('Password must be at least 8 characters.');

try {
    $pdo = pdo_mysql();

    // Ensure unique username/email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1');
    $stmt->execute([':u' => $username, ':e' => $email]);
    if ($stmt->fetch()) {
        fail('Username or email already exists.', 409);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, created_at) VALUES (:u, :e, :p, NOW())');
    $stmt->execute([':u' => $username, ':e' => $email, ':p' => $hash]);

    ok(['message' => 'Registered']);
} catch (Throwable $e) {
    fail('Server error: ' . $e->getMessage(), 500);
}
