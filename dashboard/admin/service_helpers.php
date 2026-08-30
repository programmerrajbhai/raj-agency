<?php
declare(strict_types=1);

function unique_service_slug(
    PDO $pdo,
    string $title,
    ?int $ignoreId = null
): string {
    $base = normalize_slug($title);
    $slug = $base;
    $counter = 2;

    while (true) {
        if ($ignoreId !== null) {
            $statement = $pdo->prepare(
                'SELECT id
                FROM services
                WHERE slug = ?
                AND id <> ?
                LIMIT 1'
            );

            $statement->execute([
                $slug,
                $ignoreId,
            ]);
        } else {
            $statement = $pdo->prepare(
                'SELECT id
                FROM services
                WHERE slug = ?
                LIMIT 1'
            );

            $statement->execute([
                $slug,
            ]);
        }

        if (!$statement->fetch()) {
            return $slug;
        }

        $slug = $base . '-' . $counter;
        $counter++;
    }
}

function normalize_media_reference(
    mixed $value
): string {
    $value = trim(
        (string) $value
    );

    if ($value === '') {
        return '';
    }

    $externalUrl =
        valid_http_url($value);

    if ($externalUrl !== '') {
        return $externalUrl;
    }

    if (
        preg_match(
            '#^uploads/[A-Za-z0-9._/-]+$#',
            $value
        )
    ) {
        return $value;
    }

    return '';
}

function is_youtube_url(
    string $url
): bool {
    $host = strtolower(
        (string) parse_url(
            $url,
            PHP_URL_HOST
        )
    );

    $host = preg_replace(
        '/^www\./',
        '',
        $host
    ) ?? $host;

    return in_array(
        $host,
        [
            'youtube.com',
            'm.youtube.com',
            'youtu.be',
        ],
        true
    );
}

function external_media_item(
    string $url
): ?array {
    $url = valid_http_url($url);

    if ($url === '') {
        return null;
    }

    if (is_youtube_url($url)) {
        return [
            'type' => 'youtube',
            'url' => $url,
        ];
    }

    $path = (string) parse_url(
        $url,
        PHP_URL_PATH
    );

    $extension = strtolower(
        pathinfo(
            $path,
            PATHINFO_EXTENSION
        )
    );

    $type = in_array(
        $extension,
        ['mp4', 'webm'],
        true
    )
        ? 'video'
        : 'image';

    return [
        'type' => $type,
        'url' => $url,
    ];
}

function collect_external_media(
    mixed $text
): array {
    $lines = preg_split(
        '/\R+/',
        (string) $text
    ) ?: [];

    $items = [];

    foreach (
        array_slice($lines, 0, 30)
        as $line
    ) {
        $item = external_media_item(
            trim($line)
        );

        if ($item !== null) {
            $items[] = $item;
        }
    }

    return $items;
}

function collect_uploaded_media(
    array $files
): array {
    $items = [];

    $names = $files['name'] ?? [];

    if (!is_array($names)) {
        return $items;
    }

    foreach (
        array_keys($names)
        as $index
    ) {
        $file =
            uploaded_file_from_multiple(
                $files,
                $index
            );

        if (
            (int) $file['error']
            === UPLOAD_ERR_NO_FILE
        ) {
            continue;
        }

        $items[] =
            save_media_upload($file);
    }

    return $items;
}

function service_features_from_post(
    array $media
): array {
    return [
        'top' => normalize_list(
            $_POST['feat_top'] ?? ''
        ),

        'admin' => normalize_list(
            $_POST['feat_admin'] ?? ''
        ),

        'user' => normalize_list(
            $_POST['feat_user'] ?? ''
        ),

        'tech' => normalize_list(
            $_POST['feat_tech'] ?? ''
        ),

        'files' => normalize_list(
            $_POST['feat_files'] ?? ''
        ),

        'demo_links' => [
            'frontend' => [
                'url' => valid_http_url(
                    $_POST['demo_frontend_url']
                    ?? ''
                ),

                'show' => isset(
                    $_POST['demo_frontend_show']
                ),
            ],

            'admin' => [
                'url' => valid_http_url(
                    $_POST['demo_admin_url']
                    ?? ''
                ),

                'show' => isset(
                    $_POST['demo_admin_show']
                ),
            ],

            'app' => [
                'url' => valid_http_url(
                    $_POST['demo_app_url']
                    ?? ''
                ),

                'show' => isset(
                    $_POST['demo_app_show']
                ),
            ],
        ],

        'media_gallery' =>
            array_values($media),
    ];
}

function comma_list(
    mixed $items
): string {
    if (!is_array($items)) {
        return '';
    }

    return implode(
        ', ',
        array_map(
            'strval',
            $items
        )
    );
}

function admin_media_url(
    string $url
): string {
    if (
        preg_match(
            '#^https?://#i',
            $url
        )
    ) {
        return $url;
    }

    return '../../'
        . ltrim($url, '/');
}