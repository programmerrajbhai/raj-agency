<?php
declare(strict_types=1);

require_once '../../config/db.php';

require_admin();

$allowedStatuses = [
    'pending',
    'confirmed',
    'processing',
    'completed',
    'cancelled',
];

/*
|--------------------------------------------------------------------------
| Update or delete order
|--------------------------------------------------------------------------
*/

if (request_is_post()) {
    verify_csrf();

    $action = (string) ($_POST['action'] ?? '');

    $orderId = filter_input(
        INPUT_POST,
        'order_id',
        FILTER_VALIDATE_INT
    );

    if (!$orderId || $orderId < 1) {
        flash('error', 'Invalid order ID.');

        redirect('orders.php');
    }

    if ($action === 'update_status') {
        $status = (string) ($_POST['status'] ?? '');

        if (!in_array($status, $allowedStatuses, true)) {
            flash('error', 'Invalid order status.');

            redirect('orders.php?view=' . $orderId);
        }

        $statement = $pdo->prepare(
            'UPDATE orders
             SET status = ?
             WHERE id = ?'
        );

        $statement->execute([
            $status,
            $orderId,
        ]);

        if ($statement->rowCount() > 0) {
            flash(
                'success',
                'Order status updated successfully.'
            );
        } else {
            flash(
                'success',
                'Order status is already updated.'
            );
        }

        redirect('orders.php?view=' . $orderId);
    }

    if ($action === 'delete_order') {
        try {
            $statement = $pdo->prepare(
                'DELETE FROM orders
                 WHERE id = ?'
            );

            $statement->execute([$orderId]);

            if ($statement->rowCount() > 0) {
                flash(
                    'success',
                    'Order deleted successfully.'
                );
            } else {
                flash(
                    'error',
                    'Order was not found.'
                );
            }
        } catch (Throwable $exception) {
            error_log(
                'Order deletion failed: ' .
                $exception->getMessage()
            );

            flash(
                'error',
                'The order could not be deleted.'
            );
        }

        redirect('orders.php');
    }

    flash('error', 'Invalid order action.');

    redirect('orders.php');
}

/*
|--------------------------------------------------------------------------
| Order filters
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
        order_number LIKE ?
        OR full_name LIKE ?
        OR email LIKE ?
        OR phone LIKE ?
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

$orderStatement = $pdo->prepare(
    'SELECT
        id,
        order_number,
        full_name,
        email,
        phone,
        country,
        total_amount,
        status,
        created_at
     FROM orders' .
     $whereSql .
    ' ORDER BY created_at DESC, id DESC
      LIMIT 100'
);

$orderStatement->execute($parameters);

$orders = $orderStatement->fetchAll();

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$totalOrders = (int) $pdo->query(
    'SELECT COUNT(*) FROM orders'
)->fetchColumn();

$pendingOrders = (int) $pdo->query(
    "SELECT COUNT(*)
     FROM orders
     WHERE status = 'pending'"
)->fetchColumn();

$processingOrders = (int) $pdo->query(
    "SELECT COUNT(*)
     FROM orders
     WHERE status IN ('confirmed', 'processing')"
)->fetchColumn();

$completedOrders = (int) $pdo->query(
    "SELECT COUNT(*)
     FROM orders
     WHERE status = 'completed'"
)->fetchColumn();

/*
|--------------------------------------------------------------------------
| Selected order details
|--------------------------------------------------------------------------
*/

$selectedOrder = null;
$selectedItems = [];

$viewOrderId = filter_input(
    INPUT_GET,
    'view',
    FILTER_VALIDATE_INT
);

if ($viewOrderId && $viewOrderId > 0) {
    $selectedStatement = $pdo->prepare(
        'SELECT *
         FROM orders
         WHERE id = ?
         LIMIT 1'
    );

    $selectedStatement->execute([
        $viewOrderId,
    ]);

    $selectedOrder = $selectedStatement->fetch();

    if ($selectedOrder) {
        $itemStatement = $pdo->prepare(
            'SELECT
                id,
                service_id,
                service_title,
                unit_price,
                quantity,
                line_total
             FROM order_items
             WHERE order_id = ?
             ORDER BY id ASC'
        );

        $itemStatement->execute([
            $viewOrderId,
        ]);

        $selectedItems = $itemStatement->fetchAll();
    }
}

$success = flash('success');
$error = flash('error');

function order_status_class(string $status): string
{
    return match ($status) {
        'pending' =>
            'bg-yellow-500/10 text-yellow-400',

        'confirmed' =>
            'bg-blue-500/10 text-blue-400',

        'processing' =>
            'bg-purple-500/10 text-purple-400',

        'completed' =>
            'bg-green-500/10 text-green-400',

        'cancelled' =>
            'bg-red-500/10 text-red-400',

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

    <title>Orders | Raj Admin</title>

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
            class="flex items-center gap-3 p-3 bg-yellow-500/10 text-yellow-500 rounded-lg"
        >
            <i class="ri-shopping-bag-3-line"></i>
            Orders
        </a>

        <a
            href="messages.php"
            class="flex items-center gap-3 p-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"
        >
            <i class="ri-mail-line"></i>
            Messages
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
                Orders
            </h1>

            <p class="text-gray-500 mt-1">
                View and manage customer order requests.
            </p>
        </div>

        <a
            href="../../index.php?page=portfolio"
            target="_blank"
            rel="noopener"
            class="px-5 py-3 rounded-xl border border-white/10 text-center hover:bg-white/5"
        >
            <i class="ri-store-2-line mr-1"></i>
            View Store
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
            <p class="text-sm text-gray-500">
                Total
            </p>

            <p class="text-3xl font-bold mt-2">
                <?= $totalOrders ?>
            </p>
        </div>

        <div class="bg-[#111] border border-white/5 rounded-2xl p-5">
            <p class="text-sm text-gray-500">
                Pending
            </p>

            <p class="text-3xl font-bold mt-2 text-yellow-400">
                <?= $pendingOrders ?>
            </p>
        </div>

        <div class="bg-[#111] border border-white/5 rounded-2xl p-5">
            <p class="text-sm text-gray-500">
                In Progress
            </p>

            <p class="text-3xl font-bold mt-2 text-purple-400">
                <?= $processingOrders ?>
            </p>
        </div>

        <div class="bg-[#111] border border-white/5 rounded-2xl p-5">
            <p class="text-sm text-gray-500">
                Completed
            </p>

            <p class="text-3xl font-bold mt-2 text-green-400">
                <?= $completedOrders ?>
            </p>
        </div>

    </section>

    <form
        method="GET"
        action="orders.php"
        class="bg-[#111] border border-white/5 rounded-2xl p-4 mb-7 grid grid-cols-1 md:grid-cols-[1fr_220px_auto] gap-3"
    >

        <input
            type="search"
            name="search"
            maxlength="100"
            value="<?= e($search) ?>"
            placeholder="Search order, name, email or phone..."
            class="w-full bg-black border border-white/10 rounded-xl p-3 outline-none focus:border-yellow-500"
        >

        <select
            name="status"
            class="w-full bg-black border border-white/10 rounded-xl p-3 outline-none focus:border-yellow-500"
        >
            <option value="all">
                All Status
            </option>

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

        <table class="w-full min-w-[950px] text-left">

            <thead class="bg-white/5 text-gray-400 text-sm">

                <tr>
                    <th class="p-4">Order</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Contact</th>
                    <th class="p-4">Total</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Date</th>
                    <th class="p-4 text-right">Action</th>
                </tr>

            </thead>

            <tbody class="divide-y divide-white/5">

                <?php if ($orders === []): ?>

                    <tr>
                        <td
                            colspan="7"
                            class="p-12 text-center text-gray-500"
                        >
                            No orders found.
                        </td>
                    </tr>

                <?php endif; ?>

                <?php foreach ($orders as $order): ?>

                    <?php
                    $createdTimestamp = strtotime(
                        (string) $order['created_at']
                    );

                    if ($createdTimestamp === false) {
                        $createdTimestamp = time();
                    }
                    ?>

                    <tr class="hover:bg-white/[.03]">

                        <td class="p-4">

                            <strong class="text-yellow-500">
                                <?= e(
                                    $order['order_number']
                                ) ?>
                            </strong>

                            <span class="block text-xs text-gray-600 mt-1">
                                ID: #<?= (int) $order['id'] ?>
                            </span>

                        </td>

                        <td class="p-4">

                            <strong>
                                <?= e($order['full_name']) ?>
                            </strong>

                            <span class="block text-xs text-gray-500 mt-1">
                                <?= e($order['country']) ?>
                            </span>

                        </td>

                        <td class="p-4">

                            <a
                                href="mailto:<?= e($order['email']) ?>"
                                class="block text-blue-400 hover:underline"
                            >
                                <?= e($order['email']) ?>
                            </a>

                            <a
                                href="tel:<?= e($order['phone']) ?>"
                                class="block text-sm text-gray-500 mt-1 hover:text-white"
                            >
                                <?= e($order['phone']) ?>
                            </a>

                        </td>

                        <td class="p-4 font-bold">
                            $<?= number_format(
                                (float) $order['total_amount'],
                                2
                            ) ?>
                        </td>

                        <td class="p-4">

                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold <?= e(
                                order_status_class(
                                    (string) $order['status']
                                )
                            ) ?>">
                                <?= e(
                                    ucfirst(
                                        (string) $order['status']
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
                                href="orders.php?view=<?= (int) $order['id'] ?>"
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

<?php if ($selectedOrder): ?>

    <div class="fixed inset-0 z-50 bg-black/85 backdrop-blur-sm overflow-y-auto p-4 md:p-8">

        <div class="max-w-4xl mx-auto bg-[#111] border border-white/10 rounded-3xl overflow-hidden shadow-2xl">

            <div class="p-5 md:p-7 border-b border-white/10 flex items-start justify-between gap-5">

                <div>

                    <span class="text-sm text-gray-500">
                        Order Details
                    </span>

                    <h2 class="text-2xl font-bold text-yellow-500 mt-1">
                        <?= e(
                            $selectedOrder['order_number']
                        ) ?>
                    </h2>

                </div>

                <a
                    href="orders.php"
                    class="w-11 h-11 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-xl"
                    aria-label="Close"
                >
                    <i class="ri-close-line"></i>
                </a>

            </div>

            <div class="p-5 md:p-7">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-7">

                    <div class="bg-black/40 border border-white/5 rounded-2xl p-5">

                        <h3 class="font-bold mb-4">
                            Customer Information
                        </h3>

                        <div class="space-y-3 text-sm">

                            <p>
                                <span class="text-gray-500">
                                    Name:
                                </span>

                                <?= e(
                                    $selectedOrder['full_name']
                                ) ?>
                            </p>

                            <p>
                                <span class="text-gray-500">
                                    Email:
                                </span>

                                <a
                                    href="mailto:<?= e(
                                        $selectedOrder['email']
                                    ) ?>"
                                    class="text-blue-400 hover:underline"
                                >
                                    <?= e(
                                        $selectedOrder['email']
                                    ) ?>
                                </a>
                            </p>

                            <p>
                                <span class="text-gray-500">
                                    Phone:
                                </span>

                                <a
                                    href="tel:<?= e(
                                        $selectedOrder['phone']
                                    ) ?>"
                                    class="text-green-400 hover:underline"
                                >
                                    <?= e(
                                        $selectedOrder['phone']
                                    ) ?>
                                </a>
                            </p>

                            <p>
                                <span class="text-gray-500">
                                    Country:
                                </span>

                                <?= e(
                                    $selectedOrder['country']
                                ) ?>
                            </p>

                        </div>

                    </div>

                    <div class="bg-black/40 border border-white/5 rounded-2xl p-5">

                        <h3 class="font-bold mb-4">
                            Order Information
                        </h3>

                        <div class="space-y-3 text-sm">

                            <p>
                                <span class="text-gray-500">
                                    Total:
                                </span>

                                <strong class="text-yellow-500">
                                    $<?= number_format(
                                        (float) $selectedOrder['total_amount'],
                                        2
                                    ) ?>
                                </strong>
                            </p>

                            <p>
                                <span class="text-gray-500">
                                    Status:
                                </span>

                                <?= e(
                                    ucfirst(
                                        (string) $selectedOrder['status']
                                    )
                                ) ?>
                            </p>

                            <p>
                                <span class="text-gray-500">
                                    Created:
                                </span>

                                <?= e(
                                    (string) $selectedOrder['created_at']
                                ) ?>
                            </p>

                        </div>

                    </div>

                </div>

                <?php if (!empty($selectedOrder['notes'])): ?>

                    <div class="bg-yellow-500/5 border border-yellow-500/10 rounded-2xl p-5 mb-7">

                        <h3 class="font-bold text-yellow-500 mb-2">
                            Customer Notes
                        </h3>

                        <p class="text-gray-300 whitespace-pre-line">
                            <?= e($selectedOrder['notes']) ?>
                        </p>

                    </div>

                <?php endif; ?>

                <div class="mb-7">

                    <h3 class="text-xl font-bold mb-4">
                        Ordered Services
                    </h3>

                    <div class="border border-white/10 rounded-2xl overflow-x-auto">

                        <table class="w-full min-w-[650px] text-left">

                            <thead class="bg-white/5 text-sm text-gray-400">

                                <tr>
                                    <th class="p-4">Service</th>
                                    <th class="p-4">Price</th>
                                    <th class="p-4">Quantity</th>
                                    <th class="p-4 text-right">Total</th>
                                </tr>

                            </thead>

                            <tbody class="divide-y divide-white/5">

                                <?php foreach ($selectedItems as $item): ?>

                                    <tr>

                                        <td class="p-4 font-bold">
                                            <?= e(
                                                $item['service_title']
                                            ) ?>
                                        </td>

                                        <td class="p-4">
                                            $<?= number_format(
                                                (float) $item['unit_price'],
                                                2
                                            ) ?>
                                        </td>

                                        <td class="p-4">
                                            <?= (int) $item['quantity'] ?>
                                        </td>

                                        <td class="p-4 text-right font-bold">
                                            $<?= number_format(
                                                (float) $item['line_total'],
                                                2
                                            ) ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                                <?php if ($selectedItems === []): ?>

                                    <tr>
                                        <td
                                            colspan="4"
                                            class="p-8 text-center text-gray-500"
                                        >
                                            No order items found.
                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

                <form
                    method="POST"
                    action="orders.php?view=<?= (int) $selectedOrder['id'] ?>"
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
                        name="order_id"
                        value="<?= (int) $selectedOrder['id'] ?>"
                    >

                    <label
                        for="order-status"
                        class="block font-bold mb-3"
                    >
                        Update Order Status
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-3">

                        <select
                            id="order-status"
                            name="status"
                            class="w-full bg-[#111] border border-white/10 rounded-xl p-3 outline-none focus:border-yellow-500"
                        >

                            <?php foreach ($allowedStatuses as $status): ?>

                                <option
                                    value="<?= e($status) ?>"
                                    <?= $selectedOrder['status'] === $status
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
                            Update Status
                        </button>

                    </div>

                </form>

                <form
                    method="POST"
                    action="orders.php"
                    onsubmit="return confirm('Delete this order permanently?');"
                >
                    <?= csrf_field() ?>

                    <input
                        type="hidden"
                        name="action"
                        value="delete_order"
                    >

                    <input
                        type="hidden"
                        name="order_id"
                        value="<?= (int) $selectedOrder['id'] ?>"
                    >

                    <button
                        type="submit"
                        class="w-full py-3 border border-red-500/30 text-red-400 rounded-xl hover:bg-red-500/10 font-bold"
                    >
                        <i class="ri-delete-bin-line mr-1"></i>
                        Delete Order
                    </button>

                </form>

            </div>

        </div>

    </div>

<?php endif; ?>

</body>
</html>