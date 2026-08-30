<?php
declare(strict_types=1);

function project_details_defaults(): array
{
    return [
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
}

function project_details_data(
    mixed $value
): array {
    $defaults = project_details_defaults();

    if (is_string($value) && $value !== '') {
        $decoded = json_decode(
            $value,
            true
        );

        $value = is_array($decoded)
            ? $decoded
            : [];
    }

    if (!is_array($value)) {
        return $defaults;
    }

    $details = array_merge(
        $defaults,
        $value
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
        !isset($details['key_features']) ||
        !is_array($details['key_features'])
    ) {
        $details['key_features'] = [];
    }

    $cleanFeatures = [];

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
            $cleanFeatures[] = $feature;
        }
    }

    $details['key_features'] =
        array_values($cleanFeatures);

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

function project_details_from_post(): array
{
    return project_details_data([
        'role' => clean_text(
            $_POST['project_role'] ?? '',
            150
        ),

        'duration' => clean_text(
            $_POST['project_duration'] ?? '',
            150
        ),

        'platform' => clean_text(
            $_POST['project_platform'] ?? '',
            150
        ),

        'challenge' => clean_text(
            $_POST['challenge'] ?? '',
            5000
        ),

        'solution' => clean_text(
            $_POST['solution'] ?? '',
            5000
        ),

        'result' => clean_text(
            $_POST['result'] ?? '',
            5000
        ),

        'key_features' => normalize_list(
            $_POST['key_features'] ?? '',
            30
        ),

        'testimonial' => clean_text(
            $_POST['testimonial'] ?? '',
            3000
        ),

        'testimonial_author' => clean_text(
            $_POST['testimonial_author'] ?? '',
            150
        ),

        'show_overview' =>
            isset($_POST['show_overview']),

        'show_client' =>
            isset($_POST['show_client']),

        'show_project_info' =>
            isset($_POST['show_project_info']),

        'show_case_study' =>
            isset($_POST['show_case_study']),

        'show_challenge' =>
            isset($_POST['show_challenge']),

        'show_solution' =>
            isset($_POST['show_solution']),

        'show_features' =>
            isset($_POST['show_features']),

        'show_results' =>
            isset($_POST['show_results']),

        'show_technologies' =>
            isset($_POST['show_technologies']),

        'show_gallery' =>
            isset($_POST['show_gallery']),

        'show_live_url' =>
            isset($_POST['show_live_url']),

        'show_github_url' =>
            isset($_POST['show_github_url']),

        'show_testimonial' =>
            isset($_POST['show_testimonial']),
    ]);
}

function project_feature_input(
    mixed $features
): string {
    if (!is_array($features)) {
        return '';
    }

    return implode(
        ', ',
        array_map(
            'strval',
            $features
        )
    );
}