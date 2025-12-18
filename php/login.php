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

function redis_client(): Redis {
    if (!class_exists('Redis')) {
        throw new RuntimeException('PHP Redis extension not installed/enabled (need php-redis).');
    }
    $host = (string)env('REDIS_HOST', '127.0.0.1');
    $port = (int)env('REDIS_PORT', 6379);
    $db   = (int)env('REDIS_DB', 0);
    $password = env('REDIS_PASSWORD', null);

    $r = new Redis();
    if (!$r->connect($host, $port)) {
        throw new RuntimeException('Failed to connect to Redis ' . $host . ':' . $port);
    }
    if (is_string($password) && $password !== '') {
        try {
            $r->auth($password);
        } catch (RedisException $e) {
            // If Redis has no requirepass configured, AUTH throws.
            // Keep working in that case; fail hard for real auth errors.
            $msg = $e->getMessage();
            if (!is_string($msg) || stripos($msg, 'without any password configured') === false) {
                throw $e;
            }
        }
    }
    $r->select($db);
    return $r;
}

function session_ttl_seconds(): int { return (int)env('SESSION_TTL', 604800); }
function generate_token(int $lengthBytes = 32): string { return bin2hex(random_bytes($lengthBytes)); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed', 405);
}

$username = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
$password = isset($_POST['password']) ? (string)$_POST['password'] : '';

if ($username === '' || $password === '') fail('Missing credentials.');

try {
    $pdo = pdo_mysql();
    $stmt = $pdo->prepare('SELECT id, username, email, password_hash FROM users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $username]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($password, $row['password_hash'])) {
        fail('Invalid username or password.', 401);
    }

    $user = [ 'id' => (int)$row['id'], 'username' => $row['username'], 'email' => $row['email'] ];

    // Create token and store in Redis
    $token = generate_token(32);
    $redis = redis_client();
    $ttl = session_ttl_seconds();
    $key = 'session:' . $token;
    $redis->setex($key, (int)$ttl, json_encode($user));

    ok(['token' => $token, 'user' => ['username' => $user['username'], 'email' => $user['email']]]);
} catch (Throwable $e) {
    fail('Server error: ' . $e->getMessage(), 500);
}
