<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (!request_is_post()) {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

verify_csrf();

$name = clean_text($_POST['name'] ?? '', 100);
$email = trim((string) ($_POST['email'] ?? ''));
$serviceId = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
$message = clean_text($_POST['message'] ?? '', 3000);
$website = trim((string) ($_POST['website'] ?? ''));

$_SESSION['_contact_old'] = [
    'name' => $name,
    'email' => $email,
    'service_id' => $serviceId ?: '',
    'message' => $message,
];

/*
|--------------------------------------------------------------------------
| Spam protection: hidden honeypot field
|--------------------------------------------------------------------------
*/
if ($website !== '') {
    unset($_SESSION['_contact_old']);

    flash('success', 'Thank you. Your message has been received.');

    redirect('../index.php?page=contact');
}

$errors = [];

if (mb_strlen($name) < 2) {
    $errors[] = 'Enter your name.';
}

if (
    filter_var($email, FILTER_VALIDATE_EMAIL) === false ||
    strlen($email) > 190
) {
    $errors[] = 'Enter a valid email address.';
}

if (mb_strlen($message) < 10) {
    $errors[] = 'Your message must contain at least 10 characters.';
}

/*
|--------------------------------------------------------------------------
| Prevent repeated message submission
|--------------------------------------------------------------------------
*/
$lastMessageTime = (int) ($_SESSION['last_contact_time'] ?? 0);

if ($lastMessageTime > 0 && time() - $lastMessageTime < 20) {
    $errors[] = 'Please wait a few seconds before sending another message.';
}

$serviceName = null;

if ($serviceId) {
    $serviceStatement = $pdo->prepare(
        'SELECT title
         FROM services
         WHERE id = ?
           AND is_active = 1
         LIMIT 1'
    );

    $serviceStatement->execute([$serviceId]);

    $serviceName = $serviceStatement->fetchColumn();

    if ($serviceName === false) {
        $errors[] = 'The selected service is not available.';
        $serviceName = null;
    }
}

if ($errors !== []) {
    flash('contact_error', implode(' ', $errors));

    redirect('../index.php?page=contact');
}

try {
    $statement = $pdo->prepare(
        'INSERT INTO messages
            (name, email, service, message, status)
         VALUES
            (?, ?, ?, ?, ?)'
    );

    $statement->execute([
        $name,
        $email,
        $serviceName !== null
            ? (string) $serviceName
            : 'General Inquiry',
        $message,
        'new',
    ]);

    unset($_SESSION['_contact_old']);

    $_SESSION['last_contact_time'] = time();

    flash(
        'success',
        'Thank you. Your message has been sent successfully.'
    );
} catch (Throwable $exception) {
    error_log(
        'Contact message failed: ' . $exception->getMessage()
    );

    flash(
        'contact_error',
        'Your message could not be sent. Please try again.'
    );
}

redirect('../index.php?page=contact');