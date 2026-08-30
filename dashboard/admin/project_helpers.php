<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Validate project media URL/path
|--------------------------------------------------------------------------
*/

function project_media_reference(mixed $value): string
{
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    $external = valid_http_url($value);

    if ($external !== '') {
        return $external;
    }

    if (
        preg_match(
            '#^uploads/[A-Za-z0-9._/-]+$#',
            $value
        ) &&
        !str_contains($value, '..')
    ) {
        return $value;
    }

    return '';
}

/*
|--------------------------------------------------------------------------
| YouTube URL checking
|--------------------------------------------------------------------------
*/

function project_is_youtube_url(string $url): bool
{
    $url = valid_http_url($url);

    if ($url === '') {
        return false;
    }

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

/*
|--------------------------------------------------------------------------
| Convert external URL to media item
|--------------------------------------------------------------------------
*/

function project_external_media_item(
    string $url
): ?array {
    $url = valid_http_url($url);

    if ($url === '') {
        return null;
    }

    if (project_is_youtube_url($url)) {
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

    $videoExtensions = [
        'mp4',
        'webm',
    ];

    $type = in_array(
        $extension,
        $videoExtensions,
        true
    )
        ? 'video'
        : 'image';

    return [
        'type' => $type,
        'url' => $url,
    ];
}

/*
|--------------------------------------------------------------------------
| Collect external URLs
|--------------------------------------------------------------------------
*/

function project_collect_external_media(
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
        $line = trim($line);

        if ($line === '') {
            continue;
        }

        $item = project_external_media_item(
            $line
        );

        if ($item !== null) {
            $items[] = $item;
        }
    }

    return $items;
}

/*
|--------------------------------------------------------------------------
| Collect uploaded media
|--------------------------------------------------------------------------
*/

function project_collect_uploaded_media(
    array $files
): array {
    $items = [];

    $names = $files['name'] ?? [];

    if (!is_array($names)) {
        return $items;
    }

    foreach (
        array_slice(
            array_keys($names),
            0,
            30
        ) as $index
    ) {
        $file = uploaded_file_from_multiple(
            $files,
            $index
        );

        if (
            (int) $file['error'] ===
            UPLOAD_ERR_NO_FILE
        ) {
            continue;
        }

        $items[] = save_media_upload($file);
    }

    return $items;
}

/*
|--------------------------------------------------------------------------
| Read stored project gallery
|--------------------------------------------------------------------------
*/

function project_gallery_items(
    mixed $galleryJson,
    mixed $thumbnail = '',
    mixed $videoPreview = ''
): array {
    $decoded = json_array($galleryJson);

    $items = [];

    foreach (
        array_slice($decoded, 0, 30)
        as $item
    ) {
        if (!is_array($item)) {
            continue;
        }

        $type = (string) (
            $item['type'] ??
            'image'
        );

        if (
            !in_array(
                $type,
                [
                    'image',
                    'video',
                    'youtube',
                ],
                true
            )
        ) {
            continue;
        }

        $url = project_media_reference(
            $item['url'] ?? ''
        );

        if ($url === '') {
            continue;
        }

        if (
            $type === 'youtube' &&
            !project_is_youtube_url($url)
        ) {
            continue;
        }

        $items[] = [
            'type' => $type,
            'url' => $url,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Old video_preview support
    |--------------------------------------------------------------------------
    */

    if ($items === []) {
        $previewUrl = project_media_reference(
            $videoPreview
        );

        if ($previewUrl !== '') {
            $previewItem =
                project_external_media_item(
                    $previewUrl
                );

            if ($previewItem !== null) {
                $items[] = $previewItem;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Thumbnail fallback
    |--------------------------------------------------------------------------
    */

    if ($items === []) {
        $thumbnailUrl = project_media_reference(
            $thumbnail
        );

        if ($thumbnailUrl !== '') {
            $items[] = [
                'type' => 'image',
                'url' => $thumbnailUrl,
            ];
        }
    }

    return $items;
}

/*
|--------------------------------------------------------------------------
| Read technologies JSON
|--------------------------------------------------------------------------
*/

function project_technologies(
    mixed $json
): array {
    $decoded = json_array($json);

    $technologies = [];

    foreach (
        array_slice($decoded, 0, 30)
        as $technology
    ) {
        if (!is_string($technology)) {
            continue;
        }

        $technology = clean_text(
            $technology,
            100
        );

        if ($technology !== '') {
            $technologies[] = $technology;
        }
    }

    return array_values(
        array_unique($technologies)
    );
}

/*
|--------------------------------------------------------------------------
| Comma-separated technology input
|--------------------------------------------------------------------------
*/

function project_technology_input(
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

/*
|--------------------------------------------------------------------------
| Admin media URL
|--------------------------------------------------------------------------
*/

function project_admin_media_url(
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

    return '../../' . ltrim(
        $url,
        '/'
    );
}

/*
|--------------------------------------------------------------------------
| Find thumbnail automatically
|--------------------------------------------------------------------------
*/

function project_find_thumbnail(
    array $media
): string {
    foreach ($media as $item) {
        if (
            ($item['type'] ?? '') ===
            'image'
        ) {
            return (string) (
                $item['url'] ??
                ''
            );
        }
    }

    return '';
}

/*
|--------------------------------------------------------------------------
| Find legacy video preview automatically
|--------------------------------------------------------------------------
*/

function project_find_video_preview(
    array $media
): string {
    foreach ($media as $item) {
        if (
            in_array(
                $item['type'] ?? '',
                ['video', 'youtube'],
                true
            )
        ) {
            return (string) (
                $item['url'] ??
                ''
            );
        }
    }

    return '';
}