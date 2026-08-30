<?php
declare(strict_types=1);

require_once '../../config/db.php';
require_once 'project_helpers.php';

require_admin();

/*
|--------------------------------------------------------------------------
| Quick project controls
|--------------------------------------------------------------------------
*/

if (request_is_post()) {
    verify_csrf();

    $action = (string) ($_POST['action'] ?? '');

    $projectId = filter_input(
        INPUT_POST,
        'project_id',
        FILTER_VALIDATE_INT
    );

    if (!$projectId || $projectId < 1) {
        flash('error', 'Invalid project ID.');

        redirect('projects.php');
    }

    if ($action === 'toggle_active') {
        $statement = $pdo->prepare(
            'UPDATE projects
             SET is_active =
                IF(is_active = 1, 0, 1)
             WHERE id = ?'
        );

        $statement->execute([$projectId]);

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

        $statement->execute([$projectId]);

        flash(
            'success',
            'Featured status updated.'
        );

        redirect('projects.php');
    }

    flash('error', 'Invalid project action.');

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
        ['all', 'active', 'hidden', 'featured'],
        true
    )
) {
    $statusFilter = 'all';
}

$where = [];
$parameters = [];

if ($search !== '') {
    $where[] = '(
        title LIKE ?
        OR category LIKE ?
        OR client_name LIKE ?
        OR short_desc LIKE ?
    )';

    $searchValue = '%' . $search . '%';

    $parameters[] = $searchValue;
    $parameters[] = $searchValue;
    $parameters[] = $searchValue;
    $parameters[] = $searchValue;
}

if ($categoryFilter !== 'all') {
    $where[] = 'category = ?';
    $parameters[] = $categoryFilter;
}

if ($statusFilter === 'active') {
    $where[] = 'is_active = 1';
}

if ($statusFilter === 'hidden') {
    $where[] = 'is_active = 0';
}

if ($statusFilter === 'featured') {
    $where[] = 'is_featured = 1';
}

$whereSql = $where !== []
    ? ' WHERE ' . implode(' AND ', $where)
    : '';

$statement = $pdo->prepare(
    'SELECT
        id,
        title,
        category,
        client_name,
        short_desc,
        thumbnail,
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

$statement->execute($parameters);

$projects = $statement->fetchAll();

$categories = $pdo->query(
    "SELECT DISTINCT category
     FROM projects
     WHERE category IS NOT NULL
       AND category <> ''
     ORDER BY category ASC"
)->fetchAll(PDO::FETCH_COLUMN);

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$totalProjects = (int) $pdo->query(
    'SELECT COUNT(*) FROM projects'
)->fetchColumn();

$activeProjects = (int) $pdo->query(
    'SELECT COUNT(*)
     FROM projects
     WHERE is_active = 1'
)->fetchColumn();

$featuredProjects = (int) $pdo->query(
    'SELECT COUNT(*)
     FROM projects
     WHERE is_featured = 1'
)->fetchColumn();

$totalViews = (int) $pdo->query(
    'SELECT COALESCE(SUM(views), 0)
     FROM projects'
)->fetchColumn();

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

    <title>Portfolio Projects | Raj Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css"
        rel="stylesheet"
    >
</head>

<body class="bg-[#050505] text-white min-h-screen font-sans">


<?php require __DIR__ . '/admin_sidebar.php'; ?>

<header class="lg:hidden sticky top-0 z-40 bg-[#111] border-b border-white/10 px-5 py-4 flex justify-between">

    <span class="font-bold text-yellow-500">
        RAJ ADMIN
    </span>

    <a
        href="index.php"
        class="text-gray-400"
    >
        Dashboard
    </a>

</header>

<aside class="hidden lg:flex w-64 h-screen bg-[#111] border-r border-white/5 flex-col fixed left-0 top-0">

    <div class="p-6 text-2xl font-bold text-yellow-500">
        RAJ ADMIN.
    </div>

    <nav class="flex-1 px-4 space-y-2">

        <a
            href="index.php"
            class="flex items-center gap-3 p-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"
        >
            <i class="ri-dashboard-line"></i>
            Dashboard
        </a>

        <a
            href="projects.php"
            class="flex items-center gap-3 p-3 bg-yellow-500/10 text-yellow-500 rounded-lg"
        >
            <i class="ri-gallery-line"></i>
            Portfolio Projects
        </a>

        <a
            href="add_project.php"
            class="flex items-center gap-3 p-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"
        >
            <i class="ri-add-circle-line"></i>
            Add Project
        </a>

        <a
            href="add_service.php"
            class="flex items-center gap-3 p-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"
        >
            <i class="ri-shopping-bag-line"></i>
            Add Product
        </a>

        <a
            href="orders.php"
            class="flex items-center gap-3 p-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"
        >
            <i class="ri-file-list-3-line"></i>
            Orders
        </a>

        <a
            href="messages.php"
            class="flex items-center gap-3 p-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"
        >
            <i class="ri-mail-line"></i>
            Messages
        </a>

        <a
            href="../../index.php?page=portfolio"
            target="_blank"
            rel="noopener"
            class="flex items-center gap-3 p-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"
        >
            <i class="ri-external-link-line"></i>
            View Portfolio
        </a>

    </nav>

    <div class="p-4 border-t border-white/5">

        <form
            action="logout.php"
            method="POST"
        >
            <?= csrf_field() ?>

            <button
                type="submit"
                class="w-full p-3 text-left text-red-400 hover:bg-red-500/10 rounded-lg"
            >
                <i class="ri-logout-box-line mr-2"></i>
                Logout
            </button>

        </form>

    </div>

</aside>

<main class="lg:ml-64 p-5 md:p-8">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">

        <div>
            <h1 class="text-3xl font-bold">
                Portfolio Projects
            </h1>

            <p class="text-gray-500 mt-1">
                Manage all developed projects.
            </p>
        </div>

        <a
            href="add_project.php"
            class="px-6 py-3 bg-yellow-500 text-black font-bold rounded-xl hover:bg-yellow-400 text-center"
        >
            <i class="ri-add-line mr-1"></i>
            Add Project
        </a>

    </div>

    <?php if ($success): ?>

        <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-300">
            <?= e($success) ?>
        </div>

    <?php endif; ?>

    <?php if ($error): ?>

        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-300">
            <?= e($error) ?>
        </div>

    <?php endif; ?>

    <section class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-8">

        <div class="bg-[#111] border border-white/5 rounded-2xl p-5">
            <p class="text-gray-500 text-sm">Total</p>
            <p class="text-3xl font-bold mt-2"><?= $totalProjects ?></p>
        </div>

        <div class="bg-[#111] border border-white/5 rounded-2xl p-5">
            <p class="text-gray-500 text-sm">Published</p>
            <p class="text-3xl font-bold text-green-400 mt-2"><?= $activeProjects ?></p>
        </div>

        <div class="bg-[#111] border border-white/5 rounded-2xl p-5">
            <p class="text-gray-500 text-sm">Featured</p>
            <p class="text-3xl font-bold text-yellow-400 mt-2"><?= $featuredProjects ?></p>
        </div>

        <div class="bg-[#111] border border-white/5 rounded-2xl p-5">
            <p class="text-gray-500 text-sm">Total Views</p>
            <p class="text-3xl font-bold text-blue-400 mt-2"><?= number_format($totalViews) ?></p>
        </div>

    </section>

    <form
        method="GET"
        action="projects.php"
        class="bg-[#111] border border-white/5 rounded-2xl p-4 mb-7 grid grid-cols-1 md:grid-cols-4 gap-3"
    >

        <input
            type="search"
            name="search"
            maxlength="100"
            value="<?= e($search) ?>"
            placeholder="Search project..."
            class="bg-black border border-white/10 rounded-xl p-3 outline-none focus:border-yellow-500"
        >

        <select
            name="category"
            class="bg-black border border-white/10 rounded-xl p-3 outline-none focus:border-yellow-500"
        >
            <option value="all">All Categories</option>

            <?php foreach ($categories as $category): ?>

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

        <select
            name="status"
            class="bg-black border border-white/10 rounded-xl p-3 outline-none focus:border-yellow-500"
        >
            <option value="all">All Status</option>
            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Published</option>
            <option value="hidden" <?= $statusFilter === 'hidden' ? 'selected' : '' ?>>Hidden</option>
            <option value="featured" <?= $statusFilter === 'featured' ? 'selected' : '' ?>>Featured</option>
        </select>

        <button
            type="submit"
            class="bg-yellow-500 text-black font-bold rounded-xl p-3 hover:bg-yellow-400"
        >
            Filter
        </button>

    </form>

    <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

        <?php if ($projects === []): ?>

            <div class="md:col-span-2 xl:col-span-3 bg-[#111] border border-white/5 rounded-2xl p-12 text-center text-gray-500">
                No projects found.
            </div>

        <?php endif; ?>

        <?php foreach ($projects as $project): ?>

            <?php
            $thumbnail = project_media_reference(
                $project['thumbnail'] ?? ''
            );

            $thumbnailUrl = $thumbnail !== ''
                ? project_admin_media_url($thumbnail)
                : '';
            ?>

            <article class="bg-[#111] border border-white/10 rounded-3xl overflow-hidden flex flex-col">

                <div class="h-52 bg-black relative">

                    <?php if ($thumbnailUrl !== ''): ?>

                        <img
                            src="<?= e($thumbnailUrl) ?>"
                            alt="<?= e($project['title']) ?>"
                            loading="lazy"
                            class="w-full h-full object-cover"
                        >

                    <?php else: ?>

                        <div class="w-full h-full flex items-center justify-center">
                            <i class="ri-image-line text-6xl text-gray-700"></i>
                        </div>

                    <?php endif; ?>

                    <div class="absolute top-3 left-3 flex gap-2">

                        <?php if ((int) $project['is_featured'] === 1): ?>

                            <span class="px-3 py-1 rounded-full bg-yellow-500 text-black text-xs font-bold">
                                FEATURED
                            </span>

                        <?php endif; ?>

                        <?php if ((int) $project['is_active'] === 1): ?>

                            <span class="px-3 py-1 rounded-full bg-green-500/90 text-white text-xs font-bold">
                                PUBLISHED
                            </span>

                        <?php else: ?>

                            <span class="px-3 py-1 rounded-full bg-gray-700 text-gray-300 text-xs font-bold">
                                HIDDEN
                            </span>

                        <?php endif; ?>

                    </div>

                </div>

                <div class="p-5 flex flex-col flex-1">

                    <span class="text-yellow-500 text-xs font-bold uppercase tracking-wider">
                        <?= e($project['category']) ?>
                    </span>

                    <h2 class="text-xl font-bold mt-2">
                        <?= e($project['title']) ?>
                    </h2>

                    <p class="text-gray-500 text-sm leading-6 mt-3">
                        <?= e(
                            mb_strlen((string) $project['short_desc']) > 130
                                ? mb_substr((string) $project['short_desc'], 0, 129) . '…'
                                : $project['short_desc']
                        ) ?>
                    </p>

                    <div class="flex items-center gap-4 text-xs text-gray-600 mt-4">
                        <span><i class="ri-eye-line"></i> <?= number_format((int) $project['views']) ?></span>
                        <span><i class="ri-heart-line"></i> <?= number_format((int) $project['likes']) ?></span>
                        <span>Priority: <?= (int) $project['sort_order'] ?></span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mt-6">

                        <a
                            href="edit_project.php?id=<?= (int) $project['id'] ?>"
                            class="py-2.5 text-center rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20"
                        >
                            <i class="ri-edit-line"></i>
                            Edit
                        </a>

                        <a
                            href="../../index.php?page=project-details&id=<?= (int) $project['id'] ?>"
                            target="_blank"
                            rel="noopener"
                            class="py-2.5 text-center rounded-lg bg-white/5 text-gray-300 hover:bg-white/10"
                        >
                            <i class="ri-eye-line"></i>
                            Preview
                        </a>

                    </div>

                    <div class="grid grid-cols-2 gap-2 mt-2">

                        <form method="POST">
                            <?= csrf_field() ?>

                            <input type="hidden" name="action" value="toggle_active">
                            <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">

                            <button
                                type="submit"
                                class="w-full py-2.5 rounded-lg bg-green-500/10 text-green-400 hover:bg-green-500/20"
                            >
                                <?= (int) $project['is_active'] === 1
                                    ? 'Hide'
                                    : 'Publish' ?>
                            </button>
                        </form>

                        <form method="POST">
                            <?= csrf_field() ?>

                            <input type="hidden" name="action" value="toggle_featured">
                            <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">

                            <button
                                type="submit"
                                class="w-full py-2.5 rounded-lg bg-yellow-500/10 text-yellow-400 hover:bg-yellow-500/20"
                            >
                                <?= (int) $project['is_featured'] === 1
                                    ? 'Unfeature'
                                    : 'Feature' ?>
                            </button>
                        </form>

                    </div>

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
                            class="w-full py-2.5 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20"
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