<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

$host = (string) env_value(
    'DB_HOST',
    'localhost'
);

$port = (string) env_value(
    'DB_PORT',
    '3306'
);

$database = (string) env_value(
    'DB_NAME',
    'raj_agency_db'
);

$username = (string) env_value(
    'DB_USER',
    'root'
);

$password = (string) env_value(
    'DB_PASS',
    ''
);

try {
    $dsn =
        "mysql:host={$host};"
        . "port={$port};"
        . "dbname={$database};"
        . "charset=utf8mb4";

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE =>
                PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE =>
                PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES =>
                false,

            PDO::ATTR_STRINGIFY_FETCHES =>
                false,
        ]
    );
} catch (PDOException $exception) {
    error_log(
        'Database connection failed: '
        . $exception->getMessage()
    );

    http_response_code(500);

    if (APP_DEBUG) {
        exit(
            'Database connection failed: '
            . e(
                $exception->getMessage()
            )
        );
    }

    exit(
        'The website is temporarily unavailable. '
        . 'Please try again later.'
    );
}