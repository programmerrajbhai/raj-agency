<?php
declare(strict_types=1);

$services = $pdo->query(
    'SELECT id, title
     FROM services
     WHERE is_active = 1
     ORDER BY title ASC'
)->fetchAll();

$contactError = flash('contact_error');

$old = $_SESSION['_contact_old'] ?? [];

unset($_SESSION['_contact_old']);
?>

<main class="pt-32 pb-20 min-h-screen bg-[#050505] relative overflow-hidden">

    <div class="absolute top-20 left-[-10rem] w-96 h-96 bg-yellow-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <section class="max-w-6xl mx-auto px-5 relative z-10">

        <div class="max-w-3xl mb-12">
            <span class="text-yellow-500 text-sm font-bold uppercase tracking-[.25em]">
                Contact Raj Agency
            </span>

            <h1 class="text-4xl md:text-6xl font-bold text-white mt-4 mb-5">
                Let’s Build Your Next Project.
            </h1>

            <p class="text-gray-400 text-lg">
                Tell me what you need. I will review your requirements
                and contact you as soon as possible.
            </p>
        </div>

        <?php if ($contactError): ?>
            <div class="mb-7 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-300">
                <?= e($contactError) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-7">

                <form
                    action="api/submit_contact.php"
                    method="POST"
                    class="bg-[#111] border border-white/10 rounded-3xl p-6 md:p-8 space-y-5"
                >
                    <?= csrf_field() ?>

                    <!-- Spam protection -->
                    <div class="hidden" aria-hidden="true">
                        <label for="website">Website</label>

                        <input
                            id="website"
                            name="website"
                            type="text"
                            tabindex="-1"
                            autocomplete="off"
                        >
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>
                            <label
                                for="name"
                                class="block text-sm text-gray-400 mb-2"
                            >
                                Your name
                            </label>

                            <input
                                id="name"
                                name="name"
                                type="text"
                                maxlength="100"
                                required
                                value="<?= e($old['name'] ?? '') ?>"
                                class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                            >
                        </div>

                        <div>
                            <label
                                for="email"
                                class="block text-sm text-gray-400 mb-2"
                            >
                                Email address
                            </label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                maxlength="190"
                                required
                                value="<?= e($old['email'] ?? '') ?>"
                                class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                            >
                        </div>

                    </div>

                    <div>
                        <label
                            for="service_id"
                            class="block text-sm text-gray-400 mb-2"
                        >
                            Interested service
                        </label>

                        <select
                            id="service_id"
                            name="service_id"
                            class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                        >
                            <option value="">
                                General / Custom Project
                            </option>

                            <?php foreach ($services as $service): ?>
                                <option
                                    value="<?= (int) $service['id'] ?>"
                                    <?= (string) ($old['service_id'] ?? '') === (string) $service['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($service['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label
                            for="message"
                            class="block text-sm text-gray-400 mb-2"
                        >
                            Project details
                        </label>

                        <textarea
                            id="message"
                            name="message"
                            rows="7"
                            minlength="10"
                            maxlength="3000"
                            required
                            placeholder="Describe your project, required features, budget, and expected delivery time."
                            class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                        ><?= e($old['message'] ?? '') ?></textarea>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-4 bg-yellow-500 text-black font-bold rounded-xl hover:bg-yellow-400 transition"
                    >
                        Send Message
                    </button>

                </form>
            </div>

            <aside class="lg:col-span-5 space-y-5">

                <div class="bg-[#111] border border-white/10 rounded-3xl p-7">

                    <div class="w-12 h-12 rounded-xl bg-green-500/10 text-green-400 flex items-center justify-center text-2xl mb-5">
                        <i class="ri-whatsapp-line"></i>
                    </div>

                    <h2 class="text-xl font-bold mb-2">
                        WhatsApp
                    </h2>

                    <p class="text-gray-400 text-sm mb-5">
                        For a faster response, contact me directly on WhatsApp.
                    </p>

                    <a
                        href="https://wa.me/8801310100239"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-2 text-green-400 font-bold hover:underline"
                    >
                        +880 1310-100239
                        <i class="ri-external-link-line"></i>
                    </a>

                </div>

                <div class="bg-[#111] border border-white/10 rounded-3xl p-7">

                    <div class="w-12 h-12 rounded-xl bg-yellow-500/10 text-yellow-500 flex items-center justify-center text-2xl mb-5">
                        <i class="ri-time-line"></i>
                    </div>

                    <h2 class="text-xl font-bold mb-2">
                        Response Time
                    </h2>

                    <p class="text-gray-400 text-sm">
                        Usually within 24 hours. Share complete requirements
                        for a faster and more accurate response.
                    </p>

                </div>

            </aside>

        </div>
    </section>
</main>