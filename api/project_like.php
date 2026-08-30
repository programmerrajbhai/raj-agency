<?php
declare(strict_types=1);

require_once __DIR__ .
    '/../config/db.php';

if (!request_is_post()) {
    http_response_code(405);
    header('Allow: POST');

    exit('Method Not Allowed');
}

verify_csrf();

$projectId = filter_input(
    INPUT_POST,
    'project_id',
    FILTER_VALIDATE_INT
);

$isAjax =
    ($_POST['ajax'] ?? '') === '1';

function like_response(
    bool $isAjax,
    array $data,
    int $projectId,
    int $status = 200
): never {
    if ($isAjax) {
        http_response_code($status);

        header(
            'Content-Type: application/json; charset=UTF-8'
        );

        echo json_encode(
            $data,
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    redirect(
        '../index.php?page=project-details&id=' .
        $projectId
    );
}

if (!$projectId || $projectId < 1) {
    like_response(
        $isAjax,
        [
            'success' => false,
            'message' => 'Invalid project.',
        ],
        0,
        422
    );
}

$statement = $pdo->prepare(
    'SELECT id, likes
     FROM projects
     WHERE id = ?
       AND is_active = 1
     LIMIT 1'
);

$statement->execute([$projectId]);

$project = $statement->fetch();

if (!$project) {
    like_response(
        $isAjax,
        [
            'success' => false,
            'message' => 'Project not found.',
        ],
        $projectId,
        404
    );
}

$likedProjects =
    $_SESSION['liked_projects'] ??
    [];

if (!is_array($likedProjects)) {
    $likedProjects = [];
}

$likedProjects = array_values(
    array_unique(
        array_filter(
            array_map(
                'intval',
                $likedProjects
            ),
            static fn (int $id): bool =>
                $id > 0
        )
    )
);

$alreadyLiked = in_array(
    $projectId,
    $likedProjects,
    true
);

try {
    if ($alreadyLiked) {
        $update = $pdo->prepare(
            'UPDATE projects
             SET likes =
                CASE
                    WHEN likes > 0
                    THEN likes - 1
                    ELSE 0
                END
             WHERE id = ?'
        );

        $update->execute([$projectId]);

        $likedProjects = array_values(
            array_filter(
                $likedProjects,
                static fn (int $id): bool =>
                    $id !== $projectId
            )
        );

        $liked = false;
    } else {
        $update = $pdo->prepare(
            'UPDATE projects
             SET likes = likes + 1
             WHERE id = ?'
        );

        $update->execute([$projectId]);

        $likedProjects[] = $projectId;
        $liked = true;
    }

    $_SESSION['liked_projects'] =
        array_values(
            array_unique(
                $likedProjects
            )
        );

    $countStatement = $pdo->prepare(
        'SELECT likes
         FROM projects
         WHERE id = ?'
    );

    $countStatement->execute([$projectId]);

    $likes = (int) (
        $countStatement->fetchColumn() ??
        0
    );

    like_response(
        $isAjax,
        [
            'success' => true,
            'liked' => $liked,
            'likes' => $likes,
        ],
        $projectId
    );
} catch (Throwable $exception) {
    error_log(
        'Project like failed: ' .
        $exception->getMessage()
    );

    like_response(
        $isAjax,
        [
            'success' => false,
            'message' =>
                'Like could not be updated.',
        ],
        $projectId,
        500
    );
}