<?php
// ডাটাবেস থেকে সব সার্ভিস/প্রোডাক্ট আনা
$stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC");
$posts = $stmt->fetchAll();

// ইউটিউব ভিডিও আইডি বের করার ফাংশন
if (!function_exists('getYouTubeIdFeed')) {
    function getYouTubeIdFeed($url) {
        $regExp = '/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/';
        preg_match($regExp, $url, $match);
        return (isset($match[2]) && strlen($match[2]) === 11) ? $match[2] : null;
    }
}
?>

<main class="pt-28 pb-20 min-h-screen bg-[#18191a] font-sans selection:bg-yellow-500 selection:text-black">
    <div class="max-w-[680px] mx-auto px-4">
        
        <!-- Create Post Box (Dummy) -->
        <div class="bg-[#242526] rounded-xl shadow-md border border-[#3e4042] p-4 mb-6 hidden md:block">
            <div class="flex gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center text-black font-black flex-shrink-0">R</div>
                <div class="flex-1 bg-[#3a3b3c] rounded-full px-4 py-2.5 text-gray-400 cursor-pointer hover:bg-[#4e4f50] transition flex items-center text-[15px]">
                    What's new in Raj Agency?
                </div>
            </div>
            <div class="border-t border-[#3e4042] mt-4 pt-3 flex justify-between px-2">
                <div class="flex items-center gap-2 text-gray-400 hover:bg-[#3a3b3c] px-4 py-2 rounded-lg cursor-pointer transition">
                    <i class="ri-video-add-fill text-[#f3425f] text-xl"></i> <span class="text-sm font-semibold">Live Demo</span>
                </div>
                <div class="flex items-center gap-2 text-gray-400 hover:bg-[#3a3b3c] px-4 py-2 rounded-lg cursor-pointer transition">
                    <i class="ri-image-add-fill text-[#45bd62] text-xl"></i> <span class="text-sm font-semibold">Photo/video</span>
                </div>
            </div>
        </div>

        <?php foreach($posts as $post): ?>
            <?php 
                $features = json_decode($post['features'], true);
                $media_gallery = isset($features['media_gallery']) ? $features['media_gallery'] : [];
                
                // যদি নতুন সিস্টেমে মিডিয়া না থাকে, তবে ডিফল্ট থাম্বনেল ব্যবহার করবে
                if(empty($media_gallery) && !empty($post['thumbnail'])) {
                    $media_gallery[] = ['type' => 'image', 'url' => $post['thumbnail']];
                }

                $mediaCount = count($media_gallery);
                $gridClass = 'grid-cols-1';
                if ($mediaCount == 2) $gridClass = 'grid-cols-2';
                elseif ($mediaCount >= 3) $gridClass = 'grid-cols-2 grid-rows-2';

                // Shorten description for "See more" functionality
                $fullDesc = htmlspecialchars($post['short_desc']);
                $shortDesc = strlen($fullDesc) > 160 ? substr($fullDesc, 0, 160) . '...' : $fullDesc;
                $hasMore = strlen($fullDesc) > 160;
            ?>

            <article class="bg-[#242526] rounded-xl shadow-lg border border-[#3e4042] mb-6 overflow-hidden">
                
                <!-- Post Header -->
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <a href="index.php?page=service-details&id=<?php echo $post['id']; ?>" class="w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center text-black font-black flex-shrink-0 shadow-md hover:scale-105 transition transform">
                            R
                        </a>
                        <div>
                            <a href="index.php?page=service-details&id=<?php echo $post['id']; ?>" class="text-white font-bold text-[15px] hover:underline cursor-pointer leading-tight">Raj Agency</a>
                            <div class="flex items-center gap-1 text-[13px] text-gray-400 mt-0.5">
                                <span>Sponsored</span>
                                <span>•</span>
                                <i class="ri-earth-fill text-xs"></i>
                            </div>
                        </div>
                    </div>
                    <button class="text-gray-400 hover:bg-[#3a3b3c] w-9 h-9 rounded-full transition flex items-center justify-center">
                        <i class="ri-more-fill text-xl"></i>
                    </button>
                </div>

                <!-- Post Text & Tags -->
                <div class="px-4 pb-3">
                    <a href="index.php?page=service-details&id=<?php echo $post['id']; ?>" class="hover:opacity-80 transition block">
                        <h2 class="text-white font-bold text-lg mb-1"><?php echo htmlspecialchars($post['title']); ?></h2>
                    </a>
                    
                    <p class="text-gray-300 text-[15px] mb-3 whitespace-pre-wrap">
                        <?php echo $shortDesc; ?>
                        <?php if($hasMore): ?>
                            <a href="index.php?page=service-details&id=<?php echo $post['id']; ?>" class="text-gray-400 font-bold hover:text-white hover:underline ml-1">See more</a>
                        <?php endif; ?>
                    </p>
                    
                    <?php if(!empty($features['tech'])): ?>
                        <div class="flex flex-wrap gap-2 mb-2">
                            <?php foreach($features['tech'] as $tech): ?>
                                <span class="text-[#2d88ff] text-[13px] font-semibold hover:underline cursor-pointer">#<?php echo str_replace(' ', '', $tech); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Dynamic Media Grid (Facebook Style) -->
                <?php if($mediaCount > 0): ?>
                    <div class="grid gap-[2px] bg-[#3e4042] <?php echo $gridClass; ?> <?php echo ($mediaCount >= 3) ? 'h-[400px]' : ''; ?>">
                        <?php 
                        $displayLimit = min($mediaCount, 4);
                        for($i = 0; $i < $displayLimit; $i++): 
                            $media = $media_gallery[$i];
                            $isLast = ($i == 3 && $mediaCount > 4);
                            
                            // 3 items grid special layout (First image takes full width on top)
                            $itemClass = "relative bg-black overflow-hidden flex items-center justify-center group";
                            if($mediaCount == 3 && $i == 0) $itemClass .= " col-span-2 h-[200px]";
                            elseif($mediaCount == 3) $itemClass .= " h-[198px]";
                            elseif($mediaCount >= 4) $itemClass .= " h-[198px]";
                            else $itemClass .= " min-h-[250px]";
                        ?>
                            <div class="<?php echo $itemClass; ?>" onclick="window.location.href='index.php?page=service-details&id=<?php echo $post['id']; ?>'">
                                
                                <?php if($media['type'] == 'youtube'): 
                                    $ytId = getYouTubeIdFeed($media['url']);
                                ?>
                                    <img src="https://img.youtube.com/vi/<?php echo $ytId; ?>/maxresdefault.jpg" class="w-full h-full object-cover opacity-90 cursor-pointer group-hover:opacity-100 transition" onerror="this.src='https://img.youtube.com/vi/<?php echo $ytId; ?>/hqdefault.jpg'">
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <div class="w-16 h-16 bg-black/60 rounded-full flex items-center justify-center backdrop-blur-sm border border-white/20 group-hover:scale-110 transition transform">
                                            <i class="ri-play-fill text-4xl text-white ml-1"></i>
                                        </div>
                                    </div>

                                <?php elseif($media['type'] == 'video'): ?>
                                    <video src="<?php echo htmlspecialchars($media['url']); ?>" class="w-full h-full object-cover cursor-pointer" controls preload="metadata" onclick="event.stopPropagation()"></video>
                                
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($media['url']); ?>" class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition transform group-hover:scale-105 duration-500">
                                <?php endif; ?>

                                <!-- +X More Overlay for 4+ items -->
                                <?php if($isLast): ?>
                                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center cursor-pointer hover:bg-black/70 transition backdrop-blur-[2px]">
                                        <span class="text-white text-4xl font-bold">+<?php echo $mediaCount - 4; ?></span>
                                    </div>
                                <?php endif; ?>

                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

                <!-- Price & Cart Action -->
                <div class="bg-[#3a3b3c]/30 px-4 py-3 flex items-center justify-between border-b border-[#3e4042]">
                    <div>
                        <span class="text-gray-400 text-[11px] uppercase tracking-wider font-bold block mb-0.5">
                            <?php echo ($post['file_type'] == 'app') ? '📱 Mobile App' : '💻 PHP Script'; ?>
                        </span>
                        <span class="text-white font-bold text-xl">$<?php echo number_format($post['price_basic'], 2); ?></span>
                    </div>
                    
                    <form action="index.php?page=cart_action" method="POST" onclick="event.stopPropagation()">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $post['id']; ?>">
                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($post['title']); ?>">
                        <input type="hidden" name="product_price" value="<?php echo $post['price_basic']; ?>">
                        
                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-400 text-black px-6 py-2 rounded-lg font-bold flex items-center gap-2 transition active:scale-95 shadow-md">
                            <i class="ri-shopping-cart-2-fill"></i> Buy Now
                        </button>
                    </form>
                </div>

                <!-- Engagement Stats -->
                <div class="px-4 py-2">
                    <div class="flex justify-between items-center text-gray-400 text-[13px] border-b border-[#3e4042] pb-2 mb-1">
                        <div class="flex items-center gap-1">
                            <div class="w-5 h-5 bg-[#2d88ff] rounded-full flex items-center justify-center ring-2 ring-[#242526] z-10"><i class="ri-thumb-up-fill text-white text-[10px]"></i></div>
                            <div class="w-5 h-5 bg-[#f3425f] rounded-full flex items-center justify-center ring-2 ring-[#242526] -ml-2"><i class="ri-heart-fill text-white text-[10px]"></i></div>
                            <!-- Dummy Likes Count -->
                            <span class="ml-1 hover:underline cursor-pointer likes-count"><?php echo number_format(rand(100, 2500)); ?></span>
                        </div>
                        <div class="flex gap-3">
                            <!-- Removed Comment Count -->
                            <span class="hover:underline cursor-pointer"><?php echo rand(5, 50); ?> shares</span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-1">
                        <button onclick="toggleLike(this)" class="flex-1 flex items-center justify-center gap-2 text-gray-400 hover:bg-[#3a3b3c] py-1.5 rounded-lg font-semibold transition text-[15px]">
                            <i class="ri-thumb-up-line text-xl"></i> <span>Like</span>
                        </button>
                        
                        <!-- Removed Comment Button -->

                        <button onclick="sharePost('<?php echo htmlspecialchars(addslashes($post['title'])); ?>', '<?php echo $post['id']; ?>')" class="flex-1 flex items-center justify-center gap-2 text-gray-400 hover:bg-[#3a3b3c] py-1.5 rounded-lg font-semibold transition text-[15px]">
                            <i class="ri-share-forward-line text-xl"></i> <span>Share</span>
                        </button>
                    </div>
                </div>

            </article>
        <?php endforeach; ?>

    </div>
</main>

<!-- Notification Toast for Copying Link (PC Fallback) -->
<div id="toast" class="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded-full shadow-2xl transition-all duration-300 opacity-0 pointer-events-none translate-y-5 z-50 flex items-center gap-2 font-bold">
    <i class="ri-check-line text-xl"></i> Link copied to clipboard!
</div>

<script>
    // 1. Live Like Toggle (Dummy Visual Effect)
    function toggleLike(btn) {
        const icon = btn.querySelector('i');
        const text = btn.querySelector('span');
        
        if (btn.classList.contains('text-[#2d88ff]')) {
            btn.classList.remove('text-[#2d88ff]');
            btn.classList.add('text-gray-400');
            icon.className = 'ri-thumb-up-line text-xl';
        } else {
            btn.classList.remove('text-gray-400');
            btn.classList.add('text-[#2d88ff]');
            icon.className = 'ri-thumb-up-fill text-xl';
        }
    }

    // 2. Real Share System (Web Share API / Copy Link)
    function sharePost(title, id) {
        // Create full URL to the specific product
        const currentUrl = window.location.origin + window.location.pathname.replace('feed', 'index.php') + '?page=service-details&id=' + id;
        
        // Check if browser supports native sharing (Mobile devices mostly)
        if (navigator.share) {
            navigator.share({
                title: title,
                text: 'Check out this amazing digital product: ' + title,
                url: currentUrl
            }).catch((error) => console.log('Error sharing', error));
        } else {
            // Fallback for PC: Copy to clipboard
            navigator.clipboard.writeText(currentUrl).then(() => {
                showToast();
            }).catch(err => {
                console.error('Failed to copy link: ', err);
            });
        }
    }

    // Toast Notification for Fallback Share
    function showToast() {
        const toast = document.getElementById('toast');
        toast.classList.remove('opacity-0', 'translate-y-5', 'pointer-events-none');
        
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-5', 'pointer-events-none');
        }, 3000);
    }
</script>