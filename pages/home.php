<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/service_view.php';
require_once __DIR__ . '/../includes/project_view.php';

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
     ORDER BY created_at DESC, id DESC'
);

$latestServices = $statement->fetchAll();

$featuredStatement = $pdo->query(
    'SELECT
        id,
        title,
        category,
        short_desc,
        thumbnail,
        video_preview,
        gallery,
        technologies,
        views,
        likes
     FROM projects
     WHERE is_active = 1
       AND is_featured = 1
     ORDER BY
        sort_order DESC,
        created_at DESC,
        id DESC'
);

$featuredProjects =
    $featuredStatement->fetchAll();
?>

<div class="relative w-full overflow-hidden bg-[#050505]">

    <?php require __DIR__ . '/../includes/hero.php'; ?>

    <?php require __DIR__ . '/../includes/skills.php'; ?>

    <section class="py-24 bg-[#080808] border-y border-white/5">

        <div class="max-w-7xl mx-auto px-5">

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 mb-12">

                <div>
                    <span class="text-yellow-500 text-sm font-bold uppercase tracking-[.25em]">
                        Ready To Explore
                    </span>

                    <h2 class="text-4xl md:text-6xl font-display font-bold mt-3">
                        Services & Products
                    </h2>

                    <p class="text-gray-400 mt-4 max-w-2xl">
                        Swipe or use the arrows to explore all
                        ready-made products and services.
                    </p>
                </div>

                <a
                    href="index.php?page=products"
                    class="inline-flex items-center gap-2 text-yellow-500 font-bold hover:text-yellow-400"
                >
                    View all products
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

                <div class="flex items-center justify-between gap-4 mb-6">

                    <p class="text-sm text-gray-500">
                        <i class="ri-drag-move-2-line mr-1"></i>
                        Swipe horizontally to explore
                    </p>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            id="home-products-prev"
                            class="home-product-arrow"
                            aria-label="Previous products"
                        >
                            <i class="ri-arrow-left-line"></i>
                        </button>

                        <button
                            type="button"
                            id="home-products-next"
                            class="home-product-arrow"
                            aria-label="Next products"
                        >
                            <i class="ri-arrow-right-line"></i>
                        </button>
                    </div>

                </div>

                <div
                    id="home-products-track"
                    class="home-products-track"
                >

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

                        <article class="home-product-card group bg-[#111] border border-white/10 rounded-3xl overflow-hidden hover:border-yellow-500/40 transition flex flex-col">

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

    <!-- All Featured Portfolio Projects -->

    <section class="home-featured-section py-24 bg-[#050505] border-t border-white/5">

        <div class="max-w-7xl mx-auto px-5">

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 mb-12">

                <div>
                    <span class="text-yellow-500 text-sm font-bold uppercase tracking-[.25em]">
                        Featured Work
                    </span>

                    <h2 class="text-4xl md:text-6xl font-display font-bold mt-3">
                        Portfolio Projects
                    </h2>

                    <p class="text-gray-400 mt-4 max-w-2xl">
                        Every project marked as featured from the
                        portfolio management panel appears here.
                    </p>
                </div>

                <a
                    href="index.php?page=portfolio"
                    class="inline-flex items-center gap-2 text-yellow-500 font-bold hover:text-yellow-400"
                >
                    Full Portfolio
                    <i class="ri-arrow-right-line"></i>
                </a>

            </div>

            <?php if ($featuredProjects === []): ?>

                <div class="rounded-3xl border border-white/10 bg-[#111] p-12 text-center">

                    <i class="ri-folder-open-line text-5xl text-yellow-500"></i>

                    <h3 class="text-2xl font-bold mt-4">
                        No featured project found.
                    </h3>

                    <p class="text-gray-500 mt-2">
                        Mark a portfolio project as featured from the admin panel.
                    </p>

                </div>

            <?php else: ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-7">

                    <?php foreach ($featuredProjects as $project): ?>

                        <?php
                        $projectGallery =
                            project_public_gallery(
                                $project['gallery'] ?? '',
                                $project['thumbnail'] ?? '',
                                $project['video_preview'] ?? ''
                            );

                        $firstProjectMedia =
                            $projectGallery[0] ?? null;

                        $projectTechnologies =
                            project_public_technologies(
                                $project['technologies'] ?? ''
                            );
                        ?>

                        <article class="home-project-card group rounded-[2rem] border border-white/10 bg-[#111] overflow-hidden flex flex-col">

                            <?php if ($firstProjectMedia !== null): ?>

                                <div
                                    class="home-project-visual"
                                    data-home-media-box
                                >

                                    <?php if ($firstProjectMedia['type'] === 'youtube'): ?>

                                        <?php
                                        $youtubeId =
                                            project_public_youtube_id(
                                                $firstProjectMedia['url']
                                            );
                                        ?>

                                        <a
                                            href="index.php?page=project-details&id=<?= (int) $project['id'] ?>"
                                            class="home-project-media-frame relative block"
                                        >
                                            <img
                                                src="https://img.youtube.com/vi/<?= e($youtubeId) ?>/hqdefault.jpg"
                                                alt="<?= e($project['title']) ?>"
                                                loading="lazy"
                                                data-home-project-media
                                            >

                                            <span class="absolute inset-0 flex items-center justify-center bg-black/25">
                                                <i class="ri-play-circle-fill text-6xl text-red-500"></i>
                                            </span>
                                        </a>

                                    <?php elseif ($firstProjectMedia['type'] === 'video'): ?>

                                        <video
                                            src="<?= e($firstProjectMedia['url']) ?>"
                                            controls
                                            playsinline
                                            preload="metadata"
                                            data-home-project-media
                                        ></video>

                                    <?php else: ?>

                                        <a
                                            href="index.php?page=project-details&id=<?= (int) $project['id'] ?>"
                                            class="home-project-media-frame block"
                                        >
                                            <img
                                                src="<?= e($firstProjectMedia['url']) ?>"
                                                alt="<?= e($project['title']) ?>"
                                                loading="lazy"
                                                data-home-project-media
                                            >
                                        </a>

                                    <?php endif; ?>

                                </div>

                            <?php else: ?>

                                <a
                                    href="index.php?page=project-details&id=<?= (int) $project['id'] ?>"
                                    class="aspect-video flex items-center justify-center bg-gradient-to-br from-yellow-500/10 to-black"
                                >
                                    <i class="ri-code-box-line text-7xl text-yellow-500/30"></i>
                                </a>

                            <?php endif; ?>

                            <div class="p-6 md:p-7 flex flex-col flex-1">

                                <div class="flex items-center justify-between gap-4 text-xs">

                                    <span class="text-yellow-500 font-bold uppercase tracking-wider">
                                        <?= e($project['category'] ?? 'Project') ?>
                                    </span>

                                    <span class="text-gray-500 whitespace-nowrap">
                                        <i class="ri-gallery-line"></i>
                                        <?= count($projectGallery) ?> media
                                    </span>

                                </div>

                                <a
                                    href="index.php?page=project-details&id=<?= (int) $project['id'] ?>"
                                    class="mt-3"
                                >
                                    <h3 class="text-2xl font-bold group-hover:text-yellow-500 transition">
                                        <?= e($project['title']) ?>
                                    </h3>
                                </a>

                                <p class="text-gray-400 text-sm leading-7 mt-3">
                                    <?= e(
                                        project_public_excerpt(
                                            $project['short_desc'] ?? '',
                                            180
                                        )
                                    ) ?>
                                </p>

                                <?php if ($projectTechnologies !== []): ?>

                                    <div class="flex flex-wrap gap-2 mt-5">

                                        <?php foreach (
                                            array_slice(
                                                $projectTechnologies,
                                                0,
                                                4
                                            ) as $technology
                                        ): ?>

                                            <span class="px-2.5 py-1 rounded-md bg-blue-500/10 border border-blue-500/10 text-blue-300 text-[11px] font-bold">
                                                <?= e($technology) ?>
                                            </span>

                                        <?php endforeach; ?>

                                    </div>

                                <?php endif; ?>

                                <div class="mt-auto pt-6 flex items-center justify-between gap-4">

                                    <span class="text-xs text-gray-500">
                                        <i class="ri-eye-line"></i>
                                        <?= number_format((int) $project['views']) ?>

                                        <span class="mx-2">•</span>

                                        <i class="ri-heart-line"></i>
                                        <?= number_format((int) $project['likes']) ?>
                                    </span>

                                    <a
                                        href="index.php?page=project-details&id=<?= (int) $project['id'] ?>"
                                        class="inline-flex items-center gap-2 font-bold text-sm hover:text-yellow-500 transition"
                                    >
                                        View Project
                                        <i class="ri-arrow-right-up-line"></i>
                                    </a>

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

<style>
.home-products-track {
    display: flex;
    gap: 24px;
    overflow-x: auto;
    padding: 5px 2px 26px;
    scroll-behavior: smooth;
    scroll-snap-type: x mandatory;
    overscroll-behavior-inline: contain;
    scrollbar-width: none;
}

.home-products-track::-webkit-scrollbar {
    display: none;
}

.home-product-card {
    flex: 0 0 380px;
    max-width: calc(100vw - 40px);
    scroll-snap-align: start;
    box-shadow: 0 24px 70px rgba(0, 0, 0, .36);
}

.home-product-arrow {
    width: 45px;
    height: 45px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, .12);
    border-radius: 999px;
    background: rgba(255, 255, 255, .05);
    color: #fff;
    font-size: 20px;
    transition:
        background .2s ease,
        border-color .2s ease,
        color .2s ease;
}

.home-product-arrow:hover {
    border-color: #f4b90b;
    background: #f4b90b;
    color: #000;
}

.home-project-card {
    box-shadow: 0 25px 80px rgba(0, 0, 0, .38);
    transition:
        transform .3s ease,
        border-color .3s ease,
        box-shadow .3s ease;
}

.home-project-card:hover {
    transform: translateY(-5px);
    border-color: rgba(244, 185, 11, .34);
    box-shadow: 0 35px 95px rgba(0, 0, 0, .5);
}

.home-project-visual {
    --home-media-bg: none;
    position: relative;
    isolation: isolate;
    aspect-ratio: 16 / 10;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #050505;
}

.home-project-visual::before {
    content: "";
    position: absolute;
    z-index: 0;
    inset: -30px;
    background-image:
        linear-gradient(
            rgba(0, 0, 0, .7),
            rgba(0, 0, 0, .72)
        ),
        var(--home-media-bg);
    background-position: center;
    background-size: cover;
    filter: blur(25px) saturate(.9);
    transform: scale(1.12);
    opacity: 0;
}

.home-project-visual.is-portrait::before {
    opacity: .82;
}

.home-project-media-frame,
.home-project-visual > video {
    position: relative;
    z-index: 2;
    width: 100%;
    height: 100%;
}

.home-project-visual img,
.home-project-visual video {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.home-project-visual.is-landscape img,
.home-project-visual.is-landscape video {
    object-fit: cover;
}

.home-project-visual.is-portrait {
    height: 480px;
    aspect-ratio: auto;
    padding: 22px;
}

.home-project-visual.is-portrait .home-project-media-frame,
.home-project-visual.is-portrait > video {
    width: auto;
    max-width: 62%;
    height: 100%;
    aspect-ratio: 9 / 16;
    overflow: hidden;
    border: 7px solid #17171a;
    border-radius: 29px;
    background: #050505;
    box-shadow:
        0 28px 65px rgba(0, 0, 0, .68),
        0 0 0 1px rgba(255, 255, 255, .15);
}

.home-project-visual.is-portrait img,
.home-project-visual.is-portrait video {
    object-fit: contain !important;
    border-radius: 20px;
}

@media (max-width: 767px) {
    .home-product-card {
        flex-basis: min(86vw, 350px);
    }

    .home-product-arrow {
        width: 42px;
        height: 42px;
    }

    .home-featured-section {
        padding-top: 72px;
        padding-bottom: 72px;
    }

    .home-project-card:hover {
        transform: none;
    }

    .home-project-visual.is-portrait {
        height: 510px;
        padding: 18px;
    }

    .home-project-visual.is-portrait .home-project-media-frame,
    .home-project-visual.is-portrait > video {
        max-width: 82%;
        border-width: 6px;
        border-radius: 26px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const productTrack =
        document.getElementById('home-products-track');

    const previousButton =
        document.getElementById('home-products-prev');

    const nextButton =
        document.getElementById('home-products-next');

    function productScrollAmount() {
        if (!productTrack) {
            return 380;
        }

        const card =
            productTrack.querySelector('.home-product-card');

        return card
            ? card.getBoundingClientRect().width + 24
            : productTrack.clientWidth * .85;
    }

    if (productTrack && previousButton) {
        previousButton.addEventListener('click', function () {
            productTrack.scrollBy({
                left: -productScrollAmount(),
                behavior: 'smooth'
            });
        });
    }

    if (productTrack && nextButton) {
        nextButton.addEventListener('click', function () {
            productTrack.scrollBy({
                left: productScrollAmount(),
                behavior: 'smooth'
            });
        });
    }

    function applyHomeMediaLayout(media) {
        const box =
            media.closest('[data-home-media-box]');

        if (!box) {
            return;
        }

        const isVideo =
            media.tagName === 'VIDEO';

        const width = isVideo
            ? media.videoWidth
            : media.naturalWidth;

        const height = isVideo
            ? media.videoHeight
            : media.naturalHeight;

        if (!width || !height) {
            return;
        }

        const isPortrait =
            height > width * 1.15;

        box.classList.toggle(
            'is-portrait',
            isPortrait
        );

        box.classList.toggle(
            'is-landscape',
            !isPortrait
        );

        if (isPortrait) {
            const source =
                media.currentSrc ||
                media.src ||
                '';

            box.style.setProperty(
                '--home-media-bg',
                'url("' +
                    source.replace(/"/g, '\\"') +
                '")'
            );
        }
    }

    document
        .querySelectorAll('[data-home-project-media]')
        .forEach(function (media) {
            const isVideo =
                media.tagName === 'VIDEO';

            const ready = isVideo
                ? media.readyState >= 1
                : media.complete &&
                    media.naturalWidth > 0;

            if (ready) {
                applyHomeMediaLayout(media);
                return;
            }

            media.addEventListener(
                isVideo
                    ? 'loadedmetadata'
                    : 'load',
                function () {
                    applyHomeMediaLayout(media);
                },
                { once: true }
            );
        });
});
</script>
