<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/cart.php';

if (!request_is_post()) {
    http_response_code(405);
    header('Allow: POST');

    exit('Method Not Allowed');
}

/*
|--------------------------------------------------------------------------
| Every cart action requires CSRF verification
|--------------------------------------------------------------------------
*/

verify_csrf();

$action = (string) ($_POST['action'] ?? '');

$allowedActions = [
    'add',
    'increase',
    'decrease',
    'remove',
];

if (!in_array($action, $allowedActions, true)) {
    flash('error', 'Invalid cart action.');

    redirect('../index.php?page=checkout');
}

$serviceId = filter_input(
    INPUT_POST,
    'product_id',
    FILTER_VALIDATE_INT
);

if (!$serviceId || $serviceId < 1) {
    flash('error', 'Invalid service.');

    redirect('../index.php?page=products');
}

$cart = cart_quantities();

/*
|--------------------------------------------------------------------------
| Add service
|--------------------------------------------------------------------------
*/

if ($action === 'add') {
    $statement = $pdo->prepare(
        'SELECT id
         FROM services
         WHERE id = ?
           AND is_active = 1
         LIMIT 1'
    );

    $statement->execute([$serviceId]);

    if (!$statement->fetch()) {
        flash(
            'error',
            'This service is not available.'
        );

        redirect('../index.php?page=products');
    }

    $currentQuantity = (int) (
        $cart[$serviceId] ?? 0
    );

    $cart[$serviceId] = min(
        10,
        $currentQuantity + 1
    );

    $_SESSION['cart'] = $cart;

    flash(
        'success',
        'Service added to your cart.'
    );

    redirect('../index.php?page=checkout');
}

/*
|--------------------------------------------------------------------------
| Existing cart item required
|--------------------------------------------------------------------------
*/

if (!isset($cart[$serviceId])) {
    flash(
        'error',
        'The cart item was not found.'
    );

    redirect('../index.php?page=checkout');
}

/*
|--------------------------------------------------------------------------
| Update quantity or remove
|--------------------------------------------------------------------------
*/

if ($action === 'increase') {
    $cart[$serviceId] = min(
        10,
        (int) $cart[$serviceId] + 1
    );
}

if ($action === 'decrease') {
    $cart[$serviceId] =
        (int) $cart[$serviceId] - 1;

    if ($cart[$serviceId] < 1) {
        unset($cart[$serviceId]);
    }
}

if ($action === 'remove') {
    unset($cart[$serviceId]);

    flash(
        'success',
        'Service removed from your cart.'
    );
}

$_SESSION['cart'] = $cart;

redirect('../index.php?page=checkout');