<?php
declare(strict_types=1);

require_once __DIR__ .
    '/../includes/project_view.php';

/*
|--------------------------------------------------------------------------
| Find Published Project
|--------------------------------------------------------------------------
*/

$projectId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

$project = false;

if ($projectId && $projectId > 0) {
    $statement = $pdo->prepare(
        'SELECT *
         FROM projects
         WHERE id = ?
           AND is_active = 1
         LIMIT 1'
    );

    $statement->execute([
        $projectId,
    ]);

    $project = $statement->fetch();
}

if (!$project) {
    http_response_code(404);
    ?>

    <main class="min-h-screen pt-40 px-5 text-center bg-[#050505]">

        <i class="ri-folder-warning-line text-7xl text-yellow-500"></i>

        <h1 class="text-4xl font-bold mt-5">
            Project Not Found
        </h1>

        <p class="text-gray-400 mt-3">
            This project is unavailable or has been removed.
        </p>

        <a
            href="index.php?page=portfolio"
            class="inline-block mt-7 px-6 py-3 bg-yellow-500 text-black font-bold rounded-xl"
        >
            Back to Portfolio
        </a>

    </main>

    <?php
    return;
}

/*
|--------------------------------------------------------------------------
| Count one view per session
|--------------------------------------------------------------------------
*/

$viewedProjects =
    $_SESSION['viewed_projects'] ??
    [];

if (!is_array($viewedProjects)) {
    $viewedProjects = [];
}

$viewedProjects = array_values(
    array_unique(
        array_filter(
            array_map(
                'intval',
                $viewedProjects
            ),
            static fn (int $id): bool =>
                $id > 0
        )
    )
);

if (
    !in_array(
        (int) $project['id'],
        $viewedProjects,
        true
    )
) {
    $viewStatement = $pdo->prepare(
        'UPDATE projects
         SET views = views + 1
         WHERE id = ?'
    );

    $viewStatement->execute([
        (int) $project['id'],
    ]);

    $project['views'] =
        (int) $project['views'] + 1;

    $viewedProjects[] =
        (int) $project['id'];

    $_SESSION['viewed_projects'] =
        array_slice(
            $viewedProjects,
            -500
        );
}

/*
|--------------------------------------------------------------------------
| Process Project Content
|--------------------------------------------------------------------------
*/

$details = project_public_details(
    $project['details'] ?? ''
);

$gallery = project_public_gallery(
    $project['gallery'] ?? '',
    $project['thumbnail'] ?? '',
    $project['video_preview'] ?? ''
);

$technologies =
    project_public_technologies(
        $project['technologies'] ??
        ''
    );

$projectUrl = valid_http_url(
    $project['project_url'] ??
    ''
);

$githubUrl = valid_http_url(
    $project['github_url'] ??
    ''
);

$liked = project_is_liked(
    (int) $project['id']
);

/*
|--------------------------------------------------------------------------
| Find Hero Image
|--------------------------------------------------------------------------
*/

$heroImage = project_public_media_url(
    $project['thumbnail'] ?? ''
);

if ($heroImage === '') {
    foreach ($gallery as $item) {
        if (
            ($item['type'] ?? '') ===
            'image'
        ) {
            $heroImage = $item['url'];

            break;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Project Information Cards
|--------------------------------------------------------------------------
*/

$projectInformation = [];

if ($details['role'] !== '') {
    $projectInformation[] = [
        'icon' => 'ri-user-star-line',
        'label' => 'My Role',
        'value' => $details['role'],
    ];
}

if ($details['duration'] !== '') {
    $projectInformation[] = [
        'icon' => 'ri-time-line',
        'label' => 'Duration',
        'value' => $details['duration'],
    ];
}

if ($details['platform'] !== '') {
    $projectInformation[] = [
        'icon' => 'ri-device-line',
        'label' => 'Platform',
        'value' => $details['platform'],
    ];
}

if (
    $details['show_client'] &&
    !empty($project['client_name'])
) {
    $projectInformation[] = [
        'icon' => 'ri-building-line',
        'label' => 'Client',
        'value' =>
            (string) $project['client_name'],
    ];
}

/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/

$createdTimestamp = strtotime(
    (string) $project['created_at']
);

if ($createdTimestamp === false) {
    $createdTimestamp = time();
}

/*
|--------------------------------------------------------------------------
| Related Projects
|--------------------------------------------------------------------------
*/

$relatedStatement = $pdo->prepare(
    'SELECT
        id,
        title,
        category,
        short_desc,
        thumbnail,
        is_featured
     FROM projects
     WHERE is_active = 1
       AND category = ?
       AND id <> ?
     ORDER BY
        is_featured DESC,
        sort_order DESC,
        created_at DESC
     LIMIT 3'
);

$relatedStatement->execute([
    (string) $project['category'],
    (int) $project['id'],
]);

$relatedProjects =
    $relatedStatement->fetchAll();
?>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"
>

<style>
    .project-gallery .swiper-pagination-bullet {
        background: rgba(255, 255, 255, .5);
        opacity: 1;
    }

    .project-gallery .swiper-pagination-bullet-active {
        background: #f4b90b;
    }
</style>

<main class="pb-24 min-h-screen bg-[#050505] overflow-hidden">

    <!-- Project Hero -->

    <section class="relative min-h-[680px] flex items-end pt-32 pb-16 border-b border-white/5">

        <?php if ($heroImage !== ''): ?>

            <div class="absolute inset-0">

                <img
                    src="<?= e($heroImage) ?>"
                    alt="<?= e($project['title']) ?>"
                    class="w-full h-full object-cover"
                >

                <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-[#050505]/85 to-black/40"></div>

                <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-transparent to-black/30"></div>

            </div>

        <?php else: ?>

            <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/10 via-[#080808] to-black"></div>

        <?php endif; ?>

        <div class="max-w-7xl mx-auto px-5 w-full relative z-10">

            <nav class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-400 mb-7">

                <a
                    href="index.php?page=home"
                    class="hover:text-white"
                >
                    Home
                </a>

                <span>/</span>

                <a
                    href="index.php?page=portfolio"
                    class="hover:text-white"
                >
                    Portfolio
                </a>

                <span>/</span>

                <span class="text-yellow-500">
                    Project Details
                </span>

            </nav>

            <div class="max-w-4xl">

                <div class="flex flex-wrap items-center gap-3 mb-5">

                    <span class="px-4 py-2 rounded-full bg-yellow-500 text-black text-xs font-bold uppercase tracking-wider">
                        <?= e(
                            $project['category'] ??
                            'Project'
                        ) ?>
                    </span>

                    <?php if (
                        (int) $project['is_featured'] === 1
                    ): ?>

                        <span class="px-4 py-2 rounded-full bg-white/10 border border-white/20 backdrop-blur text-yellow-400 text-xs font-bold">
                            <i class="ri-star-fill mr-1"></i>
                            Featured Project
                        </span>

                    <?php endif; ?>

                </div>

                <h1 class="text-4xl sm:text-5xl md:text-7xl font-display font-bold leading-tight">
                    <?= e($project['title']) ?>
                </h1>

                <p class="text-gray-300 text-lg md:text-xl leading-8 mt-6 max-w-3xl">
                    <?= e(
                        $project['short_desc'] ??
                        ''
                    ) ?>
                </p>

                <div class="flex flex-wrap items-center gap-5 mt-7 text-sm text-gray-400">

                    <span>
                        <i class="ri-calendar-line text-yellow-500 mr-1"></i>

                        <?= e(
                            date(
                                'd M Y',
                                $createdTimestamp
                            )
                        ) ?>
                    </span>

                    <span>
                        <i class="ri-eye-line text-blue-400 mr-1"></i>

                        <?= number_format(
                            (int) $project['views']
                        ) ?>

                        views
                    </span>

                    <span>
                        <i class="ri-heart-fill text-red-500 mr-1"></i>

                        <span id="project-like-count">
                            <?= number_format(
                                (int) $project['likes']
                            ) ?>
                        </span>

                        likes
                    </span>

                </div>

                <div class="flex flex-wrap gap-3 mt-9">

                    <?php if (
                        $details['show_live_url'] &&
                        $projectUrl !== ''
                    ): ?>

                        <a
                            href="<?= e($projectUrl) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-6 py-4 bg-yellow-500 text-black font-bold rounded-xl hover:bg-yellow-400"
                        >
                            <i class="ri-external-link-line"></i>
                            View Live Project
                        </a>

                    <?php endif; ?>

                    <?php if (
                        $details['show_github_url'] &&
                        $githubUrl !== ''
                    ): ?>

                        <a
                            href="<?= e($githubUrl) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-6 py-4 bg-white text-black font-bold rounded-xl hover:bg-gray-200"
                        >
                            <i class="ri-github-fill text-xl"></i>
                            View Source
                        </a>

                    <?php endif; ?>

                    <form
                        action="api/project_like.php"
                        method="POST"
                        id="details-like-form"
                    >
                        <?= csrf_field() ?>

                        <input
                            type="hidden"
                            name="project_id"
                            value="<?= (int) $project['id'] ?>"
                        >

                        <button
                            id="details-like-button"
                            type="submit"
                            class="inline-flex items-center gap-2 px-6 py-4 border border-white/20 backdrop-blur font-bold rounded-xl hover:bg-white/10 <?= $liked
                                ? 'text-red-400'
                                : 'text-white' ?>"
                        >
                            <i class="<?= $liked
                                ? 'ri-heart-fill'
                                : 'ri-heart-line' ?> text-xl"></i>

                            <span>
                                <?= $liked
                                    ? 'Liked'
                                    : 'Like Project' ?>
                            </span>
                        </button>

                    </form>

                    <button
                        type="button"
                        id="project-share-button"
                        class="inline-flex items-center gap-2 px-6 py-4 border border-white/20 backdrop-blur text-white font-bold rounded-xl hover:bg-white/10"
                    >
                        <i class="ri-share-forward-line text-xl"></i>

                        <span>
                            Share
                        </span>
                    </button>

                </div>

            </div>

        </div>

    </section>

    <!-- Project Information -->

    <?php if (
        $details['show_project_info'] &&
        $projectInformation !== []
    ): ?>

        <section class="max-w-7xl mx-auto px-5 mt-12">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <?php foreach (
                    $projectInformation
                    as $information
                ): ?>

                    <div class="bg-[#111] border border-white/10 rounded-2xl p-5 flex items-center gap-4">

                        <div class="w-12 h-12 rounded-xl bg-yellow-500/10 text-yellow-500 flex items-center justify-center text-2xl">

                            <i class="<?= e($information['icon']) ?>"></i>

                        </div>

                        <div>

                            <span class="block text-xs uppercase tracking-wider text-gray-500">
                                <?= e($information['label']) ?>
                            </span>

                            <strong class="block mt-1">
                                <?= e($information['value']) ?>
                            </strong>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </section>

    <?php endif; ?>

    <!-- Multiple Screenshot Gallery -->

    <?php if (
        $details['show_gallery'] &&
        $gallery !== []
    ): ?>

        <section class="max-w-7xl mx-auto px-5 mt-20">

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">

                <div>

                    <span class="text-yellow-500 text-sm font-bold uppercase tracking-[.25em]">
                        Visual Preview
                    </span>

                    <h2 class="text-3xl md:text-5xl font-bold mt-3">
                        Project Screenshots
                    </h2>

                </div>

                <span class="text-gray-500 text-sm">
                    <?= count($gallery) ?>
                    screenshots/media
                </span>

            </div>

            <div class="bg-[#0b0b0b] border border-white/10 rounded-3xl overflow-hidden shadow-2xl">

                <div class="swiper project-gallery">

                    <div class="swiper-wrapper">

                        <?php foreach (
                            $gallery as $item
                        ): ?>

                            <div class="swiper-slide bg-black">

                                <div class="w-full min-h-[300px] md:min-h-[650px] h-[70vh] max-h-[800px] flex items-center justify-center">

                                    <?php if (
                                        $item['type'] ===
                                        'youtube'
                                    ): ?>

                                        <?php
                                        $youtubeId =
                                            project_public_youtube_id(
                                                $item['url']
                                            );
                                        ?>

                                        <iframe
                                            class="w-full h-full"
                                            src="https://www.youtube-nocookie.com/embed/<?= e($youtubeId) ?>?rel=0"
                                            title="<?= e($project['title']) ?>"
                                            loading="lazy"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            referrerpolicy="strict-origin-when-cross-origin"
                                            allowfullscreen
                                        ></iframe>

                                    <?php elseif (
                                        $item['type'] ===
                                        'video'
                                    ): ?>

                                        <video
                                            src="<?= e($item['url']) ?>"
                                            controls
                                            playsinline
                                            preload="metadata"
                                            class="w-full h-full object-contain"
                                        ></video>

                                    <?php else: ?>

                                        <button
                                            type="button"
                                            class="project-image-button w-full h-full cursor-zoom-in"
                                            data-image="<?= e($item['url']) ?>"
                                        >
                                            <img
                                                src="<?= e($item['url']) ?>"
                                                alt="<?= e($project['title']) ?>"
                                                loading="lazy"
                                                class="w-full h-full object-contain"
                                            >
                                        </button>

                                    <?php endif; ?>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                    <?php if (
                        count($gallery) > 1
                    ): ?>

                        <div class="swiper-button-next !text-yellow-500"></div>

                        <div class="swiper-button-prev !text-yellow-500"></div>

                        <div class="swiper-pagination"></div>

                    <?php endif; ?>

                </div>

            </div>

        </section>

    <?php endif; ?>

    <!-- Main Details -->

    <section class="max-w-7xl mx-auto px-5 mt-20 grid grid-cols-1 lg:grid-cols-12 gap-8">

        <div class="lg:col-span-8 space-y-8">

            <!-- Overview -->

            <?php if (
                $details['show_overview'] &&
                !empty($project['short_desc'])
            ): ?>

                <article class="bg-[#111] border border-white/10 rounded-3xl p-6 md:p-9">

                    <div class="flex items-center gap-4 mb-6">

                        <div class="w-12 h-12 rounded-xl bg-yellow-500/10 text-yellow-500 flex items-center justify-center text-2xl">
                            <i class="ri-article-line"></i>
                        </div>

                        <h2 class="text-2xl md:text-3xl font-bold">
                            Project Overview
                        </h2>

                    </div>

                    <p class="text-gray-300 text-lg leading-9 whitespace-pre-line"><?= e(
                        $project['short_desc']
                    ) ?></p>

                </article>

            <?php endif; ?>

            <!-- Full Case Study -->

            <?php if (
                $details['show_case_study'] &&
                !empty($project['case_study_text'])
            ): ?>

                <article class="bg-[#111] border border-white/10 rounded-3xl p-6 md:p-9">

                    <div class="flex items-center gap-4 mb-6">

                        <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-2xl">
                            <i class="ri-file-text-line"></i>
                        </div>

                        <h2 class="text-2xl md:text-3xl font-bold">
                            Complete Case Study
                        </h2>

                    </div>

                    <p class="text-gray-300 leading-8 whitespace-pre-line"><?= e(
                        $project['case_study_text']
                    ) ?></p>

                </article>

            <?php endif; ?>

            <!-- Challenge and Solution -->

            <?php if (
                (
                    $details['show_challenge'] &&
                    $details['challenge'] !== ''
                ) ||
                (
                    $details['show_solution'] &&
                    $details['solution'] !== ''
                )
            ): ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <?php if (
                        $details['show_challenge'] &&
                        $details['challenge'] !== ''
                    ): ?>

                        <article class="bg-gradient-to-br from-red-500/10 to-[#111] border border-red-500/20 rounded-3xl p-6 md:p-8">

                            <div class="w-12 h-12 rounded-xl bg-red-500/10 text-red-400 flex items-center justify-center text-2xl mb-5">
                                <i class="ri-error-warning-line"></i>
                            </div>

                            <h2 class="text-2xl font-bold">
                                The Challenge
                            </h2>

                            <p class="text-gray-300 leading-8 mt-4 whitespace-pre-line"><?= e(
                                $details['challenge']
                            ) ?></p>

                        </article>

                    <?php endif; ?>

                    <?php if (
                        $details['show_solution'] &&
                        $details['solution'] !== ''
                    ): ?>

                        <article class="bg-gradient-to-br from-green-500/10 to-[#111] border border-green-500/20 rounded-3xl p-6 md:p-8">

                            <div class="w-12 h-12 rounded-xl bg-green-500/10 text-green-400 flex items-center justify-center text-2xl mb-5">
                                <i class="ri-lightbulb-flash-line"></i>
                            </div>

                            <h2 class="text-2xl font-bold">
                                The Solution
                            </h2>

                            <p class="text-gray-300 leading-8 mt-4 whitespace-pre-line"><?= e(
                                $details['solution']
                            ) ?></p>

                        </article>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

            <!-- Key Features -->

            <?php if (
                $details['show_features'] &&
                $details['key_features'] !== []
            ): ?>

                <article class="bg-[#111] border border-white/10 rounded-3xl p-6 md:p-9">

                    <div class="flex items-center gap-4 mb-7">

                        <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-2xl">
                            <i class="ri-function-line"></i>
                        </div>

                        <h2 class="text-2xl md:text-3xl font-bold">
                            Key Features
                        </h2>

                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <?php foreach (
                            $details['key_features']
                            as $index => $feature
                        ): ?>

                            <div class="flex items-start gap-4 bg-black/40 border border-white/5 rounded-2xl p-5">

                                <span class="w-8 h-8 shrink-0 rounded-full bg-yellow-500 text-black font-bold flex items-center justify-center text-sm">
                                    <?= $index + 1 ?>
                                </span>

                                <span class="text-gray-300 leading-7">
                                    <?= e($feature) ?>
                                </span>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </article>

            <?php endif; ?>

            <!-- Result -->

            <?php if (
                $details['show_results'] &&
                $details['result'] !== ''
            ): ?>

                <article class="bg-gradient-to-r from-yellow-500/10 to-[#111] border border-yellow-500/20 rounded-3xl p-6 md:p-9">

                    <div class="flex items-center gap-4 mb-6">

                        <div class="w-12 h-12 rounded-xl bg-yellow-500 text-black flex items-center justify-center text-2xl">
                            <i class="ri-line-chart-line"></i>
                        </div>

                        <h2 class="text-2xl md:text-3xl font-bold">
                            Project Result
                        </h2>

                    </div>

                    <p class="text-gray-200 text-lg leading-9 whitespace-pre-line"><?= e(
                        $details['result']
                    ) ?></p>

                </article>

            <?php endif; ?>

            <!-- Testimonial -->

            <?php if (
                $details['show_testimonial'] &&
                $details['testimonial'] !== ''
            ): ?>

                <article class="bg-[#111] border border-white/10 rounded-3xl p-7 md:p-10 relative overflow-hidden">

                    <i class="ri-double-quotes-l absolute top-4 right-7 text-8xl text-yellow-500/10"></i>

                    <div class="relative z-10">

                        <span class="text-yellow-500 text-sm font-bold uppercase tracking-[.25em]">
                            Client Feedback
                        </span>

                        <blockquote class="text-xl md:text-2xl text-gray-200 leading-9 mt-5 whitespace-pre-line">
                            “<?= e(
                                $details['testimonial']
                            ) ?>”
                        </blockquote>

                        <?php if (
                            $details[
                                'testimonial_author'
                            ] !== ''
                        ): ?>

                            <p class="text-yellow-500 font-bold mt-6">
                                — <?= e(
                                    $details[
                                        'testimonial_author'
                                    ]
                                ) ?>
                            </p>

                        <?php endif; ?>

                    </div>

                </article>

            <?php endif; ?>

        </div>

        <!-- Sidebar -->

        <aside class="lg:col-span-4">

            <div class="sticky top-28 space-y-6">

                <?php if (
                    $details['show_technologies'] &&
                    $technologies !== []
                ): ?>

                    <div class="bg-[#111] border border-white/10 rounded-3xl p-6">

                        <h2 class="text-xl font-bold flex items-center gap-2">
                            <i class="ri-code-box-line text-yellow-500"></i>
                            Technologies
                        </h2>

                        <div class="flex flex-wrap gap-2 mt-5">

                            <?php foreach (
                                $technologies as $technology
                            ): ?>

                                <span class="px-3 py-2 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-300 text-sm font-bold">
                                    <?= e($technology) ?>
                                </span>

                            <?php endforeach; ?>

                        </div>

                    </div>

                <?php endif; ?>

                <div class="bg-[#111] border border-white/10 rounded-3xl p-6">

                    <h2 class="text-xl font-bold">
                        Interested in a Similar Project?
                    </h2>

                    <p class="text-gray-400 text-sm leading-6 mt-3">
                        Contact me to discuss your mobile app,
                        website or custom software project.
                    </p>

                    <a
                        href="index.php?page=contact"
                        class="block w-full mt-6 py-4 bg-yellow-500 text-black text-center font-bold rounded-xl hover:bg-yellow-400"
                    >
                        Start Your Project
                    </a>

                    <a
                        href="https://wa.me/8801310100239"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center justify-center gap-2 w-full mt-3 py-3.5 border border-green-500/30 text-green-400 font-bold rounded-xl hover:bg-green-500/10"
                    >
                        <i class="ri-whatsapp-line text-xl"></i>
                        WhatsApp
                    </a>

                </div>

            </div>

        </aside>

    </section>

    <!-- Related Projects -->

    <?php if ($relatedProjects !== []): ?>

        <section class="max-w-7xl mx-auto px-5 mt-24">

            <div class="flex items-end justify-between gap-4 mb-8">

                <div>

                    <span class="text-yellow-500 text-sm font-bold uppercase tracking-[.25em]">
                        More Work
                    </span>

                    <h2 class="text-3xl md:text-5xl font-bold mt-3">
                        Related Projects
                    </h2>

                </div>

                <a
                    href="index.php?page=portfolio"
                    class="text-yellow-500 font-bold hover:underline"
                >
                    View All
                </a>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <?php foreach (
                    $relatedProjects as $related
                ): ?>

                    <?php
                    $relatedImage =
                        project_public_media_url(
                            $related['thumbnail'] ??
                            ''
                        );
                    ?>

                    <a
                        href="index.php?page=project-details&id=<?= (int) $related['id'] ?>"
                        class="group bg-[#111] border border-white/10 rounded-3xl overflow-hidden hover:border-yellow-500/40 transition"
                    >

                        <div class="h-56 bg-black overflow-hidden">

                            <?php if (
                                $relatedImage !== ''
                            ): ?>

                                <img
                                    src="<?= e($relatedImage) ?>"
                                    alt="<?= e($related['title']) ?>"
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                >

                            <?php else: ?>

                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="ri-code-box-line text-6xl text-yellow-500/30"></i>
                                </div>

                            <?php endif; ?>

                        </div>

                        <div class="p-5">

                            <span class="text-yellow-500 text-xs font-bold uppercase">
                                <?= e(
                                    $related['category'] ??
                                    'Project'
                                ) ?>
                            </span>

                            <h3 class="text-xl font-bold mt-2 group-hover:text-yellow-500">
                                <?= e($related['title']) ?>
                            </h3>

                            <p class="text-gray-500 text-sm leading-6 mt-3">
                                <?= e(
                                    project_public_excerpt(
                                        $related['short_desc'] ??
                                        '',
                                        120
                                    )
                                ) ?>
                            </p>

                        </div>

                    </a>

                <?php endforeach; ?>

            </div>

        </section>

    <?php endif; ?>

</main>

<!-- Image Lightbox -->

<div
    id="project-lightbox"
    class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/95 p-4"
>

    <button
        type="button"
        id="lightbox-close"
        class="absolute top-5 right-5 w-12 h-12 rounded-full bg-white/10 text-white text-2xl hover:bg-red-500"
        aria-label="Close image"
    >
        <i class="ri-close-line"></i>
    </button>

    <img
        id="lightbox-image"
        src=""
        alt="Project screenshot preview"
        class="max-w-full max-h-full object-contain rounded-xl"
    >

</div>

<?php if (
    $details['show_gallery'] &&
    $gallery !== []
): ?>

    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

<?php endif; ?>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        /*
        |--------------------------------------------------------------------------
        | Screenshot Gallery
        |--------------------------------------------------------------------------
        */

        <?php if (
            $details['show_gallery'] &&
            $gallery !== []
        ): ?>

        new Swiper(
            '.project-gallery',
            {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: false,

                navigation: {
                    nextEl:
                        '.swiper-button-next',

                    prevEl:
                        '.swiper-button-prev'
                },

                pagination: {
                    el:
                        '.swiper-pagination',

                    clickable: true
                },

                keyboard: {
                    enabled: true
                }
            }
        );

        <?php endif; ?>

        /*
        |--------------------------------------------------------------------------
        | Image Lightbox
        |--------------------------------------------------------------------------
        */

        const lightbox =
            document.getElementById(
                'project-lightbox'
            );

        const lightboxImage =
            document.getElementById(
                'lightbox-image'
            );

        const closeButton =
            document.getElementById(
                'lightbox-close'
            );

        function closeLightbox() {
            if (!lightbox || !lightboxImage) {
                return;
            }

            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');

            lightboxImage.src = '';

            document.body.style.overflow = '';
        }

        document
            .querySelectorAll(
                '.project-image-button'
            )
            .forEach(function (button) {
                button.addEventListener(
                    'click',
                    function () {
                        if (
                            !lightbox ||
                            !lightboxImage
                        ) {
                            return;
                        }

                        lightboxImage.src =
                            button.dataset.image;

                        lightbox.classList.remove(
                            'hidden'
                        );

                        lightbox.classList.add(
                            'flex'
                        );

                        document.body.style.overflow =
                            'hidden';
                    }
                );
            });

        if (closeButton) {
            closeButton.addEventListener(
                'click',
                closeLightbox
            );
        }

        if (lightbox) {
            lightbox.addEventListener(
                'click',
                function (event) {
                    if (event.target === lightbox) {
                        closeLightbox();
                    }
                }
            );
        }

        document.addEventListener(
            'keydown',
            function (event) {
                if (event.key === 'Escape') {
                    closeLightbox();
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Real Like System
        |--------------------------------------------------------------------------
        */

        const likeForm =
            document.getElementById(
                'details-like-form'
            );

        const likeButton =
            document.getElementById(
                'details-like-button'
            );

        const likeCount =
            document.getElementById(
                'project-like-count'
            );

        if (likeForm && likeButton) {
            likeForm.addEventListener(
                'submit',
                async function (event) {
                    event.preventDefault();

                    if (likeButton.disabled) {
                        return;
                    }

                    likeButton.disabled = true;

                    const formData =
                        new FormData(likeForm);

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
                            likeButton.querySelector(
                                'i'
                            );

                        const text =
                            likeButton.querySelector(
                                'span'
                            );

                        likeButton.classList.toggle(
                            'text-red-400',
                            result.liked
                        );

                        likeButton.classList.toggle(
                            'text-white',
                            !result.liked
                        );

                        if (icon) {
                            icon.className =
                                result.liked
                                    ? 'ri-heart-fill text-xl'
                                    : 'ri-heart-line text-xl';
                        }

                        if (text) {
                            text.textContent =
                                result.liked
                                    ? 'Liked'
                                    : 'Like Project';
                        }

                        if (likeCount) {
                            likeCount.textContent =
                                Number(
                                    result.likes
                                ).toLocaleString();
                        }
                    } catch (error) {
                        alert(
                            'Like could not be updated.'
                        );
                    } finally {
                        likeButton.disabled = false;
                    }
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Share System
        |--------------------------------------------------------------------------
        */

        const shareButton =
            document.getElementById(
                'project-share-button'
            );

        if (shareButton) {
            shareButton.addEventListener(
                'click',
                async function () {
                    const shareData = {
                        title:
                            <?= json_encode(
                                (string) $project['title'],
                                JSON_UNESCAPED_SLASHES |
                                JSON_UNESCAPED_UNICODE
                            ) ?>,

                        url:
                            window.location.href
                    };

                    if (navigator.share) {
                        try {
                            await navigator.share(
                                shareData
                            );
                        } catch (error) {
                            return;
                        }
                    } else if (
                        navigator.clipboard
                    ) {
                        await navigator.clipboard
                            .writeText(
                                shareData.url
                            );

                        const text =
                            shareButton.querySelector(
                                'span'
                            );

                        if (text) {
                            const previous =
                                text.textContent;

                            text.textContent =
                                'Link Copied';

                            setTimeout(
                                function () {
                                    text.textContent =
                                        previous;
                                },
                                1800
                            );
                        }
                    } else {
                        window.prompt(
                            'Copy this project link:',
                            shareData.url
                        );
                    }
                }
            );
        }
    }
);
</script>