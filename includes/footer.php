<?php
declare(strict_types=1);
?>

<footer class="border-t border-white/5 bg-black pt-16 pb-10 mt-20">

    <div class="max-w-7xl mx-auto px-5">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 pb-12">

            <div>

                <a
                    href="index.php?page=home"
                    class="text-2xl font-display font-bold flex items-center gap-2"
                >
                    <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                    RAJ AGENCY
                </a>

                <p class="text-gray-500 text-sm leading-6 mt-4 max-w-sm">
                    Professional mobile app, website, custom
                    software and automation development services.
                </p>

            </div>

            <div>

                <h3 class="font-bold mb-4">
                    Quick Links
                </h3>

                <nav class="space-y-3 text-sm text-gray-500">

                    <a
                        href="index.php?page=home"
                        class="block hover:text-yellow-500 transition"
                    >
                        Home
                    </a>

                    <a
                        href="index.php?page=portfolio"
                        class="block hover:text-yellow-500 transition"
                    >
                        Portfolio
                    </a>

                    <a
                        href="index.php?page=about"
                        class="block hover:text-yellow-500 transition"
                    >
                        About Me
                    </a>

                    <a
                        href="index.php?page=contact"
                        class="block hover:text-yellow-500 transition"
                    >
                        Contact
                    </a>

                </nav>

            </div>

            <div>

                <h3 class="font-bold mb-4">
                    Connect
                </h3>

                <div class="flex flex-wrap gap-3">

                    <a
                        href="https://github.com/programmerrajbhai"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="w-11 h-11 rounded-full border border-white/10 text-gray-400 flex items-center justify-center hover:border-yellow-500 hover:text-yellow-500 transition"
                        aria-label="GitHub"
                    >
                        <i class="ri-github-fill text-xl"></i>
                    </a>

                    <a
                        href="https://wa.me/8801310100239"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="w-11 h-11 rounded-full border border-white/10 text-gray-400 flex items-center justify-center hover:border-green-500 hover:text-green-400 transition"
                        aria-label="WhatsApp"
                    >
                        <i class="ri-whatsapp-line text-xl"></i>
                    </a>

                    <a
                        href="index.php?page=contact"
                        class="w-11 h-11 rounded-full border border-white/10 text-gray-400 flex items-center justify-center hover:border-blue-500 hover:text-blue-400 transition"
                        aria-label="Email and contact"
                    >
                        <i class="ri-mail-line text-xl"></i>
                    </a>

                </div>

            </div>

        </div>

        <div class="border-t border-white/5 pt-7 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-gray-600">

            <p>
                © <?= date('Y') ?> Raj Agency. All rights reserved.
            </p>

            <p>
                Developed by Habib Islam Raj
            </p>

        </div>

    </div>

</footer>

<script>
(function () {
    const menuButton =
        document.getElementById('mobile-menu-btn');

    const mobileMenu =
        document.getElementById('mobile-menu');

    if (!menuButton || !mobileMenu) {
        return;
    }

    const firstLine = menuButton.children[0];
    const secondLine = menuButton.children[1];

    let menuOpen = false;
    let closeTimer = null;

    function openMenu() {
        if (closeTimer) {
            clearTimeout(closeTimer);
        }

        menuOpen = true;

        mobileMenu.classList.remove('hidden');
        mobileMenu.classList.add('flex');

        document.body.style.overflow = 'hidden';

        menuButton.setAttribute(
            'aria-expanded',
            'true'
        );

        requestAnimationFrame(function () {
            mobileMenu.classList.remove(
                'opacity-0',
                'translate-y-10'
            );

            mobileMenu.classList.add(
                'opacity-100',
                'translate-y-0'
            );
        });

        if (firstLine) {
            firstLine.style.transform =
                'translateY(5px) rotate(45deg)';
        }

        if (secondLine) {
            secondLine.style.width = '2rem';

            secondLine.style.transform =
                'translateY(-5px) rotate(-45deg)';
        }
    }

    function closeMenu() {
        menuOpen = false;

        mobileMenu.classList.remove(
            'opacity-100',
            'translate-y-0'
        );

        mobileMenu.classList.add(
            'opacity-0',
            'translate-y-10'
        );

        document.body.style.overflow = '';

        menuButton.setAttribute(
            'aria-expanded',
            'false'
        );

        if (firstLine) {
            firstLine.style.transform = '';
        }

        if (secondLine) {
            secondLine.style.width = '';
            secondLine.style.transform = '';
        }

        closeTimer = setTimeout(function () {
            mobileMenu.classList.add('hidden');
            mobileMenu.classList.remove('flex');
        }, 220);
    }

    menuButton.addEventListener(
        'click',
        function () {
            if (menuOpen) {
                closeMenu();
            } else {
                openMenu();
            }
        }
    );

    mobileMenu
        .querySelectorAll('a')
        .forEach(function (link) {
            link.addEventListener(
                'click',
                closeMenu
            );
        });

    document.addEventListener(
        'keydown',
        function (event) {
            if (
                event.key === 'Escape' &&
                menuOpen
            ) {
                closeMenu();
            }
        }
    );
})();
</script>

</body>
</html>