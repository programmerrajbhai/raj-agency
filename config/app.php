<?php
declare(strict_types=1);

if (defined('RAJ_APP_BOOTSTRAPPED')) {
    return;
}

define('RAJ_APP_BOOTSTRAPPED', true);
define('ROOT_PATH', dirname(__DIR__));

function load_environment(string $file): void
{
    if (!is_readable($file)) {
        return;
    }

    $lines = file(
        $file,
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if (
            $line === ''
            || str_starts_with($line, '#')
            || !str_contains($line, '=')
        ) {
            continue;
        }

        [$key, $value] = array_map(
            'trim',
            explode('=', $line, 2)
        );

        if ($key === '' || getenv($key) !== false) {
            continue;
        }

        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];

            if (
                ($first === '"' && $last === '"')
                || ($first === "'" && $last === "'")
            ) {
                $value = substr($value, 1, -1);
            }
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

function env_value(
    string $key,
    ?string $default = null
): ?string {
    $value = getenv($key);

    return $value === false
        ? $default
        : $value;
}

function env_bool(
    string $key,
    bool $default = false
): bool {
    $value = env_value($key);

    if ($value === null) {
        return $default;
    }

    return filter_var(
        $value,
        FILTER_VALIDATE_BOOLEAN,
        FILTER_NULL_ON_FAILURE
    ) ?? $default;
}

load_environment(ROOT_PATH . '/.env');

define(
    'APP_ENV',
    env_value('APP_ENV', 'production')
);

define(
    'APP_DEBUG',
    env_bool('APP_DEBUG', false)
);

define(
    'APP_URL',
    rtrim(
        (string) env_value('APP_URL', ''),
        '/'
    )
);

date_default_timezone_set(
    (string) env_value(
        'APP_TIMEZONE',
        'Asia/Dhaka'
    )
);

ini_set(
    'display_errors',
    APP_DEBUG ? '1' : '0'
);

error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps =
        (
            !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off'
        )
        || strtolower(
            (string) (
                $_SERVER['HTTP_X_FORWARDED_PROTO']
                ?? ''
            )
        ) === 'https';

    session_name('raj_agency_session');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

if (!ob_get_level()) {
    ob_start();
}

header_remove('X-Powered-By');

header(
    'X-Content-Type-Options: nosniff'
);

header(
    'X-Frame-Options: SAMEORIGIN'
);

header(
    'Referrer-Policy: strict-origin-when-cross-origin'
);

header(
    'Permissions-Policy: camera=(), microphone=(), geolocation=()'
);

function e(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function redirect(
    string $location,
    int $status = 302
): never {
    header(
        'Location: ' . $location,
        true,
        $status
    );

    exit;
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');

    if (APP_URL !== '') {
        return APP_URL
            . (
                $path === ''
                ? ''
                : '/' . $path
            );
    }

    return $path;
}

function request_is_post(): bool
{
    return (
        $_SERVER['REQUEST_METHOD']
        ?? 'GET'
    ) === 'POST';
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] =
            bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return
        '<input type="hidden"'
        . ' name="csrf_token"'
        . ' value="'
        . e(csrf_token())
        . '">';
}

function verify_csrf(): void
{
    $submitted = (string) (
        $_POST['csrf_token']
        ?? ''
    );

    $stored = (string) (
        $_SESSION['_csrf_token']
        ?? ''
    );

    if (
        $stored === ''
        || $submitted === ''
        || !hash_equals(
            $stored,
            $submitted
        )
    ) {
        http_response_code(419);

        exit(
            'Your session expired. '
            . 'Please refresh the page '
            . 'and try again.'
        );
    }
}

function flash(
    string $key,
    ?string $message = null
): ?string {
    if ($message !== null) {
        $_SESSION['_flash'][$key] =
            $message;

        return null;
    }

    $value =
        $_SESSION['_flash'][$key]
        ?? null;

    unset($_SESSION['_flash'][$key]);

    return is_string($value)
        ? $value
        : null;
}

function is_admin(): bool
{
    return
        !empty(
            $_SESSION['admin_logged_in']
        )
        && !empty(
            $_SESSION['admin_id']
        );
}

function require_admin(): void
{
    if (!is_admin()) {
        flash(
            'error',
            'Please log in to continue.'
        );

        redirect('login.php');
    }

    $lastActivity = (int) (
        $_SESSION['admin_last_activity']
        ?? 0
    );

    if (
        $lastActivity > 0
        && time() - $lastActivity > 1800
    ) {
        unset(
            $_SESSION['admin_logged_in'],
            $_SESSION['admin_id'],
            $_SESSION['admin_username'],
            $_SESSION['admin_last_activity']
        );

        session_regenerate_id(true);

        flash(
            'error',
            'Your admin session expired. Please log in again.'
        );

        redirect('login.php');
    }

    $_SESSION['admin_last_activity'] =
        time();
}

function clean_text(
    mixed $value,
    int $maxLength
): string {
    $value = trim(
        strip_tags(
            (string) $value
        )
    );

    return mb_substr(
        $value,
        0,
        $maxLength
    );
}

function valid_http_url(
    mixed $value,
    int $maxLength = 2048
): string {
    $value = trim(
        (string) $value
    );

    if ($value === '') {
        return '';
    }

    if (
        strlen($value) > $maxLength
        || filter_var(
            $value,
            FILTER_VALIDATE_URL
        ) === false
    ) {
        return '';
    }

    $scheme = strtolower(
        (string) parse_url(
            $value,
            PHP_URL_SCHEME
        )
    );

    return in_array(
        $scheme,
        ['http', 'https'],
        true
    )
        ? $value
        : '';
}

function normalize_slug(
    string $title
): string {
    $slug = strtolower(
        trim($title)
    );

    $slug = preg_replace(
        '/[^a-z0-9]+/i',
        '-',
        $slug
    ) ?? '';

    $slug = trim($slug, '-');

    return $slug !== ''
        ? $slug
        : 'service';
}

function normalize_list(
    mixed $value,
    int $maxItems = 30
): array {
    $parts = array_slice(
        explode(
            ',',
            (string) $value
        ),
        0,
        $maxItems
    );

    $parts = array_map(
        static fn(string $item): string =>
            clean_text($item, 120),
        $parts
    );

    return array_values(
        array_filter(
            $parts,
            static fn(string $item): bool =>
                $item !== ''
        )
    );
}

function json_array(
    mixed $json
): array {
    if (
        !is_string($json)
        || $json === ''
    ) {
        return [];
    }

    $decoded = json_decode(
        $json,
        true
    );

    return is_array($decoded)
        ? $decoded
        : [];
}

function save_media_upload(
    array $file
): array {
    $error = (int) (
        $file['error']
        ?? UPLOAD_ERR_NO_FILE
    );

    if ($error === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException(
            'No file was selected.'
        );
    }

    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException(
            'The file upload failed. Please try again.'
        );
    }

    $size = (int) (
        $file['size']
        ?? 0
    );

    if (
        $size < 1
        || $size > 15 * 1024 * 1024
    ) {
        throw new RuntimeException(
            'Each media file must be 15 MB or smaller.'
        );
    }

    $tmp = (string) (
        $file['tmp_name']
        ?? ''
    );

    if (
        $tmp === ''
        || !is_uploaded_file($tmp)
    ) {
        throw new RuntimeException(
            'Invalid uploaded file.'
        );
    }

    $finfo = new finfo(
        FILEINFO_MIME_TYPE
    );

    $mime = (string) $finfo->file(
        $tmp
    );

    $allowed = [
        'image/jpeg' => [
            'extension' => 'jpg',
            'type' => 'image',
        ],
        'image/png' => [
            'extension' => 'png',
            'type' => 'image',
        ],
        'image/webp' => [
            'extension' => 'webp',
            'type' => 'image',
        ],
        'image/gif' => [
            'extension' => 'gif',
            'type' => 'image',
        ],
        'video/mp4' => [
            'extension' => 'mp4',
            'type' => 'video',
        ],
        'video/webm' => [
            'extension' => 'webm',
            'type' => 'video',
        ],
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException(
            'Only JPG, PNG, WEBP, GIF, MP4, and WEBM files are allowed.'
        );
    }

    if (
        $allowed[$mime]['type'] ===
        'image'
        && @getimagesize($tmp) === false
    ) {
        throw new RuntimeException(
            'The uploaded image is invalid.'
        );
    }

    $uploadDirectory =
        ROOT_PATH . '/uploads';

    if (
        !is_dir($uploadDirectory)
        && !mkdir(
            $uploadDirectory,
            0755,
            true
        )
        && !is_dir($uploadDirectory)
    ) {
        throw new RuntimeException(
            'The upload directory could not be created.'
        );
    }

    $filename =
        bin2hex(random_bytes(16))
        . '.'
        . $allowed[$mime]['extension'];

    $destination =
        $uploadDirectory
        . '/'
        . $filename;

    if (
        !move_uploaded_file(
            $tmp,
            $destination
        )
    ) {
        throw new RuntimeException(
            'The file could not be saved.'
        );
    }

    return [
        'type' =>
            $allowed[$mime]['type'],
        'url' =>
            'uploads/' . $filename,
    ];
}

function uploaded_file_from_multiple(
    array $files,
    int|string $index
): array {
    return [
        'name' =>
            $files['name'][$index]
            ?? '',
        'type' =>
            $files['type'][$index]
            ?? '',
        'tmp_name' =>
            $files['tmp_name'][$index]
            ?? '',
        'error' =>
            $files['error'][$index]
            ?? UPLOAD_ERR_NO_FILE,
        'size' =>
            $files['size'][$index]
            ?? 0,
    ];
}