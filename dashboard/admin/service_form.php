<?php
declare(strict_types=1);

$mediaUrlsText = (string) (
    $_POST['media_urls_text']
    ?? ''
);
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
        <?= e($pageTitle) ?> | Raj Admin
    </title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css"
        rel="stylesheet"
    >
</head>

<body class="bg-[#050505] text-white min-h-screen px-4 py-8">

<?php require __DIR__ . '/admin_sidebar.php'; ?>



<main class="max-w-6xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">

        <div>
            <a
                href="index.php"
                class="text-sm text-gray-400 hover:text-white"
            >
                ← Back to Dashboard
            </a>

            <h1 class="text-3xl font-bold mt-2">
                <?= e($pageTitle) ?>
            </h1>
        </div>

        <?php if ($isEdit): ?>

            <a
                href="../../index.php?page=service-details&id=<?= (int) $serviceId ?>"
                target="_blank"
                rel="noopener"
                class="px-5 py-3 rounded-xl border border-blue-500/30 text-blue-400 hover:bg-blue-500/10 text-center"
            >
                <i class="ri-eye-line mr-1"></i>
                Live Preview
            </a>

        <?php endif; ?>

    </div>

    <?php if ($errors !== []): ?>

        <div class="mb-6 rounded-2xl border border-red-500/30 bg-red-500/10 p-5 text-red-300">

            <p class="font-bold mb-2">
                Please fix the following:
            </p>

            <ul class="list-disc pl-5 space-y-1">

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= e($error) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>

    <form
        method="POST"
        enctype="multipart/form-data"
        class="space-y-6"
    >

        <?= csrf_field() ?>

        <section class="bg-[#111] border border-white/10 rounded-2xl p-5 md:p-7">

            <h2 class="text-xl font-bold text-yellow-500 mb-5">
                Basic Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label
                        for="title"
                        class="block text-sm text-gray-400 mb-2"
                    >
                        Service title
                    </label>

                    <input
                        id="title"
                        name="title"
                        type="text"
                        maxlength="150"
                        required
                        value="<?= e($form['title']) ?>"
                        class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                    >
                </div>

                <div>
                    <label
                        for="price"
                        class="block text-sm text-gray-400 mb-2"
                    >
                        Price ($)
                    </label>

                    <input
                        id="price"
                        name="price"
                        type="number"
                        min="0.01"
                        max="99999999.99"
                        step="0.01"
                        required
                        value="<?= e($form['price']) ?>"
                        class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                    >
                </div>

                <div>
                    <label
                        for="file_type"
                        class="block text-sm text-gray-400 mb-2"
                    >
                        Service type
                    </label>

                    <select
                        id="file_type"
                        name="file_type"
                        class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                    >

                        <option
                            value="web"
                            <?= $form['file_type'] === 'web' ? 'selected' : '' ?>
                        >
                            PHP Script / Website
                        </option>

                        <option
                            value="app"
                            <?= $form['file_type'] === 'app' ? 'selected' : '' ?>
                        >
                            Mobile App
                        </option>

                        <option
                            value="ui"
                            <?= $form['file_type'] === 'ui' ? 'selected' : '' ?>
                        >
                            UI Kit
                        </option>

                    </select>
                </div>

                <div class="flex items-end">

                    <label class="w-full flex items-center justify-between gap-3 bg-black border border-white/10 rounded-xl p-3.5 cursor-pointer">

                        <span>
                            <span class="block font-bold">
                                Visible on website
                            </span>

                            <span class="block text-xs text-gray-500">
                                Turn off to hide this service.
                            </span>
                        </span>

                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            <?= $form['is_active'] ? 'checked' : '' ?>
                            class="w-5 h-5 accent-yellow-500"
                        >

                    </label>

                </div>

                <div class="md:col-span-2">

                    <label
                        for="thumbnail"
                        class="block text-sm text-gray-400 mb-2"
                    >
                        Thumbnail URL or uploads/ path
                    </label>

                    <input
                        id="thumbnail"
                        name="thumbnail"
                        type="text"
                        maxlength="2048"
                        value="<?= e($form['thumbnail']) ?>"
                        placeholder="https://example.com/image.jpg or uploads/image.jpg"
                        class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                    >

                    <p class="text-xs text-gray-500 mt-2">
                        Leave blank to use the first uploaded image automatically.
                    </p>

                </div>

                <div class="md:col-span-2">

                    <label
                        for="short_desc"
                        class="block text-sm text-gray-400 mb-2"
                    >
                        Short description
                    </label>

                    <textarea
                        id="short_desc"
                        name="short_desc"
                        rows="4"
                        maxlength="1000"
                        class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                    ><?= e($form['short_desc']) ?></textarea>

                </div>

            </div>

        </section>

        <section class="bg-[#111] border border-white/10 rounded-2xl p-5 md:p-7">

            <h2 class="text-xl font-bold text-yellow-500 mb-5">
                Demo Links
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                <?php
                $demoLabels = [
                    'frontend' => 'Frontend Demo',
                    'admin' => 'Admin Panel',
                    'app' => 'App / APK Link',
                ];
                ?>

                <?php foreach ($demoLabels as $key => $label): ?>

                    <div class="bg-black border border-white/10 rounded-xl p-4">

                        <label class="flex justify-between gap-3 items-center mb-3">

                            <span class="font-bold text-sm">
                                <?= e($label) ?>
                            </span>

                            <input
                                type="checkbox"
                                name="demo_<?= e($key) ?>_show"
                                value="1"
                                <?= !empty($demoLinks[$key]['show']) ? 'checked' : '' ?>
                                class="w-5 h-5 accent-yellow-500"
                            >

                        </label>

                        <input
                            type="url"
                            name="demo_<?= e($key) ?>_url"
                            maxlength="2048"
                            value="<?= e($demoLinks[$key]['url'] ?? '') ?>"
                            placeholder="https://..."
                            class="w-full bg-[#111] border border-white/10 rounded-lg p-3 outline-none focus:border-yellow-500"
                        >

                    </div>

                <?php endforeach; ?>

            </div>

        </section>

        <section class="bg-[#111] border border-white/10 rounded-2xl p-5 md:p-7">

            <h2 class="text-xl font-bold text-yellow-500 mb-2">
                Features
            </h2>

            <p class="text-xs text-gray-500 mb-5">
                Separate every feature using a comma.
            </p>

            <?php
            $featureLabels = [
                'top' => 'Top Features',
                'admin' => 'Admin Features',
                'user' => 'User Features',
                'tech' => 'Technology Used',
                'files' => 'Files Included',
            ];
            ?>

            <div class="space-y-5">

                <?php foreach ($featureLabels as $key => $label): ?>

                    <div>

                        <label
                            for="feat_<?= e($key) ?>"
                            class="block text-sm text-gray-400 mb-2"
                        >
                            <?= e($label) ?>
                        </label>

                        <input
                            id="feat_<?= e($key) ?>"
                            name="feat_<?= e($key) ?>"
                            type="text"
                            value="<?= e(comma_list($features[$key] ?? [])) ?>"
                            placeholder="Feature one, Feature two, Feature three"
                            class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                        >

                    </div>

                <?php endforeach; ?>

            </div>

        </section>

        <?php if ($isEdit && $existingMedia !== []): ?>

            <section class="bg-[#111] border border-white/10 rounded-2xl p-5 md:p-7">

                <h2 class="text-xl font-bold text-yellow-500 mb-5">
                    Existing Media
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                    <?php foreach ($existingMedia as $index => $item): ?>

                        <div class="bg-black border border-white/10 rounded-xl overflow-hidden">

                            <div class="h-44 bg-[#080808] flex items-center justify-center overflow-hidden">

                                <?php if ($item['type'] === 'image'): ?>

                                    <img
                                        src="<?= e(admin_media_url($item['url'])) ?>"
                                        alt=""
                                        loading="lazy"
                                        class="w-full h-full object-cover"
                                    >

                                <?php elseif ($item['type'] === 'video'): ?>

                                    <video
                                        src="<?= e(admin_media_url($item['url'])) ?>"
                                        controls
                                        preload="metadata"
                                        class="w-full h-full object-cover"
                                    ></video>

                                <?php else: ?>

                                    <i class="ri-youtube-fill text-6xl text-red-500"></i>

                                <?php endif; ?>

                            </div>

                            <div class="p-4 space-y-4">

                                <label class="flex items-center gap-2 text-red-400 cursor-pointer">

                                    <input
                                        type="checkbox"
                                        name="remove_media[]"
                                        value="<?= (int) $index ?>"
                                        class="accent-red-500"
                                    >

                                    Remove this media

                                </label>

                                <div>

                                    <label class="block text-xs text-gray-500 mb-2">
                                        Or replace with a new file
                                    </label>

                                    <input
                                        type="file"
                                        name="replace_media[<?= (int) $index ?>]"
                                        accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm"
                                        class="w-full text-xs text-gray-400 file:bg-white/10 file:text-white file:border-0 file:rounded file:px-3 file:py-2"
                                    >

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </section>

        <?php endif; ?>

        <section class="bg-[#111] border border-white/10 rounded-2xl p-5 md:p-7">

            <h2 class="text-xl font-bold text-yellow-500 mb-5">
                Add Media
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>

                    <label
                        for="media_files"
                        class="block text-sm text-gray-400 mb-2"
                    >
                        Upload images or videos
                    </label>

                    <input
                        id="media_files"
                        type="file"
                        name="media_files[]"
                        multiple
                        accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm"
                        class="w-full bg-black border border-white/10 rounded-xl p-3 text-sm text-gray-400 file:bg-yellow-500 file:text-black file:border-0 file:rounded-lg file:px-4 file:py-2 file:font-bold"
                    >

                    <p class="text-xs text-gray-500 mt-2">
                        Maximum 15 MB per file.
                    </p>

                </div>

                <div>

                    <label
                        for="media_urls_text"
                        class="block text-sm text-gray-400 mb-2"
                    >
                        External media or YouTube URLs
                    </label>

                    <textarea
                        id="media_urls_text"
                        name="media_urls_text"
                        rows="5"
                        placeholder="One URL per line"
                        class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                    ><?= e($mediaUrlsText) ?></textarea>

                </div>

            </div>

        </section>

        <div class="flex flex-col sm:flex-row gap-4 justify-end pb-10">

            <a
                href="index.php"
                class="px-7 py-4 rounded-xl border border-white/10 text-center hover:bg-white/5"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="px-8 py-4 rounded-xl bg-yellow-500 text-black font-bold hover:bg-yellow-400"
            >
                <i class="ri-save-line mr-1"></i>
                <?= e($submitText) ?>
            </button>

        </div>

    </form>

</main>

</body>
</html>