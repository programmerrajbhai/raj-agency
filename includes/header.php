<?php
declare(strict_types=1);

require_once __DIR__ . '/cart.php';

$pageTitles = [
    'home' => 'Raj Agency | App, Website & Automation Developer',

    'feed' => 'Latest Products | Raj Agency',

    'portfolio' => 'Portfolio Projects | Raj Agency',

    'project-details' => 'Project Details | Raj Agency',

    'products' => 'Digital Products & Services | Raj Agency',

    'service-details' => 'Product Details | Raj Agency',

    'checkout' => 'Complete Your Order | Raj Agency',

    'order-success' => 'Order Received | Raj Agency',

    'contact' => 'Contact Raj Agency | Start Your Project',

    'about' => 'About Habib Islam Raj | Raj Agency',

    '404' => 'Page Not Found | Raj Agency',
];

$documentTitle = $pageTitles[$page] ?? 'Raj Agency';

$description =
    'Professional Android, iOS, Flutter, website, custom software, '
    . 'and automation development services by Habib Islam Raj.';

$currentCartCount = cart_count();

$globalSuccess = flash('success');
$globalError = flash('error');

function nav_active(
    string $target,
    string $current
): string {
    $groups = [
        'home' => [
            'home',
        ],

        'portfolio' => [
            'portfolio',
            'project-details',
        ],

        'products' => [
            'products',
            'service-details',
            'checkout',
            'order-success',
        ],

        'about' => [
            'about',
        ],

        'contact' => [
            'contact',
        ],
    ];

    return in_array(
        $current,
        $groups[$target] ?? [$target],
        true
    )
        ? 'text-yellow-500'
        : 'text-white';
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="<?= e($description) ?>"
    >

    <meta name="theme-color" content="#050505">

    <meta property="og:type" content="website">

    <meta
        property="og:title"
        content="<?= e($documentTitle) ?>"
    >

    <meta
        property="og:description"
        content="<?= e($description) ?>"
    >

    <?php if (APP_URL !== ''): ?>
        <link
            rel="canonical"
            href="<?= e(
                APP_URL
                . '/index.php?page='
                . rawurlencode($page)
            ) ?>"
        >

        <meta
            property="og:url"
            content="<?= e(
                APP_URL
                . '/index.php?page='
                . rawurlencode($page)
            ) ?>"
        >
    <?php endif; ?>

    <title><?= e($documentTitle) ?></title>

    <link
        rel="stylesheet"
        href="assets/css/portfolio-premium.css"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700;900&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet"
    >

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bg: '#050505',
                        card: '#101010',
                        border: '#1F1F1F',
                        accent: '#F4B90B',
                        muted: '#9CA3AF'
                    },

                    fontFamily: {
                        sans: [
                            'Inter',
                            'sans-serif'
                        ],

                        display: [
                            'Space Grotesk',
                            'sans-serif'
                        ]
                    }
                }
            }
        };
    </script>

    <style>
        body {
            background: #050505;
            color: #ffffff;
        }

        .glass-nav {
            background: rgba(5, 5, 5, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .nav-link {
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;

            width: 0;
            height: 1px;

            bottom: -5px;
            left: 0;

            background: #f4b90b;

            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        #mobile-menu {
            transition:
                opacity 0.22s ease,
                transform 0.22s ease;
        }

        #mobile-menu-btn span {
            transition:
                transform 0.22s ease,
                width 0.22s ease;
        }
    </style>
</head>

<body
    class="page-<?= e($page) ?>
           antialiased font-sans
           selection:bg-yellow-500
           selection:text-black"
>

<nav
    class="fixed w-full z-50 top-0 left-0 glass-nav"
>
    <div
        class="max-w-7xl mx-auto px-5 h-24
               flex justify-between items-center"
    >
        <a
            href="index.php?page=home"
            class="text-xl md:text-2xl font-display font-bold
                   uppercase tracking-tighter
                   flex items-center gap-2"
        >
            <span
                class="w-3 h-3 bg-yellow-500 rounded-full"
            ></span>

            Habib Islam Raj
        </a>

        <!-- Desktop Navigation -->
        <ul
            class="hidden lg:flex items-center gap-7
                   text-sm font-medium tracking-wide"
        >
            <li>
                <a
                    href="index.php?page=home"
                    class="nav-link <?= nav_active(
                        'home',
                        $page
                    ) ?> hover:text-yellow-500 transition"
                >
                    HOME
                </a>
            </li>

            <li>
                <a
                    href="index.php?page=portfolio"
                    class="nav-link <?= nav_active(
                        'portfolio',
                        $page
                    ) ?> hover:text-yellow-500 transition"
                >
                    PORTFOLIO
                </a>
            </li>

            <li>
                <a
                    href="index.php?page=products"
                    class="nav-link <?= nav_active(
                        'products',
                        $page
                    ) ?> hover:text-yellow-500 transition"
                >
                    PRODUCTS
                </a>
            </li>

            <li>
                <a
                    href="index.php?page=about"
                    class="nav-link <?= nav_active(
                        'about',
                        $page
                    ) ?> hover:text-yellow-500 transition"
                >
                    ABOUT ME
                </a>
            </li>

            <li>
                <a
                    href="index.php?page=contact"
                    class="nav-link <?= nav_active(
                        'contact',
                        $page
                    ) ?> hover:text-yellow-500 transition"
                >
                    CONTACT
                </a>
            </li>
        </ul>

        <div class="flex items-center gap-3">
            <a
                href="index.php?page=checkout"
                class="relative w-11 h-11 rounded-full
                       border border-white/10
                       flex items-center justify-center
                       hover:border-yellow-500
                       hover:text-yellow-500 transition"
                aria-label="Shopping cart"
            >
                <i class="ri-shopping-cart-2-line text-xl"></i>

                <?php if ($currentCartCount > 0): ?>
                    <span
                        class="absolute -top-1 -right-1
                               min-w-5 h-5 px-1 rounded-full
                               bg-yellow-500 text-black
                               text-[11px] font-bold
                               flex items-center justify-center"
                    >
                        <?= $currentCartCount ?>
                    </span>
                <?php endif; ?>
            </a>

            <a
                href="index.php?page=contact"
                class="hidden md:flex px-6 py-3
                       bg-white text-black text-xs
                       font-bold uppercase tracking-widest
                       rounded-full hover:bg-yellow-500 transition"
            >
                Hire Me
            </a>

            <button
                id="mobile-menu-btn"
                type="button"
                class="lg:hidden space-y-2
                       cursor-pointer group z-50 relative"
                aria-label="Open menu"
                aria-expanded="false"
                aria-controls="mobile-menu"
            >
                <span
                    class="block w-8 h-[2px] bg-white"
                ></span>

                <span
                    class="block w-5 h-[2px]
                           bg-white ml-auto"
                ></span>
            </button>
        </div>
    </div>
</nav>

<!-- Mobile Navigation -->
<div
    id="mobile-menu"
    class="fixed inset-0 bg-black/95 z-40
           hidden flex-col items-center justify-center
           space-y-7 opacity-0 translate-y-10
           backdrop-blur-xl"
>
    <a
        href="index.php?page=home"
        class="text-3xl font-bold hover:text-yellow-500"
    >
        HOME
    </a>

    <a
        href="index.php?page=portfolio"
        class="text-3xl font-bold hover:text-yellow-500"
    >
        PORTFOLIO
    </a>

    <a
        href="index.php?page=products"
        class="text-3xl font-bold hover:text-yellow-500"
    >
        PRODUCTS
    </a>

    <a
        href="index.php?page=about"
        class="text-3xl font-bold hover:text-yellow-500"
    >
        ABOUT ME
    </a>

    <a
        href="index.php?page=contact"
        class="text-3xl font-bold hover:text-yellow-500"
    >
        CONTACT
    </a>

    <a
        href="index.php?page=checkout"
        class="text-3xl font-bold text-yellow-500"
    >
        CART (<?= $currentCartCount ?>)
    </a>
</div>

<?php if ($globalSuccess): ?>
    <div
        class="fixed top-28 right-5 z-[70]
               max-w-md p-4 rounded-xl
               bg-green-500/95 text-white shadow-2xl"
        role="status"
    >
        <?= e($globalSuccess) ?>
    </div>
<?php endif; ?>

<?php if ($globalError): ?>
    <div
        class="fixed top-28 right-5 z-[70]
               max-w-md p-4 rounded-xl
               bg-red-500/95 text-white shadow-2xl"
        role="alert"
    >
        <?= e($globalError) ?>
    </div>
<?php endif; ?>