<?php
declare(strict_types=1);

require_once '../../config/db.php';
require_once '../../includes/service_view.php';
require_admin();

$allowedStatuses = [
    'new',
    'read',
    'replied',
    'archived',
];

/*
|--------------------------------------------------------------------------
| Message Actions
|--------------------------------------------------------------------------
*/

if (request_is_post()) {
    verify_csrf();

    $action = (string) ($_POST['action'] ?? '');

    $messageId = filter_input(
        INPUT_POST,
        'message_id',
        FILTER_VALIDATE_INT
    );

    if (!$messageId || $messageId < 1) {
        flash('error', 'Invalid message ID.');

        redirect('messages.php');
    }

    if ($action === 'update_status') {
        $status = (string) ($_POST['status'] ?? '');

        if (!in_array($status, $allowedStatuses, true)) {
            flash('error', 'Invalid message status.');

            redirect('messages.php?view=' . $messageId);
        }

        $statement = $pdo->prepare(
            'UPDATE messages
             SET status = ?
             WHERE id = ?'
        );

        $statement->execute([
            $status,
            $messageId,
        ]);

        flash(
            'success',
            'Message status updated successfully.'
        );

        redirect('messages.php?view=' . $messageId);
    }

    if ($action === 'delete_message') {
        $statement = $pdo->prepare(
            'DELETE FROM messages
             WHERE id = ?'
        );

        $statement->execute([$messageId]);

        if ($statement->rowCount() > 0) {
            flash(
                'success',
                'Message deleted successfully.'
            );
        } else {
            flash(
                'error',
                'Message was not found.'
            );
        }

        redirect('messages.php');
    }

    flash('error', 'Invalid message action.');

    redirect('messages.php');
}

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$statusFilter = clean_text(
    $_GET['status'] ?? 'all',
    20
);

if (
    $statusFilter !== 'all' &&
    !in_array(
        $statusFilter,
        $allowedStatuses,
        true
    )
) {
    $statusFilter = 'all';
}

$search = clean_text(
    $_GET['search'] ?? '',
    100
);

$where = [];
$parameters = [];

if ($statusFilter !== 'all') {
    $where[] = 'status = ?';
    $parameters[] = $statusFilter;
}

if ($search !== '') {
    $where[] = '(
        name LIKE ?
        OR email LIKE ?
        OR service LIKE ?
        OR message LIKE ?
    )';

    $searchValue = '%' . $search . '%';

    $parameters[] = $searchValue;
    $parameters[] = $searchValue;
    $parameters[] = $searchValue;
    $parameters[] = $searchValue;
}

$whereSql = $where !== []
    ? ' WHERE ' . implode(' AND ', $where)
    : '';

$messageStatement = $pdo->prepare(
    'SELECT
        id,
        name,
        email,
        service,
        message,
        status,
        created_at
     FROM messages' .
     $whereSql .
    ' ORDER BY created_at DESC, id DESC
      LIMIT 100'
);

$messageStatement->execute($parameters);

$messages = $messageStatement->fetchAll();

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$totalMessages = (int) $pdo->query(
    'SELECT COUNT(*) FROM messages'
)->fetchColumn();

$newMessages = (int) $pdo->query(
    "SELECT COUNT(*)
     FROM messages
     WHERE status = 'new'"
)->fetchColumn();

$readMessages = (int) $pdo->query(
    "SELECT COUNT(*)
     FROM messages
     WHERE status = 'read'"
)->fetchColumn();

$repliedMessages = (int) $pdo->query(
    "SELECT COUNT(*)
     FROM messages
     WHERE status = 'replied'"
)->fetchColumn();

/*
|--------------------------------------------------------------------------
| Selected Message
|--------------------------------------------------------------------------
*/

$selectedMessage = null;

$viewMessageId = filter_input(
    INPUT_GET,
    'view',
    FILTER_VALIDATE_INT
);

if ($viewMessageId && $viewMessageId > 0) {
    $selectedStatement = $pdo->prepare(
        'SELECT *
         FROM messages
         WHERE id = ?
         LIMIT 1'
    );

    $selectedStatement->execute([
        $viewMessageId,
    ]);

    $selectedMessage = $selectedStatement->fetch();
}

$success = flash('success');
$error = flash('error');

function message_status_class(string $status): string
{
    return match ($status) {
        'new' =>
            'bg-blue-500/10 text-blue-400',

        'read' =>
            'bg-yellow-500/10 text-yellow-400',

        'replied' =>
            'bg-green-500/10 text-green-400',

        'archived' =>
            'bg-gray-500/10 text-gray-400',

        default =>
            'bg-gray-500/10 text-gray-400',
    };
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

    <title>Messages | Raj Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css"
        rel="stylesheet"
    >
</head>

<body class="bg-[#050505] text-white min-h-screen font-sans">
<?php require __DIR__ . '/admin_sidebar.php'; ?>



<header class="lg:hidden sticky top-0 z-40 bg-[#111] border-b border-white/10 px-5 py-4 flex items-center justify-between">

    <span class="font-bold text-yellow-500">
        RAJ ADMIN
    </span>

    <a
        href="index.php"
        class="text-sm text-gray-400"
    >
        Dashboard
    </a>

</header>

<aside class="hidden lg:flex w-64 h-screen bg-[#111] border-r border-white/5 flex-col fixed left-0 top-0">

    <div class="p-6 text-2xl font-bold text-yellow-500">
        RAJ ADMIN.
    </div>

    <nav class="flex-1 px-4 space-y-2">

        <a
            href="index.php"
            class="flex items-center gap-3 p-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"
        >
            <i class="ri-dashboard-line"></i>
            Dashboard
        </a>

        <a
            href="add_service.php"
            class="flex items-center gap-3 p-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"
        >
            <i class="ri-add-circle-line"></i>
            Add Service
        </a>

        <a
            href="orders.php"
            class="flex items-center gap-3 p-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"
        >
            <i class="ri-shopping-bag-3-line"></i>
            Orders
        </a>

        <a
            href="messages.php"
            class="flex items-center gap-3 p-3 bg-yellow-500/10 text-yellow-500 rounded-lg"
        >
            <i class="ri-mail-line"></i>
            Messages

            <?php if ($newMessages > 0): ?>

                <span class="ml-auto min-w-6 h-6 px-1 rounded-full bg-blue-500 text-white text-xs flex items-center justify-center">
                    <?= $newMessages ?>
                </span>

            <?php endif; ?>

        </a>

        <a
            href="../../index.php"
            target="_blank"
            rel="noopener"
            class="flex items-center gap-3 p-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"
        >
            <i class="ri-external-link-line"></i>
            View Website
        </a>

    </nav>

    <div class="p-4 border-t border-white/5">

        <p class="text-xs text-gray-500 mb-3 truncate">
            Signed in:
            <?= e(
                $_SESSION['admin_username'] ??
                'Admin'
            ) ?>
        </p>

        <form
            action="logout.php"
            method="POST"
        >
            <?= csrf_field() ?>

            <button
                type="submit"
                class="w-full p-3 text-left text-red-400 hover:bg-red-500/10 rounded-lg"
            >
                <i class="ri-logout-box-line mr-2"></i>
                Logout
            </button>

        </form>

    </div>

</aside>

<main class="lg:ml-64 p-5 md:p-8">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">

        <div>
            <h1 class="text-3xl font-bold">
                Messages
            </h1>

            <p class="text-gray-500 mt-1">
                View customer inquiries and reply by email.
            </p>
        </div>

        <a
            href="../../index.php?page=contact"
            target="_blank"
            rel="noopener"
            class="px-5 py-3 rounded-xl border border-white/10 text-center hover:bg-white/5"
        >
            <i class="ri-contacts-line mr-1"></i>
            View Contact Page
        </a>

    </div>

    <?php if ($success): ?>

        <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-300">
            <?= e($success) ?>
        </div>

    <?php endif; ?>

    <?php if ($error): ?>

        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-300">
            <?= e($error) ?>
        </div>

    <?php endif; ?>

    <section class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-8">

        <div class="bg-[#111] border border-white/5 rounded-2xl p-5">
            <p class="text-sm text-gray-500">Total</p>
            <p class="text-3xl font-bold mt-2"><?= $totalMessages ?></p>
        </div>

        <div class="bg-[#111] border border-white/5 rounded-2xl p-5">
            <p class="text-sm text-gray-500">New</p>
            <p class="text-3xl font-bold mt-2 text-blue-400"><?= $newMessages ?></p>
        </div>

        <div class="bg-[#111] border border-white/5 rounded-2xl p-5">
            <p class="text-sm text-gray-500">Read</p>
            <p class="text-3xl font-bold mt-2 text-yellow-400"><?= $readMessages ?></p>
        </div>

        <div class="bg-[#111] border border-white/5 rounded-2xl p-5">
            <p class="text-sm text-gray-500">Replied</p>
            <p class="text-3xl font-bold mt-2 text-green-400"><?= $repliedMessages ?></p>
        </div>

    </section>

    <form
        method="GET"
        action="messages.php"
        class="bg-[#111] border border-white/5 rounded-2xl p-4 mb-7 grid grid-cols-1 md:grid-cols-[1fr_220px_auto] gap-3"
    >

        <input
            type="search"
            name="search"
            maxlength="100"
            value="<?= e($search) ?>"
            placeholder="Search name, email, service or message..."
            class="w-full bg-black border border-white/10 rounded-xl p-3 outline-none focus:border-yellow-500"
        >

        <select
            name="status"
            class="w-full bg-black border border-white/10 rounded-xl p-3 outline-none focus:border-yellow-500"
        >

            <option value="all">All Status</option>

            <?php foreach ($allowedStatuses as $status): ?>

                <option
                    value="<?= e($status) ?>"
                    <?= $statusFilter === $status
                        ? 'selected'
                        : '' ?>
                >
                    <?= e(ucfirst($status)) ?>
                </option>

            <?php endforeach; ?>

        </select>

        <button
            type="submit"
            class="px-6 py-3 bg-yellow-500 text-black font-bold rounded-xl hover:bg-yellow-400"
        >
            Filter
        </button>

    </form>

    <section class="bg-[#111] rounded-2xl border border-white/5 overflow-x-auto">

        <table class="w-full min-w-[900px] text-left">

            <thead class="bg-white/5 text-gray-400 text-sm">

                <tr>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Service</th>
                    <th class="p-4">Message</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Date</th>
                    <th class="p-4 text-right">Action</th>
                </tr>

            </thead>

            <tbody class="divide-y divide-white/5">

                <?php if ($messages === []): ?>

                    <tr>
                        <td
                            colspan="6"
                            class="p-12 text-center text-gray-500"
                        >
                            No messages found.
                        </td>
                    </tr>

                <?php endif; ?>

                <?php foreach ($messages as $message): ?>

                    <?php
                    $createdTimestamp = strtotime(
                        (string) $message['created_at']
                    );

                    if ($createdTimestamp === false) {
                        $createdTimestamp = time();
                    }
                    ?>

                    <tr class="hover:bg-white/[.03]">

                        <td class="p-4">

                            <strong>
                                <?= e($message['name']) ?>
                            </strong>

                            <a
                                href="mailto:<?= e($message['email']) ?>"
                                class="block text-xs text-blue-400 hover:underline mt-1"
                            >
                                <?= e($message['email']) ?>
                            </a>

                        </td>

                        <td class="p-4 text-gray-300">
                            <?= e(
                                $message['service'] ??
                                'General Inquiry'
                            ) ?>
                        </td>

                        <td class="p-4 text-gray-400 max-w-sm">

                            <p class="truncate">
                                <?= e(
                                    service_excerpt(
                                        $message['message'],
                                        100
                                    )
                                ) ?>
                            </p>

                        </td>

                        <td class="p-4">

                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold <?= e(
                                message_status_class(
                                    (string) $message['status']
                                )
                            ) ?>">
                                <?= e(
                                    ucfirst(
                                        (string) $message['status']
                                    )
                                ) ?>
                            </span>

                        </td>

                        <td class="p-4 text-sm text-gray-400">

                            <?= e(
                                date(
                                    'd M Y',
                                    $createdTimestamp
                                )
                            ) ?>

                            <span class="block text-xs text-gray-600 mt-1">
                                <?= e(
                                    date(
                                        'h:i A',
                                        $createdTimestamp
                                    )
                                ) ?>
                            </span>

                        </td>

                        <td class="p-4 text-right">

                            <a
                                href="messages.php?view=<?= (int) $message['id'] ?>"
                                class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20"
                            >
                                <i class="ri-eye-line"></i>
                                View
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </section>

</main>

<?php if ($selectedMessage): ?>

    <?php
    $replySubject = rawurlencode(
        'Re: ' .
        (
            $selectedMessage['service'] ??
            'Your inquiry to Raj Agency'
        )
    );

    $replyBody = rawurlencode(
        'Hello ' .
        $selectedMessage['name'] .
        ",\n\n"
    );
    ?>

    <div class="fixed inset-0 z-50 bg-black/85 backdrop-blur-sm overflow-y-auto p-4 md:p-8">

        <div class="max-w-3xl mx-auto bg-[#111] border border-white/10 rounded-3xl overflow-hidden shadow-2xl">

            <div class="p-5 md:p-7 border-b border-white/10 flex items-start justify-between gap-5">

                <div>
                    <span class="text-sm text-gray-500">
                        Message Details
                    </span>

                    <h2 class="text-2xl font-bold mt-1">
                        <?= e($selectedMessage['name']) ?>
                    </h2>
                </div>

                <a
                    href="messages.php"
                    class="w-11 h-11 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-xl"
                    aria-label="Close"
                >
                    <i class="ri-close-line"></i>
                </a>

            </div>

            <div class="p-5 md:p-7">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">

                    <div class="bg-black/40 border border-white/5 rounded-2xl p-5">

                        <span class="text-xs text-gray-500">
                            Email Address
                        </span>

                        <a
                            href="mailto:<?= e($selectedMessage['email']) ?>"
                            class="block text-blue-400 hover:underline mt-2 break-all"
                        >
                            <?= e($selectedMessage['email']) ?>
                        </a>

                    </div>

                    <div class="bg-black/40 border border-white/5 rounded-2xl p-5">

                        <span class="text-xs text-gray-500">
                            Interested Service
                        </span>

                        <strong class="block mt-2">
                            <?= e(
                                $selectedMessage['service'] ??
                                'General Inquiry'
                            ) ?>
                        </strong>

                    </div>

                </div>

                <div class="bg-black/40 border border-white/5 rounded-2xl p-5 mb-6">

                    <h3 class="font-bold mb-4">
                        Customer Message
                    </h3>

                    <p class="text-gray-300 leading-7 whitespace-pre-wrap break-words"><?= e(
                        $selectedMessage['message']
                    ) ?></p>

                </div>

                <a
                    href="mailto:<?= e($selectedMessage['email']) ?>?subject=<?= e($replySubject) ?>&body=<?= e($replyBody) ?>"
                    class="flex items-center justify-center gap-2 w-full py-4 bg-blue-500 text-white font-bold rounded-xl hover:bg-blue-400 mb-5"
                >
                    <i class="ri-mail-send-line"></i>
                    Reply by Email
                </a>

                <form
                    method="POST"
                    action="messages.php?view=<?= (int) $selectedMessage['id'] ?>"
                    class="bg-black/40 border border-white/5 rounded-2xl p-5 mb-5"
                >
                    <?= csrf_field() ?>

                    <input
                        type="hidden"
                        name="action"
                        value="update_status"
                    >

                    <input
                        type="hidden"
                        name="message_id"
                        value="<?= (int) $selectedMessage['id'] ?>"
                    >

                    <label
                        for="message-status"
                        class="block font-bold mb-3"
                    >
                        Update Message Status
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-3">

                        <select
                            id="message-status"
                            name="status"
                            class="w-full bg-[#111] border border-white/10 rounded-xl p-3 outline-none focus:border-yellow-500"
                        >

                            <?php foreach ($allowedStatuses as $status): ?>

                                <option
                                    value="<?= e($status) ?>"
                                    <?= $selectedMessage['status'] === $status
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= e(ucfirst($status)) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <button
                            type="submit"
                            class="px-6 py-3 bg-yellow-500 text-black font-bold rounded-xl hover:bg-yellow-400"
                        >
                            Update
                        </button>

                    </div>

                </form>

                <form
                    method="POST"
                    action="messages.php"
                    onsubmit="return confirm('Delete this message permanently?');"
                >
                    <?= csrf_field() ?>

                    <input
                        type="hidden"
                        name="action"
                        value="delete_message"
                    >

                    <input
                        type="hidden"
                        name="message_id"
                        value="<?= (int) $selectedMessage['id'] ?>"
                    >

                    <button
                        type="submit"
                        class="w-full py-3 border border-red-500/30 text-red-400 rounded-xl hover:bg-red-500/10 font-bold"
                    >
                        <i class="ri-delete-bin-line mr-1"></i>
                        Delete Message
                    </button>

                </form>

            </div>

        </div>

    </div>

<?php endif; ?>

</body>
</html>