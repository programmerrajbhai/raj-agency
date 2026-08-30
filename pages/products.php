<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/service_view.php';

$services = $pdo->query(
    'SELECT
        id,
        title,
        short_desc,
        price_basic,
        features,
        thumbnail,
        demo_url,
        file_type
     FROM services
     WHERE is_active = 1
     ORDER BY created_at DESC, id DESC'
)->fetchAll();
?>

<main class="pt-36 pb-24 min-h-screen bg-[#050505]">

    <section class="max-w-7xl mx-auto px-5">

        <div class="text-center max-w-3xl mx-auto mb-12">

            <span class="text-yellow-500 text-sm font-bold uppercase tracking-[.25em]">
                Raj Agency Marketplace
            </span>

            <h1 class="text-5xl md:text-7xl font-display font-bold mt-4">
                Digital Products.
            </h1>

            <p class="text-gray-400 mt-5">
                Browse active apps, websites, scripts and UI products.
            </p>

        </div>

        <div
            id="portfolio-filters"
            class="flex flex-wrap justify-center gap-2 mb-12"
        >

            <?php foreach (
                [
                    'all' => 'All',
                    'web' => 'Websites',
                    'app' => 'Apps',
                    'ui' => 'UI Kits',
                ] as $key => $label
            ): ?>

                <button
                    type="button"
                    data-filter="<?= e($key) ?>"
                    class="portfolio-filter <?= $key === 'all'
                        ? 'bg-yellow-500 text-black'
                        : 'bg-white/5 text-gray-300' ?> px-5 py-2.5 rounded-full border border-white/10 font-bold text-sm hover:border-yellow-500 transition"
                >
                    <?= e($label) ?>
                </button>

            <?php endforeach; ?>

        </div>

        <?php if ($services === []): ?>

            <div class="rounded-3xl border border-white/10 bg-[#111] p-14 text-center">

                <i class="ri-store-2-line text-6xl text-yellow-500"></i>

                <h2 class="text-2xl font-bold mt-5">
                    No active service found.
                </h2>

                <p class="text-gray-400 mt-2">
                    Please check again later or request a custom project.
                </p>

                <a
                    href="index.php?page=contact"
                    class="inline-block mt-6 px-6 py-3 bg-yellow-500 text-black font-bold rounded-xl"
                >
                    Contact Me
                </a>

            </div>

        <?php else: ?>

            <div
                id="portfolio-grid"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
            >

                <?php foreach ($services as $service): ?>

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

                    $demoItems = service_demo_items(
                        $features,
                        $service['demo_url'] ?? ''
                    );
                    ?>

                    <article
                        data-type="<?= e($meta['key']) ?>"
                        class="portfolio-item group rounded-3xl bg-[#111] border border-white/10 overflow-hidden hover:border-yellow-500/40 transition flex flex-col"
                    >

                        <a
                            href="index.php?page=service-details&id=<?= (int) $service['id'] ?>"
                            class="relative block h-64 bg-gradient-to-br from-[#181818] to-black overflow-hidden"
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

                            <span class="absolute top-4 left-4 bg-black/80 text-yellow-500 border border-yellow-500/20 px-3 py-1.5 rounded-full text-xs font-bold">
                                <?= e($meta['badge']) ?>
                            </span>

                        </a>

                        <div class="p-6 flex flex-col flex-1">

                            <div class="flex items-start justify-between gap-4">

                                <a
                                    href="index.php?page=service-details&id=<?= (int) $service['id'] ?>"
                                    class="hover:text-yellow-500 transition"
                                >
                                    <h2 class="text-xl font-bold leading-tight">
                                        <?= e($service['title']) ?>
                                    </h2>
                                </a>

                                <strong class="text-xl whitespace-nowrap">
                                    $<?= number_format(
                                        (float) $service['price_basic'],
                                        2
                                    ) ?>
                                </strong>

                            </div>

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
                                            4
                                        ) as $tech
                                    ): ?>

                                        <span class="px-2.5 py-1 text-[11px] rounded-md bg-white/5 border border-white/10 text-gray-300">
                                            <?= e($tech) ?>
                                        </span>

                                    <?php endforeach; ?>

                                </div>

                            <?php endif; ?>

                            <div class="mt-auto grid grid-cols-2 gap-3">

                                <a
                                    href="index.php?page=service-details&id=<?= (int) $service['id'] ?>"
                                    class="py-3 rounded-xl border border-white/10 text-center font-bold hover:bg-white/5"
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
                                        value="<?= (int) $service['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="w-full py-3 rounded-xl bg-yellow-500 text-black font-bold hover:bg-yellow-400"
                                    >
                                        <i class="ri-shopping-cart-line mr-1"></i>
                                        Buy
                                    </button>

                                </form>

                            </div>

                            <?php if ($demoItems !== []): ?>

                                <a
                                    href="<?= e($demoItems[0]['url']) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-3 text-center text-sm text-yellow-500 hover:underline"
                                >
                                    <i class="ri-external-link-line"></i>
                                    <?= e($demoItems[0]['title']) ?>
                                </a>

                            <?php endif; ?>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

            <div
                id="portfolio-empty"
                class="hidden rounded-3xl border border-white/10 bg-[#111] p-12 text-center text-gray-400"
            >
                No product found in this category.
            </div>

        <?php endif; ?>

    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.portfolio-filter');
    const items = document.querySelectorAll('.portfolio-item');
    const empty = document.getElementById('portfolio-empty');

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            buttons.forEach(function (item) {
                item.classList.remove(
                    'bg-yellow-500',
                    'text-black'
                );

                item.classList.add(
                    'bg-white/5',
                    'text-gray-300'
                );
            });

            button.classList.remove(
                'bg-white/5',
                'text-gray-300'
            );

            button.classList.add(
                'bg-yellow-500',
                'text-black'
            );

            const filter = button.dataset.filter;
            let visible = 0;

            items.forEach(function (item) {
                const show =
                    filter === 'all' ||
                    item.dataset.type === filter;

                item.classList.toggle('hidden', !show);

                if (show) {
                    visible++;
                }
            });

            if (empty) {
                empty.classList.toggle(
                    'hidden',
                    visible !== 0
                );
            }
        });
    });
});
</script>