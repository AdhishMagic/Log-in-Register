<?php
/** DB + cache clients */

function app_config(): array {
    static $cfg = null;
    if ($cfg === null) {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
        if (!file_exists($path)) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Missing config.php']);
            exit;
        }
        $cfg = require $path;
    }
    return $cfg;
}

function pdo_mysql(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $c = app_config()['mysql'];
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $c['host'], $c['port'], $c['database'], $c['charset']);
    $pdo = new PDO($dsn, $c['username'], $c['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function redis_client(): Redis {
    static $r = null;
    if ($r instanceof Redis) return $r;

    $c = app_config()['redis'];
    $r = new Redis();
    $r->connect($c['host'], $c['port']);
    if (!empty($c['password'])) { $r->auth($c['password']); }
    if (isset($c['db'])) { $r->select((int)$c['db']); }
    return $r;
}

/**
 * Returns MongoDB Manager from ext-mongodb without Composer dependency.
 * We'll use low-level driver classes (MongoDB\Driver\Manager, Query, BulkWrite).
 */
function mongo_manager(): MongoDB\Driver\Manager {
    static $m = null;
    if ($m instanceof MongoDB\Driver\Manager) return $m;
    $c = app_config()['mongodb'];
    $m = new MongoDB\Driver\Manager($c['uri']);
    return $m;
}

function mongo_namespace(): string {
    $c = app_config()['mongodb'];
    return $c['database'] . '.' . $c['collection'];
}
