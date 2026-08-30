<?php
declare(strict_types=1);

require_once '../../config/db.php';

$adminCount = (int) $pdo
    ->query('SELECT COUNT(*) FROM admins')
    ->fetchColumn();

$errors = [];

if (
    request_is_post()
    && $adminCount === 0
) {
    verify_csrf();

    $setupKey = (string) (
        $_POST['setup_key'] ?? ''
    );

    $expectedKey = (string) env_value(
        'SETUP_KEY',
        ''
    );

    $username = clean_text(
        $_POST['username'] ?? '',
        50
    );

    $password = (string) (
        $_POST['password'] ?? ''
    );

    $passwordConfirmation = (string) (
        $_POST['password_confirmation']
        ?? ''
    );

    if (
        $expectedKey === ''
        || strlen($expectedKey) < 32
    ) {
        $errors[] =
            'SETUP_KEY is missing or too short in the .env file.';
    } elseif (
        !hash_equals(
            $expectedKey,
            $setupKey
        )
    ) {
        $errors[] =
            'The setup key is incorrect.';
    }

    if (
        !preg_match(
            '/^[A-Za-z0-9_.-]{3,50}$/',
            $username
        )
    ) {
        $errors[] =
            'Username must contain 3–50 letters, numbers, dots, dashes, or underscores.';
    }

    if (strlen($password) < 10) {
        $errors[] =
            'Password must be at least 10 characters.';
    }

    if (
        $password !==
        $passwordConfirmation
    ) {
        $errors[] =
            'Password confirmation does not match.';
    }

    if ($errors === []) {
        $statement = $pdo->prepare(
            'INSERT INTO admins
            (username, password)
            VALUES (?, ?)'
        );

        $statement->execute([
            $username,
            password_hash(
                $password,
                PASSWORD_DEFAULT
            ),
        ]);

        flash(
            'success',
            'Admin account created successfully. Please log in.'
        );

        redirect('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="robots"
        content="noindex,nofollow"
    >

    <title>
        Secure Admin Setup | Raj Agency
    </title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-[#050505] text-white flex items-center justify-center p-5">
<?php require __DIR__ . '/admin_sidebar.php'; ?>


<main class="w-full max-w-lg bg-[#111] border border-white/10 rounded-3xl p-8 shadow-2xl">

    <h1 class="text-3xl font-bold mb-2">
        Admin Setup
    </h1>

    <?php if ($adminCount > 0): ?>

        <?php http_response_code(403); ?>

        <div class="mt-6 rounded-2xl border border-yellow-500/30 bg-yellow-500/10 p-5 text-yellow-200">
            Admin setup is disabled because an administrator already exists.
        </div>

        <a
            href="login.php"
            class="mt-6 inline-block text-yellow-400 hover:underline"
        >
            Go to Admin Login
        </a>

    <?php else: ?>

        <p class="text-gray-400 mb-6">
            Create the first administrator.
            This page automatically locks after creation.
        </p>

        <?php if ($errors !== []): ?>

            <div class="mb-6 rounded-2xl border border-red-500/30 bg-red-500/10 p-4 text-red-300">

                <ul class="list-disc pl-5 space-y-1">

                    <?php foreach ($errors as $error): ?>

                        <li>
                            <?= e($error) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>

        <form
            method="POST"
            class="space-y-5"
            autocomplete="off"
        >

            <?= csrf_field() ?>

            <div>
                <label
                    for="setup_key"
                    class="block text-sm text-gray-300 mb-2"
                >
                    Setup key from .env
                </label>

                <input
                    id="setup_key"
                    name="setup_key"
                    type="password"
                    required
                    class="w-full rounded-xl bg-black border border-white/10 p-3 outline-none focus:border-yellow-500"
                >
            </div>

            <div>
                <label
                    for="username"
                    class="block text-sm text-gray-300 mb-2"
                >
                    Admin username
                </label>

                <input
                    id="username"
                    name="username"
                    type="text"
                    minlength="3"
                    maxlength="50"
                    required
                    value="<?= e($_POST['username'] ?? '') ?>"
                    class="w-full rounded-xl bg-black border border-white/10 p-3 outline-none focus:border-yellow-500"
                >
            </div>

            <div>
                <label
                    for="password"
                    class="block text-sm text-gray-300 mb-2"
                >
                    Password
                </label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    minlength="10"
                    required
                    class="w-full rounded-xl bg-black border border-white/10 p-3 outline-none focus:border-yellow-500"
                >
            </div>

            <div>
                <label
                    for="password_confirmation"
                    class="block text-sm text-gray-300 mb-2"
                >
                    Confirm password
                </label>

                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    minlength="10"
                    required
                    class="w-full rounded-xl bg-black border border-white/10 p-3 outline-none focus:border-yellow-500"
                >
            </div>

            <button
                type="submit"
                class="w-full rounded-xl bg-yellow-500 text-black font-bold p-4 hover:bg-yellow-400"
            >
                Create Secure Admin
            </button>

        </form>

    <?php endif; ?>

</main>

</body>
</html>