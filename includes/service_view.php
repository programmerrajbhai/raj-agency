<?php
declare(strict_types=1);

if (defined('RAJ_SERVICE_VIEW_LOADED')) {
    return;
}

define('RAJ_SERVICE_VIEW_LOADED', true);

function service_feature_data(mixed $json): array
{
    $decoded = json_array($json);

    foreach (['top', 'admin', 'user', 'tech', 'files'] as $key) {
        if (
            !isset($decoded[$key]) ||
            !is_array($decoded[$key])
        ) {
            $decoded[$key] = [];
        }

        $decoded[$key] = array_values(
            array_slice($decoded[$key], 0, 30)
        );
    }

    return $decoded;
}

function service_media_url(mixed $value): string
{
    $value = trim((string) $value);

    $external = valid_http_url($value);

    if ($external !== '') {
        return $external;
    }

    if (
        preg_match('#^uploads/[A-Za-z0-9._/-]+$#', $value) &&
        !str_contains($value, '..')
    ) {
        return $value;
    }

    return '';
}

function service_youtube_id(string $url): string
{
    $url = valid_http_url($url);

    if ($url === '') {
        return '';
    }

    $host = strtolower(
        (string) parse_url($url, PHP_URL_HOST)
    );

    $host = preg_replace('/^www\./', '', $host) ?? $host;

    $path = trim(
        (string) parse_url($url, PHP_URL_PATH),
        '/'
    );

    $id = '';

    if ($host === 'youtu.be') {
        $id = explode('/', $path)[0] ?? '';
    } elseif (
        in_array(
            $host,
            ['youtube.com', 'm.youtube.com'],
            true
        )
    ) {
        parse_str(
            (string) parse_url($url, PHP_URL_QUERY),
            $query
        );

        $id = (string) ($query['v'] ?? '');

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
    ) ? $id : '';
}

function service_media_items(
    array $features,
    mixed $thumbnail = ''
): array {
    $stored = is_array(
        $features['media_gallery'] ?? null
    )
        ? $features['media_gallery']
        : [];

    $items = [];

    foreach (array_slice($stored, 0, 20) as $item) {
        if (!is_array($item)) {
            continue;
        }

        $type = (string) ($item['type'] ?? 'image');

        if (
            !in_array(
                $type,
                ['image', 'video', 'youtube'],
                true
            )
        ) {
            continue;
        }

        $mediaUrl = service_media_url(
            $item['url'] ?? ''
        );

        if ($mediaUrl === '') {
            continue;
        }

        if (
            $type === 'youtube' &&
            service_youtube_id($mediaUrl) === ''
        ) {
            continue;
        }

        $items[] = [
            'type' => $type,
            'url' => $mediaUrl,
        ];
    }

    if ($items === []) {
        $thumbnailUrl = service_media_url($thumbnail);

        if ($thumbnailUrl !== '') {
            $items[] = [
                'type' => 'image',
                'url' => $thumbnailUrl,
            ];
        }
    }

    return $items;
}

function service_demo_items(
    array $features,
    mixed $fallbackDemo = ''
): array {
    $stored = is_array(
        $features['demo_links'] ?? null
    )
        ? $features['demo_links']
        : [];

    $labels = [
        'frontend' => [
            'title' => 'Frontend Demo',
            'icon' => 'ri-macbook-line',
        ],
        'admin' => [
            'title' => 'Admin Panel',
            'icon' => 'ri-dashboard-3-line',
        ],
        'app' => [
            'title' => 'App / APK',
            'icon' => 'ri-smartphone-line',
        ],
    ];

    $items = [];

    foreach ($labels as $key => $meta) {
        $defaultShow =
            $key === 'frontend' &&
            $stored === [];

        $show = (bool) (
            $stored[$key]['show'] ??
            $defaultShow
        );

        $rawUrl =
            $stored[$key]['url'] ??
            (
                $key === 'frontend'
                    ? $fallbackDemo
                    : ''
            );

        $demoUrl = valid_http_url($rawUrl);

        if ($show && $demoUrl !== '') {
            $items[] = [
                'key' => $key,
                'title' => $meta['title'],
                'icon' => $meta['icon'],
                'url' => $demoUrl,
            ];
        }
    }

    return $items;
}

function service_type_meta(mixed $type): array
{
    return match ((string) $type) {
        'app' => [
            'key' => 'app',
            'label' => 'Mobile App',
            'badge' => 'FLUTTER',
            'icon' => 'ri-smartphone-line',
        ],

        'ui' => [
            'key' => 'ui',
            'label' => 'UI Design',
            'badge' => 'UI KIT',
            'icon' => 'ri-layout-4-line',
        ],

        default => [
            'key' => 'web',
            'label' => 'Website / Script',
            'badge' => 'WEB',
            'icon' => 'ri-code-s-slash-line',
        ],
    };
}

function service_excerpt(
    mixed $text,
    int $length = 150
): string {
    $text = clean_text($text, 2000);

    if (mb_strlen($text) > $length) {
        return mb_substr(
            $text,
            0,
            $length - 1
        ) . '…';
    }

    return $text;
}