<?php
declare(strict_types=1);

require_once __DIR__
    . '/../config/db.php';

require_once __DIR__
    . '/../includes/cart.php';

if (!request_is_post()) {
    http_response_code(405);
    header('Allow: POST');

    exit('Method Not Allowed');
}

verify_csrf();

$fullName = clean_text(
    $_POST['full_name'] ?? '',
    100
);

$email = trim(
    (string) (
        $_POST['email'] ?? ''
    )
);

$phone = clean_text(
    $_POST['phone'] ?? '',
    30
);

$country = clean_text(
    $_POST['country'] ?? '',
    80
);

$notes = clean_text(
    $_POST['notes'] ?? '',
    1000
);

$_SESSION['_checkout_old'] = [
    'full_name' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'country' => $country,
    'notes' => $notes,
];

$errors = [];

if (mb_strlen($fullName) < 2) {
    $errors[] =
        'Enter your full name.';
}

if (
    filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    ) === false
    || strlen($email) > 190
) {
    $errors[] =
        'Enter a valid email address.';
}

if (
    !preg_match(
        '/^[0-9+()\-\s]{7,30}$/',
        $phone
    )
) {
    $errors[] =
        'Enter a valid phone or WhatsApp number.';
}

if (mb_strlen($country) < 2) {
    $errors[] =
        'Enter your country.';
}

$cartItems =
    load_cart_items($pdo);

if ($cartItems === []) {
    $errors[] =
        'Your cart is empty.';
}

$lastOrderTime = (int) (
    $_SESSION['last_order_time']
    ?? 0
);

if (
    $lastOrderTime > 0
    && time() - $lastOrderTime < 10
) {
    $errors[] =
        'Please wait a few seconds before submitting another order.';
}

if ($errors !== []) {
    flash(
        'checkout_error',
        implode(' ', $errors)
    );

    redirect(
        '../index.php?page=checkout'
    );
}

$total = cart_total($cartItems);

$orderNumber =
    'RAJ-'
    . date('Ymd')
    . '-'
    . strtoupper(
        bin2hex(
            random_bytes(4)
        )
    );

try {
    $pdo->beginTransaction();

    $orderStatement = $pdo->prepare(
        'INSERT INTO orders (
            order_number,
            full_name,
            email,
            phone,
            country,
            notes,
            total_amount,
            status
        ) VALUES (
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

    $orderStatement->execute([
        $orderNumber,
        $fullName,
        $email,
        $phone,
        $country,

        $notes !== ''
            ? $notes
            : null,

        $total,
        'pending',
    ]);

    $orderId = (int) (
        $pdo->lastInsertId()
    );

    $itemStatement = $pdo->prepare(
        'INSERT INTO order_items (
            order_id,
            service_id,
            service_title,
            unit_price,
            quantity,
            line_total
        ) VALUES (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )'
    );

    foreach ($cartItems as $item) {
        $itemStatement->execute([
            $orderId,
            $item['id'],
            $item['title'],
            $item['price'],
            $item['quantity'],
            $item['line_total'],
        ]);
    }

    $pdo->commit();

    $_SESSION['cart'] = [];

    unset(
        $_SESSION['_checkout_old']
    );

    $_SESSION['last_order_time'] =
        time();

    $_SESSION['order_success'] = [
        'order_number' =>
            $orderNumber,

        'full_name' =>
            $fullName,

        'email' =>
            $email,

        'total' =>
            $total,
    ];

    redirect(
        '../index.php?page=order-success'
    );
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Order creation failed: '
        . $exception->getMessage()
    );

    flash(
        'checkout_error',
        'The order could not be submitted. Please try again.'
    );

    redirect(
        '../index.php?page=checkout'
    );
}