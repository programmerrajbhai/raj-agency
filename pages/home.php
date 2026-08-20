<?php
// Database theke sob service/product ana
$stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC");
$posts = $stmt->fetchAll();

// YouTube video ID ber korar function
if (!function_exists('getYouTubeIdFeed')) {
    function getYouTubeIdFeed($url) {
        $regExp = '/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/';
        preg_match($regExp, $url, $match);
        return (isset($match[2]) && strlen($match[2]) === 11) ? $match[2] : null;
    }
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

<div class="relative w-full overflow-hidden selection:bg-accent selection:text-black font-sans bg-[#050505]">
    
    <!-- Hero Section Include -->
    <?php include 'includes/hero.php'; ?>

    <!-- Newsfeed Section -->
    <section id="newsfeed-section" class="py-20 bg-[#0a0a0a] min-h-screen relative z-10">
        <div class="max-w-[600px] mx-auto px-4">
            
            <div class="text-center mb-12">
                <h2 class="text-3xl font-display font-bold text-white flex items-center justify-center gap-3">
                    <i class="ri-flashlight-fill text-accent"></i> Latest Updates
                </h2>
                <p class="text-gray-400 text-sm mt-2">Explore our newest scripts, apps, and features.</p>
            </div>

            <?php foreach($posts as $post): ?>
                <?php 
                    $features = json_decode($post['features'], true);
                    $media_gallery = isset($features['media_gallery']) ? $features['media_gallery'] : [];
                    
                    if(empty($media_gallery) && !empty($post['thumbnail'])) {
                        $media_gallery[] = ['type' => 'image', 'url' => $post['thumbnail']];
                    }
                    $mediaCount = count($media_gallery);

                    // Shorten description for "See more" functionality
                    $fullDesc = htmlspecialchars($post['short_desc']);
                    $shortDesc = strlen($fullDesc) > 160 ? substr($fullDesc, 0, 160) . '...' : $fullDesc;
                    $hasMore = strlen($fullDesc) > 160;
                ?>

                <article class="bg-[#111] rounded-2xl shadow-2xl border border-white/10 mb-10 overflow-hidden font-sans hover:border-white/20 transition-colors duration-300">
                    
                    <!-- Header -->
                    <div class="p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3 group">
                            <a href="index.php?page=service-details&id=<?php echo $post['id']; ?>" class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center text-black font-black flex-shrink-0 shadow-[0_0_15px_rgba(244,185,11,0.2)] hover:scale-105 transition transform">
                                R
                            </a>
                            <div>
                                <a href="index.php?page=service-details&id=<?php echo $post['id']; ?>" class="text-white font-bold text-[16px] hover:text-accent transition-colors flex items-center gap-1">
                                    Raj Agency <i class="ri-verified-badge-fill text-blue-500 text-[14px]"></i>
                                </a>
                                <div class="flex items-center gap-1.5 text-[12px] text-gray-400 mt-0.5">
                                    <a href="index.php?page=service-details&id=<?php echo $post['id']; ?>" class="font-medium tracking-wide hover:underline">Sponsored</a>
                                    <span>•</span>
                                    <i class="ri-earth-fill"></i>
                                </div>
                            </div>
                        </div>
                        <a href="index.php?page=service-details&id=<?php echo $post['id']; ?>" class="text-gray-400 hover:text-white hover:bg-white/10 w-10 h-10 rounded-full transition flex items-center justify-center" title="View Full Post">
                            <i class="ri-external-link-line text-xl"></i>
                        </a>
                    </div>

                    <!-- Description -->
                    <div class="px-5 pb-4">
                        <a href="index.php?page=service-details&id=<?php echo $post['id']; ?>" class="block hover:opacity-80 transition">
                            <h2 class="text-white font-bold text-[19px] mb-2 leading-tight"><?php echo htmlspecialchars($post['title']); ?></h2>
                        </a>

                        <p class="text-gray-300 text-[15px] mb-3 whitespace-pre-wrap leading-relaxed opacity-90">
                            <?php echo $shortDesc; ?>
                            <?php if($hasMore): ?>
                                <a href="index.php?page=service-details&id=<?php echo $post['id']; ?>" class="text-gray-400 font-bold hover:text-white hover:underline ml-1">See more</a>
                            <?php endif; ?>
                        </p>
                        
                        <?php if(!empty($features['tech'])): ?>
                            <div class="flex flex-wrap gap-2 mb-2 mt-2">
                                <?php foreach($features['tech'] as $tech): ?>
                                    <span class="px-2.5 py-1 bg-blue-500/10 text-blue-400 text-[12px] font-bold rounded-md uppercase tracking-wider hover:bg-blue-500/20 cursor-pointer transition">
                                        <?php echo htmlspecialchars($tech); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Media Slider -->
                    <?php if($mediaCount > 0): ?>
                        <div class="relative w-full bg-black group border-y border-white/5">
                            <div class="swiper post-swiper w-full h-[400px] md:h-[450px]">
                                <div class="swiper-wrapper">
                                    <?php foreach($media_gallery as $index => $media): ?>
                                        <div class="swiper-slide w-full h-full flex items-center justify-center bg-[#050505]">
                                            <?php if($media['type'] == 'youtube'): 
                                                $ytId = getYouTubeIdFeed($media['url']);
                                            ?>
                                                <div class="relative w-full h-full cursor-pointer" onclick="window.open('<?php echo htmlspecialchars($media['url']); ?>', '_blank')">
                                                    <img src="https://img.youtube.com/vi/<?php echo $ytId; ?>/maxresdefault.jpg" class="w-full h-full object-cover opacity-80 hover:opacity-100 transition duration-500" onerror="this.src='https://img.youtube.com/vi/<?php echo $ytId; ?>/hqdefault.jpg'">
                                                    <div class="absolute inset-0 flex items-center justify-center">
                                                        <div class="w-16 h-16 bg-red-600/90 rounded-full flex items-center justify-center shadow-[0_0_20px_rgba(255,0,0,0.5)] backdrop-blur-sm transform transition hover:scale-110">
                                                            <i class="ri-play-fill text-4xl text-white ml-1"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php elseif($media['type'] == 'video'): ?>
                                                <video src="<?php echo htmlspecialchars(strpos($media['url'], 'http') === 0 ? $media['url'] : $media['url']); ?>" class="w-full h-full object-cover" controls playsinline preload="metadata"></video>
                                            <?php else: ?>
                                                <img src="<?php echo htmlspecialchars(strpos($media['url'], 'http') === 0 ? $media['url'] : $media['url']); ?>" class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition">
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php if($mediaCount > 1): ?>
                                    <div class="swiper-pagination"></div>
                                    <div class="swiper-button-next custom-swiper-btn"></div>
                                    <div class="swiper-button-prev custom-swiper-btn"></div>
                                    <div class="absolute top-4 right-4 bg-black/60 backdrop-blur-md text-white text-xs font-bold px-3 py-1.5 rounded-full z-10 border border-white/10">
                                        <i class="ri-gallery-fill mr-1"></i> 1 / <?php echo $mediaCount; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Actions & Price -->
                    <div class="p-4 bg-gradient-to-r from-white/5 to-transparent border-b border-white/5">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-accent text-[11px] uppercase tracking-[0.2em] font-black block mb-1">
                                    <?php echo ($post['file_type'] == 'app') ? '📱 Mobile App' : '💻 PHP Script'; ?>
                                </span>
                                <span class="text-white font-bold text-2xl drop-shadow-md">$<?php echo number_format($post['price_basic'], 2); ?></span>
                            </div>
                            
                            <form action="index.php?page=cart_action" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo $post['id']; ?>">
                                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($post['title']); ?>">
                                <input type="hidden" name="product_price" value="<?php echo $post['price_basic']; ?>">
                                
                                <button type="submit" class="bg-white text-black hover:bg-accent px-6 py-2.5 rounded-full font-bold flex items-center gap-2 transition-all transform hover:scale-105 shadow-lg">
                                    <i class="ri-shopping-bag-3-fill"></i> Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Engagement (Like, Comment, Share) -->
                    <div class="px-2 py-3">
                        <div class="flex justify-between items-center text-gray-400 text-[13px] px-3 pb-3 mb-1 border-b border-white/5">
                            <div class="flex items-center gap-1.5 cursor-pointer hover:text-white transition">
                                <div class="flex -space-x-1">
                                    <div class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center ring-2 ring-[#111] z-20"><i class="ri-thumb-up-fill text-white text-[10px]"></i></div>
                                    <div class="w-5 h-5 bg-red-500 rounded-full flex items-center justify-center ring-2 ring-[#111] z-10"><i class="ri-heart-fill text-white text-[10px]"></i></div>
                                </div>
                                <span class="font-medium ml-1 text-white"><?php echo number_format(rand(100, 2500)); ?></span>
                            </div>
                            <div class="flex gap-4">
                                <a href="index.php?page=service-details&id=<?php echo $post['id']; ?>" class="hover:text-white hover:underline cursor-pointer transition"><?php echo rand(10, 150); ?> comments</a>
                                <span class="hover:text-white cursor-pointer transition"><?php echo rand(5, 50); ?> shares</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between px-1 pt-1">
                            <button class="flex-1 flex items-center justify-center gap-2 text-gray-400 hover:text-white hover:bg-white/5 py-2 rounded-xl font-semibold transition text-[15px]">
                                <i class="ri-thumb-up-line text-xl"></i> Like
                            </button>
                            <a href="index.php?page=service-details&id=<?php echo $post['id']; ?>" class="flex-1 flex items-center justify-center gap-2 text-gray-400 hover:text-white hover:bg-white/5 py-2 rounded-xl font-semibold transition text-[15px]">
                                <i class="ri-chat-3-line text-xl"></i> Comment
                            </a>
                            <button class="flex-1 flex items-center justify-center gap-2 text-gray-400 hover:text-white hover:bg-white/5 py-2 rounded-xl font-semibold transition text-[15px]">
                                <i class="ri-share-forward-line text-xl"></i> Share
                            </button>
                        </div>
                    </div>

                </article>
            <?php endforeach; ?>

        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var swipers = document.querySelectorAll('.post-swiper');
        
        swipers.forEach(function(swiperContainer) {
            var counterBadge = swiperContainer.querySelector('.absolute.top-4');

            new Swiper(swiperContainer, {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: false,
                pagination: {
                    el: swiperContainer.querySelector('.swiper-pagination'),
                    clickable: true,
                    dynamicBullets: true,
                },
                navigation: {
                    nextEl: swiperContainer.querySelector('.swiper-button-next'),
                    prevEl: swiperContainer.querySelector('.swiper-button-prev'),
                },
                on: {
                    slideChange: function () {
                        if(counterBadge) {
                            var currentSlide = this.realIndex + 1;
                            var totalSlides = this.slides.length;
                            counterBadge.innerHTML = `<i class="ri-gallery-fill mr-1"></i> ${currentSlide} / ${totalSlides}`;
                        }
                    }
                }
            });
        });
    });
</script>

<style>
    .custom-swiper-btn {
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        width: 35px !important;
        height: 35px !important;
        border-radius: 50%;
        color: white !important;
        border: 1px solid rgba(255, 255, 255, 0.2);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .custom-swiper-btn::after {
        font-size: 14px !important;
        font-weight: bold;
    }
    .post-swiper:hover .custom-swiper-btn {
        opacity: 1; 
    }
    .swiper-pagination-bullet {
        background: rgba(255, 255, 255, 0.5) !important;
        opacity: 1 !important;
    }
    .swiper-pagination-bullet-active {
        background: #F4B90B !important; 
        transform: scale(1.2);
    }
</style>