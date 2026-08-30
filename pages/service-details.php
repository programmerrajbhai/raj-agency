<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/service_view.php';

$serviceId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

$product = false;

if ($serviceId && $serviceId > 0) {
    $statement = $pdo->prepare(
        'SELECT *
         FROM services
         WHERE id = ?
           AND is_active = 1
         LIMIT 1'
    );

    $statement->execute([$serviceId]);

    $product = $statement->fetch();
}

if (!$product) {
    http_response_code(404);
    ?>

    <main class="min-h-screen pt-40 px-5 text-center bg-[#050505]">

        <i class="ri-error-warning-line text-7xl text-yellow-500"></i>

        <h1 class="text-4xl font-bold mt-5">
            Service Not Found
        </h1>

        <p class="text-gray-400 mt-3">
            This service is unavailable or has been removed.
        </p>

        <a
            href="index.php?page=products"
            class="inline-block mt-7 px-6 py-3 bg-yellow-500 text-black font-bold rounded-xl"
        >
            Back to Portfolio
        </a>

    </main>

    <?php
    return;
}

$updateViews = $pdo->prepare(
    'UPDATE services
     SET views = views + 1
     WHERE id = ?'
);

$updateViews->execute([
    (int) $product['id'],
]);

$features = service_feature_data(
    $product['features'] ?? ''
);

$media = service_media_items(
    $features,
    $product['thumbnail'] ?? ''
);

$demoItems = service_demo_items(
    $features,
    $product['demo_url'] ?? ''
);

$meta = service_type_meta(
    $product['file_type'] ?? 'web'
);

$rawOverview = (string) (
    !empty($product['full_desc'])
        ? $product['full_desc']
        : ($product['short_desc'] ?? '')
);

$rawOverview = preg_replace(
    '/<[^>]+>/',
    ' ',
    $rawOverview
) ?? $rawOverview;

$overview = clean_text(
    $rawOverview,
    6000
);

$updatedAt = (string) (
    $product['updated_at'] ??
    $product['created_at'] ??
    ''
);

$displayDate = '';

if ($updatedAt !== '') {
    $updatedTimestamp = strtotime($updatedAt);

    if ($updatedTimestamp !== false) {
        $displayDate = date(
            'd M Y',
            $updatedTimestamp
        );
    }
}

$featureGroups = [
    'top' => [
        'title' => 'Top Features',
        'icon' => 'ri-fire-line',
        'color' => 'text-yellow-500',
    ],

    'admin' => [
        'title' => 'Admin Features',
        'icon' => 'ri-shield-user-line',
        'color' => 'text-purple-400',
    ],

    'user' => [
        'title' => 'User Features',
        'icon' => 'ri-user-star-line',
        'color' => 'text-blue-400',
    ],
];

$relatedStatement = $pdo->prepare(
    'SELECT
        id,
        title,
        price_basic,
        thumbnail
     FROM services
     WHERE is_active = 1
       AND file_type = ?
       AND id <> ?
     ORDER BY created_at DESC, id DESC
     LIMIT 3'
);

$relatedStatement->execute([
    $meta['key'],
    (int) $product['id'],
]);

$relatedServices = $relatedStatement->fetchAll();
?>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"
>

<main class="pt-32 pb-24 min-h-screen bg-[#050505]">

    <section class="border-b border-white/5 bg-[#090909] py-10">

        <div class="max-w-7xl mx-auto px-5">

            <nav class="text-xs uppercase tracking-widest text-gray-500 mb-5">

                <a
                    href="index.php?page=home"
                    class="hover:text-white"
                >
                    Home
                </a>

                <span class="mx-2">/</span>

                <a
                    href="index.php?page=products"
                    class="hover:text-white"
                >
                    Portfolio
                </a>

                <span class="mx-2">/</span>

                <span class="text-yellow-500">
                    Details
                </span>

            </nav>

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-5">

                <div>

                    <span class="inline-flex items-center gap-2 text-xs font-bold text-yellow-500 border border-yellow-500/20 bg-yellow-500/5 px-3 py-1.5 rounded-full">

                        <i class="<?= e($meta['icon']) ?>"></i>

                        <?= e($meta['label']) ?>

                    </span>

                    <h1 class="text-4xl md:text-6xl font-display font-bold mt-4">
                        <?= e($product['title']) ?>
                    </h1>

                </div>

                <div class="text-sm text-gray-500 md:text-right">

                    <?php if ($displayDate !== ''): ?>

                        <span class="block">
                            Updated: <?= e($displayDate) ?>
                        </span>

                    <?php endif; ?>

                    <span class="block mt-1">

                        <i class="ri-eye-line"></i>

                        <?= number_format(
                            (int) $product['views'] + 1
                        ) ?>

                        views

                    </span>

                </div>

            </div>

        </div>

    </section>

    <section class="max-w-7xl mx-auto px-5 mt-10 grid grid-cols-1 lg:grid-cols-3 gap-9">

        <div class="lg:col-span-2 space-y-8">

            <div class="rounded-3xl overflow-hidden border border-white/10 bg-[#111]">

                <?php if ($media === []): ?>

                    <div class="aspect-video flex flex-col items-center justify-center bg-gradient-to-br from-[#171717] to-black">

                        <i class="<?= e($meta['icon']) ?> text-8xl text-yellow-500/40"></i>

                        <span class="text-gray-500 mt-3">
                            Preview is not available
                        </span>

                    </div>

                <?php else: ?>

                    <div class="swiper service-gallery aspect-video">

                        <div class="swiper-wrapper">

                            <?php foreach ($media as $item): ?>

                                <div class="swiper-slide bg-black flex items-center justify-center">

                                    <?php if ($item['type'] === 'youtube'): ?>

                                        <?php
                                        $youtubeId = service_youtube_id(
                                            $item['url']
                                        );
                                        ?>

                                        <iframe
                                            class="w-full h-full"
                                            src="https://www.youtube-nocookie.com/embed/<?= e($youtubeId) ?>?rel=0"
                                            title="<?= e($product['title']) ?>"
                                            loading="lazy"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            referrerpolicy="strict-origin-when-cross-origin"
                                            allowfullscreen
                                        ></iframe>

                                    <?php elseif ($item['type'] === 'video'): ?>

                                        <video
                                            src="<?= e($item['url']) ?>"
                                            controls
                                            playsinline
                                            preload="metadata"
                                            class="w-full h-full object-contain"
                                        ></video>

                                    <?php else: ?>

                                        <img
                                            src="<?= e($item['url']) ?>"
                                            alt="<?= e($product['title']) ?>"
                                            loading="lazy"
                                            class="w-full h-full object-contain"
                                        >

                                    <?php endif; ?>

                                </div>

                            <?php endforeach; ?>

                        </div>

                        <?php if (count($media) > 1): ?>

                            <div class="swiper-button-next !text-yellow-500"></div>

                            <div class="swiper-button-prev !text-yellow-500"></div>

                            <div class="swiper-pagination"></div>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            </div>

            <?php if ($demoItems !== []): ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <?php foreach ($demoItems as $demo): ?>

                        <a
                            href="<?= e($demo['url']) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="bg-[#111] border border-white/10 rounded-2xl p-5 hover:border-yellow-500/40 transition flex items-center gap-4"
                        >

                            <span class="w-12 h-12 rounded-xl bg-yellow-500/10 text-yellow-500 flex items-center justify-center text-2xl">

                                <i class="<?= e($demo['icon']) ?>"></i>

                            </span>

                            <span>

                                <strong class="block">
                                    <?= e($demo['title']) ?>
                                </strong>

                                <small class="text-gray-500">

                                    Open securely

                                    <i class="ri-external-link-line"></i>

                                </small>

                            </span>

                        </a>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

            <div class="bg-[#111] border border-white/10 rounded-3xl p-6 md:p-9">

                <h2 class="text-2xl font-bold border-l-4 border-yellow-500 pl-4">
                    Overview
                </h2>

                <p class="text-gray-400 leading-8 mt-5 whitespace-pre-line"><?= e(
                    $overview !== ''
                        ? $overview
                        : 'Complete details will be provided after your inquiry.'
                ) ?></p>

            </div>

            <?php foreach ($featureGroups as $key => $group): ?>

                <?php if ($features[$key] !== []): ?>

                    <div class="bg-[#111] border border-white/10 rounded-3xl p-6 md:p-9">

                        <h2 class="text-2xl font-bold flex items-center gap-3">

                            <i class="<?= e(
                                $group['icon'] .
                                ' ' .
                                $group['color']
                            ) ?>"></i>

                            <?= e($group['title']) ?>

                        </h2>

                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">

                            <?php foreach ($features[$key] as $feature): ?>

                                <li class="flex items-start gap-3 text-gray-300">

                                    <i class="ri-checkbox-circle-fill text-green-500 mt-0.5"></i>

                                    <span>
                                        <?= e(
                                            clean_text(
                                                $feature,
                                                160
                                            )
                                        ) ?>
                                    </span>

                                </li>

                            <?php endforeach; ?>

                        </ul>

                    </div>

                <?php endif; ?>

            <?php endforeach; ?>

            <?php if ($features['tech'] !== []): ?>

                <div class="bg-[#111] border border-white/10 rounded-3xl p-6 md:p-9">

                    <h2 class="text-2xl font-bold">
                        Technology Used
                    </h2>

                    <div class="flex flex-wrap gap-3 mt-6">

                        <?php foreach ($features['tech'] as $tech): ?>

                            <span class="px-4 py-2 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-300 text-sm font-bold">
                                <?= e(
                                    clean_text(
                                        $tech,
                                        120
                                    )
                                ) ?>
                            </span>

                        <?php endforeach; ?>

                    </div>

                </div>

            <?php endif; ?>

            <?php if ($features['files'] !== []): ?>

                <div class="bg-[#111] border border-white/10 rounded-3xl p-6 md:p-9">

                    <h2 class="text-2xl font-bold">
                        What You Will Get
                    </h2>

                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">

                        <?php foreach ($features['files'] as $file): ?>

                            <li class="flex gap-3 text-gray-300">

                                <i class="ri-file-check-fill text-yellow-500"></i>

                                <?= e(
                                    clean_text(
                                        $file,
                                        160
                                    )
                                ) ?>

                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php endif; ?>

        </div>

        <aside class="lg:col-span-1">

            <div class="sticky top-28 space-y-6">

                <div class="bg-[#111] border border-white/10 rounded-3xl p-7 shadow-2xl">

                    <span class="text-sm text-gray-500">
                        Regular License
                    </span>

                    <div class="text-5xl font-display font-bold mt-2 mb-7">
                        $<?= number_format(
                            (float) $product['price_basic'],
                            2
                        ) ?>
                    </div>

                    <ul class="space-y-3 text-sm text-gray-400 mb-7">

                        <li>
                            <i class="ri-check-line text-green-500 mr-2"></i>
                            Quality checked service
                        </li>

                        <li>
                            <i class="ri-check-line text-green-500 mr-2"></i>
                            Secure order request
                        </li>

                        <li>
                            <i class="ri-check-line text-green-500 mr-2"></i>
                            Support discussion included
                        </li>

                    </ul>

                    <form
                        action="api/cart_action.php"
                        method="POST"
                    >
                        <?= csrf_field() ?>

                        <input
                            type="hidden"
                            name="action"
                            value="add"
                        >

                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= (int) $product['id'] ?>"
                        >

                        <button
                            type="submit"
                            class="w-full py-4 bg-yellow-500 text-black font-bold rounded-xl hover:bg-yellow-400 transition"
                        >
                            <i class="ri-shopping-cart-2-line mr-1"></i>
                            Add to Cart
                        </button>

                    </form>

                    <a
                        href="index.php?page=contact"
                        class="block w-full py-3.5 border border-white/10 text-center font-bold rounded-xl hover:bg-white/5 mt-3"
                    >
                        Ask Before Ordering
                    </a>

                </div>

                <div class="bg-[#111] border border-white/10 rounded-3xl p-6">

                    <dl class="divide-y divide-white/5 text-sm">

                        <div class="flex justify-between py-3">
                            <dt class="text-gray-500">
                                Category
                            </dt>

                            <dd>
                                <?= e($meta['label']) ?>
                            </dd>
                        </div>

                        <div class="flex justify-between py-3">
                            <dt class="text-gray-500">
                                Delivery
                            </dt>

                            <dd>
                                After discussion
                            </dd>
                        </div>

                        <div class="flex justify-between py-3">
                            <dt class="text-gray-500">
                                Status
                            </dt>

                            <dd class="text-green-400">
                                Available
                            </dd>
                        </div>

                    </dl>

                </div>

            </div>

        </aside>

    </section>

    <?php if ($relatedServices !== []): ?>

        <section class="max-w-7xl mx-auto px-5 mt-20">

            <div class="flex items-end justify-between gap-4 mb-7">

                <h2 class="text-3xl font-bold">
                    Related Services
                </h2>

                <a
                    href="index.php?page=products"
                    class="text-yellow-500 hover:underline"
                >
                    View all
                </a>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <?php foreach ($relatedServices as $related): ?>

                    <?php
                    $relatedImage = service_media_url(
                        $related['thumbnail'] ?? ''
                    );
                    ?>

                    <a
                        href="index.php?page=service-details&id=<?= (int) $related['id'] ?>"
                        class="bg-[#111] border border-white/10 rounded-2xl overflow-hidden hover:border-yellow-500/40 transition group"
                    >

                        <div class="h-44 bg-black">

                            <?php if ($relatedImage !== ''): ?>

                                <img
                                    src="<?= e($relatedImage) ?>"
                                    alt="<?= e($related['title']) ?>"
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105 transition"
                                >

                            <?php else: ?>

                                <div class="w-full h-full flex items-center justify-center">

                                    <i class="ri-code-box-line text-5xl text-yellow-500/30"></i>

                                </div>

                            <?php endif; ?>

                        </div>

                        <div class="p-5 flex justify-between gap-3">

                            <strong>
                                <?= e($related['title']) ?>
                            </strong>

                            <span>
                                $<?= number_format(
                                    (float) $related['price_basic'],
                                    2
                                ) ?>
                            </span>

                        </div>

                    </a>

                <?php endforeach; ?>

            </div>

        </section>

    <?php endif; ?>

</main>

<?php if (count($media) > 1): ?>

    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        new Swiper('.service-gallery', {
            slidesPerView: 1,
            loop: false,

            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            },

            pagination: {
                el: '.swiper-pagination',
                clickable: true
            }
        });
    });
    </script>

<?php endif; ?>