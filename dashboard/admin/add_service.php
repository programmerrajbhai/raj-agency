<?php
declare(strict_types=1);

require_once '../../config/db.php';
require_once 'service_helpers.php';

require_admin();

$errors = [];
$existingMedia = [];
$isEdit = false;

$pageTitle = 'Add New Service';
$submitText = 'Create Service';

$form = [
    'title' => '',
    'price' => '',
    'file_type' => 'web',
    'thumbnail' => '',
    'short_desc' => '',
    'is_active' => true,
];

$features = [
    'top' => [],
    'admin' => [],
    'user' => [],
    'tech' => [],
    'files' => [],
];

$demoLinks = [
    'frontend' => [
        'url' => '',
        'show' => true,
    ],

    'admin' => [
        'url' => '',
        'show' => false,
    ],

    'app' => [
        'url' => '',
        'show' => false,
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
            $media = [];

            if (
                isset(
                    $_FILES['media_files']
                )
            ) {
                $media =
                    collect_uploaded_media(
                        $_FILES['media_files']
                    );
            }

            $externalMedia =
                collect_external_media(
                    $_POST['media_urls_text']
                    ?? ''
                );

            $media = array_merge(
                $media,
                $externalMedia
            );

            if ($thumbnail === '') {
                foreach ($media as $item) {
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
                    $media
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
                    $form['title']
                );

            $statement = $pdo->prepare(
                'INSERT INTO services (
                    title,
                    slug,
                    short_desc,
                    price_basic,
                    features,
                    thumbnail,
                    demo_url,
                    file_type,
                    is_active
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )'
            );

            $statement->execute([
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
            ]);

            flash(
                'success',
                'Service created successfully.'
            );

            redirect('index.php');
        } catch (Throwable $exception) {
            error_log(
                'Service creation failed: '
                . $exception->getMessage()
            );

            $errors[] =
                $exception
                instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'The service could not be created. Please try again.';
        }
    }
}

require 'service_form.php';