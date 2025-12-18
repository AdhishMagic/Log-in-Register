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

function session_ttl_seconds(): int { return (int)env('SESSION_TTL', 604800); }

function header_token(): ?string {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    foreach ($headers as $k => $v) {
        if (strtolower((string)$k) === 'x-session-token') {
            $val = is_array($v) ? ($v[0] ?? null) : $v;
            $val = is_string($val) ? trim($val) : null;
            return $val ?: null;
        }
    }
    if (isset($_GET['token'])) return trim((string)$_GET['token']);
    if (isset($_POST['token'])) return trim((string)$_POST['token']);
    return null;
}

function read_json_body(): array {
    $raw = file_get_contents('php://input') ?: '';
    if ($raw === '') return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
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

function mongo_manager(): MongoDB\Driver\Manager {
    if (!class_exists('MongoDB\\Driver\\Manager')) {
        throw new RuntimeException('PHP MongoDB extension not installed/enabled (need php-mongodb).');
    }
    $uri = (string)env('MONGO_URI', 'mongodb://127.0.0.1:27017');
    return new MongoDB\Driver\Manager($uri);
}

function mongo_namespace(): string {
    $db = (string)env('MONGO_DB', 'guvi_app');
    $col = (string)env('MONGO_COLLECTION', 'profiles');
    return $db . '.' . $col;
}

$method = $_SERVER['REQUEST_METHOD'];

function require_user_from_token(): array {
    $token = header_token();
    if (!$token) fail('Missing token.', 401);
    $redis = redis_client();
    $raw = $redis->get('session:' . $token);
    if (!$raw) fail('Invalid or expired token.', 401);
    $user = json_decode($raw, true);
    if (!is_array($user) || !isset($user['id'])) fail('Invalid session.', 401);

    // Touch TTL (sliding expiration)
    $ttl = session_ttl_seconds();
    $redis->expire('session:' . $token, (int)$ttl);

    return $user; // [id, username, email]
}

try {
    $user = require_user_from_token();

    if ($method === 'GET') {
        // Fetch profile from MongoDB
        $manager = mongo_manager();
        $ns = mongo_namespace();
        $filter = ['user_id' => (int)$user['id']];
        $query = new MongoDB\Driver\Query($filter, ['limit' => 1]);
        $cursor = $manager->executeQuery($ns, $query);
        $profile = null;
        foreach ($cursor as $doc) { $profile = json_decode(json_encode($doc), true); break; }

        if ($profile) {
            unset($profile['_id']);
        }

        ok([
            'user' => [ 'username' => $user['username'], 'email' => $user['email'] ],
            'profile' => $profile ?: (object)[]
        ]);
    }

    if ($method === 'POST') {
        $body = read_json_body();
        $data = isset($body['profile']) && is_array($body['profile']) ? $body['profile'] : [];

        $doc = [
            'user_id' => (int)$user['id'],
            'age' => isset($data['age']) && $data['age'] !== '' ? (int)$data['age'] : null,
            'dob' => isset($data['dob']) ? (string)$data['dob'] : null,
            'contact' => isset($data['contact']) ? (string)$data['contact'] : null,
            'address' => isset($data['address']) ? (string)$data['address'] : null,
            'updated_at' => new MongoDB\BSON\UTCDateTime((int)(microtime(true) * 1000)),
        ];

        $manager = mongo_manager();
        $ns = mongo_namespace();
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->update(['user_id' => (int)$user['id']], ['$set' => $doc], ['upsert' => true]);
        $result = $manager->executeBulkWrite($ns, $bulk);

        ok(['message' => 'Updated']);
    }

    fail('Method not allowed', 405);
} catch (Throwable $e) {
    fail('Server error: ' . $e->getMessage(), 500);
}
