<?php
declare(strict_types=1);

if (
    !isset($pdo) ||
    !$pdo instanceof PDO ||
    !function_exists('is_admin') ||
    !is_admin()
) {
    return;
}

if (!function_exists('shared_admin_count')) {
    function shared_admin_count(PDO $pdo, string $sql): int
    {
        try {
            return (int) $pdo->query($sql)->fetchColumn();
        } catch (Throwable $exception) {
            error_log(
                'Admin sidebar count failed: ' .
                $exception->getMessage()
            );

            return 0;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Active Menu Detection
|--------------------------------------------------------------------------
*/

$currentAdminFile = basename(
    (string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php')
);

$activeAdminMenu = match ($currentAdminFile) {
    'projects.php',
    'edit_project.php' => 'projects',

    'add_project.php' => 'add-project',

    'edit_service.php' => 'products',

    'add_service.php' => 'add-product',

    'orders.php' => 'orders',

    'messages.php' => 'messages',

    default => 'dashboard',
};

/*
|--------------------------------------------------------------------------
| Sidebar Counters
|--------------------------------------------------------------------------
*/

$projectCount = shared_admin_count(
    $pdo,
    'SELECT COUNT(*) FROM projects'
);

$productCount = shared_admin_count(
    $pdo,
    'SELECT COUNT(*) FROM services'
);

$pendingCount = shared_admin_count(
    $pdo,
    "SELECT COUNT(*) FROM orders WHERE status = 'pending'"
);

$messageCount = shared_admin_count(
    $pdo,
    "SELECT COUNT(*) FROM messages WHERE status = 'new'"
);

/*
|--------------------------------------------------------------------------
| Sidebar Menu
|--------------------------------------------------------------------------
*/

$adminMenuGroups = [
    [
        'label' => 'Overview',
        'items' => [
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => 'ri-dashboard-line',
                'url' => 'index.php',
            ],
        ],
    ],

    [
        'label' => 'Portfolio',
        'items' => [
            [
                'key' => 'projects',
                'label' => 'Portfolio Projects',
                'icon' => 'ri-gallery-line',
                'url' => 'projects.php',
                'badge' => $projectCount,
            ],
            [
                'key' => 'add-project',
                'label' => 'Add Project',
                'icon' => 'ri-image-add-line',
                'url' => 'add_project.php',
            ],
        ],
    ],

    [
        'label' => 'Products & Sales',
        'items' => [
            [
                'key' => 'products',
                'label' => 'All Products',
                'icon' => 'ri-shopping-bag-3-line',
                'url' => 'index.php#products',
                'badge' => $productCount,
            ],
            [
                'key' => 'add-product',
                'label' => 'Add Product',
                'icon' => 'ri-add-circle-line',
                'url' => 'add_service.php',
            ],
            [
                'key' => 'orders',
                'label' => 'Orders',
                'icon' => 'ri-file-list-3-line',
                'url' => 'orders.php',
                'badge' => $pendingCount,
                'badge_type' => 'warning',
            ],
            [
                'key' => 'messages',
                'label' => 'Messages',
                'icon' => 'ri-mail-line',
                'url' => 'messages.php',
                'badge' => $messageCount,
                'badge_type' => 'info',
            ],
        ],
    ],

    [
        'label' => 'Website',
        'items' => [
            [
                'key' => '',
                'label' => 'View Portfolio',
                'icon' => 'ri-user-star-line',
                'url' => '../../index.php?page=portfolio',
                'external' => true,
            ],
            [
                'key' => '',
                'label' => 'View Shop',
                'icon' => 'ri-store-2-line',
                'url' => '../../index.php?page=products',
                'external' => true,
            ],
            [
                'key' => '',
                'label' => 'View Website',
                'icon' => 'ri-global-line',
                'url' => '../../index.php',
                'external' => true,
            ],
        ],
    ],
];
?>

<link
    href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css"
    rel="stylesheet"
>

<style>
    :root {
        --admin-sidebar-width: 270px;
        --admin-yellow: #facc15;
        --admin-panel: #101012;
        --admin-border: rgba(255, 255, 255, 0.07);
    }

    /*
    |--------------------------------------------------------------------------
    | Hide Previous Sidebar
    |--------------------------------------------------------------------------
    */

    body > aside:not(#sharedAdminSidebar),
    body > header:not(#sharedAdminMobileHeader) {
        display: none !important;
    }

    #sharedAdminMobileHeader {
        display: none;
    }

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    */

    #sharedAdminSidebar {
        position: fixed;
        inset: 0 auto 0 0;
        z-index: 1000;

        width: var(--admin-sidebar-width);
        height: 100vh;

        display: flex;
        flex-direction: column;

        color: #ffffff;

        background:
            radial-gradient(
                circle at 15% 0%,
                rgba(250, 204, 21, 0.08),
                transparent 26%
            ),
            var(--admin-panel);

        border-right: 1px solid var(--admin-border);
        box-shadow: 25px 0 70px rgba(0, 0, 0, 0.22);
    }

    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    */

    .shared-admin-brand {
        min-height: 82px;

        display: flex;
        align-items: center;
        gap: 11px;

        padding: 18px 20px;

        border-bottom: 1px solid var(--admin-border);
    }

    .shared-admin-logo {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;

        display: grid;
        place-items: center;

        color: #050505;
        font-size: 21px;

        border-radius: 13px;

        background: linear-gradient(
            135deg,
            #fde047,
            #eab308
        );

        box-shadow: 0 10px 30px rgba(234, 179, 8, 0.18);
    }

    .shared-admin-title {
        margin: 0;

        color: var(--admin-yellow);

        font-size: 18px;
        font-weight: 900;
    }

    .shared-admin-subtitle {
        margin: 4px 0 0;

        color: #666670;

        font-size: 9px;
        font-weight: 800;

        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .shared-admin-close {
        display: none;

        width: 38px;
        height: 38px;

        margin-left: auto;

        color: #bbbbbb;
        font-size: 21px;

        border: 1px solid var(--admin-border);
        border-radius: 11px;

        background: rgba(255, 255, 255, 0.04);

        cursor: pointer;
    }

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */

    .shared-admin-nav {
        flex: 1;

        padding: 13px 12px 22px;

        overflow-y: auto;

        scrollbar-width: thin;
        scrollbar-color: #303035 transparent;
    }

    .shared-admin-label {
        margin: 18px 10px 7px;

        color: #55555e;

        font-size: 9px;
        font-weight: 800;

        letter-spacing: 0.19em;
        text-transform: uppercase;
    }

    .shared-admin-label:first-child {
        margin-top: 5px;
    }

    .shared-admin-link {
        display: flex;
        align-items: center;

        min-height: 47px;

        margin: 3px 0;
        padding: 10px 11px;

        gap: 11px;

        color: #9a9aa4;

        font-size: 13px;
        font-weight: 600;

        text-decoration: none;

        border: 1px solid transparent;
        border-radius: 13px;

        transition: 0.2s ease;
    }

    .shared-admin-link:hover {
        color: #ffffff;

        background: rgba(255, 255, 255, 0.045);
        border-color: rgba(255, 255, 255, 0.05);

        transform: translateX(2px);
    }

    .shared-admin-link.is-active {
        color: #080808;
        font-weight: 800;

        background: linear-gradient(
            135deg,
            #fde047,
            #eab308
        );

        box-shadow: 0 11px 28px rgba(234, 179, 8, 0.13);
    }

    .shared-admin-icon {
        width: 25px;
        flex: 0 0 25px;

        display: grid;
        place-items: center;

        font-size: 18px;
    }

    .shared-admin-text {
        flex: 1;
        min-width: 0;

        overflow: hidden;

        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /*
    |--------------------------------------------------------------------------
    | Badge
    |--------------------------------------------------------------------------
    */

    .shared-admin-badge {
        min-width: 23px;
        height: 23px;

        padding: 0 6px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        color: #d4d4dc;

        font-size: 10px;
        font-weight: 800;

        border-radius: 999px;

        background: rgba(255, 255, 255, 0.07);
    }

    .shared-admin-badge.warning {
        color: #facc15;
        background: rgba(250, 204, 21, 0.11);
    }

    .shared-admin-badge.info {
        color: #60a5fa;
        background: rgba(59, 130, 246, 0.12);
    }

    .shared-admin-link.is-active .shared-admin-badge {
        color: #111111;
        background: rgba(0, 0, 0, 0.11);
    }

    /*
    |--------------------------------------------------------------------------
    | Footer
    |--------------------------------------------------------------------------
    */

    .shared-admin-footer {
        padding: 13px 12px 16px;

        border-top: 1px solid var(--admin-border);

        background: rgba(0, 0, 0, 0.13);
    }

    .shared-admin-user {
        display: flex;
        align-items: center;

        gap: 10px;

        padding: 8px 9px 12px;
    }

    .shared-admin-avatar {
        width: 35px;
        height: 35px;

        display: grid;
        place-items: center;

        color: #fde047;

        font-weight: 900;

        border: 1px solid rgba(250, 204, 21, 0.13);
        border-radius: 11px;

        background: rgba(250, 204, 21, 0.08);
    }

    .shared-admin-name {
        margin: 0;

        max-width: 150px;

        overflow: hidden;

        color: #e5e5e8;

        font-size: 12px;
        font-weight: 700;

        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .shared-admin-role {
        margin: 3px 0 0;

        color: #5e5e67;

        font-size: 9px;
        font-weight: 700;

        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .shared-admin-logout {
        width: 100%;

        display: flex;
        align-items: center;
        justify-content: center;

        gap: 8px;

        padding: 10px 13px;

        color: #fb7185;

        font-size: 12px;
        font-weight: 700;

        border: 1px solid rgba(244, 63, 94, 0.12);
        border-radius: 11px;

        background: rgba(244, 63, 94, 0.06);

        cursor: pointer;
    }

    .shared-admin-logout:hover {
        color: #fecdd3;
        background: rgba(244, 63, 94, 0.12);
    }

    #sharedAdminOverlay {
        display: none;
    }

    /*
    |--------------------------------------------------------------------------
    | Desktop Content Position
    |--------------------------------------------------------------------------
    */

    @media (min-width: 1024px) {
        body > main {
            width: auto !important;
            margin-left: var(--admin-sidebar-width) !important;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Mobile
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1023px) {
        #sharedAdminMobileHeader {
            position: sticky;
            top: 0;
            z-index: 900;

            min-height: 66px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 11px 15px;

            background: rgba(13, 13, 15, 0.94);
            border-bottom: 1px solid var(--admin-border);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .shared-mobile-brand {
            display: flex;
            align-items: center;

            gap: 9px;

            color: var(--admin-yellow);

            font-size: 15px;
            font-weight: 900;

            text-decoration: none;
        }

        .shared-mobile-logo,
        .shared-mobile-button {
            width: 40px;
            height: 40px;

            display: grid;
            place-items: center;

            color: #dddddd;

            font-size: 20px;

            border: 1px solid var(--admin-border);
            border-radius: 12px;

            background: rgba(255, 255, 255, 0.04);

            text-decoration: none;
        }

        .shared-mobile-logo {
            width: 36px;
            height: 36px;

            color: #080808;

            font-size: 17px;

            border: 0;

            background: linear-gradient(
                135deg,
                #fde047,
                #eab308
            );
        }

        .shared-mobile-actions {
            display: flex;
            gap: 8px;
        }

        .shared-mobile-button {
            cursor: pointer;
        }

        #sharedAdminSidebar {
            width: min(86vw, 290px);

            transform: translateX(-105%);

            transition: transform 0.25s ease;
        }

        #sharedAdminSidebar.is-open {
            transform: translateX(0);
        }

        .shared-admin-close {
            display: grid;
            place-items: center;
        }

        #sharedAdminOverlay {
            position: fixed;
            inset: 0;
            z-index: 990;

            display: block;

            visibility: hidden;
            opacity: 0;

            background: rgba(0, 0, 0, 0.7);

            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);

            transition: 0.25s ease;
        }

        #sharedAdminOverlay.is-open {
            visibility: visible;
            opacity: 1;
        }

        body.shared-admin-open {
            overflow: hidden;
        }

        body > main {
            width: auto !important;
            margin-left: 0 !important;
        }
    }
</style>

<!-- Mobile Header -->
<header id="sharedAdminMobileHeader">
    <a href="index.php" class="shared-mobile-brand">
        <span class="shared-mobile-logo">
            <i class="ri-code-s-slash-line"></i>
        </span>

        <span>RAJ ADMIN.</span>
    </a>

    <div class="shared-mobile-actions">
        <a
            href="add_project.php"
            class="shared-mobile-button"
            aria-label="Add project"
        >
            <i class="ri-add-line"></i>
        </a>

        <button
            type="button"
            id="sharedAdminOpen"
            class="shared-mobile-button"
            aria-label="Open menu"
            aria-expanded="false"
        >
            <i class="ri-menu-3-line"></i>
        </button>
    </div>
</header>

<div
    id="sharedAdminOverlay"
    aria-hidden="true"
></div>

<!-- Shared Sidebar -->
<aside
    id="sharedAdminSidebar"
    aria-label="Admin navigation"
>
    <div class="shared-admin-brand">
        <div class="shared-admin-logo">
            <i class="ri-code-s-slash-line"></i>
        </div>

        <div>
            <p class="shared-admin-title">
                RAJ ADMIN.
            </p>

            <p class="shared-admin-subtitle">
                Control Center
            </p>
        </div>

        <button
            type="button"
            id="sharedAdminClose"
            class="shared-admin-close"
            aria-label="Close menu"
        >
            <i class="ri-close-line"></i>
        </button>
    </div>

    <nav class="shared-admin-nav">
        <?php foreach ($adminMenuGroups as $group): ?>
            <p class="shared-admin-label">
                <?= e($group['label']) ?>
            </p>

            <?php foreach ($group['items'] as $item): ?>
                <?php
                $isExternal = !empty($item['external']);

                $isActive =
                    $item['key'] !== '' &&
                    $item['key'] === $activeAdminMenu;

                $badge = (int) ($item['badge'] ?? 0);

                $badgeType = (string) (
                    $item['badge_type'] ?? ''
                );
                ?>

                <a
                    href="<?= e($item['url']) ?>"
                    class="shared-admin-link<?= $isActive ? ' is-active' : '' ?>"
                    <?= $isExternal
                        ? 'target="_blank" rel="noopener"'
                        : ''
                    ?>
                >
                    <span class="shared-admin-icon">
                        <i class="<?= e($item['icon']) ?>"></i>
                    </span>

                    <span class="shared-admin-text">
                        <?= e($item['label']) ?>
                    </span>

                    <?php if (
                        $badge > 0 ||
                        in_array(
                            $item['key'],
                            ['projects', 'products'],
                            true
                        )
                    ): ?>
                        <span
                            class="shared-admin-badge <?= e($badgeType) ?>"
                        >
                            <?= $badge ?>
                        </span>
                    <?php elseif ($isExternal): ?>
                        <i class="ri-arrow-right-up-line"></i>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>

    <div class="shared-admin-footer">
        <div class="shared-admin-user">
            <div class="shared-admin-avatar">
                <?= e(
                    strtoupper(
                        substr(
                            (string) (
                                $_SESSION['admin_username'] ?? 'A'
                            ),
                            0,
                            1
                        )
                    )
                ) ?>
            </div>

            <div>
                <p class="shared-admin-name">
                    <?= e(
                        $_SESSION['admin_username'] ?? 'Admin'
                    ) ?>
                </p>

                <p class="shared-admin-role">
                    Administrator
                </p>
            </div>
        </div>

        <form action="logout.php" method="POST">
            <?= csrf_field() ?>

            <button
                type="submit"
                class="shared-admin-logout"
            >
                <i class="ri-logout-box-r-line"></i>
                Secure Logout
            </button>
        </form>
    </div>
</aside>

<script>
    (() => {
        const sidebar =
            document.getElementById('sharedAdminSidebar');

        const overlay =
            document.getElementById('sharedAdminOverlay');

        const openButton =
            document.getElementById('sharedAdminOpen');

        const closeButton =
            document.getElementById('sharedAdminClose');

        if (
            !sidebar ||
            !overlay ||
            !openButton ||
            !closeButton
        ) {
            return;
        }

        const setMenu = (open) => {
            sidebar.classList.toggle('is-open', open);
            overlay.classList.toggle('is-open', open);

            document.body.classList.toggle(
                'shared-admin-open',
                open
            );

            openButton.setAttribute(
                'aria-expanded',
                String(open)
            );

            overlay.setAttribute(
                'aria-hidden',
                String(!open)
            );
        };

        openButton.addEventListener(
            'click',
            () => setMenu(true)
        );

        closeButton.addEventListener(
            'click',
            () => setMenu(false)
        );

        overlay.addEventListener(
            'click',
            () => setMenu(false)
        );

        sidebar.querySelectorAll('a').forEach((link) => {
            link.addEventListener(
                'click',
                () => setMenu(false)
            );
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setMenu(false);
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                setMenu(false);
            }
        });
    })();
</script>