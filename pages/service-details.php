<?php
// ১. ডাটাবেস কানেকশন ও ডাটা ফেচ
if (!isset($_GET['id'])) {
    echo "<script>window.location.href='index.php?page=portfolio';</script>";
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<h1 class='text-white text-center mt-32'>Product Not Found</h1>";
    exit;
}

// ২. ডাটা প্রসেসিং (JSON Decode)
// আমরা ধরে নিচ্ছি আপনি এডমিন প্যানেল থেকে এই ফরম্যাটে JSON সেভ করবেন:
// {
//    "top": ["Feature 1", "Feature 2"],
//    "admin": ["Admin Feat 1", "Admin Feat 2"],
//    "user": ["User Feat 1"],
//    "tech": ["Laravel", "Flutter"],
//    "files": ["Source Code", "Documentation"]
// }
// যদি সাধারণ লিস্ট থাকে, তাও সাপোর্ট করবে।

$rawFeatures = json_decode($product['features'], true);
$isStructured = isset($rawFeatures['top']) || isset($rawFeatures['admin']); 

// ভেরিয়েবল সেটআপ
$type = isset($product['file_type']) ? $product['file_type'] : 'web';
$isApp = ($type == 'app');
$demoUrl = isset($product['demo_url']) ? $product['demo_url'] : '#';
?>

<main class="pt-32 pb-20 min-h-screen bg-[#030303] relative overflow-hidden selection:bg-accent selection:text-black" onmousemove="handleMouseMove(event)">
    
    <div class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-accent/5 rounded-full blur-[120px] animate-blob"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-500/5 rounded-full blur-[120px] animate-blob animation-delay-2000"></div>
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150"></div>
    </div>

    <div class="relative z-10 border-b border-white/5 bg-black/40 backdrop-blur-md py-8">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex items-center gap-2 text-[10px] font-bold text-muted uppercase tracking-widest mb-4">
                <a href="index.php" class="hover:text-white transition">Home</a> <span class="text-white/20">/</span> 
                <a href="index.php?page=portfolio" class="hover:text-white transition">Store</a> <span class="text-white/20">/</span>
                <span class="text-accent">Item Details</span>
            </div>
            <h1 class="font-display text-3xl md:text-5xl font-bold text-white mb-4"><?php echo htmlspecialchars($product['title']); ?></h1>
            <div class="flex flex-wrap items-center gap-4 text-sm text-muted">
                <span class="bg-white/10 px-3 py-1 rounded text-white text-xs font-bold uppercase"><?php echo $isApp ? 'Mobile App' : 'Web Script'; ?></span>
                <div class="flex items-center gap-1 text-yellow-400">★★★★★ <span class="text-muted ml-1">(4.9 Ratings)</span></div>
                <span>• Updated: 20 Jan 2026</span>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 mt-10 grid grid-cols-1 lg:grid-cols-3 gap-10 relative z-10">
        
        <div class="lg:col-span-2 space-y-10">
            
            <div class="rounded-2xl overflow-hidden border border-white/10 shadow-2xl bg-[#0a0a0a]">
                <img src="<?php echo htmlspecialchars($product['thumbnail']); ?>" class="w-full h-auto" alt="Preview">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="<?php echo $demoUrl; ?>" target="_blank" class="group relative overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-blue-800 p-6 text-center transition-transform hover:scale-[1.02]">
                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-20 transition"></div>
                    <h3 class="text-xl font-bold text-white mb-1">Frontend Demo</h3>
                    <p class="text-blue-100 text-xs mb-3">View User Interface</p>
                    <span class="inline-block bg-white text-blue-900 text-xs font-bold px-4 py-2 rounded-lg shadow-lg">Click Here</span>
                </a>
                <a href="<?php echo $demoUrl; ?>/admin" target="_blank" class="group relative overflow-hidden rounded-xl bg-gradient-to-r from-purple-600 to-purple-800 p-6 text-center transition-transform hover:scale-[1.02]">
                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-20 transition"></div>
                    <h3 class="text-xl font-bold text-white mb-1">Admin Panel</h3>
                    <p class="text-purple-100 text-xs mb-3">View Backend Control</p>
                    <span class="inline-block bg-white text-purple-900 text-xs font-bold px-4 py-2 rounded-lg shadow-lg">Click Here</span>
                </a>
            </div>

            <div class="spotlight-card bg-white/[0.02] border border-white/5 rounded-2xl p-8 relative overflow-hidden">
                <div class="pointer-events-none absolute -inset-px opacity-0 group-hover:opacity-100 transition duration-300" style="background: radial-gradient(600px circle at var(--mouse-x) var(--mouse-y), rgba(255,255,255,0.06), transparent 40%);"></div>
                
                <div class="prose prose-invert max-w-none text-muted mb-10">
                    <h3 class="text-white text-xl font-bold mb-4 border-l-4 border-accent pl-3">Overview</h3>
                    <p><?php echo htmlspecialchars($product['short_desc']); ?></p>
                </div>

                <?php if ($isStructured): ?>
                    
                    <?php if(!empty($rawFeatures['top'])): ?>
                    <div class="mb-10">
                        <h3 class="text-white text-lg font-bold mb-4">🔥 Top Features</h3>
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <?php foreach($rawFeatures['top'] as $feat): ?>
                                <li class="flex items-start gap-3 text-sm text-gray-300">
                                    <svg class="w-5 h-5 text-accent shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <?php echo $feat; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <?php if(!empty($rawFeatures['admin'])): ?>
                    <div class="mb-10">
                        <h3 class="text-white text-lg font-bold mb-4">🛡️ Admin Features</h3>
                        <ul class="space-y-2">
                            <?php foreach($rawFeatures['admin'] as $feat): ?>
                                <li class="flex items-center gap-3 text-sm text-gray-400">
                                    <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                                    <?php echo $feat; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <?php if(!empty($rawFeatures['tech'])): ?>
                    <div class="mb-10">
                        <h3 class="text-white text-lg font-bold mb-4">💻 Technology Used</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach($rawFeatures['tech'] as $tech): ?>
                                <span class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-xs font-bold text-white uppercase tracking-wide">
                                    <?php echo $tech; ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="mb-10">
                        <h3 class="text-white text-lg font-bold mb-4">✨ Key Features</h3>
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <?php 
                                if(is_array($rawFeatures)) {
                                    foreach($rawFeatures as $feat) {
                                        echo '<li class="flex items-center gap-3 text-sm text-gray-300"><svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> '.$feat.'</li>';
                                    }
                                }
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="bg-[#111] rounded-xl p-6 border border-white/5">
                    <h4 class="text-white font-bold mb-4">📦 What You Will Get?</h4>
                    <ul class="space-y-2 text-sm text-muted">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Full Source Code</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Project Documentation</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Database SQL File</li>
                    </ul>
                </div>

            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="sticky top-28 space-y-6">
                
                <div class="spotlight-card relative bg-[#0F0F0F] border border-white/10 rounded-2xl p-6 overflow-hidden shadow-2xl group">
                    <div class="pointer-events-none absolute -inset-px opacity-0 group-hover:opacity-100 transition duration-300" style="background: radial-gradient(600px circle at var(--mouse-x) var(--mouse-y), rgba(244,185,11,0.1), transparent 40%);"></div>
                    
                    <div class="relative z-10">
                        <div class="flex justify-between items-center mb-6">
                            <span class="text-sm text-muted font-bold">Regular License</span>
                            <span class="text-3xl font-display font-bold text-white">$<?php echo number_format($product['price_basic'], 2); ?></span>
                        </div>

                        <ul class="space-y-3 mb-6 text-sm text-gray-400">
                            <li class="flex gap-2"><svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Quality Checked</li>
                            <li class="flex gap-2"><svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Future Updates</li>
                            <li class="flex gap-2"><svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> 6 Months Support</li>
                        </ul>

                        <form action="index.php?page=cart_action" method="POST" class="mb-3">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['title']); ?>">
                            <input type="hidden" name="product_price" value="<?php echo $product['price_basic']; ?>">
                            
                            <button type="submit" class="magnetic-btn w-full py-4 bg-accent text-black font-bold uppercase tracking-widest rounded-xl hover:bg-[#FFD700] transition-all shadow-[0_0_20px_rgba(244,185,11,0.3)] flex justify-center items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                Add to Cart
                            </button>
                        </form>
                        
                        <a href="<?php echo $demoUrl; ?>" target="_blank" class="block w-full py-3 text-center border border-white/10 text-white font-bold rounded-xl hover:bg-white hover:text-black transition">
                            <?php echo $isApp ? 'Download Test APK' : 'Live Preview'; ?>
                        </a>
                    </div>
                </div>

                <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6">
                    <table class="w-full text-sm text-left">
                        <tbody class="divide-y divide-white/5">
                            <tr><td class="py-3 text-muted">Released</td><td class="py-3 text-white text-right">20 Jan 2026</td></tr>
                            <tr><td class="py-3 text-muted">Version</td><td class="py-3 text-white text-right">1.2.0</td></tr>
                            <tr><td class="py-3 text-muted">Category</td><td class="py-3 text-white text-right uppercase"><?php echo $type; ?></td></tr>
                            <tr><td class="py-3 text-muted">Compatible</td><td class="py-3 text-white text-right">Browsers, Android</td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

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
        
        const btns = document.querySelectorAll(".magnetic-btn");
        btns.forEach((btn) => {
            btn.addEventListener("mousemove", (e) => {
                const rect = btn.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                btn.style.transform = `translate(${x * 0.1}px, ${y * 0.1}px)`;
            });
            btn.addEventListener("mouseleave", () => {
                btn.style.transform = "translate(0, 0)";
            });
        });
    </script>
</main>