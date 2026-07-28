<?php
/** Подключение к MySQL (PDO) + доступ к конфигу. */

function cfg(string $path = null)
{
    static $config;
    if ($config === null) {
        $config = require __DIR__ . '/config.php';
    }
    if ($path === null) return $config;
    $node = $config;
    foreach (explode('.', $path) as $part) {
        if (!is_array($node) || !array_key_exists($part, $node)) return null;
        $node = $node[$part];
    }
    return $node;
}

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) return $pdo;

    $d = cfg('db');
    $dsn = "mysql:host={$d['host']};dbname={$d['name']};charset={$d['charset']}";
    try {
        $pdo = new PDO($dsn, $d['user'], $d['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        exit('Ошибка подключения к базе данных. Проверьте app/config.php');
    }
    return $pdo;
}
