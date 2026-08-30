<?php
declare(strict_types=1);

$order =
    $_SESSION['order_success']
    ?? null;

unset(
    $_SESSION['order_success']
);

if (!is_array($order)) {
    redirect(
        'index.php?page=products'
    );
}
?>

<main class="min-h-screen pt-32 pb-20 bg-[#050505] flex items-center justify-center px-5">

    <section class="w-full max-w-2xl bg-[#111] border border-white/10 rounded-3xl p-8 md:p-12 text-center shadow-2xl">

        <div class="w-20 h-20 mx-auto rounded-full bg-green-500/10 border border-green-500/20 flex items-center justify-center text-green-400 text-4xl mb-6">
            ✓
        </div>

        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">
            Order Request Received
        </h1>

        <p class="text-gray-400 mb-8">
            Thank you,
            <?= e($order['full_name'] ?? '') ?>.
            We will contact you after reviewing your order.
        </p>

        <div class="bg-black/50 border border-white/5 rounded-2xl p-6 text-left space-y-4 mb-8">

            <div class="flex justify-between gap-5">

                <span class="text-gray-500">
                    Order number
                </span>

                <strong class="text-yellow-500">
                    <?= e($order['order_number'] ?? '') ?>
                </strong>

            </div>

            <div class="flex justify-between gap-5">

                <span class="text-gray-500">
                    Email
                </span>

                <strong class="text-white break-all">
                    <?= e($order['email'] ?? '') ?>
                </strong>

            </div>

            <div class="flex justify-between gap-5">

                <span class="text-gray-500">
                    Total
                </span>

                <strong class="text-white">
                    $<?= number_format(
                        (float) (
                            $order['total']
                            ?? 0
                        ),
                        2
                    ) ?>
                </strong>

            </div>

            <div class="flex justify-between gap-5">

                <span class="text-gray-500">
                    Status
                </span>

                <strong class="text-yellow-400">
                    Pending confirmation
                </strong>

            </div>

        </div>

        <p class="text-sm text-gray-500 mb-7">
            This is not a payment receipt.
            Payment and delivery will be confirmed separately.
        </p>

        <a
            href="index.php?page=products"
            class="inline-block px-7 py-3.5 rounded-xl bg-yellow-500 text-black font-bold hover:bg-yellow-400"
        >
            Continue Browsing
        </a>

    </section>

</main>