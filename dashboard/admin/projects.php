<?php
declare(strict_types=1);

require_once '../../config/db.php';
require_once 'project_helpers.php';

require_admin();

/*
|--------------------------------------------------------------------------
| Disable Admin Page Cache
|--------------------------------------------------------------------------
*/

header(
    'Cache-Control: no-store, no-cache, must-revalidate, max-age=0'
);

header('Pragma: no-cache');

/*
|--------------------------------------------------------------------------
| Quick Project Actions
|--------------------------------------------------------------------------
*/

if (request_is_post()) {
    verify_csrf();

    $action = (string) (
        $_POST['action'] ?? ''
    );

    $projectId = filter_input(
        INPUT_POST,
        'project_id',
        FILTER_VALIDATE_INT
    );

    if (
        !$projectId ||
        $projectId < 1
    ) {
        flash(
            'error',
            'Invalid project ID.'
        );

        redirect('projects.php');
    }

    if ($action === 'toggle_active') {
        $statement = $pdo->prepare(
            'UPDATE projects
             SET is_active =
                IF(is_active = 1, 0, 1)
             WHERE id = ?'
        );

        $statement->execute([
            $projectId,
        ]);

        flash(
            'success',
            'Project visibility updated.'
        );

        redirect('projects.php');
    }

    if ($action === 'toggle_featured') {
        $statement = $pdo->prepare(
            'UPDATE projects
             SET is_featured =
                IF(is_featured = 1, 0, 1)
             WHERE id = ?'
        );

        $statement->execute([
            $projectId,
        ]);

        flash(
            'success',
            'Featured status updated.'
        );

        redirect('projects.php');
    }

    flash(
        'error',
        'Invalid project action.'
    );

    redirect('projects.php');
}

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$search = clean_text(
    $_GET['search'] ?? '',
    100
);

$categoryFilter = clean_text(
    $_GET['category'] ?? 'all',
    80
);

$statusFilter = clean_text(
    $_GET['status'] ?? 'all',
    20
);

if (
    !in_array(
        $statusFilter,
        [
            'all',
            'active',
            'hidden',
            'featured',
        ],
        true
    )
) {
    $statusFilter = 'all';
}

$where = [];
$parameters = [];

if ($search !== '') {
    $where[] =
        '(
            title LIKE ?
            OR category LIKE ?
            OR client_name LIKE ?
            OR short_desc LIKE ?
        )';

    $searchValue =
        '%' . $search . '%';

    array_push(
        $parameters,
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue
    );
}

if ($categoryFilter !== 'all') {
    $where[] = 'category = ?';

    $parameters[] =
        $categoryFilter;
}

if ($statusFilter === 'active') {
    $where[] = 'is_active = 1';
} elseif ($statusFilter === 'hidden') {
    $where[] = 'is_active = 0';
} elseif ($statusFilter === 'featured') {
    $where[] = 'is_featured = 1';
}

$whereSql = $where !== []
    ? ' WHERE ' . implode(
        ' AND ',
        $where
    )
    : '';

/*
|--------------------------------------------------------------------------
| Load Projects Including Current Gallery
|--------------------------------------------------------------------------
*/

$statement = $pdo->prepare(
    'SELECT
        id,
        title,
        category,
        client_name,
        short_desc,
        thumbnail,
        video_preview,
        gallery,
        is_featured,
        is_active,
        sort_order,
        views,
        likes,
        created_at,
        updated_at
     FROM projects' .
     $whereSql .
    ' ORDER BY
        is_featured DESC,
        sort_order DESC,
        created_at DESC,
        id DESC
      LIMIT 200'
);

$statement->execute(
    $parameters
);

$projects =
    $statement->fetchAll();

/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/

$categories = $pdo->query(
    "SELECT DISTINCT category
     FROM projects
     WHERE category IS NOT NULL
       AND category <> ''
     ORDER BY category ASC"
)->fetchAll(
    PDO::FETCH_COLUMN
);

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$totalProjects = (int) $pdo
    ->query(
        'SELECT COUNT(*)
         FROM projects'
    )
    ->fetchColumn();

$activeProjects = (int) $pdo
    ->query(
        'SELECT COUNT(*)
         FROM projects
         WHERE is_active = 1'
    )
    ->fetchColumn();

$featuredProjects = (int) $pdo
    ->query(
        'SELECT COUNT(*)
         FROM projects
         WHERE is_featured = 1'
    )
    ->fetchColumn();

$totalViews = (int) $pdo
    ->query(
        'SELECT COALESCE(SUM(views), 0)
         FROM projects'
    )
    ->fetchColumn();

$success = flash('success');
$error = flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="robots"
        content="noindex,nofollow"
    >

    <title>
        Portfolio Projects | Raj Admin
    </title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css"
        rel="stylesheet"
    >
</head>

<body
    class="bg-[#050505] text-white
           min-h-screen font-sans antialiased"
>
    <?php require __DIR__ . '/admin_sidebar.php'; ?>

    <main
        class="lg:ml-[270px]
               p-4 sm:p-6 lg:p-8 min-h-screen"
    >
        <!-- Page Header -->
        <div
            class="flex flex-col sm:flex-row
                   sm:items-center justify-between
                   gap-4 mb-8"
        >
            <div>
                <p
                    class="text-xs font-bold tracking-[.2em]
                           text-yellow-400 uppercase mb-2"
                >
                    Portfolio Manager
                </p>

                <h1 class="text-3xl font-black">
                    Portfolio Projects
                </h1>

                <p class="text-gray-500 mt-2">
                    Manage all developed projects and their media.
                </p>
            </div>

            <a
                href="add_project.php"
                class="inline-flex items-center justify-center
                       gap-2 bg-yellow-400 text-black
                       px-5 py-3 rounded-xl font-bold
                       hover:bg-yellow-300 transition"
            >
                <i class="ri-add-line text-lg"></i>
                Add Project
            </a>
        </div>

        <!-- Success Message -->
        <?php if ($success): ?>
            <div
                class="mb-6 p-4 rounded-xl
                       bg-green-500/10
                       border border-green-500/20
                       text-green-300"
            >
                <i class="ri-checkbox-circle-line mr-2"></i>

                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if ($error): ?>
            <div
                class="mb-6 p-4 rounded-xl
                       bg-red-500/10
                       border border-red-500/20
                       text-red-300"
            >
                <i class="ri-error-warning-line mr-2"></i>

                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <section
            class="grid grid-cols-2
                   xl:grid-cols-4
                   gap-3 sm:gap-4 mb-7"
        >
            <div
                class="bg-[#111]
                       border border-white/5
                       rounded-2xl p-4 sm:p-5"
            >
                <p class="text-xs text-gray-500">
                    Total
                </p>

                <p class="text-3xl font-black mt-2">
                    <?= $totalProjects ?>
                </p>
            </div>

            <div
                class="bg-[#111]
                       border border-white/5
                       rounded-2xl p-4 sm:p-5"
            >
                <p class="text-xs text-gray-500">
                    Published
                </p>

                <p
                    class="text-3xl font-black
                           mt-2 text-green-400"
                >
                    <?= $activeProjects ?>
                </p>
            </div>

            <div
                class="bg-[#111]
                       border border-white/5
                       rounded-2xl p-4 sm:p-5"
            >
                <p class="text-xs text-gray-500">
                    Featured
                </p>

                <p
                    class="text-3xl font-black
                           mt-2 text-yellow-400"
                >
                    <?= $featuredProjects ?>
                </p>
            </div>

            <div
                class="bg-[#111]
                       border border-white/5
                       rounded-2xl p-4 sm:p-5"
            >
                <p class="text-xs text-gray-500">
                    Total Views
                </p>

                <p
                    class="text-3xl font-black
                           mt-2 text-blue-400"
                >
                    <?= number_format($totalViews) ?>
                </p>
            </div>
        </section>

        <!-- Search Filters -->
        <form
            method="GET"
            class="grid grid-cols-1 md:grid-cols-4
                   gap-3 bg-[#111]
                   border border-white/5
                   rounded-2xl p-3 mb-7"
        >
            <input
                type="search"
                name="search"
                value="<?= e($search) ?>"
                placeholder="Search project..."
                class="bg-black
                       border border-white/10
                       rounded-xl p-3 outline-none
                       focus:border-yellow-500"
            >

            <select
                name="category"
                class="bg-black
                       border border-white/10
                       rounded-xl p-3 outline-none
                       focus:border-yellow-500"
            >
                <option value="all">
                    All Categories
                </option>

                <?php foreach ($categories as $category): ?>
                    <option
                        value="<?= e($category) ?>"
                        <?= $categoryFilter === $category
                            ? 'selected'
                            : ''
                        ?>
                    >
                        <?= e($category) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select
                name="status"
                class="bg-black
                       border border-white/10
                       rounded-xl p-3 outline-none
                       focus:border-yellow-500"
            >
                <option value="all">
                    All Status
                </option>

                <option
                    value="active"
                    <?= $statusFilter === 'active'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Published
                </option>

                <option
                    value="hidden"
                    <?= $statusFilter === 'hidden'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Hidden
                </option>

                <option
                    value="featured"
                    <?= $statusFilter === 'featured'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Featured
                </option>
            </select>

            <button
                type="submit"
                class="bg-yellow-400 text-black
                       font-bold rounded-xl p-3
                       hover:bg-yellow-300 transition"
            >
                <i class="ri-filter-3-line mr-1"></i>
                Filter
            </button>
        </form>

        <!-- Projects Grid -->
        <section
            class="grid grid-cols-1
                   md:grid-cols-2
                   xl:grid-cols-3 gap-6"
        >
            <?php if ($projects === []): ?>
                <div
                    class="md:col-span-2
                           xl:col-span-3
                           bg-[#111]
                           border border-dashed
                           border-white/10
                           rounded-2xl p-12
                           text-center text-gray-500"
                >
                    <i
                        class="ri-folder-open-line text-5xl"
                    ></i>

                    <p class="mt-3">
                        No projects found.
                    </p>
                </div>
            <?php endif; ?>

            <?php foreach ($projects as $project): ?>
                <?php
                /*
                |--------------------------------------------------------------------------
                | Load Current Gallery Media
                |--------------------------------------------------------------------------
                */

                $adminMediaItems =
                    project_gallery_items(
                        $project['gallery'] ?? '',
                        $project['thumbnail'] ?? '',
                        $project['video_preview'] ?? ''
                    );

                $adminPreview =
                    $adminMediaItems[0] ?? null;

                $previewType =
                    is_array($adminPreview)
                        ? (string) (
                            $adminPreview['type'] ?? ''
                        )
                        : '';

                $previewReference =
                    is_array($adminPreview)
                        ? project_media_reference(
                            $adminPreview['url'] ?? ''
                        )
                        : '';

                $previewUrl =
                    $previewReference !== ''
                        ? project_admin_media_url(
                            $previewReference
                        )
                        : '';

                /*
                |--------------------------------------------------------------------------
                | Prevent Old Browser Cache
                |--------------------------------------------------------------------------
                */

                if (
                    $previewUrl !== ''
                    && !preg_match(
                        '#^https?://#i',
                        $previewReference
                    )
                ) {
                    $version = strtotime(
                        (string) (
                            $project['updated_at']
                            ?? ''
                        )
                    );

                    $previewUrl .=
                        '?v=' .
                        (
                            $version !== false
                                ? $version
                                : time()
                        );
                }

                $description = (string) (
                    $project['short_desc']
                    ?? ''
                );

                $description =
                    mb_strlen($description) > 130
                        ? mb_substr(
                            $description,
                            0,
                            129
                        ) . '…'
                        : $description;
                ?>

                <article
                    class="bg-[#111]
                           border border-white/10
                           rounded-3xl overflow-hidden
                           flex flex-col
                           hover:border-yellow-500/30
                           transition"
                >
                    <!-- Current Media Preview -->
                    <div
                        class="h-52 bg-black
                               relative overflow-hidden"
                    >
                        <?php if (
                            $previewType === 'image'
                            && $previewUrl !== ''
                        ): ?>
                            <img
                                src="<?= e($previewUrl) ?>"
                                alt="<?= e($project['title']) ?>"
                                loading="lazy"
                                class="w-full h-full object-cover"
                            >

                        <?php elseif (
                            $previewType === 'video'
                            && $previewUrl !== ''
                        ): ?>
                            <video
                                src="<?= e($previewUrl) ?>"
                                muted
                                playsinline
                                preload="metadata"
                                class="w-full h-full
                                       object-cover bg-black"
                            ></video>

                            <span
                                class="absolute bottom-3 right-3
                                       px-3 py-1 rounded-full
                                       bg-black/80
                                       text-white text-xs"
                            >
                                <i class="ri-play-circle-line"></i>
                                Video
                            </span>

                        <?php elseif (
                            $previewType === 'youtube'
                        ): ?>
                            <div
                                class="w-full h-full
                                       flex flex-col
                                       items-center justify-center
                                       bg-gradient-to-br
                                       from-red-950/40 to-black"
                            >
                                <i
                                    class="ri-youtube-fill
                                           text-6xl text-red-500"
                                ></i>

                                <span
                                    class="text-sm
                                           text-gray-400 mt-2"
                                >
                                    YouTube Video
                                </span>
                            </div>

                        <?php else: ?>
                            <div
                                class="w-full h-full
                                       flex items-center
                                       justify-center"
                            >
                                <i
                                    class="ri-image-line
                                           text-6xl text-gray-700"
                                ></i>
                            </div>
                        <?php endif; ?>

                        <!-- Status Labels -->
                        <div
                            class="absolute top-3 left-3
                                   flex flex-wrap gap-2"
                        >
                            <?php if (
                                (int) $project['is_featured'] === 1
                            ): ?>
                                <span
                                    class="px-3 py-1
                                           rounded-full
                                           bg-yellow-400
                                           text-black
                                           text-xs font-bold"
                                >
                                    FEATURED
                                </span>
                            <?php endif; ?>

                            <?php if (
                                (int) $project['is_active'] === 1
                            ): ?>
                                <span
                                    class="px-3 py-1
                                           rounded-full
                                           bg-green-500/90
                                           text-white
                                           text-xs font-bold"
                                >
                                    PUBLISHED
                                </span>
                            <?php else: ?>
                                <span
                                    class="px-3 py-1
                                           rounded-full
                                           bg-gray-700
                                           text-gray-300
                                           text-xs font-bold"
                                >
                                    HIDDEN
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Project Information -->
                    <div class="p-5 flex flex-col flex-1">
                        <span
                            class="text-yellow-400 text-xs
                                   font-bold uppercase
                                   tracking-wider"
                        >
                            <?= e(
                                $project['category']
                                ?: 'Project'
                            ) ?>
                        </span>

                        <h2 class="text-xl font-bold mt-2">
                            <?= e($project['title']) ?>
                        </h2>

                        <p
                            class="text-gray-500 text-sm
                                   leading-6 mt-3"
                        >
                            <?= e($description) ?>
                        </p>

                        <div
                            class="flex flex-wrap
                                   items-center gap-4
                                   text-xs text-gray-600 mt-4"
                        >
                            <span>
                                <i class="ri-eye-line"></i>

                                <?= number_format(
                                    (int) $project['views']
                                ) ?>
                            </span>

                            <span>
                                <i class="ri-heart-line"></i>

                                <?= number_format(
                                    (int) $project['likes']
                                ) ?>
                            </span>

                            <span>
                                Priority:
                                <?= (int) $project['sort_order'] ?>
                            </span>
                        </div>

                        <!-- Edit and Preview -->
                        <div
                            class="grid grid-cols-2
                                   gap-2 mt-auto pt-6"
                        >
                            <a
                                href="edit_project.php?id=<?= (int) $project['id'] ?>"
                                class="py-2.5 text-center
                                       rounded-lg
                                       bg-blue-500/10
                                       text-blue-400
                                       hover:bg-blue-500/20"
                            >
                                <i class="ri-edit-line"></i>
                                Edit
                            </a>

                            <a
                                href="../../index.php?page=project-details&id=<?= (int) $project['id'] ?>"
                                target="_blank"
                                rel="noopener"
                                class="py-2.5 text-center
                                       rounded-lg bg-white/5
                                       text-gray-300
                                       hover:bg-white/10"
                            >
                                <i class="ri-eye-line"></i>
                                Preview
                            </a>
                        </div>

                        <!-- Visibility Controls -->
                        <div
                            class="grid grid-cols-2
                                   gap-2 mt-2"
                        >
                            <form method="POST">
                                <?= csrf_field() ?>

                                <input
                                    type="hidden"
                                    name="action"
                                    value="toggle_active"
                                >

                                <input
                                    type="hidden"
                                    name="project_id"
                                    value="<?= (int) $project['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="w-full py-2.5
                                           rounded-lg
                                           bg-green-500/10
                                           text-green-400
                                           hover:bg-green-500/20"
                                >
                                    <?= (int) $project['is_active'] === 1
                                        ? 'Hide'
                                        : 'Publish'
                                    ?>
                                </button>
                            </form>

                            <form method="POST">
                                <?= csrf_field() ?>

                                <input
                                    type="hidden"
                                    name="action"
                                    value="toggle_featured"
                                >

                                <input
                                    type="hidden"
                                    name="project_id"
                                    value="<?= (int) $project['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="w-full py-2.5
                                           rounded-lg
                                           bg-yellow-500/10
                                           text-yellow-400
                                           hover:bg-yellow-500/20"
                                >
                                    <?= (int) $project['is_featured'] === 1
                                        ? 'Unfeature'
                                        : 'Feature'
                                    ?>
                                </button>
                            </form>
                        </div>

                        <!-- Delete -->
                        <form
                            action="delete_project.php"
                            method="POST"
                            class="mt-2"
                            onsubmit="return confirm('Delete this portfolio project permanently?');"
                        >
                            <?= csrf_field() ?>

                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $project['id'] ?>"
                            >

                            <button
                                type="submit"
                                class="w-full py-2.5
                                       rounded-lg
                                       bg-red-500/10
                                       text-red-400
                                       hover:bg-red-500/20"
                            >
                                <i class="ri-delete-bin-line"></i>
                                Delete
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
</body>
</html>