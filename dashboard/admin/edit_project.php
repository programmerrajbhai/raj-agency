<?php
declare(strict_types=1);

require_once '../../config/db.php';
require_once 'project_helpers.php';
require_once 'project_details_helpers.php';

require_admin();

$projectId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$projectId || $projectId < 1) {
    flash('error', 'Invalid project ID.');

    redirect('projects.php');
}

$statement = $pdo->prepare(
    'SELECT *
     FROM projects
     WHERE id = ?
     LIMIT 1'
);

$statement->execute([$projectId]);

$project = $statement->fetch();

if (!$project) {
    flash('error', 'Project was not found.');

    redirect('projects.php');
}

/*
|--------------------------------------------------------------------------
| Existing project data
|--------------------------------------------------------------------------
*/

$existingMedia = project_gallery_items(
    $project['gallery'] ?? '',
    $project['thumbnail'] ?? '',
    $project['video_preview'] ?? ''
);

$technologies = project_technologies(
    $project['technologies'] ?? ''
);

$details = project_details_data(
    $project['details'] ?? ''
);

$errors = [];
$isEdit = true;

$pageTitle = 'Edit Portfolio Project';
$submitText = 'Save Project';

$form = [
    'title' => (string) $project['title'],

    'category' => (string) (
        $project['category'] ??
        ''
    ),

    'client_name' => (string) (
        $project['client_name'] ??
        ''
    ),

    'short_desc' => (string) (
        $project['short_desc'] ??
        ''
    ),

    'case_study_text' => (string) (
        $project['case_study_text'] ??
        ''
    ),

    'thumbnail' => (string) (
        $project['thumbnail'] ??
        ''
    ),

    'project_url' => (string) (
        $project['project_url'] ??
        ''
    ),

    'github_url' => (string) (
        $project['github_url'] ??
        ''
    ),

    'is_featured' =>
        (int) (
            $project['is_featured'] ??
            0
        ) === 1,

    'is_active' =>
        (int) (
            $project['is_active'] ??
            1
        ) === 1,

    'sort_order' =>
        (int) (
            $project['sort_order'] ??
            0
        ),
];

if (request_is_post()) {
    verify_csrf();

    $form = [
        'title' => clean_text(
            $_POST['title'] ?? '',
            150
        ),

        'category' => clean_text(
            $_POST['category'] ?? '',
            80
        ),

        'client_name' => clean_text(
            $_POST['client_name'] ?? '',
            100
        ),

        'short_desc' => clean_text(
            $_POST['short_desc'] ?? '',
            1000
        ),

        'case_study_text' => clean_text(
            $_POST['case_study_text'] ?? '',
            10000
        ),

        'thumbnail' => trim(
            (string) (
                $_POST['thumbnail'] ??
                ''
            )
        ),

        'project_url' => trim(
            (string) (
                $_POST['project_url'] ??
                ''
            )
        ),

        'github_url' => trim(
            (string) (
                $_POST['github_url'] ??
                ''
            )
        ),

        'is_featured' =>
            isset($_POST['is_featured']),

        'is_active' =>
            isset($_POST['is_active']),

        'sort_order' => max(
            0,
            min(
                9999,
                (int) (
                    $_POST['sort_order'] ??
                    0
                )
            )
        ),
    ];

    $technologies = normalize_list(
        $_POST['technologies'] ?? '',
        30
    );

    $details = project_details_from_post();

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (mb_strlen($form['title']) < 3) {
        $errors[] =
            'Project title must contain at least 3 characters.';
    }

    if (mb_strlen($form['category']) < 2) {
        $errors[] =
            'Enter a valid project category.';
    }

    if (mb_strlen($form['short_desc']) < 10) {
        $errors[] =
            'Short description must contain at least 10 characters.';
    }

    $thumbnail = project_media_reference(
        $form['thumbnail']
    );

    if (
        $form['thumbnail'] !== '' &&
        $thumbnail === ''
    ) {
        $errors[] =
            'Thumbnail must be a valid HTTP/HTTPS URL or uploads/ path.';
    }

    $projectUrl = valid_http_url(
        $form['project_url']
    );

    if (
        $form['project_url'] !== '' &&
        $projectUrl === ''
    ) {
        $errors[] =
            'Live project URL is invalid.';
    }

    $githubUrl = valid_http_url(
        $form['github_url']
    );

    if (
        $form['github_url'] !== '' &&
        $githubUrl === ''
    ) {
        $errors[] =
            'GitHub URL is invalid.';
    }

    /*
    |--------------------------------------------------------------------------
    | Update Project
    |--------------------------------------------------------------------------
    */

    if ($errors === []) {
        try {
            $removeIndexes = array_map(
                'intval',
                (array) (
                    $_POST['remove_media'] ??
                    []
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
                        (int) $replacement['error'] !==
                        UPLOAD_ERR_NO_FILE
                    ) {
                        $item = save_media_upload(
                            $replacement
                        );
                    }
                }

                $finalMedia[] = $item;
            }

            if (isset($_FILES['media_files'])) {
                $newUploads =
                    project_collect_uploaded_media(
                        $_FILES['media_files']
                    );

                $finalMedia = array_merge(
                    $finalMedia,
                    $newUploads
                );
            }

            $externalMedia =
                project_collect_external_media(
                    $_POST['media_urls_text'] ??
                    ''
                );

            $finalMedia = array_merge(
                $finalMedia,
                $externalMedia
            );

            $finalMedia = array_values(
                array_slice(
                    $finalMedia,
                    0,
                    30
                )
            );

            if ($thumbnail === '') {
                $thumbnail =
                    project_find_thumbnail(
                        $finalMedia
                    );
            }

            $videoPreview =
                project_find_video_preview(
                    $finalMedia
                );

            $galleryJson = json_encode(
                $finalMedia,
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE |
                JSON_THROW_ON_ERROR
            );

            $technologyJson = json_encode(
                array_values($technologies),
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE |
                JSON_THROW_ON_ERROR
            );

            $detailsJson = json_encode(
                $details,
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE |
                JSON_THROW_ON_ERROR
            );

            $update = $pdo->prepare(
                'UPDATE projects SET
                    title = ?,
                    category = ?,
                    short_desc = ?,
                    thumbnail = ?,
                    video_preview = ?,
                    gallery = ?,
                    client_name = ?,
                    case_study_text = ?,
                    technologies = ?,
                    details = ?,
                    project_url = ?,
                    github_url = ?,
                    is_featured = ?,
                    is_active = ?,
                    sort_order = ?
                 WHERE id = ?'
            );

            $update->execute([
                $form['title'],
                $form['category'],
                $form['short_desc'],

                $thumbnail !== ''
                    ? $thumbnail
                    : null,

                $videoPreview !== ''
                    ? $videoPreview
                    : null,

                $galleryJson,

                $form['client_name'] !== ''
                    ? $form['client_name']
                    : null,

                $form['case_study_text'] !== ''
                    ? $form['case_study_text']
                    : null,

                $technologyJson,
                $detailsJson,

                $projectUrl !== ''
                    ? $projectUrl
                    : null,

                $githubUrl !== ''
                    ? $githubUrl
                    : null,

                $form['is_featured'] ? 1 : 0,
                $form['is_active'] ? 1 : 0,
                $form['sort_order'],
                $projectId,
            ]);

            flash(
                'success',
                'Advanced portfolio project updated successfully.'
            );

            redirect('projects.php');
        } catch (Throwable $exception) {
            error_log(
                'Project update failed: ' .
                $exception->getMessage()
            );

            $errors[] =
                $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'The project could not be updated. Please try again.';
        }
    }
}

require 'project_form.php';