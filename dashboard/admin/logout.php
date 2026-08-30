<?php
declare(strict_types=1);

require_once '../../config/app.php';

if (!request_is_post()) {
    http_response_code(405);
    header('Allow: POST');

    exit('Method Not Allowed');
}

verify_csrf();

unset(
    $_SESSION['admin_logged_in'],
    $_SESSION['admin_id'],
    $_SESSION['admin_username'],
    $_SESSION['admin_last_activity']
);

session_regenerate_id(true);

flash(
    'success',
    'You have been logged out safely.'
);

redirect('login.php');