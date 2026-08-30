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

$serviceId = filter_input(
    INPUT_POST,
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

try {
    $statement = $pdo->prepare(
        'DELETE FROM services
        WHERE id = ?'
    );

    $statement->execute([
        $serviceId,
    ]);

    if ($statement->rowCount() === 1) {
        flash(
            'success',
            'Service deleted successfully.'
        );
    } else {
        flash(
            'error',
            'Service was not found.'
        );
    }
} catch (PDOException $exception) {
    error_log(
        'Service deletion failed: '
        . $exception->getMessage()
    );

    flash(
        'error',
        'This service could not be deleted. It may be connected to an existing order.'
    );
}

redirect('index.php');