<?php
declare(strict_types=1);

require_once '../../config/db.php';
require_admin();

function dashboard_media_url(mixed $value): string
{
    $path = trim((string) $value);

    if ($path === '') {
        return '';
    }

    $remoteUrl = valid_http_url($path);

    if ($remoteUrl !== '') {
        return $remoteUrl;
    }

    $path = str_replace('\\', '/', ltrim($path, '/'));

    if ($path === '' || str_contains($path, '..')) {
        return '';
    }

    return '../../' . $path;
}

function dashboard_project_image(array $project): string
{
    $thumbnail = dashboard_media_url($project['thumbnail'] ?? '');

    if ($thumbnail !== '') {
        return $thumbnail;
    }

    $gallery = json_array($project['gallery'] ?? '');

    foreach ($gallery as $media) {
        if (is_string($media)) {
            $candidate = dashboard_media_url($media);

            if ($candidate !== '') {
                return $candidate;
            }

            continue;
        }

        if (!is_array($media)) {
            continue;
        }

        $type = strtolower((string) ($media['type'] ?? 'image'));

        if ($type !== 'image') {
            continue;
        }

        $candidate = dashboard_media_url($media['url'] ?? '');

        if ($candidate !== '') {
            return $candidate;
        }
    }

    return '';
}

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$totalProjects = (int) $pdo
    ->query('SELECT COUNT(*) FROM projects')
    ->fetchColumn();

$activeProjects = (int) $pdo
    ->query('SELECT COUNT(*) FROM projects WHERE is_active = 1')
    ->fetchColumn();

$featuredProjects = (int) $pdo
    ->query('SELECT COUNT(*) FROM projects WHERE is_featured = 1')
    ->fetchColumn();

$projectViews = (int) $pdo
    ->query('SELECT COALESCE(SUM(views), 0) FROM projects')
    ->fetchColumn();

$totalServices = (int) $pdo
    ->query('SELECT COUNT(*) FROM services')
    ->fetchColumn();

$activeServices = (int) $pdo
    ->query('SELECT COUNT(*) FROM services WHERE is_active = 1')
    ->fetchColumn();

$totalOrders = (int) $pdo
    ->query('SELECT COUNT(*) FROM orders')
    ->fetchColumn();

$pendingOrders = (int) $pdo
    ->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")
    ->fetchColumn();

$totalMessages = (int) $pdo
    ->query('SELECT COUNT(*) FROM messages')
    ->fetchColumn();

$newMessages = (int) $pdo
    ->query("SELECT COUNT(*) FROM messages WHERE status = 'new'")
    ->fetchColumn();

/*
|--------------------------------------------------------------------------
| Recent Portfolio Projects
|--------------------------------------------------------------------------
*/

$projects = $pdo->query(
    'SELECT
        id,
        title,
        category,
        thumbnail,
        gallery,
        is_active,
        is_featured,
        views,
        updated_at
     FROM projects
     ORDER BY sort_order ASC, id DESC
     LIMIT 6'
)->fetchAll();

/*
|--------------------------------------------------------------------------
| Products
|--------------------------------------------------------------------------
*/

$services = $pdo->query(
    'SELECT
        id,
        title,
        price_basic,
        file_type,
        thumbnail,
        is_active,
        updated_at
     FROM services
     ORDER BY id DESC'
)->fetchAll();

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

    <meta name="robots" content="noindex,nofollow">

    <title>Admin Dashboard | Raj Agency</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css"
        rel="stylesheet"
    >

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            background:
                radial-gradient(
                    circle at 75% 0%,
                    rgba(234, 179, 8, 0.07),
                    transparent 30%
                ),
                #050505;
        }

        .admin-panel {
            background: linear-gradient(
                145deg,
                rgba(24, 24, 27, 0.96),
                rgba(13, 13, 15, 0.96)
            );
        }

        .admin-glow {
            transition:
                border-color 0.25s ease,
                box-shadow 0.25s ease,
                transform 0.25s ease;
        }

        .admin-glow:hover {
            border-color: rgba(234, 179, 8, 0.35);
            box-shadow: 0 16px 50px rgba(234, 179, 8, 0.08);
            transform: translateY(-2px);
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 20px;
        }
    </style>
</head>

<body class="text-white min-h-screen font-sans antialiased">
    <?php require __DIR__ . '/admin_sidebar.php'; ?>
<?php require __DIR__ . '/admin_sidebar.php'; ?>
    <!-- Mobile Header -->
    <header
        class="lg:hidden sticky top-0 z-40 bg-[#0d0d0f]/95
               backdrop-blur-xl border-b border-white/10
               px-4 py-3 flex items-center justify-between"
    >
        <a
            href="index.php"
            class="font-black tracking-tight text-yellow-400"
        >
            RAJ ADMIN.
        </a>

        <div class="flex items-center gap-2">
            <a
                href="add_project.php"
                class="w-10 h-10 rounded-xl bg-yellow-400 text-black
                       flex items-center justify-center"
                aria-label="Add project"
            >
                <i class="ri-add-line text-xl"></i>
            </a>

            <a
                href="../../index.php"
                target="_blank"
                rel="noopener"
                class="w-10 h-10 rounded-xl border border-white/10
                       text-gray-300 flex items-center justify-center"
                aria-label="View website"
            >
                <i class="ri-external-link-line"></i>
            </a>
        </div>
    </header>

    <!-- Desktop Sidebar -->
    <aside
        class="hidden lg:flex w-64 h-screen bg-[#0d0d0f]
               border-r border-white/5 flex-col
               fixed left-0 top-0 z-50"
    >
        <div
            class="px-6 py-7 text-2xl font-black
                   tracking-tight text-yellow-400"
        >
            RAJ ADMIN.
        </div>

        <nav class="flex-1 px-4 space-y-1.5 overflow-y-auto pb-5">

            <p
                class="px-3 pt-2 pb-1 text-[10px] font-bold
                       tracking-[.18em] text-gray-600 uppercase"
            >
                Overview
            </p>

            <a
                href="index.php"
                class="flex items-center gap-3 px-3 py-3
                       bg-yellow-400 text-black font-semibold rounded-xl"
            >
                <i class="ri-dashboard-line text-lg"></i>
                Dashboard
            </a>

            <p
                class="px-3 pt-5 pb-1 text-[10px] font-bold
                       tracking-[.18em] text-gray-600 uppercase"
            >
                Portfolio
            </p>

            <a
                href="projects.php"
                class="flex items-center justify-between gap-3 px-3 py-3
                       text-gray-400 hover:text-white hover:bg-white/5
                       rounded-xl transition"
            >
                <span class="flex items-center gap-3">
                    <i class="ri-gallery-line text-lg"></i>
                    Portfolio Projects
                </span>

                <span
                    class="min-w-6 h-6 px-1.5 rounded-full
                           bg-violet-500/15 text-violet-300 text-xs
                           flex items-center justify-center"
                >
                    <?= $totalProjects ?>
                </span>
            </a>

            <a
                href="add_project.php"
                class="flex items-center gap-3 px-3 py-3
                       text-gray-400 hover:text-white hover:bg-white/5
                       rounded-xl transition"
            >
                <i class="ri-image-add-line text-lg"></i>
                Add Project
            </a>

            <p
                class="px-3 pt-5 pb-1 text-[10px] font-bold
                       tracking-[.18em] text-gray-600 uppercase"
            >
                Shop
            </p>

            <a
                href="#products"
                class="flex items-center justify-between gap-3 px-3 py-3
                       text-gray-400 hover:text-white hover:bg-white/5
                       rounded-xl transition"
            >
                <span class="flex items-center gap-3">
                    <i class="ri-shopping-bag-3-line text-lg"></i>
                    Products
                </span>

                <span
                    class="min-w-6 h-6 px-1.5 rounded-full
                           bg-yellow-500/15 text-yellow-300 text-xs
                           flex items-center justify-center"
                >
                    <?= $totalServices ?>
                </span>
            </a>

            <a
                href="add_service.php"
                class="flex items-center gap-3 px-3 py-3
                       text-gray-400 hover:text-white hover:bg-white/5
                       rounded-xl transition"
            >
                <i class="ri-add-circle-line text-lg"></i>
                Add Product
            </a>

            <a
                href="orders.php"
                class="flex items-center justify-between gap-3 px-3 py-3
                       text-gray-400 hover:text-white hover:bg-white/5
                       rounded-xl transition"
            >
                <span class="flex items-center gap-3">
                    <i class="ri-file-list-3-line text-lg"></i>
                    Orders
                </span>

                <?php if ($pendingOrders > 0): ?>
                    <span
                        class="min-w-6 h-6 px-1.5 rounded-full
                               bg-yellow-400 text-black text-xs font-bold
                               flex items-center justify-center"
                    >
                        <?= $pendingOrders ?>
                    </span>
                <?php endif; ?>
            </a>

            <a
                href="messages.php"
                class="flex items-center justify-between gap-3 px-3 py-3
                       text-gray-400 hover:text-white hover:bg-white/5
                       rounded-xl transition"
            >
                <span class="flex items-center gap-3">
                    <i class="ri-mail-line text-lg"></i>
                    Messages
                </span>

                <?php if ($newMessages > 0): ?>
                    <span
                        class="min-w-6 h-6 px-1.5 rounded-full
                               bg-blue-500 text-white text-xs font-bold
                               flex items-center justify-center"
                    >
                        <?= $newMessages ?>
                    </span>
                <?php endif; ?>
            </a>

            <p
                class="px-3 pt-5 pb-1 text-[10px] font-bold
                       tracking-[.18em] text-gray-600 uppercase"
            >
                Website
            </p>

            <a
                href="../../index.php?page=portfolio"
                target="_blank"
                rel="noopener"
                class="flex items-center gap-3 px-3 py-3
                       text-gray-400 hover:text-white hover:bg-white/5
                       rounded-xl transition"
            >
                <i class="ri-user-star-line text-lg"></i>
                View Portfolio
            </a>

            <a
                href="../../index.php"
                target="_blank"
                rel="noopener"
                class="flex items-center gap-3 px-3 py-3
                       text-gray-400 hover:text-white hover:bg-white/5
                       rounded-xl transition"
            >
                <i class="ri-external-link-line text-lg"></i>
                View Website
            </a>
        </nav>

        <div class="p-4 border-t border-white/5">
            <p class="text-xs text-gray-500 px-3 mb-2 truncate">
                Signed in:
                <?= e($_SESSION['admin_username'] ?? 'Admin') ?>
            </p>

            <form action="logout.php" method="POST">
                <?= csrf_field() ?>

                <button
                    type="submit"
                    class="w-full px-3 py-3 text-left text-red-400
                           hover:bg-red-500/10 rounded-xl transition"
                >
                    <i class="ri-logout-box-line mr-2"></i>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 p-4 sm:p-6 lg:p-8 xl:p-10">

        <div
            class="flex flex-col xl:flex-row xl:items-center
                   justify-between gap-5 mb-8"
        >
            <div>
                <p
                    class="text-xs font-bold tracking-[.2em]
                           text-yellow-400 uppercase mb-2"
                >
                    Control Center
                </p>

                <h1 class="text-3xl sm:text-4xl font-black tracking-tight">
                    Dashboard
                </h1>

                <p class="text-gray-500 mt-2">
                    Manage portfolio projects, products, orders and messages.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a
                    href="add_project.php"
                    class="inline-flex items-center gap-2 bg-yellow-400
                           text-black px-5 py-3 rounded-xl font-bold
                           hover:bg-yellow-300 transition"
                >
                    <i class="ri-image-add-line"></i>
                    Add Project
                </a>

                <a
                    href="add_service.php"
                    class="inline-flex items-center gap-2 border
                           border-white/10 bg-white/5 px-5 py-3
                           rounded-xl font-bold hover:bg-white/10 transition"
                >
                    <i class="ri-shopping-bag-3-line"></i>
                    Add Product
                </a>

                <form
                    action="logout.php"
                    method="POST"
                    class="lg:hidden"
                >
                    <?= csrf_field() ?>

                    <button
                        type="submit"
                        class="px-4 py-3 rounded-xl border
                               border-red-500/20 text-red-400"
                        aria-label="Logout"
                    >
                        <i class="ri-logout-box-line"></i>
                    </button>
                </form>
            </div>
        </div>

        <?php if ($success): ?>
            <div
                class="mb-6 p-4 rounded-xl bg-green-500/10
                       border border-green-500/20 text-green-300
                       flex items-center gap-3"
            >
                <i class="ri-checkbox-circle-line text-xl"></i>
                <span><?= e($success) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div
                class="mb-6 p-4 rounded-xl bg-red-500/10
                       border border-red-500/20 text-red-300
                       flex items-center gap-3"
            >
                <i class="ri-error-warning-line text-xl"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Mobile Quick Menu -->
        <section
            class="lg:hidden grid grid-cols-2 sm:grid-cols-4
                   gap-3 mb-7"
        >
            <a
                href="projects.php"
                class="admin-panel border border-white/5
                       rounded-2xl p-4 text-center"
            >
                <i class="ri-gallery-line text-2xl text-violet-400"></i>
                <p class="text-xs text-gray-400 mt-2">Projects</p>
            </a>

            <a
                href="add_project.php"
                class="admin-panel border border-white/5
                       rounded-2xl p-4 text-center"
            >
                <i class="ri-image-add-line text-2xl text-yellow-400"></i>
                <p class="text-xs text-gray-400 mt-2">Add Project</p>
            </a>

            <a
                href="orders.php"
                class="admin-panel border border-white/5
                       rounded-2xl p-4 text-center"
            >
                <i class="ri-file-list-3-line text-2xl text-orange-400"></i>
                <p class="text-xs text-gray-400 mt-2">Orders</p>
            </a>

            <a
                href="messages.php"
                class="admin-panel border border-white/5
                       rounded-2xl p-4 text-center"
            >
                <i class="ri-mail-line text-2xl text-blue-400"></i>
                <p class="text-xs text-gray-400 mt-2">Messages</p>
            </a>
        </section>

        <!-- Statistics -->
        <section
            class="grid grid-cols-1 sm:grid-cols-2
                   xl:grid-cols-4 gap-4 mb-10"
        >
            <a
                href="projects.php"
                class="admin-panel admin-glow p-5 sm:p-6 rounded-2xl
                       border border-white/5 group"
            >
                <div class="flex items-start justify-between">
                    <div
                        class="w-11 h-11 rounded-xl bg-violet-500/10
                               text-violet-400 flex items-center
                               justify-center text-xl"
                    >
                        <i class="ri-gallery-line"></i>
                    </div>

                    <i
                        class="ri-arrow-right-up-line text-gray-600
                               group-hover:text-violet-400 transition"
                    ></i>
                </div>

                <p class="text-gray-400 text-sm mt-5">
                    Portfolio Projects
                </p>

                <p class="text-4xl font-black mt-1">
                    <?= $totalProjects ?>
                </p>

                <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs mt-3">
                    <span class="text-green-400">
                        <?= $activeProjects ?> published
                    </span>

                    <span class="text-yellow-400">
                        <?= $featuredProjects ?> featured
                    </span>

                    <span class="text-gray-500">
                        <?= number_format($projectViews) ?> views
                    </span>
                </div>
            </a>

            <a
                href="#products"
                class="admin-panel admin-glow p-5 sm:p-6 rounded-2xl
                       border border-white/5 group"
            >
                <div class="flex items-start justify-between">
                    <div
                        class="w-11 h-11 rounded-xl bg-yellow-500/10
                               text-yellow-400 flex items-center
                               justify-center text-xl"
                    >
                        <i class="ri-shopping-bag-3-line"></i>
                    </div>

                    <i
                        class="ri-arrow-right-down-line text-gray-600
                               group-hover:text-yellow-400 transition"
                    ></i>
                </div>

                <p class="text-gray-400 text-sm mt-5">Products</p>

                <p class="text-4xl font-black mt-1">
                    <?= $totalServices ?>
                </p>

                <p class="text-xs text-green-400 mt-3">
                    <?= $activeServices ?> available for purchase
                </p>
            </a>

            <a
                href="orders.php"
                class="admin-panel admin-glow p-5 sm:p-6 rounded-2xl
                       border border-white/5 group"
            >
                <div class="flex items-start justify-between">
                    <div
                        class="w-11 h-11 rounded-xl bg-orange-500/10
                               text-orange-400 flex items-center
                               justify-center text-xl"
                    >
                        <i class="ri-file-list-3-line"></i>
                    </div>

                    <i
                        class="ri-arrow-right-up-line text-gray-600
                               group-hover:text-orange-400 transition"
                    ></i>
                </div>

                <p class="text-gray-400 text-sm mt-5">Orders</p>

                <p class="text-4xl font-black mt-1">
                    <?= $totalOrders ?>
                </p>

                <p class="text-xs text-yellow-400 mt-3">
                    <?= $pendingOrders ?> waiting for action
                </p>
            </a>

            <a
                href="messages.php"
                class="admin-panel admin-glow p-5 sm:p-6 rounded-2xl
                       border border-white/5 group"
            >
                <div class="flex items-start justify-between">
                    <div
                        class="w-11 h-11 rounded-xl bg-blue-500/10
                               text-blue-400 flex items-center
                               justify-center text-xl"
                    >
                        <i class="ri-mail-line"></i>
                    </div>

                    <i
                        class="ri-arrow-right-up-line text-gray-600
                               group-hover:text-blue-400 transition"
                    ></i>
                </div>

                <p class="text-gray-400 text-sm mt-5">Messages</p>

                <p class="text-4xl font-black mt-1">
                    <?= $totalMessages ?>
                </p>

                <p class="text-xs text-blue-400 mt-3">
                    <?= $newMessages ?> unread messages
                </p>
            </a>
        </section>

        <!-- Recent Projects -->
        <section class="mb-10">
            <div
                class="flex flex-wrap items-end justify-between
                       gap-3 mb-5"
            >
                <div>
                    <p
                        class="text-xs uppercase tracking-[.18em]
                               text-violet-400 font-bold mb-1"
                    >
                        Portfolio
                    </p>

                    <h2 class="text-2xl font-black">
                        Recent Projects
                    </h2>
                </div>

                <div class="flex gap-2">
                    <a
                        href="projects.php"
                        class="px-4 py-2.5 rounded-xl border
                               border-white/10 text-sm text-gray-300
                               hover:bg-white/5 transition"
                    >
                        Manage all
                    </a>

                    <a
                        href="add_project.php"
                        class="px-4 py-2.5 rounded-xl bg-violet-500/15
                               text-violet-300 text-sm font-bold
                               hover:bg-violet-500/25 transition"
                    >
                        + Add project
                    </a>
                </div>
            </div>

            <div
                class="grid grid-cols-1 md:grid-cols-2
                       2xl:grid-cols-3 gap-4"
            >
                <?php if ($projects === []): ?>
                    <div
                        class="md:col-span-2 2xl:col-span-3 admin-panel
                               border border-dashed border-white/10
                               rounded-2xl p-10 text-center"
                    >
                        <div
                            class="w-14 h-14 rounded-2xl
                                   bg-violet-500/10 text-violet-400
                                   flex items-center justify-center
                                   text-2xl mx-auto mb-4"
                        >
                            <i class="ri-gallery-line"></i>
                        </div>

                        <h3 class="font-bold text-lg">
                            No portfolio project yet
                        </h3>

                        <p class="text-sm text-gray-500 mt-2 mb-5">
                            Add your first project with multiple images
                            and videos.
                        </p>

                        <a
                            href="add_project.php"
                            class="inline-flex items-center gap-2 px-5 py-3
                                   rounded-xl bg-yellow-400 text-black font-bold"
                        >
                            <i class="ri-add-line"></i>
                            Add First Project
                        </a>
                    </div>
                <?php endif; ?>

                <?php foreach ($projects as $project): ?>
                    <?php
                    $projectImage = dashboard_project_image($project);
                    ?>

                    <article
                        class="admin-panel admin-glow border
                               border-white/5 rounded-2xl p-3
                               flex gap-4 min-w-0"
                    >
                        <div
                            class="w-24 sm:w-28 aspect-square shrink-0
                                   rounded-xl overflow-hidden bg-black/50
                                   border border-white/5"
                        >
                            <?php if ($projectImage !== ''): ?>
                                <img
                                    src="<?= e($projectImage) ?>"
                                    alt="<?= e($project['title']) ?>"
                                    loading="lazy"
                                    class="w-full h-full object-cover"
                                >
                            <?php else: ?>
                                <div
                                    class="w-full h-full flex items-center
                                           justify-center text-gray-700 text-2xl"
                                >
                                    <i class="ri-image-line"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="min-w-0 flex-1 py-1 flex flex-col">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p
                                        class="text-[10px] uppercase
                                               tracking-wider text-violet-400
                                               truncate"
                                    >
                                        <?= e($project['category'] ?: 'Project') ?>
                                    </p>

                                    <h3
                                        class="font-bold mt-1 truncate"
                                        title="<?= e($project['title']) ?>"
                                    >
                                        <?= e($project['title']) ?>
                                    </h3>
                                </div>

                                <?php if ((int) $project['is_featured'] === 1): ?>
                                    <i
                                        class="ri-star-fill text-yellow-400"
                                        title="Featured"
                                    ></i>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center gap-3 text-xs mt-2">
                                <?php if ((int) $project['is_active'] === 1): ?>
                                    <span class="text-green-400">
                                        <i
                                            class="ri-checkbox-blank-circle-fill
                                                   text-[7px]"
                                        ></i>
                                        Published
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-500">
                                        <i class="ri-eye-off-line"></i>
                                        Hidden
                                    </span>
                                <?php endif; ?>

                                <span class="text-gray-500">
                                    <i class="ri-eye-line"></i>
                                    <?= number_format((int) $project['views']) ?>
                                </span>
                            </div>

                            <div class="flex items-center gap-2 mt-auto pt-3">
                                <a
                                    href="../../index.php?page=project-details&id=<?= (int) $project['id'] ?>"
                                    target="_blank"
                                    rel="noopener"
                                    class="w-9 h-9 rounded-lg bg-white/5
                                           text-gray-400 hover:text-white
                                           flex items-center justify-center"
                                    aria-label="Preview project"
                                >
                                    <i class="ri-eye-line"></i>
                                </a>

                                <a
                                    href="edit_project.php?id=<?= (int) $project['id'] ?>"
                                    class="w-9 h-9 rounded-lg bg-blue-500/10
                                           text-blue-400 hover:bg-blue-500/20
                                           flex items-center justify-center"
                                    aria-label="Edit project"
                                >
                                    <i class="ri-edit-line"></i>
                                </a>

                                <form
                                    action="delete_project.php"
                                    method="POST"
                                    class="ml-auto"
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
                                        class="w-9 h-9 rounded-lg
                                               bg-red-500/10 text-red-400
                                               hover:bg-red-500/20
                                               flex items-center justify-center"
                                        aria-label="Delete project"
                                    >
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Products -->
        <section id="products" class="scroll-mt-6">
            <div
                class="flex flex-wrap items-end justify-between
                       gap-3 mb-5"
            >
                <div>
                    <p
                        class="text-xs uppercase tracking-[.18em]
                               text-yellow-400 font-bold mb-1"
                    >
                        Purchase System
                    </p>

                    <h2 class="text-2xl font-black">
                        All Products
                    </h2>
                </div>

                <div class="flex gap-2">
                    <a
                        href="../../index.php?page=products"
                        target="_blank"
                        rel="noopener"
                        class="px-4 py-2.5 rounded-xl border
                               border-white/10 text-sm text-gray-300
                               hover:bg-white/5 transition"
                    >
                        View shop
                    </a>

                    <a
                        href="add_service.php"
                        class="px-4 py-2.5 rounded-xl bg-yellow-400
                               text-black text-sm font-bold
                               hover:bg-yellow-300 transition"
                    >
                        + Add product
                    </a>
                </div>
            </div>

            <div
                class="admin-panel rounded-2xl border
                       border-white/5 overflow-x-auto"
            >
                <table class="w-full min-w-[790px] text-left">
                    <thead
                        class="bg-white/[.04] text-gray-500
                               text-xs uppercase tracking-wider"
                    >
                        <tr>
                            <th class="p-4">ID</th>
                            <th class="p-4">Product</th>
                            <th class="p-4">Price</th>
                            <th class="p-4">Type</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/5">
                        <?php if ($services === []): ?>
                            <tr>
                                <td
                                    colspan="6"
                                    class="p-10 text-center text-gray-500"
                                >
                                    No products found.

                                    <a
                                        href="add_service.php"
                                        class="text-yellow-400"
                                    >
                                        Add a product
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($services as $service): ?>
                            <?php
                            $serviceImage = dashboard_media_url(
                                $service['thumbnail'] ?? ''
                            );
                            ?>

                            <tr class="hover:bg-white/[.025] transition">
                                <td class="p-4 text-gray-500">
                                    #<?= (int) $service['id'] ?>
                                </td>

                                <td class="p-4">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div
                                            class="w-12 h-12 shrink-0 rounded-xl
                                                   overflow-hidden bg-black/50
                                                   border border-white/5"
                                        >
                                            <?php if ($serviceImage !== ''): ?>
                                                <img
                                                    src="<?= e($serviceImage) ?>"
                                                    alt=""
                                                    loading="lazy"
                                                    class="w-full h-full object-cover"
                                                >
                                            <?php else: ?>
                                                <div
                                                    class="w-full h-full
                                                           flex items-center
                                                           justify-center
                                                           text-gray-700"
                                                >
                                                    <i class="ri-image-line"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <span
                                            class="font-bold max-w-xs truncate"
                                        >
                                            <?= e($service['title']) ?>
                                        </span>
                                    </div>
                                </td>

                                <td class="p-4 text-yellow-400 font-semibold">
                                    $<?= number_format(
                                        (float) $service['price_basic'],
                                        2
                                    ) ?>
                                </td>

                                <td class="p-4">
                                    <span
                                        class="text-[10px] uppercase
                                               tracking-wider bg-white/5
                                               px-2.5 py-1.5 rounded-lg
                                               text-gray-400"
                                    >
                                        <?= e($service['file_type']) ?>
                                    </span>
                                </td>

                                <td class="p-4">
                                    <?php if ((int) $service['is_active'] === 1): ?>
                                        <span
                                            class="px-2.5 py-1.5 rounded-full
                                                   text-xs bg-green-500/10
                                                   text-green-400"
                                        >
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="px-2.5 py-1.5 rounded-full
                                                   text-xs bg-gray-500/10
                                                   text-gray-400"
                                        >
                                            Hidden
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="p-4">
                                    <div
                                        class="flex justify-end
                                               items-center gap-2"
                                    >
                                        <a
                                            href="../../index.php?page=service-details&id=<?= (int) $service['id'] ?>"
                                            target="_blank"
                                            rel="noopener"
                                            class="w-9 h-9 rounded-lg
                                                   bg-white/5 text-gray-400
                                                   hover:text-white
                                                   flex items-center
                                                   justify-center"
                                            aria-label="Preview product"
                                        >
                                            <i class="ri-eye-line"></i>
                                        </a>

                                        <a
                                            href="edit_service.php?id=<?= (int) $service['id'] ?>"
                                            class="w-9 h-9 rounded-lg
                                                   bg-blue-500/10 text-blue-400
                                                   hover:bg-blue-500/20
                                                   flex items-center
                                                   justify-center"
                                            aria-label="Edit product"
                                        >
                                            <i class="ri-edit-line"></i>
                                        </a>

                                        <form
                                            action="delete_service.php"
                                            method="POST"
                                            onsubmit="return confirm('Delete this product permanently?');"
                                        >
                                            <?= csrf_field() ?>

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $service['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="w-9 h-9 rounded-lg
                                                       bg-red-500/10
                                                       text-red-400
                                                       hover:bg-red-500/20
                                                       flex items-center
                                                       justify-center"
                                                aria-label="Delete product"
                                            >
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>