<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';

$page = clean_text(
    $_GET['page'] ?? 'home',
    50
);

/*
|--------------------------------------------------------------------------
| Cart API compatibility route
|--------------------------------------------------------------------------
*/

if ($page === 'cart_action') {
    require __DIR__ . '/api/cart_action.php';

    exit;
}

/*
|--------------------------------------------------------------------------
| Allowed Frontend Pages
|--------------------------------------------------------------------------
*/

$allowedPages = [
    'home',
    'feed',

    // Social-style project portfolio
    'portfolio',
    'project-details',

    // Purchasable services/products
    'products',
    'service-details',
    'checkout',
    'order-success',

    'contact',
    'about',
];

if (!in_array($page, $allowedPages, true)) {
    http_response_code(404);

    $page = '404';
}

/*
|--------------------------------------------------------------------------
| Website Header
|--------------------------------------------------------------------------
*/

require __DIR__ . '/includes/header.php';

/*
|--------------------------------------------------------------------------
| Page Content
|--------------------------------------------------------------------------
*/

if ($page === '404') {
    ?>

    <main class="min-h-screen pt-40 text-center px-5 bg-[#050505]">

        <i class="ri-error-warning-line text-7xl text-yellow-500"></i>

        <h1 class="text-5xl font-bold text-white mt-5">
            404
        </h1>

        <p class="text-gray-400 mt-3">
            The requested page was not found.
        </p>

        <a
            href="index.php?page=home"
            class="inline-block mt-7 px-6 py-3 rounded-xl bg-yellow-500 text-black font-bold"
        >
            Return Home
        </a>

    </main>

    <?php
} else {
    require __DIR__ . '/pages/' . $page . '.php';
}

/*
|--------------------------------------------------------------------------
| Website Footer
|--------------------------------------------------------------------------
*/

require __DIR__ . '/includes/footer.php';