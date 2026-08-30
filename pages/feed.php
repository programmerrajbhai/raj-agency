<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/service_view.php';

$posts = $pdo->query(
    'SELECT
        id,
        title,
        short_desc,
        price_basic,
        features,
        thumbnail,
        file_type,
        created_at
     FROM services
     WHERE is_active = 1
     ORDER BY created_at DESC, id DESC
     LIMIT 30'
)->fetchAll();
?>

<main class="pt-36 pb-24 min-h-screen bg-[#050505]">

    <section class="max-w-2xl mx-auto px-4">

        <div class="text-center mb-12">

            <span class="text-yellow-500 font-bold uppercase tracking-[.25em] text-sm">
                Latest Updates
            </span>

            <h1 class="text-4xl md:text-5xl font-bold mt-3">
                Project Feed
            </h1>

            <p class="text-gray-400 mt-3">
                Explore the newest active products and
                development services.
            </p>

        </div>

        <?php if ($posts === []): ?>

            <div class="bg-[#111] border border-white/10 rounded-3xl p-12 text-center">

                <i class="ri-rss-line text-5xl text-yellow-500"></i>

                <p class="text-gray-400 mt-4">
                    No update is available right now.
                </p>

            </div>

        <?php endif; ?>

        <?php foreach ($posts as $post): ?>

            <?php
            $features = service_feature_data(
                $post['features'] ?? ''
            );

            $media = service_media_items(
                $features,
                $post['thumbnail'] ?? ''
            );

            $firstMedia = $media[0] ?? null;

            $meta = service_type_meta(
                $post['file_type'] ?? 'web'
            );

            $createdTimestamp = strtotime(
                (string) $post['created_at']
            );

            if ($createdTimestamp === false) {
                $createdTimestamp = time();
            }
            ?>

            <article class="bg-[#111] border border-white/10 rounded-3xl overflow-hidden mb-9">

                <div class="p-5 flex items-center justify-between">

                    <div class="flex items-center gap-3">

                        <div class="w-12 h-12 rounded-full bg-yellow-500 text-black font-black flex items-center justify-center">
                            R
                        </div>

                        <div>

                            <p class="font-bold">
                                Raj Agency
                                <i class="ri-verified-badge-fill text-blue-500"></i>
                            </p>

                            <time
                                class="text-xs text-gray-500"
                                datetime="<?= e(
                                    date(
                                        'c',
                                        $createdTimestamp
                                    )
                                ) ?>"
                            >
                                <?= e(
                                    date(
                                        'd M Y',
                                        $createdTimestamp
                                    )
                                ) ?>
                            </time>

                        </div>

                    </div>

                    <span class="text-xs font-bold text-yellow-500 border border-yellow-500/20 px-3 py-1 rounded-full">
                        <?= e($meta['badge']) ?>
                    </span>

                </div>

                <div class="px-5 pb-5">

                    <a
                        href="index.php?page=service-details&id=<?= (int) $post['id'] ?>"
                        class="hover:text-yellow-500"
                    >
                        <h2 class="text-xl font-bold">
                            <?= e($post['title']) ?>
                        </h2>
                    </a>

                    <p class="text-gray-400 mt-2 leading-6">
                        <?= e(
                            service_excerpt(
                                $post['short_desc'] ?? '',
                                260
                            )
                        ) ?>
                    </p>

                    <?php if ($features['tech'] !== []): ?>

                        <div class="flex flex-wrap gap-2 mt-4">

                            <?php foreach (
                                array_slice(
                                    $features['tech'],
                                    0,
                                    5
                                ) as $tech
                            ): ?>

                                <span class="px-2.5 py-1 rounded-md bg-blue-500/10 text-blue-300 text-xs font-bold">
                                    <?= e($tech) ?>
                                </span>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </div>

                <?php if ($firstMedia !== null): ?>

                    <div class="block aspect-video bg-black overflow-hidden">

                        <?php if ($firstMedia['type'] === 'youtube'): ?>

                            <?php
                            $youtubeId = service_youtube_id(
                                $firstMedia['url']
                            );
                            ?>

                            <a
                                href="index.php?page=service-details&id=<?= (int) $post['id'] ?>"
                                class="relative block w-full h-full"
                            >

                                <img
                                    src="https://img.youtube.com/vi/<?= e($youtubeId) ?>/hqdefault.jpg"
                                    alt="<?= e($post['title']) ?>"
                                    loading="lazy"
                                    class="w-full h-full object-cover"
                                >

                                <span class="absolute inset-0 flex items-center justify-center">

                                    <i class="ri-play-circle-fill text-7xl text-red-500"></i>

                                </span>

                            </a>

                        <?php elseif ($firstMedia['type'] === 'video'): ?>

                            <video
                                src="<?= e($firstMedia['url']) ?>"
                                class="w-full h-full object-cover"
                                controls
                                playsinline
                                preload="metadata"
                            ></video>

                        <?php else: ?>

                            <a
                                href="index.php?page=service-details&id=<?= (int) $post['id'] ?>"
                                class="block w-full h-full"
                            >

                                <img
                                    src="<?= e($firstMedia['url']) ?>"
                                    alt="<?= e($post['title']) ?>"
                                    loading="lazy"
                                    class="w-full h-full object-cover hover:scale-105 transition duration-500"
                                >

                            </a>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

                <div class="p-5 flex items-center justify-between gap-4">

                    <div>
                        <span class="text-xs text-gray-500">
                            Price
                        </span>

                        <strong class="block text-2xl">
                            $<?= number_format(
                                (float) $post['price_basic'],
                                2
                            ) ?>
                        </strong>
                    </div>

                    <div class="flex items-center gap-3">

                        <a
                            href="index.php?page=service-details&id=<?= (int) $post['id'] ?>"
                            class="px-4 py-3 border border-white/10 rounded-xl font-bold hover:bg-white/5"
                        >
                            Details
                        </a>

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
                                value="<?= (int) $post['id'] ?>"
                            >

                            <button
                                type="submit"
                                class="px-4 py-3 bg-yellow-500 text-black rounded-xl font-bold hover:bg-yellow-400"
                                aria-label="Add to cart"
                            >
                                <i class="ri-shopping-cart-line"></i>
                            </button>

                        </form>

                    </div>

                </div>

            </article>

        <?php endforeach; ?>

    </section>

</main>