<?php
declare(strict_types=1);

require_once '../../config/db.php';
require_once 'service_helpers.php';

require_admin();

$serviceId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (
    !$serviceId
    || $serviceId < 1
) {
    flash(
        'error',
        'Invalid service ID.'
    );

    redirect('index.php');
}

$statement = $pdo->prepare(
    'SELECT *
    FROM services
    WHERE id = ?
    LIMIT 1'
);

$statement->execute([
    $serviceId,
]);

$service = $statement->fetch();

if (!$service) {
    flash(
        'error',
        'Service not found.'
    );

    redirect('index.php');
}

$storedFeatures = json_array(
    $service['features'] ?? null
);

$storedMedia = is_array(
    $storedFeatures['media_gallery']
    ?? null
)
    ? $storedFeatures['media_gallery']
    : [];

$existingMedia = [];

foreach ($storedMedia as $item) {
    if (!is_array($item)) {
        continue;
    }

    $mediaType = (
        $item['type'] ?? ''
    );

    $type = in_array(
        $mediaType,
        [
            'image',
            'video',
            'youtube',
        ],
        true
    )
        ? (string) $mediaType
        : 'image';

    $mediaUrl =
        normalize_media_reference(
            $item['url'] ?? ''
        );

    if ($mediaUrl !== '') {
        $existingMedia[] = [
            'type' => $type,
            'url' => $mediaUrl,
        ];
    }
}

$storedDemo = is_array(
    $storedFeatures['demo_links']
    ?? null
)
    ? $storedFeatures['demo_links']
    : [];

$errors = [];
$isEdit = true;

$pageTitle = 'Edit Service';
$submitText = 'Save Changes';

$form = [
    'title' =>
        (string) $service['title'],

    'price' =>
        (string) $service['price_basic'],

    'file_type' =>
        (string) (
            $service['file_type']
            ?? 'web'
        ),

    'thumbnail' =>
        (string) (
            $service['thumbnail']
            ?? ''
        ),

    'short_desc' =>
        (string) (
            $service['short_desc']
            ?? ''
        ),

    'is_active' =>
        (int) (
            $service['is_active']
            ?? 1
        ) === 1,
];

$features = [
    'top' => is_array(
        $storedFeatures['top'] ?? null
    )
        ? $storedFeatures['top']
        : [],

    'admin' => is_array(
        $storedFeatures['admin'] ?? null
    )
        ? $storedFeatures['admin']
        : [],

    'user' => is_array(
        $storedFeatures['user'] ?? null
    )
        ? $storedFeatures['user']
        : [],

    'tech' => is_array(
        $storedFeatures['tech'] ?? null
    )
        ? $storedFeatures['tech']
        : [],

    'files' => is_array(
        $storedFeatures['files'] ?? null
    )
        ? $storedFeatures['files']
        : [],
];

$demoLinks = [
    'frontend' => [
        'url' => valid_http_url(
            $storedDemo['frontend']['url']
            ?? $service['demo_url']
            ?? ''
        ),

        'show' => (bool) (
            $storedDemo['frontend']['show']
            ?? true
        ),
    ],

    'admin' => [
        'url' => valid_http_url(
            $storedDemo['admin']['url']
            ?? ''
        ),

        'show' => (bool) (
            $storedDemo['admin']['show']
            ?? false
        ),
    ],

    'app' => [
        'url' => valid_http_url(
            $storedDemo['app']['url']
            ?? ''
        ),

        'show' => (bool) (
            $storedDemo['app']['show']
            ?? false
        ),
    ],
];

if (request_is_post()) {
    verify_csrf();

    $form = [
        'title' => clean_text(
            $_POST['title'] ?? '',
            150
        ),

        'price' => trim(
            (string) (
                $_POST['price'] ?? ''
            )
        ),

        'file_type' => (string) (
            $_POST['file_type']
            ?? 'web'
        ),

        'thumbnail' => trim(
            (string) (
                $_POST['thumbnail']
                ?? ''
            )
        ),

        'short_desc' => clean_text(
            $_POST['short_desc'] ?? '',
            1000
        ),

        'is_active' => isset(
            $_POST['is_active']
        ),
    ];

    $features = [
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
    ];

    foreach (
        ['frontend', 'admin', 'app']
        as $demoType
    ) {
        $fieldName =
            "demo_{$demoType}_url";

        $showField =
            "demo_{$demoType}_show";

        $rawUrl = trim(
            (string) (
                $_POST[$fieldName] ?? ''
            )
        );

        $validUrl =
            valid_http_url($rawUrl);

        if (
            $rawUrl !== ''
            && $validUrl === ''
        ) {
            $errors[] =
                ucfirst($demoType)
                . ' demo URL is invalid.';
        }

        $demoLinks[$demoType] = [
            'url' => $validUrl,
            'show' => isset(
                $_POST[$showField]
            ),
        ];
    }

    if (
        mb_strlen(
            $form['title']
        ) < 3
    ) {
        $errors[] =
            'Service title must be at least 3 characters.';
    }

    $price = filter_var(
        $form['price'],
        FILTER_VALIDATE_FLOAT
    );

    if (
        $price === false
        || $price < 0.01
        || $price > 99999999.99
    ) {
        $errors[] =
            'Enter a valid price greater than zero.';
    }

    if (
        !in_array(
            $form['file_type'],
            ['web', 'app', 'ui'],
            true
        )
    ) {
        $errors[] =
            'Invalid service type.';
    }

    $thumbnail =
        normalize_media_reference(
            $form['thumbnail']
        );

    if (
        $form['thumbnail'] !== ''
        && $thumbnail === ''
    ) {
        $errors[] =
            'Thumbnail must be a valid HTTP/HTTPS URL or uploads/ path.';
    }

    if ($errors === []) {
        try {
            $removeIndexes = array_map(
                'intval',
                (array) (
                    $_POST['remove_media']
                    ?? []
                )
            );

            $finalMedia = [];

            foreach (
                $existingMedia
                as $index => $item
            ) {
                if (
                    in_array(
                        $index,
                        $removeIndexes,
                        true
                    )
                ) {
                    continue;
                }

                if (
                    isset(
                        $_FILES['replace_media']
                    )
                ) {
                    $replacement =
                        uploaded_file_from_multiple(
                            $_FILES['replace_media'],
                            $index
                        );

                    if (
                        (int) $replacement['error']
                        !== UPLOAD_ERR_NO_FILE
                    ) {
                        $item =
                            save_media_upload(
                                $replacement
                            );
                    }
                }

                $finalMedia[] = $item;
            }

            if (
                isset(
                    $_FILES['media_files']
                )
            ) {
                $newUploads =
                    collect_uploaded_media(
                        $_FILES['media_files']
                    );

                $finalMedia = array_merge(
                    $finalMedia,
                    $newUploads
                );
            }

            $externalMedia =
                collect_external_media(
                    $_POST['media_urls_text']
                    ?? ''
                );

            $finalMedia = array_merge(
                $finalMedia,
                $externalMedia
            );

            if ($thumbnail === '') {
                foreach (
                    $finalMedia
                    as $item
                ) {
                    if (
                        ($item['type'] ?? '')
                        === 'image'
                    ) {
                        $thumbnail =
                            (string) $item['url'];

                        break;
                    }
                }
            }

            $featureData =
                service_features_from_post(
                    $finalMedia
                );

            $featureJson = json_encode(
                $featureData,
                JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_THROW_ON_ERROR
            );

            $slug =
                unique_service_slug(
                    $pdo,
                    $form['title'],
                    (int) $serviceId
                );

            $update = $pdo->prepare(
                'UPDATE services SET
                    title = ?,
                    slug = ?,
                    short_desc = ?,
                    price_basic = ?,
                    features = ?,
                    thumbnail = ?,
                    demo_url = ?,
                    file_type = ?,
                    is_active = ?
                WHERE id = ?'
            );

            $update->execute([
                $form['title'],
                $slug,
                $form['short_desc'],
                $price,
                $featureJson,

                $thumbnail !== ''
                    ? $thumbnail
                    : null,

                $demoLinks['frontend']['url']
                    !== ''
                    ? $demoLinks['frontend']['url']
                    : null,

                $form['file_type'],

                $form['is_active']
                    ? 1
                    : 0,

                $serviceId,
            ]);

            flash(
                'success',
                'Service updated successfully.'
            );

            redirect('index.php');
        } catch (Throwable $exception) {
            error_log(
                'Service update failed: '
                . $exception->getMessage()
            );

            $errors[] =
                $exception
                instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'The service could not be updated. Please try again.';
        }
    }
}

require 'service_form.php';