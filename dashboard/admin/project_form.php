<?php
declare(strict_types=1);

require_once __DIR__ .
    '/project_details_helpers.php';

$details = project_details_data(
    $details ?? null
);

$mediaUrlsText = (string) (
    $_POST['media_urls_text'] ??
    ''
);

if (!function_exists('project_switch')) {
    function project_switch(
        string $name,
        string $label,
        string $description,
        bool $checked,
        string $target = ''
    ): void {
        ?>

        <label class="flex items-center justify-between gap-4 bg-black border border-white/10 rounded-xl p-4 cursor-pointer hover:border-yellow-500/30 transition">

            <span>
                <strong class="block">
                    <?= e($label) ?>
                </strong>

                <small class="block text-gray-500 mt-1">
                    <?= e($description) ?>
                </small>
            </span>

            <span class="relative inline-flex items-center">

                <input
                    type="checkbox"
                    name="<?= e($name) ?>"
                    value="1"
                    <?= $checked ? 'checked' : '' ?>
                    <?= $target !== ''
                        ? 'data-controls="' .
                            e($target) .
                            '"'
                        : '' ?>
                    class="project-switch peer sr-only"
                >

                <span class="w-12 h-7 rounded-full bg-gray-700 peer-checked:bg-yellow-500 transition"></span>

                <span class="absolute left-1 top-1 w-5 h-5 rounded-full bg-white transition-transform peer-checked:translate-x-5"></span>

            </span>

        </label>

        <?php
    }
}
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

    <title><?= e($pageTitle) ?> | Raj Admin</title>

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
                href="projects.php"
                class="text-sm text-gray-400 hover:text-white"
            >
                ← Back to Projects
            </a>

            <h1 class="text-3xl font-bold mt-2">
                <?= e($pageTitle) ?>
            </h1>

            <p class="text-gray-500 mt-2">
                Enable only the sections you want clients to see.
            </p>

        </div>

        <?php if ($isEdit): ?>

            <a
                href="../../index.php?page=project-details&id=<?= (int) $projectId ?>"
                target="_blank"
                rel="noopener"
                class="px-5 py-3 rounded-xl border border-blue-500/30 text-blue-400 hover:bg-blue-500/10 text-center"
            >
                <i class="ri-eye-line mr-1"></i>
                Client Preview
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
                    <li><?= e($error) ?></li>
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

        <!-- Basic Information -->

        <section class="bg-[#111] border border-white/10 rounded-3xl p-5 md:p-7">

            <h2 class="text-xl font-bold text-yellow-500 mb-5">
                1. Basic Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div class="md:col-span-2">

                    <label
                        for="title"
                        class="block text-sm text-gray-400 mb-2"
                    >
                        Project title
                    </label>

                    <input
                        id="title"
                        name="title"
                        type="text"
                        maxlength="150"
                        required
                        value="<?= e($form['title']) ?>"
                        placeholder="Example: Nova Live Video Chat App"
                        class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                    >

                </div>

                <div>

                    <label
                        for="category"
                        class="block text-sm text-gray-400 mb-2"
                    >
                        Category
                    </label>

                    <input
                        id="category"
                        name="category"
                        type="text"
                        maxlength="80"
                        required
                        list="project-categories"
                        value="<?= e($form['category']) ?>"
                        placeholder="Mobile App"
                        class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                    >

                    <datalist id="project-categories">
                        <option value="Mobile App">
                        <option value="Website">
                        <option value="Web Application">
                        <option value="Automation">
                        <option value="SaaS">
                        <option value="UI/UX Design">
                        <option value="Admin Panel">
                    </datalist>

                </div>

                <div>

                    <label
                        for="client_name"
                        class="block text-sm text-gray-400 mb-2"
                    >
                        Client/company name
                    </label>

                    <input
                        id="client_name"
                        name="client_name"
                        type="text"
                        maxlength="100"
                        value="<?= e($form['client_name']) ?>"
                        placeholder="Optional"
                        class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                    >

                </div>

                <div class="md:col-span-2">

                    <label
                        for="short_desc"
                        class="block text-sm text-gray-400 mb-2"
                    >
                        Short project introduction
                    </label>

                    <textarea
                        id="short_desc"
                        name="short_desc"
                        rows="4"
                        minlength="10"
                        maxlength="1000"
                        required
                        placeholder="Briefly explain what the project is and why it was developed."
                        class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                    ><?= e($form['short_desc']) ?></textarea>

                </div>

                <div class="md:col-span-2">

                    <?php project_switch(
                        'show_overview',
                        'Show Overview',
                        'Display project introduction on details page.',
                        $details['show_overview']
                    ); ?>

                </div>

            </div>

        </section>

        <!-- Client and Project Information -->

        <section class="bg-[#111] border border-white/10 rounded-3xl p-5 md:p-7">

            <h2 class="text-xl font-bold text-yellow-500 mb-5">
                2. Client & Project Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">

                <?php project_switch(
                    'show_client',
                    'Show Client Name',
                    'Show the client/company name publicly.',
                    $details['show_client']
                ); ?>

                <?php project_switch(
                    'show_project_info',
                    'Show Project Information',
                    'Show your role, duration and platform.',
                    $details['show_project_info'],
                    'project-info-fields'
                ); ?>

            </div>

            <div
                id="project-info-fields"
                class="grid grid-cols-1 md:grid-cols-3 gap-5"
            >

                <div>

                    <label
                        for="project_role"
                        class="block text-sm text-gray-400 mb-2"
                    >
                        Your role
                    </label>

                    <input
                        id="project_role"
                        name="project_role"
                        type="text"
                        maxlength="150"
                        value="<?= e($details['role']) ?>"
                        placeholder="Lead Developer"
                        class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                    >

                </div>

                <div>

                    <label
                        for="project_duration"
                        class="block text-sm text-gray-400 mb-2"
                    >
                        Project duration
                    </label>

                    <input
                        id="project_duration"
                        name="project_duration"
                        type="text"
                        maxlength="150"
                        value="<?= e($details['duration']) ?>"
                        placeholder="3 months"
                        class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                    >

                </div>

                <div>

                    <label
                        for="project_platform"
                        class="block text-sm text-gray-400 mb-2"
                    >
                        Platform
                    </label>

                    <input
                        id="project_platform"
                        name="project_platform"
                        type="text"
                        maxlength="150"
                        value="<?= e($details['platform']) ?>"
                        placeholder="Android, iOS, Web"
                        class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                    >

                </div>

            </div>

        </section>

        <!-- Case Study -->

        <section class="bg-[#111] border border-white/10 rounded-3xl p-5 md:p-7">

            <h2 class="text-xl font-bold text-yellow-500 mb-5">
                3. Complete Case Study
            </h2>

            <?php project_switch(
                'show_case_study',
                'Show Full Case Study',
                'Display complete project details.',
                $details['show_case_study'],
                'case-study-fields'
            ); ?>

            <div
                id="case-study-fields"
                class="mt-5"
            >

                <label
                    for="case_study_text"
                    class="block text-sm text-gray-400 mb-2"
                >
                    Complete project description
                </label>

                <textarea
                    id="case_study_text"
                    name="case_study_text"
                    rows="12"
                    maxlength="10000"
                    placeholder="Explain the complete project, workflow, important modules and development process."
                    class="w-full bg-black border border-white/10 rounded-xl p-3.5 leading-7 outline-none focus:border-yellow-500"
                ><?= e($form['case_study_text']) ?></textarea>

            </div>

        </section>

        <!-- Challenge -->

        <section class="bg-[#111] border border-white/10 rounded-3xl p-5 md:p-7">

            <?php project_switch(
                'show_challenge',
                'Show Project Challenge',
                'Explain the main client or technical problem.',
                $details['show_challenge'],
                'challenge-fields'
            ); ?>

            <div
                id="challenge-fields"
                class="mt-5"
            >

                <label
                    for="challenge"
                    class="block text-sm text-gray-400 mb-2"
                >
                    Project challenge
                </label>

                <textarea
                    id="challenge"
                    name="challenge"
                    rows="7"
                    maxlength="5000"
                    placeholder="What problem or challenge did the project have?"
                    class="w-full bg-black border border-white/10 rounded-xl p-3.5 leading-7 outline-none focus:border-yellow-500"
                ><?= e($details['challenge']) ?></textarea>

            </div>

        </section>

        <!-- Solution -->

        <section class="bg-[#111] border border-white/10 rounded-3xl p-5 md:p-7">

            <?php project_switch(
                'show_solution',
                'Show Your Solution',
                'Explain how you solved the project challenge.',
                $details['show_solution'],
                'solution-fields'
            ); ?>

            <div
                id="solution-fields"
                class="mt-5"
            >

                <label
                    for="solution"
                    class="block text-sm text-gray-400 mb-2"
                >
                    Your solution
                </label>

                <textarea
                    id="solution"
                    name="solution"
                    rows="7"
                    maxlength="5000"
                    placeholder="Explain your approach and how you solved the problem."
                    class="w-full bg-black border border-white/10 rounded-xl p-3.5 leading-7 outline-none focus:border-yellow-500"
                ><?= e($details['solution']) ?></textarea>

            </div>

        </section>

        <!-- Features -->

        <section class="bg-[#111] border border-white/10 rounded-3xl p-5 md:p-7">

            <?php project_switch(
                'show_features',
                'Show Key Features',
                'Display the important features as cards.',
                $details['show_features'],
                'feature-fields'
            ); ?>

            <div
                id="feature-fields"
                class="mt-5"
            >

                <label
                    for="key_features"
                    class="block text-sm text-gray-400 mb-2"
                >
                    Key features
                </label>

                <textarea
                    id="key_features"
                    name="key_features"
                    rows="5"
                    maxlength="5000"
                    placeholder="Video Call, Real-time Chat, Push Notification, Admin Panel"
                    class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                ><?= e(
                    project_feature_input(
                        $details['key_features']
                    )
                ) ?></textarea>

                <p class="text-xs text-gray-500 mt-2">
                    Separate features using commas.
                </p>

            </div>

        </section>

        <!-- Results -->

        <section class="bg-[#111] border border-white/10 rounded-3xl p-5 md:p-7">

            <?php project_switch(
                'show_results',
                'Show Project Result',
                'Display the outcome or business result.',
                $details['show_results'],
                'result-fields'
            ); ?>

            <div
                id="result-fields"
                class="mt-5"
            >

                <label
                    for="result"
                    class="block text-sm text-gray-400 mb-2"
                >
                    Project result
                </label>

                <textarea
                    id="result"
                    name="result"
                    rows="7"
                    maxlength="5000"
                    placeholder="Explain the final result, performance improvement or client benefit."
                    class="w-full bg-black border border-white/10 rounded-xl p-3.5 leading-7 outline-none focus:border-yellow-500"
                ><?= e($details['result']) ?></textarea>

            </div>

        </section>

        <!-- Technologies -->

        <section class="bg-[#111] border border-white/10 rounded-3xl p-5 md:p-7">

            <?php project_switch(
                'show_technologies',
                'Show Technologies',
                'Display the technology stack publicly.',
                $details['show_technologies'],
                'technology-fields'
            ); ?>

            <div
                id="technology-fields"
                class="mt-5"
            >

                <label
                    for="technologies"
                    class="block text-sm text-gray-400 mb-2"
                >
                    Technologies used
                </label>

                <input
                    id="technologies"
                    name="technologies"
                    type="text"
                    maxlength="1000"
                    value="<?= e(
                        project_technology_input(
                            $technologies
                        )
                    ) ?>"
                    placeholder="Flutter, Firebase, PHP, MySQL"
                    class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                >

            </div>

        </section>

        <!-- Project Links -->

        <section class="bg-[#111] border border-white/10 rounded-3xl p-5 md:p-7">

            <h2 class="text-xl font-bold text-yellow-500 mb-5">
                4. Project Links
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>

                    <?php project_switch(
                        'show_live_url',
                        'Show Live Project Button',
                        'Allow clients to open the live project.',
                        $details['show_live_url'],
                        'live-url-field'
                    ); ?>

                    <div
                        id="live-url-field"
                        class="mt-4"
                    >

                        <input
                            name="project_url"
                            type="url"
                            maxlength="2048"
                            value="<?= e($form['project_url']) ?>"
                            placeholder="https://example.com"
                            class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                        >

                    </div>

                </div>

                <div>

                    <?php project_switch(
                        'show_github_url',
                        'Show GitHub Button',
                        'Show the source repository publicly.',
                        $details['show_github_url'],
                        'github-url-field'
                    ); ?>

                    <div
                        id="github-url-field"
                        class="mt-4"
                    >

                        <input
                            name="github_url"
                            type="url"
                            maxlength="2048"
                            value="<?= e($form['github_url']) ?>"
                            placeholder="https://github.com/..."
                            class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                        >

                    </div>

                </div>

            </div>

        </section>

        <!-- Testimonial -->

        <section class="bg-[#111] border border-white/10 rounded-3xl p-5 md:p-7">

            <?php project_switch(
                'show_testimonial',
                'Show Client Testimonial',
                'Display client feedback on the details page.',
                $details['show_testimonial'],
                'testimonial-fields'
            ); ?>

            <div
                id="testimonial-fields"
                class="mt-5 grid grid-cols-1 gap-5"
            >

                <textarea
                    name="testimonial"
                    rows="6"
                    maxlength="3000"
                    placeholder="Write the client feedback..."
                    class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                ><?= e($details['testimonial']) ?></textarea>

                <input
                    name="testimonial_author"
                    type="text"
                    maxlength="150"
                    value="<?= e($details['testimonial_author']) ?>"
                    placeholder="Client name or company"
                    class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                >

            </div>

        </section>

        <!-- Gallery Control -->

        <section class="bg-[#111] border border-white/10 rounded-3xl p-5 md:p-7">

            <h2 class="text-xl font-bold text-yellow-500 mb-5">
                5. Screenshots, Videos & Gallery
            </h2>

            <?php project_switch(
                'show_gallery',
                'Show Project Gallery',
                'Display multiple screenshots and videos to clients.',
                $details['show_gallery']
            ); ?>

            <div class="mt-5">

                <label
                    for="thumbnail"
                    class="block text-sm text-gray-400 mb-2"
                >
                    Custom cover image URL/path
                </label>

                <input
                    id="thumbnail"
                    name="thumbnail"
                    type="text"
                    maxlength="2048"
                    value="<?= e($form['thumbnail']) ?>"
                    placeholder="Optional — first uploaded image will be the cover"
                    class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                >

            </div>

            <?php if (
                $isEdit &&
                $existingMedia !== []
            ): ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mt-7">

                    <?php foreach (
                        $existingMedia
                        as $index => $item
                    ): ?>

                        <div class="bg-black border border-white/10 rounded-xl overflow-hidden">

                            <div class="h-44 flex items-center justify-center overflow-hidden">

                                <?php if ($item['type'] === 'image'): ?>

                                    <img
                                        src="<?= e(
                                            project_admin_media_url(
                                                $item['url']
                                            )
                                        ) ?>"
                                        alt=""
                                        class="w-full h-full object-cover"
                                    >

                                <?php elseif ($item['type'] === 'video'): ?>

                                    <video
                                        src="<?= e(
                                            project_admin_media_url(
                                                $item['url']
                                            )
                                        ) ?>"
                                        controls
                                        class="w-full h-full object-cover"
                                    ></video>

                                <?php else: ?>

                                    <i class="ri-youtube-fill text-6xl text-red-500"></i>

                                <?php endif; ?>

                            </div>

                            <div class="p-4 space-y-4">

                                <label class="flex items-center gap-2 text-red-400">

                                    <input
                                        type="checkbox"
                                        name="remove_media[]"
                                        value="<?= (int) $index ?>"
                                    >

                                    Remove screenshot/media

                                </label>

                                <input
                                    type="file"
                                    name="replace_media[<?= (int) $index ?>]"
                                    accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm"
                                    class="w-full text-xs text-gray-400"
                                >

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-7">

                <div>

                    <label class="block text-sm text-gray-400 mb-2">
                        Upload multiple screenshots/videos
                    </label>

                    <input
                        type="file"
                        name="media_files[]"
                        multiple
                        accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm"
                        class="w-full bg-black border border-white/10 rounded-xl p-3 text-sm text-gray-400 file:bg-yellow-500 file:text-black file:border-0 file:rounded-lg file:px-4 file:py-2 file:font-bold"
                    >

                    <p class="text-xs text-gray-500 mt-2">
                        Select multiple files together. Maximum 15 MB per file.
                    </p>

                </div>

                <div>

                    <label class="block text-sm text-gray-400 mb-2">
                        External image/video/YouTube URLs
                    </label>

                    <textarea
                        name="media_urls_text"
                        rows="6"
                        placeholder="One URL per line"
                        class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                    ><?= e($mediaUrlsText) ?></textarea>

                </div>

            </div>

        </section>

        <!-- Publish Controls -->

        <section class="bg-[#111] border border-white/10 rounded-3xl p-5 md:p-7">

            <h2 class="text-xl font-bold text-yellow-500 mb-5">
                6. Publish Controls
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                <label class="flex items-center justify-between gap-3 bg-black border border-white/10 rounded-xl p-4 cursor-pointer">

                    <span>
                        <strong class="block">Published</strong>
                        <small class="text-gray-500">Show in portfolio.</small>
                    </span>

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        <?= $form['is_active'] ? 'checked' : '' ?>
                        class="w-5 h-5 accent-yellow-500"
                    >

                </label>

                <label class="flex items-center justify-between gap-3 bg-black border border-white/10 rounded-xl p-4 cursor-pointer">

                    <span>
                        <strong class="block">Featured</strong>
                        <small class="text-gray-500">Highlight this project.</small>
                    </span>

                    <input
                        type="checkbox"
                        name="is_featured"
                        value="1"
                        <?= $form['is_featured'] ? 'checked' : '' ?>
                        class="w-5 h-5 accent-yellow-500"
                    >

                </label>

                <div>

                    <label class="block text-sm text-gray-400 mb-2">
                        Display priority
                    </label>

                    <input
                        name="sort_order"
                        type="number"
                        min="0"
                        max="9999"
                        value="<?= (int) $form['sort_order'] ?>"
                        class="w-full bg-black border border-white/10 rounded-xl p-3.5 outline-none focus:border-yellow-500"
                    >

                </div>

            </div>

        </section>

        <div class="flex flex-col sm:flex-row gap-4 justify-end pb-10">

            <a
                href="projects.php"
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

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        const switches =
            document.querySelectorAll(
                '[data-controls]'
            );

        function syncSwitch(toggle) {
            const targetId =
                toggle.getAttribute(
                    'data-controls'
                );

            const target =
                document.getElementById(
                    targetId
                );

            if (!target) {
                return;
            }

            target.classList.toggle(
                'hidden',
                !toggle.checked
            );
        }

        switches.forEach(function (toggle) {
            syncSwitch(toggle);

            toggle.addEventListener(
                'change',
                function () {
                    syncSwitch(toggle);
                }
            );
        });
    }
);
</script>

<?php require __DIR__ . '/project_media_sorter.php'; ?>

</body>
</html>