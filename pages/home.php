<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/service_view.php';

$statement = $pdo->query(
    'SELECT
        id,
        title,
        short_desc,
        price_basic,
        features,
        thumbnail,
        file_type
     FROM services
     WHERE is_active = 1
     ORDER BY created_at DESC, id DESC
     LIMIT 6'
);

$latestServices = $statement->fetchAll();
?>

<div class="relative w-full overflow-hidden bg-[#050505]">

    <?php require __DIR__ . '/../includes/hero.php'; ?>

    <?php require __DIR__ . '/../includes/skills.php'; ?>

    <section class="py-24 bg-[#080808] border-y border-white/5">

        <div class="max-w-7xl mx-auto px-5">

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 mb-12">

                <div>
                    <span class="text-yellow-500 text-sm font-bold uppercase tracking-[.25em]">
                        Latest Work
                    </span>

                    <h2 class="text-4xl md:text-6xl font-display font-bold mt-3">
                        Services & Products
                    </h2>

                    <p class="text-gray-400 mt-4 max-w-2xl">
                        Ready-made digital products and custom
                        development services for your business.
                    </p>
                </div>

                <a
                    href="index.php?page=products"
                    class="inline-flex items-center gap-2 text-yellow-500 font-bold hover:text-yellow-400"
                >
                    View all services
                    <i class="ri-arrow-right-line"></i>
                </a>

            </div>

            <?php if ($latestServices === []): ?>

                <div class="rounded-3xl border border-white/10 bg-[#111] p-12 text-center">

                    <i class="ri-folder-open-line text-5xl text-yellow-500"></i>

                    <h3 class="text-2xl font-bold mt-4">
                        New services are coming soon.
                    </h3>

                    <a
                        href="index.php?page=contact"
                        class="inline-block mt-5 text-yellow-500 hover:underline"
                    >
                        Discuss a custom project
                    </a>

                </div>

            <?php else: ?>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">

                    <?php foreach ($latestServices as $service): ?>

                        <?php
                        $meta = service_type_meta(
                            $service['file_type'] ?? 'web'
                        );

                        $thumbnail = service_media_url(
                            $service['thumbnail'] ?? ''
                        );

                        $features = service_feature_data(
                            $service['features'] ?? ''
                        );
                        ?>

                        <article class="group bg-[#111] border border-white/10 rounded-3xl overflow-hidden hover:border-yellow-500/40 transition flex flex-col">

                            <a
                                href="index.php?page=service-details&id=<?= (int) $service['id'] ?>"
                                class="block h-60 bg-gradient-to-br from-[#171717] to-black overflow-hidden relative"
                            >

                                <?php if ($thumbnail !== ''): ?>

                                    <img
                                        src="<?= e($thumbnail) ?>"
                                        alt="<?= e($service['title']) ?>"
                                        loading="lazy"
                                        class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                    >

                                <?php else: ?>

                                    <div class="w-full h-full flex items-center justify-center">

                                        <i class="<?= e($meta['icon']) ?> text-7xl text-yellow-500/40"></i>

                                    </div>

                                <?php endif; ?>

                                <span class="absolute top-4 left-4 bg-black/75 border border-white/10 backdrop-blur px-3 py-1.5 rounded-full text-xs font-bold text-yellow-500">
                                    <?= e($meta['badge']) ?>
                                </span>

                            </a>

                            <div class="p-6 flex flex-col flex-1">

                                <a href="index.php?page=service-details&id=<?= (int) $service['id'] ?>">

                                    <h3 class="text-xl font-bold group-hover:text-yellow-500 transition">
                                        <?= e($service['title']) ?>
                                    </h3>

                                </a>

                                <p class="text-gray-400 text-sm leading-6 mt-3 mb-5">
                                    <?= e(
                                        service_excerpt(
                                            $service['short_desc'] ?? ''
                                        )
                                    ) ?>
                                </p>

                                <?php if ($features['tech'] !== []): ?>

                                    <div class="flex flex-wrap gap-2 mb-6">

                                        <?php foreach (
                                            array_slice(
                                                $features['tech'],
                                                0,
                                                3
                                            ) as $tech
                                        ): ?>

                                            <span class="text-[11px] font-bold px-2.5 py-1 bg-white/5 border border-white/10 rounded-md text-gray-300">
                                                <?= e($tech) ?>
                                            </span>

                                        <?php endforeach; ?>

                                    </div>

                                <?php endif; ?>

                                <div class="mt-auto flex items-center justify-between gap-4">

                                    <div>
                                        <span class="block text-xs text-gray-500">
                                            Starting from
                                        </span>

                                        <strong class="text-2xl">
                                            $<?= number_format(
                                                (float) $service['price_basic'],
                                                2
                                            ) ?>
                                        </strong>
                                    </div>

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
                                            value="<?= (int) $service['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="px-5 py-3 rounded-xl bg-yellow-500 text-black font-bold hover:bg-yellow-400 transition"
                                        >
                                            <i class="ri-shopping-cart-2-line mr-1"></i>
                                            Add
                                        </button>

                                    </form>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>
    </section>

    <section class="py-24 px-5">

        <div class="max-w-5xl mx-auto rounded-[2rem] border border-yellow-500/20 bg-gradient-to-r from-yellow-500/10 to-transparent p-8 md:p-14 text-center">

            <h2 class="text-3xl md:text-5xl font-bold">
                Need a custom app, website or automation?
            </h2>

            <p class="text-gray-400 mt-4 mb-8">
                Share your requirements and get a solution
                made for your exact business needs.
            </p>

            <a
                href="index.php?page=contact"
                class="inline-flex items-center gap-2 px-7 py-4 rounded-full bg-yellow-500 text-black font-bold hover:bg-yellow-400"
            >
                Start Your Project
                <i class="ri-arrow-right-line"></i>
            </a>

        </div>

    </section>

</div>