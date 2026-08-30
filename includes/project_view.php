<?php
declare(strict_types=1);

if (defined('RAJ_PROJECT_VIEW_LOADED')) {
    return;
}

define('RAJ_PROJECT_VIEW_LOADED', true);

function project_public_media_url(
    mixed $value
): string {
    $value = trim((string) $value);

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

function project_public_youtube_id(
    string $url
): string {
    $url = valid_http_url($url);

    if ($url === '') {
        return '';
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

    $path = trim(
        (string) parse_url(
            $url,
            PHP_URL_PATH
        ),
        '/'
    );

    $id = '';

    if ($host === 'youtu.be') {
        $id = explode(
            '/',
            $path
        )[0] ?? '';
    }

    if (
        in_array(
            $host,
            [
                'youtube.com',
                'm.youtube.com',
            ],
            true
        )
    ) {
        parse_str(
            (string) parse_url(
                $url,
                PHP_URL_QUERY
            ),
            $query
        );

        $id = (string) (
            $query['v'] ??
            ''
        );

        if (
            $id === '' &&
            preg_match(
                '#^(?:embed|shorts)/([A-Za-z0-9_-]{11})#',
                $path,
                $match
            )
        ) {
            $id = $match[1];
        }
    }

    return preg_match(
        '/^[A-Za-z0-9_-]{11}$/',
        $id
    )
        ? $id
        : '';
}

function project_public_media_type(
    string $url
): string {
    if (
        project_public_youtube_id($url) !== ''
    ) {
        return 'youtube';
    }

    $extension = strtolower(
        pathinfo(
            (string) parse_url(
                $url,
                PHP_URL_PATH
            ),
            PATHINFO_EXTENSION
        )
    );

    return in_array(
        $extension,
        ['mp4', 'webm'],
        true
    )
        ? 'video'
        : 'image';
}

function project_public_gallery(
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

        $url = project_public_media_url(
            $item['url'] ?? ''
        );

        if ($url === '') {
            continue;
        }

        $type = (string) (
            $item['type'] ??
            project_public_media_type($url)
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

        if (
            $type === 'youtube' &&
            project_public_youtube_id(
                $url
            ) === ''
        ) {
            continue;
        }

        $items[] = [
            'type' => $type,
            'url' => $url,
        ];
    }

    if ($items === []) {
        $previewUrl =
            project_public_media_url(
                $videoPreview
            );

        if ($previewUrl !== '') {
            $items[] = [
                'type' =>
                    project_public_media_type(
                        $previewUrl
                    ),
                'url' => $previewUrl,
            ];
        }
    }

    if ($items === []) {
        $thumbnailUrl =
            project_public_media_url(
                $thumbnail
            );

        if ($thumbnailUrl !== '') {
            $items[] = [
                'type' => 'image',
                'url' => $thumbnailUrl,
            ];
        }
    }

    $unique = [];

    foreach ($items as $item) {
        $key =
            $item['type'] .
            '|' .
            $item['url'];

        $unique[$key] = $item;
    }

    return array_values($unique);
}

function project_public_technologies(
    mixed $json
): array {
    $decoded = json_array($json);

    $items = [];

    foreach (
        array_slice($decoded, 0, 30)
        as $technology
    ) {
        $technology = clean_text(
            $technology,
            100
        );

        if ($technology !== '') {
            $items[] = $technology;
        }
    }

    return array_values(
        array_unique($items)
    );
}

function project_public_details(
    mixed $json
): array {
    $defaults = [
        'role' => '',
        'duration' => '',
        'platform' => '',
        'challenge' => '',
        'solution' => '',
        'result' => '',
        'key_features' => [],
        'testimonial' => '',
        'testimonial_author' => '',

        'show_overview' => true,
        'show_client' => false,
        'show_project_info' => false,
        'show_case_study' => true,
        'show_challenge' => false,
        'show_solution' => false,
        'show_features' => true,
        'show_results' => false,
        'show_technologies' => true,
        'show_gallery' => true,
        'show_live_url' => true,
        'show_github_url' => false,
        'show_testimonial' => false,
    ];

    $decoded = json_array($json);

    $details = array_merge(
        $defaults,
        $decoded
    );

    foreach (
        [
            'role',
            'duration',
            'platform',
            'challenge',
            'solution',
            'result',
            'testimonial',
            'testimonial_author',
        ] as $key
    ) {
        $details[$key] = clean_text(
            $details[$key] ?? '',
            in_array(
                $key,
                [
                    'challenge',
                    'solution',
                    'result',
                    'testimonial',
                ],
                true
            )
                ? 5000
                : 150
        );
    }

    if (
        !is_array(
            $details['key_features'] ??
            null
        )
    ) {
        $details['key_features'] = [];
    }

    $features = [];

    foreach (
        array_slice(
            $details['key_features'],
            0,
            30
        ) as $feature
    ) {
        $feature = clean_text(
            $feature,
            180
        );

        if ($feature !== '') {
            $features[] = $feature;
        }
    }

    $details['key_features'] =
        array_values($features);

    foreach (
        [
            'show_overview',
            'show_client',
            'show_project_info',
            'show_case_study',
            'show_challenge',
            'show_solution',
            'show_features',
            'show_results',
            'show_technologies',
            'show_gallery',
            'show_live_url',
            'show_github_url',
            'show_testimonial',
        ] as $key
    ) {
        $details[$key] = (bool) (
            $details[$key] ??
            false
        );
    }

    return $details;
}

function project_public_excerpt(
    mixed $text,
    int $length = 220
): string {
    $text = clean_text(
        $text,
        3000
    );

    if (mb_strlen($text) > $length) {
        return mb_substr(
            $text,
            0,
            $length - 1
        ) . '…';
    }

    return $text;
}

function project_is_liked(
    int $projectId
): bool {
    $liked = $_SESSION[
        'liked_projects'
    ] ?? [];

    if (!is_array($liked)) {
        return false;
    }

    return in_array(
        $projectId,
        array_map(
            'intval',
            $liked
        ),
        true
    );
}