<?php
declare(strict_types=1);

require_once __DIR__
    . '/../includes/cart.php';

$cartItems =
    load_cart_items($pdo);

if ($cartItems === []) {
    flash(
        'error',
        'Your cart is empty.'
    );

    redirect(
        'index.php?page=products'
    );
}

$total = cart_total($cartItems);

$checkoutError =
    flash('checkout_error');

$old =
    $_SESSION['_checkout_old']
    ?? [];

unset(
    $_SESSION['_checkout_old']
);
?>

<main class="pt-32 pb-20 min-h-screen bg-[#050505]">

    <section class="max-w-6xl mx-auto px-5">

        <div class="mb-9">

            <h1 class="text-4xl md:text-5xl font-bold text-white mb-3">
                Complete Your Order
            </h1>

            <p class="text-gray-400">
                Submit your details. We will confirm payment and delivery with you directly.
            </p>

        </div>

        <?php if ($checkoutError): ?>

            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-300">
                <?= e($checkoutError) ?>
            </div>

        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-7">

                <form
                    id="order-form"
                    action="api/process_order.php"
                    method="POST"
                    class="bg-[#111] border border-white/10 rounded-2xl p-6 md:p-8"
                >

                    <?= csrf_field() ?>

                    <h2 class="text-xl font-bold text-white mb-6">
                        Your Information
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>

                            <label
                                for="full_name"
                                class="block text-sm text-gray-400 mb-2"
                            >
                                Full name
                            </label>

                            <input
                                id="full_name"
                                name="full_name"
                                type="text"
                                maxlength="100"
                                required
                                value="<?= e($old['full_name'] ?? '') ?>"
                                class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                            >

                        </div>

                        <div>

                            <label
                                for="email"
                                class="block text-sm text-gray-400 mb-2"
                            >
                                Email address
                            </label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                maxlength="190"
                                required
                                value="<?= e($old['email'] ?? '') ?>"
                                class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                            >

                        </div>

                        <div>

                            <label
                                for="phone"
                                class="block text-sm text-gray-400 mb-2"
                            >
                                Phone / WhatsApp
                            </label>

                            <input
                                id="phone"
                                name="phone"
                                type="tel"
                                maxlength="30"
                                required
                                value="<?= e($old['phone'] ?? '') ?>"
                                placeholder="+8801XXXXXXXXX"
                                class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                            >

                        </div>

                        <div>

                            <label
                                for="country"
                                class="block text-sm text-gray-400 mb-2"
                            >
                                Country
                            </label>

                            <input
                                id="country"
                                name="country"
                                type="text"
                                maxlength="80"
                                required
                                value="<?= e($old['country'] ?? 'Bangladesh') ?>"
                                class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                            >

                        </div>

                        <div class="md:col-span-2">

                            <label
                                for="notes"
                                class="block text-sm text-gray-400 mb-2"
                            >
                                Project requirements
                                <span class="text-gray-600">
                                    (optional)
                                </span>
                            </label>

                            <textarea
                                id="notes"
                                name="notes"
                                rows="5"
                                maxlength="1000"
                                placeholder="Tell us about required customization, delivery time, or other details."
                                class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                            ><?= e($old['notes'] ?? '') ?></textarea>

                        </div>

                    </div>

                    <div class="mt-6 p-4 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-300 text-sm">

                        <strong>
                            No automatic payment will be charged.
                        </strong>

                        After submitting, Raj Agency will contact you to confirm payment and delivery.

                    </div>

                </form>

            </div>

            <div class="lg:col-span-5">

                <div class="bg-[#111] border border-white/10 rounded-2xl p-6 sticky top-28">

                    <h2 class="text-xl font-bold mb-5">
                        Order Summary
                    </h2>

                    <div class="space-y-4 max-h-[360px] overflow-y-auto pr-1">

                        <?php foreach ($cartItems as $item): ?>

                            <div class="bg-black/50 border border-white/5 rounded-xl p-4">

                                <div class="flex justify-between gap-4">

                                    <div class="min-w-0">

                                        <p class="font-bold text-white truncate">
                                            <?= e($item['title']) ?>
                                        </p>

                                        <p class="text-xs text-gray-500 mt-1">
                                            $<?= number_format($item['price'], 2) ?>
                                            ×
                                            <?= (int) $item['quantity'] ?>
                                        </p>

                                    </div>

                                    <p class="font-bold text-yellow-500">
                                        $<?= number_format($item['line_total'], 2) ?>
                                    </p>

                                </div>

                                <div class="flex items-center gap-2 mt-4">

                                    <form
                                        action="api/cart_action.php"
                                        method="POST"
                                    >

                                        <?= csrf_field() ?>

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="decrease"
                                        >

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?= (int) $item['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10"
                                        >
                                            −
                                        </button>

                                    </form>

                                    <span class="min-w-8 text-center font-bold">
                                        <?= (int) $item['quantity'] ?>
                                    </span>

                                    <form
                                        action="api/cart_action.php"
                                        method="POST"
                                    >

                                        <?= csrf_field() ?>

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="increase"
                                        >

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?= (int) $item['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10"
                                        >
                                            +
                                        </button>

                                    </form>

                                    <form
                                        action="api/cart_action.php"
                                        method="POST"
                                        class="ml-auto"
                                    >

                                        <?= csrf_field() ?>

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="remove"
                                        >

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?= (int) $item['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="text-xs text-red-400 hover:text-red-300"
                                        >
                                            Remove
                                        </button>

                                    </form>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                    <div class="border-t border-white/10 mt-6 pt-5 flex items-center justify-between">

                        <span class="font-bold text-lg">
                            Total
                        </span>

                        <span class="text-3xl font-bold text-yellow-500">
                            $<?= number_format($total, 2) ?>
                        </span>

                    </div>

                    <button
                        type="submit"
                        form="order-form"
                        class="w-full mt-6 py-4 rounded-xl bg-yellow-500 text-black font-bold hover:bg-yellow-400"
                    >
                        Submit Order Request
                    </button>

                    <p class="text-xs text-gray-500 text-center mt-4">
                        Prices are securely calculated from the database.
                    </p>

                </div>

            </div>

        </div>

    </section>

</main>