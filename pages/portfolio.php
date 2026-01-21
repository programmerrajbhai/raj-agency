<?php
// ডাটাবেস থেকে সব সার্ভিস আনা
$stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC");
$services = $stmt->fetchAll();
?>

<main class="pt-32 pb-20 min-h-screen bg-[#030303] relative overflow-hidden selection:bg-accent selection:text-black">

    <div class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-accent/5 rounded-full blur-[120px] animate-blob"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-500/5 rounded-full blur-[120px] animate-blob animation-delay-2000"></div>
        <div class="absolute top-[20%] right-[20%] w-[20%] h-[20%] bg-blue-500/5 rounded-full blur-[100px] animate-blob animation-delay-4000"></div>
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150"></div>
    </div>

    <section class="max-w-7xl mx-auto px-6 mb-20 text-center relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-white/10 bg-white/5 backdrop-blur-md mb-6">
            <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-muted">Premium Marketplace</span>
        </div>
        
        <h2 class="font-display text-5xl md:text-8xl font-bold uppercase mb-8 leading-none tracking-tight">
            Digital <span class="text-transparent bg-clip-text bg-gradient-to-r from-accent via-yellow-200 to-accent animate-gradient-x">Assets.</span>
        </h2>
        
        <div class="inline-flex p-1.5 bg-white/5 border border-white/10 backdrop-blur-xl rounded-full gap-1 shadow-2xl relative z-20" id="filter-buttons">
            <button class="filter-btn active px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-300 relative group overflow-hidden" data-filter="all">
                <span class="absolute inset-0 bg-accent opacity-0 group-[.active]:opacity-100 transition-opacity duration-300"></span>
                <span class="relative z-10 text-muted group-[.active]:text-black group-hover:text-white group-[.active]:group-hover:text-black transition-colors">All</span>
            </button>
            <button class="filter-btn px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-300 relative group overflow-hidden" data-filter="web">
                <span class="absolute inset-0 bg-accent opacity-0 group-[.active]:opacity-100 transition-opacity duration-300"></span>
                <span class="relative z-10 text-muted group-[.active]:text-black group-hover:text-white group-[.active]:group-hover:text-black transition-colors">PHP Scripts</span>
            </button>
            <button class="filter-btn px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-300 relative group overflow-hidden" data-filter="app">
                <span class="absolute inset-0 bg-accent opacity-0 group-[.active]:opacity-100 transition-opacity duration-300"></span>
                <span class="relative z-10 text-muted group-[.active]:text-black group-hover:text-white group-[.active]:group-hover:text-black transition-colors">Apps</span>
            </button>
            <button class="filter-btn px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-300 relative group overflow-hidden" data-filter="ui">
                <span class="absolute inset-0 bg-accent opacity-0 group-[.active]:opacity-100 transition-opacity duration-300"></span>
                <span class="relative z-10 text-muted group-[.active]:text-black group-hover:text-white group-[.active]:group-hover:text-black transition-colors">UI Kits</span>
            </button>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 relative z-10 min-h-[500px]" onmousemove="handleMouseMove(event)">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="portfolio-grid">
            
            <?php foreach($services as $svc): ?>
                <?php 
                    $type = isset($svc['file_type']) ? $svc['file_type'] : 'web';
                    $isApp = ($type == 'app');
                    $badgeText = ($type == 'app') ? 'FLUTTER' : (($type == 'ui') ? 'UI KIT' : 'PHP');
                    $badgeColor = ($type == 'app') ? 'text-purple-400 border-purple-400/20 bg-purple-400/10' : (($type == 'ui') ? 'text-pink-400 border-pink-400/20 bg-pink-400/10' : 'text-accent border-accent/20 bg-accent/10');
                    $demoUrl = isset($svc['demo_url']) ? $svc['demo_url'] : '#';
                ?>
                
                <div onclick="window.location.href='index.php?page=service-details&id=<?php echo $svc['id']; ?>'" 
                     class="filter-item <?php echo $type; ?> group relative rounded-[2rem] bg-white/[0.02] border border-white/5 hover:border-white/10 transition-colors duration-300 flex flex-col h-full overflow-hidden spotlight-card cursor-pointer">
                    
                    <div class="pointer-events-none absolute -inset-px opacity-0 group-hover:opacity-100 transition duration-300"
                         style="background: radial-gradient(600px circle at var(--mouse-x) var(--mouse-y), rgba(255,255,255,0.06), transparent 40%);">
                    </div>

                    <div class="relative flex flex-col h-full z-10">
                        
                        <div class="relative h-[280px] overflow-hidden m-2 rounded-[1.5rem] border border-white/5">
                            <img src="<?php echo htmlspecialchars($svc['thumbnail']); ?>" class="w-full h-full object-cover transition duration-700 group-hover:scale-110 group-hover:rotate-1" alt="Cover">
                            
                            <div class="absolute top-3 left-3 flex gap-2">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border backdrop-blur-md <?php echo $badgeColor; ?>">
                                    <?php echo $badgeText; ?>
                                </span>
                            </div>

                            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center gap-4">
                                
                                <a href="<?php echo $demoUrl; ?>" target="_blank" onclick="event.stopPropagation()" 
                                   class="w-14 h-14 rounded-full bg-white text-black flex items-center justify-center hover:scale-110 transition shadow-[0_0_20px_rgba(255,255,255,0.3)]"
                                   title="<?php echo $isApp ? 'Download APK' : 'Live Demo'; ?>">
                                    
                                    <?php if($isApp): ?>
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    <?php else: ?>
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    <?php endif; ?>
                                </a>
                                
                                <span class="w-14 h-14 rounded-full bg-white/10 border border-white/20 text-white flex items-center justify-center hover:bg-white hover:text-black transition hover:scale-110 backdrop-blur-md">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </span>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-xl font-display font-bold text-white group-hover:text-accent transition-colors duration-300 leading-tight pr-4">
                                    <?php echo htmlspecialchars($svc['title']); ?>
                                </h3>
                                <span class="font-display font-bold text-lg text-white">$<?php echo $svc['price_basic']; ?></span>
                            </div>
                            
                            <p class="text-muted text-sm line-clamp-2 mb-6 opacity-70 group-hover:opacity-100 transition-opacity">
                                <?php echo htmlspecialchars($svc['short_desc']); ?>
                            </p>

                            <form action="api/cart_action.php" method="POST" class="mt-auto" onclick="event.stopPropagation()">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo $svc['id']; ?>">
                                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($svc['title']); ?>">
                                <input type="hidden" name="product_price" value="<?php echo $svc['price_basic']; ?>">
                                
                                <button type="submit" class="magnetic-btn w-full py-4 rounded-xl text-sm font-bold uppercase tracking-wider text-black bg-accent relative overflow-hidden group/btn">
                                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover/btn:translate-y-0 transition-transform duration-300 ease-out"></div>
                                    <span class="relative z-10 flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4 transition-transform group-hover/btn:-rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                        Buy Now
                                    </span>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

            <?php endforeach; ?>
        </div>
    </section>

    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob { animation: blob 7s infinite; }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script>
        function handleMouseMove(e) {
            const cards = document.querySelectorAll(".spotlight-card");
            for (const card of cards) {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                card.style.setProperty("--mouse-x", `${x}px`);
                card.style.setProperty("--mouse-y", `${y}px`);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            gsap.registerPlugin(ScrollTrigger);
            
            const items = document.querySelectorAll('.filter-item');
            gsap.from(items, {
                y: 80, opacity: 0, duration: 1, stagger: 0.1, ease: "power3.out",
                scrollTrigger: { trigger: "#portfolio-grid", start: "top 85%" }
            });

            const filterBtns = document.querySelectorAll('.filter-btn');
            filterBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    const filter = btn.getAttribute('data-filter');

                    items.forEach(item => {
                        if (filter === 'all' || item.classList.contains(filter)) {
                            gsap.to(item, { display: 'flex', opacity: 1, scale: 1, duration: 0.4, ease: 'power2.out' });
                        } else {
                            gsap.to(item, { opacity: 0, scale: 0.9, duration: 0.3, onComplete: () => item.style.display = 'none' });
                        }
                    });
                });
            });
        });
    </script>
</main>