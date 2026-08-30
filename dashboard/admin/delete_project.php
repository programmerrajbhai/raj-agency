<?php
declare(strict_types=1);

require_once '../../config/db.php';

require_admin();

if (!request_is_post()) {
    http_response_code(405);
    header('Allow: POST');

    exit('Method Not Allowed');
}

verify_csrf();

$projectId = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

if (!$projectId || $projectId < 1) {
    flash('error', 'Invalid project ID.');

    redirect('projects.php');
}

$statement = $pdo->prepare(
    'DELETE FROM projects
     WHERE id = ?'
);

$statement->execute([$projectId]);

if ($statement->rowCount() > 0) {
    flash(
        'success',
        'Portfolio project deleted successfully.'
    );
} else {
    flash(
        'error',
        'Project was not found.'
    );
}

redirect('projects.php');