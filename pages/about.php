<?php
declare(strict_types=1);
?>

<main class="pt-36 pb-24 min-h-screen bg-[#050505] relative overflow-hidden">

    <div class="absolute top-20 right-[-10rem] w-96 h-96 bg-yellow-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <section class="max-w-7xl mx-auto px-5 relative z-10">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

            <div class="relative max-w-xl mx-auto lg:mx-0">

                <div class="absolute -inset-5 border border-yellow-500/20 rounded-[3rem] rotate-3"></div>

                <div class="relative rounded-[3rem] overflow-hidden border border-white/10 bg-gradient-to-b from-yellow-500/10 to-[#111]">

                    <img
                        src="assets/profile.png"
                        alt="Habib Islam Raj - Software Developer"
                        class="w-full h-auto max-h-[650px] object-cover object-center"
                    >

                    <div class="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-black to-transparent"></div>

                    <div class="absolute bottom-6 left-6 right-6 bg-black/70 backdrop-blur-md border border-white/10 rounded-2xl p-5">

                        <span class="text-yellow-500 text-xs font-bold uppercase tracking-widest">
                            Software Developer
                        </span>

                        <h2 class="text-2xl font-bold mt-1">
                            Habib Islam Raj
                        </h2>

                    </div>

                </div>

            </div>

            <div>

                <span class="text-yellow-500 text-sm font-bold uppercase tracking-[.25em]">
                    About The Developer
                </span>

                <h1 class="text-4xl md:text-6xl font-display font-bold leading-tight mt-4">
                    Building Software That Helps Businesses Grow.
                </h1>

                <div class="space-y-5 text-gray-400 text-lg leading-8 mt-7">

                    <p>
                        I’m Habib Islam Raj, a professional
                        Android, iOS, web and automation developer.
                    </p>

                    <p>
                        I develop custom mobile applications,
                        business websites, PHP systems, admin panels
                        and automation solutions based on real
                        business requirements.
                    </p>

                    <p>
                        My focus is clean design, secure development,
                        good performance and a simple user experience.
                    </p>

                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 py-8 mt-3">

                    <div class="bg-[#111] border border-white/10 rounded-2xl p-5">

                        <strong class="block text-3xl font-bold text-yellow-500">
                            5+
                        </strong>

                        <span class="block text-xs text-gray-500 uppercase tracking-wider mt-2">
                            Years Experience
                        </span>

                    </div>

                    <div class="bg-[#111] border border-white/10 rounded-2xl p-5">

                        <strong class="block text-3xl font-bold text-yellow-500">
                            150+
                        </strong>

                        <span class="block text-xs text-gray-500 uppercase tracking-wider mt-2">
                            Projects
                        </span>

                    </div>

                    <div class="bg-[#111] border border-white/10 rounded-2xl p-5 col-span-2 sm:col-span-1">

                        <strong class="block text-3xl font-bold text-yellow-500">
                            24h
                        </strong>

                        <span class="block text-xs text-gray-500 uppercase tracking-wider mt-2">
                            Response Goal
                        </span>

                    </div>

                </div>

                <div class="flex flex-col sm:flex-row gap-4">

                    <a
                        href="index.php?page=contact"
                        class="inline-flex justify-center items-center gap-2 px-7 py-4 bg-yellow-500 text-black font-bold rounded-xl hover:bg-yellow-400 transition"
                    >
                        <i class="ri-send-plane-fill"></i>
                        Start Your Project
                    </a>

                    <a
                        href="https://wa.me/8801310100239"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex justify-center items-center gap-2 px-7 py-4 border border-green-500/30 text-green-400 font-bold rounded-xl hover:bg-green-500/10 transition"
                    >
                        <i class="ri-whatsapp-line text-xl"></i>
                        WhatsApp
                    </a>

                </div>

            </div>

        </div>

    </section>

    <section class="max-w-7xl mx-auto px-5 mt-24 relative z-10">

        <div class="text-center max-w-3xl mx-auto mb-12">

            <span class="text-yellow-500 text-sm font-bold uppercase tracking-[.25em]">
                What I Build
            </span>

            <h2 class="text-3xl md:text-5xl font-bold mt-3">
                Development Services
            </h2>

        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

            <?php
            $services = [
                [
                    'icon' => 'ri-smartphone-line',
                    'title' => 'Mobile Apps',
                    'text' => 'Android, iOS and Flutter application development.',
                ],
                [
                    'icon' => 'ri-code-s-slash-line',
                    'title' => 'Web Development',
                    'text' => 'Business websites, PHP systems and web applications.',
                ],
                [
                    'icon' => 'ri-dashboard-3-line',
                    'title' => 'Admin Panels',
                    'text' => 'Secure dashboards and complete management systems.',
                ],
                [
                    'icon' => 'ri-robot-2-line',
                    'title' => 'Automation',
                    'text' => 'Custom automation tools that save time and manual work.',
                ],
            ];
            ?>

            <?php foreach ($services as $service): ?>

                <article class="bg-[#111] border border-white/10 rounded-3xl p-6 hover:border-yellow-500/30 transition">

                    <div class="w-14 h-14 rounded-2xl bg-yellow-500/10 text-yellow-500 flex items-center justify-center text-3xl mb-5">

                        <i class="<?= e($service['icon']) ?>"></i>

                    </div>

                    <h3 class="text-xl font-bold">
                        <?= e($service['title']) ?>
                    </h3>

                    <p class="text-gray-400 text-sm leading-6 mt-3">
                        <?= e($service['text']) ?>
                    </p>

                </article>

            <?php endforeach; ?>

        </div>

    </section>

</main>