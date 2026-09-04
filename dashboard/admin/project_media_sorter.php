<?php
declare(strict_types=1);

if (!isset($isEdit)) {
    return;
}
?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    if (typeof Sortable === 'undefined') {
        console.error('Gallery sorting library could not be loaded.');
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Existing gallery media
    |--------------------------------------------------------------------------
    */

    const removeInputs = Array.from(
        document.querySelectorAll(
            'input[name="remove_media[]"]'
        )
    );

    const existingCards = removeInputs
        .map(function (input) {
            return input.closest('div.bg-black');
        })
        .filter(Boolean);

    if (existingCards.length > 0) {
        const existingGrid =
            existingCards[0].parentElement;

        existingGrid.id =
            'sortable-existing-media';

        const helpBox =
            document.createElement('div');

        helpBox.className =
            'mt-6 mb-4 rounded-xl border ' +
            'border-yellow-500/30 bg-yellow-500/10 ' +
            'px-4 py-3 text-sm text-yellow-200';

        helpBox.innerHTML =
            '<strong>Arrange Gallery:</strong> ' +
            'Drag screenshots or videos into your preferred order. ' +
            'The first available image will automatically become the cover.';

        existingGrid.parentNode.insertBefore(
            helpBox,
            existingGrid
        );

        existingCards.forEach(function (card) {
            const removeInput =
                card.querySelector(
                    'input[name="remove_media[]"]'
                );

            const originalIndex =
                removeInput.value;

            const mediaType =
                card.querySelector('video')
                    ? 'video'
                    : card.querySelector(
                        '.ri-youtube-fill'
                    )
                        ? 'youtube'
                        : 'image';

            card.dataset.mediaIndex =
                originalIndex;

            card.dataset.mediaType =
                mediaType;

            card.classList.add(
                'project-sort-card',
                'transition',
                'duration-200'
            );

            /*
             * Submitted according to the current
             * drag-and-drop position.
             */
            const orderInput =
                document.createElement('input');

            orderInput.type = 'hidden';
            orderInput.name = 'media_order[]';
            orderInput.value = originalIndex;

            card.appendChild(orderInput);

            /*
             * Drag toolbar
             */
            const toolbar =
                document.createElement('div');

            toolbar.className =
                'flex items-center justify-between ' +
                'gap-3 border-b border-white/10 ' +
                'bg-[#171717] px-3 py-2';

            const positionText =
                document.createElement('span');

            positionText.className =
                'media-position text-xs ' +
                'font-bold text-gray-300';

            const rightSide =
                document.createElement('div');

            rightSide.className =
                'flex items-center gap-2';

            const coverBadge =
                document.createElement('span');

            coverBadge.className =
                'media-cover-badge hidden rounded-full ' +
                'bg-yellow-500 px-2 py-1 text-[10px] ' +
                'font-black text-black';

            coverBadge.textContent = 'COVER';

            const dragHandle =
                document.createElement('button');

            dragHandle.type = 'button';

            dragHandle.className =
                'media-drag-handle cursor-grab ' +
                'rounded-lg border border-white/10 ' +
                'bg-black px-3 py-1.5 text-xs ' +
                'font-bold text-gray-200 ' +
                'hover:border-yellow-500/50 ' +
                'hover:text-yellow-400 ' +
                'active:cursor-grabbing';

            dragHandle.innerHTML =
                '<i class="ri-drag-move-2-line mr-1"></i>' +
                'Drag';

            rightSide.appendChild(coverBadge);
            rightSide.appendChild(dragHandle);

            toolbar.appendChild(positionText);
            toolbar.appendChild(rightSide);

            card.insertBefore(
                toolbar,
                card.firstChild
            );

            removeInput.addEventListener(
                'change',
                function () {
                    card.classList.toggle(
                        'opacity-40',
                        removeInput.checked
                    );

                    card.classList.toggle(
                        'ring-1',
                        removeInput.checked
                    );

                    card.classList.toggle(
                        'ring-red-500',
                        removeInput.checked
                    );

                    updateExistingLabels();
                }
            );
        });

        function updateExistingLabels() {
            const cards = Array.from(
                existingGrid.children
            ).filter(function (card) {
                return card.classList.contains(
                    'project-sort-card'
                );
            });

            let visiblePosition = 0;
            let coverAssigned = false;

            cards.forEach(function (card) {
                const removeInput =
                    card.querySelector(
                        'input[name="remove_media[]"]'
                    );

                const position =
                    card.querySelector(
                        '.media-position'
                    );

                const coverBadge =
                    card.querySelector(
                        '.media-cover-badge'
                    );

                coverBadge.classList.add('hidden');

                if (removeInput.checked) {
                    position.textContent =
                        'Will be removed';

                    return;
                }

                visiblePosition++;

                position.textContent =
                    'Position ' +
                    visiblePosition +
                    ' • ' +
                    card.dataset.mediaType
                        .toUpperCase();

                if (
                    !coverAssigned &&
                    card.dataset.mediaType ===
                        'image'
                ) {
                    coverBadge.classList.remove(
                        'hidden'
                    );

                    coverAssigned = true;
                }
            });
        }

        new Sortable(existingGrid, {
            animation: 180,
            handle: '.media-drag-handle',
            draggable: '.project-sort-card',
            ghostClass: 'opacity-30',
            chosenClass: 'ring-2',
            dragClass: 'shadow-2xl',

            delay: 120,
            delayOnTouchOnly: true,
            touchStartThreshold: 4,

            forceFallback: true,
            fallbackOnBody: true,
            swapThreshold: 0.65,

            onSort: updateExistingLabels
        });

        updateExistingLabels();
    }

    /*
    |--------------------------------------------------------------------------
    | Newly selected screenshots/videos
    |--------------------------------------------------------------------------
    */

    const fileInput =
        document.querySelector(
            'input[name="media_files[]"]'
        );

    if (!fileInput) {
        return;
    }

    const previewGrid =
        document.createElement('div');

    previewGrid.id =
        'sortable-new-media';

    previewGrid.className =
        'mt-4 grid grid-cols-1 ' +
        'gap-3 sm:grid-cols-2';

    fileInput.parentElement.appendChild(
        previewGrid
    );

    let selectedFiles = [];
    let previewUrls = [];

    function createId() {
        return (
            Date.now().toString(36) +
            '-' +
            Math.random()
                .toString(36)
                .slice(2)
        );
    }

    function syncFileInput() {
        const orderedIds = Array.from(
            previewGrid.children
        ).map(function (card) {
            return card.dataset.fileId;
        });

        selectedFiles = orderedIds
            .map(function (id) {
                return selectedFiles.find(
                    function (item) {
                        return item.id === id;
                    }
                );
            })
            .filter(Boolean);

        try {
            const transfer =
                new DataTransfer();

            selectedFiles.forEach(
                function (item) {
                    transfer.items.add(
                        item.file
                    );
                }
            );

            fileInput.files =
                transfer.files;
        } catch (error) {
            console.warn(
                'Selected file order could not be updated.',
                error
            );
        }

        Array.from(
            previewGrid.children
        ).forEach(function (card, index) {
            card.querySelector(
                '.new-media-position'
            ).textContent =
                'New file ' + (index + 1);
        });
    }

    function renderNewFiles() {
        previewUrls.forEach(function (url) {
            URL.revokeObjectURL(url);
        });

        previewUrls = [];
        previewGrid.innerHTML = '';

        selectedFiles.forEach(function (item) {
            const card =
                document.createElement('div');

            card.dataset.fileId = item.id;

            card.className =
                'new-media-card overflow-hidden ' +
                'rounded-xl border border-white/10 ' +
                'bg-black';

            const toolbar =
                document.createElement('div');

            toolbar.className =
                'flex items-center justify-between ' +
                'gap-2 border-b border-white/10 ' +
                'bg-[#171717] px-3 py-2';

            const position =
                document.createElement('span');

            position.className =
                'new-media-position text-xs ' +
                'font-bold text-gray-300';

            const controls =
                document.createElement('div');

            controls.className =
                'flex items-center gap-2';

            const dragButton =
                document.createElement('button');

            dragButton.type = 'button';

            dragButton.className =
                'new-media-drag cursor-grab ' +
                'rounded-md border border-white/10 ' +
                'px-2 py-1 text-xs text-yellow-400';

            dragButton.innerHTML =
                '<i class="ri-drag-move-2-line"></i> Drag';

            const removeButton =
                document.createElement('button');

            removeButton.type = 'button';

            removeButton.className =
                'rounded-md border border-red-500/30 ' +
                'px-2 py-1 text-xs text-red-400';

            removeButton.innerHTML =
                '<i class="ri-delete-bin-line"></i>';

            removeButton.addEventListener(
                'click',
                function () {
                    selectedFiles =
                        selectedFiles.filter(
                            function (current) {
                                return (
                                    current.id !==
                                    item.id
                                );
                            }
                        );

                    renderNewFiles();
                    syncFileInput();
                }
            );

            controls.appendChild(
                dragButton
            );

            controls.appendChild(
                removeButton
            );

            toolbar.appendChild(position);
            toolbar.appendChild(controls);

            const isVideo =
                item.file.type.startsWith(
                    'video/'
                );

            const preview =
                document.createElement(
                    isVideo
                        ? 'video'
                        : 'img'
                );

            const previewUrl =
                URL.createObjectURL(
                    item.file
                );

            previewUrls.push(previewUrl);

            preview.src = previewUrl;

            preview.className =
                'h-32 w-full object-cover';

            if (isVideo) {
                preview.muted = true;
                preview.preload = 'metadata';
            }

            const filename =
                document.createElement('p');

            filename.className =
                'truncate px-3 py-2 ' +
                'text-xs text-gray-400';

            filename.textContent =
                item.file.name;

            card.appendChild(toolbar);
            card.appendChild(preview);
            card.appendChild(filename);

            previewGrid.appendChild(card);
        });

        syncFileInput();
    }

    fileInput.addEventListener(
        'change',
        function () {
            selectedFiles = Array.from(
                fileInput.files
            ).map(function (file) {
                return {
                    id: createId(),
                    file: file
                };
            });

            renderNewFiles();
        }
    );

    new Sortable(previewGrid, {
        animation: 180,
        handle: '.new-media-drag',
        draggable: '.new-media-card',
        ghostClass: 'opacity-30',

        delay: 120,
        delayOnTouchOnly: true,
        touchStartThreshold: 4,

        forceFallback: true,
        fallbackOnBody: true,

        onSort: syncFileInput
    });
});
</script>