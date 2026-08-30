<?php
declare(strict_types=1);

require_once '../../config/db.php';

if (is_admin()) {
    redirect('index.php');
}

$error = flash('error');
$success = flash('success');

$lockedUntil = (int) (
    $_SESSION['login_locked_until']
    ?? 0
);

if (
    $lockedUntil > 0
    && $lockedUntil <= time()
) {
    unset(
        $_SESSION['login_locked_until'],
        $_SESSION['login_attempts']
    );

    $lockedUntil = 0;
}

if (request_is_post()) {
    verify_csrf();

    if ($lockedUntil > time()) {
        $remainingMinutes = max(
            1,
            (int) ceil(
                ($lockedUntil - time()) / 60
            )
        );

        $error =
            "Too many failed attempts. "
            . "Try again in {$remainingMinutes} minute(s).";
    } else {
        $username = clean_text(
            $_POST['username'] ?? '',
            50
        );

        $password = (string) (
            $_POST['password'] ?? ''
        );

        if (
            $username === ''
            || $password === ''
        ) {
            $error =
                'Username and password are required.';
        } else {
            $statement = $pdo->prepare(
                'SELECT
                    id,
                    username,
                    password
                FROM admins
                WHERE username = ?
                LIMIT 1'
            );

            $statement->execute([
                $username,
            ]);

            $admin = $statement->fetch();

            if (
                $admin
                && password_verify(
                    $password,
                    (string) $admin['password']
                )
            ) {
                if (
                    password_needs_rehash(
                        (string) $admin['password'],
                        PASSWORD_DEFAULT
                    )
                ) {
                    $rehash = $pdo->prepare(
                        'UPDATE admins
                        SET password = ?
                        WHERE id = ?'
                    );

                    $rehash->execute([
                        password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        ),
                        $admin['id'],
                    ]);
                }

                session_regenerate_id(true);

                unset(
                    $_SESSION['login_attempts'],
                    $_SESSION['login_locked_until']
                );

                $_SESSION['admin_logged_in'] =
                    true;

                $_SESSION['admin_id'] =
                    (int) $admin['id'];

                $_SESSION['admin_username'] =
                    (string) $admin['username'];

                $_SESSION['admin_last_activity'] =
                    time();

                redirect('index.php');
            }

            $attempts = (int) (
                $_SESSION['login_attempts']
                ?? 0
            ) + 1;

            $_SESSION['login_attempts'] =
                $attempts;

            if ($attempts >= 5) {
                $_SESSION['login_locked_until'] =
                    time() + 900;

                $lockedUntil = (int) (
                    $_SESSION['login_locked_until']
                );

                $error =
                    'Too many failed attempts. '
                    . 'Login is locked for 15 minutes.';
            } else {
                $remaining =
                    5 - $attempts;

                $error =
                    "Incorrect username or password. "
                    . "{$remaining} attempt(s) remaining.";
            }
        }
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
        Admin Login | Raj Agency
    </title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .glass {
            background: rgba(255, 255, 255, .035);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, .08);
        }
    </style>
</head>

<body class="bg-[#050505] min-h-screen flex items-center justify-center relative overflow-hidden p-4 text-white">
<?php require __DIR__ . '/admin_sidebar.php'; ?>


<div class="absolute -top-32 -left-32 w-96 h-96 bg-yellow-500/10 rounded-full blur-[120px]"></div>

<div class="absolute -bottom-32 -right-32 w-96 h-96 bg-purple-600/10 rounded-full blur-[120px]"></div>

<main class="relative z-10 w-full max-w-md p-8 glass rounded-3xl shadow-2xl">

    <div class="text-center mb-8">

        <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-2xl bg-yellow-500/10 text-yellow-500 mb-4 border border-yellow-500/20">

            <svg
                class="w-8 h-8"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                ></path>
            </svg>

        </div>

        <h1 class="text-3xl font-bold">
            Admin Login
        </h1>

        <p class="text-gray-500 text-sm mt-2">
            Secure Raj Agency dashboard
        </p>

    </div>

    <?php if ($error): ?>

        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-300 text-sm text-center">
            <?= e($error) ?>
        </div>

    <?php endif; ?>

    <?php if ($success): ?>

        <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-300 text-sm text-center">
            <?= e($success) ?>
        </div>

    <?php endif; ?>

    <form
        method="POST"
        class="space-y-6"
        autocomplete="on"
    >

        <?= csrf_field() ?>

        <div>
            <label
                for="username"
                class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2"
            >
                Username
            </label>

            <input
                id="username"
                type="text"
                name="username"
                maxlength="50"
                autocomplete="username"
                required
                autofocus
                value="<?= e($_POST['username'] ?? '') ?>"
                class="w-full bg-black/50 border border-white/10 px-5 py-4 rounded-xl outline-none focus:border-yellow-500/60 transition"
                placeholder="Enter username"
            >
        </div>

        <div>
            <label
                for="password"
                class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2"
            >
                Password
            </label>

            <input
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                required
                class="w-full bg-black/50 border border-white/10 px-5 py-4 rounded-xl outline-none focus:border-yellow-500/60 transition"
                placeholder="Enter password"
            >
        </div>

        <button
            type="submit"
            <?= $lockedUntil > time() ? 'disabled' : '' ?>
            class="w-full py-4 bg-gradient-to-r from-yellow-500 to-yellow-600 disabled:opacity-40 disabled:cursor-not-allowed text-black font-bold rounded-xl hover:shadow-[0_0_20px_rgba(234,179,8,.3)] transition"
        >
            Access Dashboard
        </button>

    </form>

    <div class="mt-8 text-center">

        <a
            href="../../index.php"
            class="text-xs text-gray-500 hover:text-white transition"
        >
            ← Back to Website
        </a>

    </div>

</main>

</body>
</html>