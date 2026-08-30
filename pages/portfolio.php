<?php
declare(strict_types=1);

require_once __DIR__ .
    '/../includes/project_view.php';

$search = clean_text(
    $_GET['search'] ?? '',
    100
);

$categoryFilter = clean_text(
    $_GET['category'] ?? 'all',
    80
);

$featuredFilter =
    ($_GET['featured'] ?? '') === '1';

$pageNumber = filter_input(
    INPUT_GET,
    'p',
    FILTER_VALIDATE_INT
);

$pageNumber =
    $pageNumber && $pageNumber > 0
        ? $pageNumber
        : 1;

$perPage = 12;

$where = [
    'is_active = 1',
];

$parameters = [];

if ($search !== '') {
    $where[] = '(
        title LIKE ?
        OR short_desc LIKE ?
        OR category LIKE ?
        OR client_name LIKE ?
    )';

    $searchValue =
        '%' . $search . '%';

    $parameters[] = $searchValue;
    $parameters[] = $searchValue;
    $parameters[] = $searchValue;
    $parameters[] = $searchValue;
}

if ($categoryFilter !== 'all') {
    $where[] = 'category = ?';

    $parameters[] = $categoryFilter;
}

if ($featuredFilter) {
    $where[] = 'is_featured = 1';
}

$whereSql =
    ' WHERE ' .
    implode(
        ' AND ',
        $where
    );

$countStatement = $pdo->prepare(
    'SELECT COUNT(*)
     FROM projects' .
     $whereSql
);

$countStatement->execute($parameters);

$totalProjects = (int) (
    $countStatement->fetchColumn() ??
    0
);

$totalPages = max(
    1,
    (int) ceil(
        $totalProjects / $perPage
    )
);

$pageNumber = min(
    $pageNumber,
    $totalPages
);

$offset =
    ($pageNumber - 1) *
    $perPage;

$projectStatement = $pdo->prepare(
    'SELECT
        id,
        title,
        category,
        short_desc,
        thumbnail,
        video_preview,
        gallery,
        client_name,
        technologies,
        details,
        is_featured,
        views,
        likes,
        created_at
     FROM projects' .
     $whereSql .
    ' ORDER BY
        is_featured DESC,
        sort_order DESC,
        created_at DESC,
        id DESC
      LIMIT ' .
      $perPage .
     ' OFFSET ' .
      $offset
);

$projectStatement->execute(
    $parameters
);

$projects =
    $projectStatement->fetchAll();

$categories = $pdo->query(
    "SELECT DISTINCT category
     FROM projects
     WHERE is_active = 1
       AND category IS NOT NULL
       AND category <> ''
     ORDER BY category ASC"
)->fetchAll(PDO::FETCH_COLUMN);

function portfolio_page_url(
    int $page,
    string $search,
    string $category,
    bool $featured
): string {
    $query = [
        'page' => 'portfolio',
        'p' => $page,
    ];

    if ($search !== '') {
        $query['search'] = $search;
    }

    if ($category !== 'all') {
        $query['category'] =
            $category;
    }

    if ($featured) {
        $query['featured'] = '1';
    }

    return 'index.php?' .
        http_build_query($query);
}
?>

<main class="pt-32 pb-24 min-h-screen bg-[#050505]">

    <section class="max-w-5xl mx-auto px-5 mb-14">

        <div class="text-center max-w-3xl mx-auto">

            <span class="text-yellow-500 text-sm font-bold uppercase tracking-[.3em]">
                Selected Work
            </span>

            <h1 class="text-5xl md:text-7xl font-display font-bold mt-4">
                Project Portfolio
            </h1>

            <p class="text-gray-400 text-lg mt-5 leading-8">
                Explore the mobile apps, websites,
                SaaS products and automation systems
                I have developed.
            </p>

        </div>

        <form
            method="GET"
            action="index.php"
            class="mt-10 bg-[#111] border border-white/10 rounded-3xl p-4 grid grid-cols-1 md:grid-cols-[1fr_220px_auto] gap-3"
        >
            <input
                type="hidden"
                name="page"
                value="portfolio"
            >

            <input
                type="search"
                name="search"
                maxlength="100"
                value="<?= e($search) ?>"
                placeholder="Search projects..."
                class="bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
            >

            <select
                name="category"
                class="bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
            >
                <option value="all">
                    All Categories
                </option>

                <?php foreach (
                    $categories as $category
                ): ?>

                    <option
                        value="<?= e($category) ?>"
                        <?= $categoryFilter === $category
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e($category) ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <button
                type="submit"
                class="px-7 py-3.5 bg-yellow-500 text-black font-bold rounded-xl hover:bg-yellow-400"
            >
                Search
            </button>

        </form>

        <div class="flex flex-wrap justify-center gap-3 mt-5">

            <a
                href="index.php?page=portfolio"
                class="px-5 py-2.5 rounded-full border <?= !$featuredFilter && $categoryFilter === 'all'
                    ? 'border-yellow-500 bg-yellow-500 text-black'
                    : 'border-white/10 bg-white/5 text-gray-300' ?> font-bold text-sm"
            >
                All Projects
            </a>

            <a
                href="index.php?page=portfolio&featured=1"
                class="px-5 py-2.5 rounded-full border <?= $featuredFilter
                    ? 'border-yellow-500 bg-yellow-500 text-black'
                    : 'border-white/10 bg-white/5 text-gray-300' ?> font-bold text-sm"
            >
                <i class="ri-star-fill mr-1"></i>
                Featured
            </a>

        </div>

    </section>

    <section class="max-w-3xl mx-auto px-4">

        <?php if ($projects === []): ?>

            <div class="bg-[#111] border border-white/10 rounded-3xl p-14 text-center">

                <i class="ri-folder-search-line text-6xl text-yellow-500"></i>

                <h2 class="text-2xl font-bold mt-5">
                    No project found
                </h2>

                <p class="text-gray-500 mt-2">
                    Try another search or category.
                </p>

            </div>

        <?php endif; ?>

        <?php foreach ($projects as $project): ?>

            <?php
            $details =
                project_public_details(
                    $project['details'] ??
                    ''
                );

            $gallery =
                project_public_gallery(
                    $project['gallery'] ??
                    '',
                    $project['thumbnail'] ??
                    '',
                    $project['video_preview'] ??
                    ''
                );

            $firstMedia =
                $gallery[0] ??
                null;

            $technologies =
                project_public_technologies(
                    $project['technologies'] ??
                    ''
                );

            $liked = project_is_liked(
                (int) $project['id']
            );

            $createdTimestamp =
                strtotime(
                    (string) $project['created_at']
                );

            if ($createdTimestamp === false) {
                $createdTimestamp = time();
            }
            ?>

            <article class="bg-[#111] border border-white/10 rounded-3xl overflow-hidden mb-10 hover:border-white/20 transition shadow-2xl">

                <!-- Social Post Header -->

                <div class="p-5 flex items-center justify-between">

                    <div class="flex items-center gap-3">

                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center text-black font-black">
                            R
                        </div>

                        <div>

                            <p class="font-bold">
                                Raj Agency

                                <i class="ri-verified-badge-fill text-blue-500"></i>
                            </p>

                            <div class="text-xs text-gray-500 mt-1">

                                <?= e(
                                    date(
                                        'd M Y',
                                        $createdTimestamp
                                    )
                                ) ?>

                                <span class="mx-1">•</span>

                                <?= e(
                                    $project['category'] ??
                                    'Project'
                                ) ?>

                            </div>

                        </div>

                    </div>

                    <?php if (
                        (int) $project['is_featured'] === 1
                    ): ?>

                        <span class="px-3 py-1.5 rounded-full bg-yellow-500/10 border border-yellow-500/20 text-yellow-500 text-xs font-bold">

                            <i class="ri-star-fill"></i>
                            Featured

                        </span>

                    <?php endif; ?>

                </div>

                <!-- Text Content -->

                <div class="px-5 pb-5">

                    <a
                        href="index.php?page=project-details&id=<?= (int) $project['id'] ?>"
                        class="hover:text-yellow-500 transition"
                    >
                        <h2 class="text-2xl font-bold">
                            <?= e($project['title']) ?>
                        </h2>
                    </a>

                    <p class="text-gray-400 leading-7 mt-3">
                        <?= e(
                            project_public_excerpt(
                                $project['short_desc'] ??
                                '',
                                280
                            )
                        ) ?>
                    </p>

                    <?php if (
                        $details['show_client'] &&
                        !empty($project['client_name'])
                    ): ?>

                        <p class="text-sm text-gray-500 mt-3">
                            <i class="ri-building-line text-yellow-500"></i>
                            Client:
                            <?= e($project['client_name']) ?>
                        </p>

                    <?php endif; ?>

                    <?php if (
                        $details['show_technologies'] &&
                        $technologies !== []
                    ): ?>

                        <div class="flex flex-wrap gap-2 mt-4">

                            <?php foreach (
                                array_slice(
                                    $technologies,
                                    0,
                                    5
                                ) as $technology
                            ): ?>

                                <span class="px-2.5 py-1 rounded-md bg-blue-500/10 border border-blue-500/10 text-blue-300 text-xs font-bold">
                                    <?= e($technology) ?>
                                </span>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </div>

                <!-- Media Preview -->

                <?php if ($firstMedia !== null): ?>

                    <div class="aspect-video bg-black overflow-hidden">

                        <?php if (
                            $firstMedia['type'] ===
                            'youtube'
                        ): ?>

                            <?php
                            $youtubeId =
                                project_public_youtube_id(
                                    $firstMedia['url']
                                );
                            ?>

                            <a
                                href="index.php?page=project-details&id=<?= (int) $project['id'] ?>"
                                class="relative block w-full h-full"
                            >

                                <img
                                    src="https://img.youtube.com/vi/<?= e($youtubeId) ?>/hqdefault.jpg"
                                    alt="<?= e($project['title']) ?>"
                                    loading="lazy"
                                    class="w-full h-full object-cover"
                                >

                                <span class="absolute inset-0 flex items-center justify-center bg-black/20">

                                    <i class="ri-play-circle-fill text-7xl text-red-500"></i>

                                </span>

                            </a>

                        <?php elseif (
                            $firstMedia['type'] ===
                            'video'
                        ): ?>

                            <video
                                src="<?= e($firstMedia['url']) ?>"
                                controls
                                playsinline
                                preload="metadata"
                                class="w-full h-full object-cover"
                            ></video>

                        <?php else: ?>

                            <a
                                href="index.php?page=project-details&id=<?= (int) $project['id'] ?>"
                                class="block w-full h-full"
                            >

                                <img
                                    src="<?= e($firstMedia['url']) ?>"
                                    alt="<?= e($project['title']) ?>"
                                    loading="lazy"
                                    class="w-full h-full object-cover hover:scale-105 transition duration-500"
                                >

                            </a>

                        <?php endif; ?>

                    </div>

                <?php else: ?>

                    <a
                        href="index.php?page=project-details&id=<?= (int) $project['id'] ?>"
                        class="aspect-video flex items-center justify-center bg-gradient-to-br from-yellow-500/10 to-black"
                    >
                        <i class="ri-code-box-line text-8xl text-yellow-500/30"></i>
                    </a>

                <?php endif; ?>

                <?php if (count($gallery) > 1): ?>

                    <div class="px-5 py-3 text-xs text-gray-500 border-t border-white/5">

                        <i class="ri-gallery-line"></i>

                        <?= count($gallery) ?>
                        screenshots/media available

                    </div>

                <?php endif; ?>

                <!-- Engagement Count -->

                <div class="px-5 pt-4 flex items-center justify-between text-sm text-gray-500">

                    <span>
                        <i class="ri-heart-fill text-red-500"></i>

                        <span
                            class="like-count"
                            data-project="<?= (int) $project['id'] ?>"
                        >
                            <?= number_format(
                                (int) $project['likes']
                            ) ?>
                        </span>
                    </span>

                    <span>
                        <?= number_format(
                            (int) $project['views']
                        ) ?>
                        views
                    </span>

                </div>

                <!-- Social Actions -->

                <div class="p-3 mt-3 border-t border-white/5 grid grid-cols-3 gap-2">

                    <form
                        action="api/project_like.php"
                        method="POST"
                        class="project-like-form"
                    >
                        <?= csrf_field() ?>

                        <input
                            type="hidden"
                            name="project_id"
                            value="<?= (int) $project['id'] ?>"
                        >

                        <button
                            type="submit"
                            class="like-button w-full py-3 rounded-xl font-bold hover:bg-white/5 transition <?= $liked
                                ? 'text-red-400'
                                : 'text-gray-400' ?>"
                        >
                            <i class="<?= $liked
                                ? 'ri-heart-fill'
                                : 'ri-heart-line' ?> text-xl mr-1"></i>

                            <span>
                                <?= $liked
                                    ? 'Liked'
                                    : 'Like' ?>
                            </span>
                        </button>

                    </form>

                    <a
                        href="index.php?page=project-details&id=<?= (int) $project['id'] ?>"
                        class="py-3 rounded-xl text-gray-400 font-bold hover:bg-white/5 hover:text-white text-center transition"
                    >
                        <i class="ri-article-line text-xl mr-1"></i>
                        Details
                    </a>

                    <button
                        type="button"
                        data-share-url="index.php?page=project-details&id=<?= (int) $project['id'] ?>"
                        data-share-title="<?= e($project['title']) ?>"
                        class="project-share-button py-3 rounded-xl text-gray-400 font-bold hover:bg-white/5 hover:text-white transition"
                    >
                        <i class="ri-share-forward-line text-xl mr-1"></i>
                        Share
                    </button>

                </div>

            </article>

        <?php endforeach; ?>

        <?php if ($totalPages > 1): ?>

            <nav class="flex items-center justify-center gap-3 mt-12">

                <?php if ($pageNumber > 1): ?>

                    <a
                        href="<?= e(
                            portfolio_page_url(
                                $pageNumber - 1,
                                $search,
                                $categoryFilter,
                                $featuredFilter
                            )
                        ) ?>"
                        class="px-5 py-3 rounded-xl border border-white/10 hover:border-yellow-500"
                    >
                        ← Previous
                    </a>

                <?php endif; ?>

                <span class="px-5 py-3 text-gray-500">
                    Page <?= $pageNumber ?>
                    of <?= $totalPages ?>
                </span>

                <?php if (
                    $pageNumber < $totalPages
                ): ?>

                    <a
                        href="<?= e(
                            portfolio_page_url(
                                $pageNumber + 1,
                                $search,
                                $categoryFilter,
                                $featuredFilter
                            )
                        ) ?>"
                        class="px-5 py-3 rounded-xl bg-yellow-500 text-black font-bold"
                    >
                        Next →
                    </a>

                <?php endif; ?>

            </nav>

        <?php endif; ?>

    </section>

</main>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        const likeForms =
            document.querySelectorAll(
                '.project-like-form'
            );

        likeForms.forEach(function (form) {
            form.addEventListener(
                'submit',
                async function (event) {
                    event.preventDefault();

                    const button =
                        form.querySelector(
                            '.like-button'
                        );

                    const projectInput =
                        form.querySelector(
                            '[name="project_id"]'
                        );

                    if (
                        !button ||
                        !projectInput ||
                        button.disabled
                    ) {
                        return;
                    }

                    button.disabled = true;

                    const formData =
                        new FormData(form);

                    formData.append(
                        'ajax',
                        '1'
                    );

                    try {
                        const response =
                            await fetch(
                                'api/project_like.php',
                                {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-Requested-With':
                                            'XMLHttpRequest'
                                    }
                                }
                            );

                        const result =
                            await response.json();

                        if (!result.success) {
                            throw new Error(
                                result.message ||
                                'Like failed'
                            );
                        }

                        const icon =
                            button.querySelector('i');

                        const text =
                            button.querySelector(
                                'span'
                            );

                        button.classList.toggle(
                            'text-red-400',
                            result.liked
                        );

                        button.classList.toggle(
                            'text-gray-400',
                            !result.liked
                        );

                        if (icon) {
                            icon.className =
                                result.liked
                                    ? 'ri-heart-fill text-xl mr-1'
                                    : 'ri-heart-line text-xl mr-1';
                        }

                        if (text) {
                            text.textContent =
                                result.liked
                                    ? 'Liked'
                                    : 'Like';
                        }

                        const count =
                            document.querySelector(
                                '.like-count[data-project="' +
                                projectInput.value +
                                '"]'
                            );

                        if (count) {
                            count.textContent =
                                Number(
                                    result.likes
                                ).toLocaleString();
                        }
                    } catch (error) {
                        alert(
                            'Like could not be updated.'
                        );
                    } finally {
                        button.disabled = false;
                    }
                }
            );
        });

        document
            .querySelectorAll(
                '.project-share-button'
            )
            .forEach(function (button) {
                button.addEventListener(
                    'click',
                    async function () {
                        const relativeUrl =
                            button.dataset.shareUrl;

                        const shareUrl =
                            new URL(
                                relativeUrl,
                                window.location.href
                            ).href;

                        const title =
                            button.dataset.shareTitle ||
                            'Raj Agency Project';

                        if (navigator.share) {
                            try {
                                await navigator.share({
                                    title: title,
                                    url: shareUrl
                                });
                            } catch (error) {
                                return;
                            }
                        } else if (
                            navigator.clipboard
                        ) {
                            await navigator.clipboard
                                .writeText(shareUrl);

                            const original =
                                button.innerHTML;

                            button.innerHTML =
                                '<i class="ri-check-line text-xl mr-1"></i> Copied';

                            setTimeout(
                                function () {
                                    button.innerHTML =
                                        original;
                                },
                                1800
                            );
                        } else {
                            window.prompt(
                                'Copy this project link:',
                                shareUrl
                            );
                        }
                    }
                );
            });
    }
);
</script>