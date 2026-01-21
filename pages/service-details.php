<?php
// ১. ডাটাবেস থেকে প্রোডাক্ট রিট্রিভ করা
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

// ফিচার লিস্ট JSON ডিকোড করা
$features = json_decode($product['features'], true);
?>

<main class="pt-32 pb-20 bg-bg min-h-screen">
    
    <div class="bg-[#101010] border-b border-white/5 py-10">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex items-center gap-2 text-xs text-muted uppercase tracking-widest mb-4">
                <a href="index.php" class="hover:text-white">Home</a> / 
                <a href="index.php?page=portfolio" class="hover:text-white">Portfolio</a> / 
                <span class="text-accent">Details</span>
            </div>
            <h1 class="font-display text-4xl md:text-5xl font-bold text-white mb-2"><?php echo htmlspecialchars($product['title']); ?></h1>
            
            <div class="flex items-center gap-6 text-sm text-muted">
                <div class="flex items-center gap-1 text-accent">
                    ★★★★★ <span class="text-muted ml-1">(<?php echo rand(10, 100); ?> Ratings)</span>
                </div>
                <div>Created by <span class="text-white font-bold">Raj Agency</span></div>
                <div>Last Update: <span class="text-white">20 Jan 2026</span></div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 mt-10 grid grid-cols-1 lg:grid-cols-3 gap-10">
        
        <div class="lg:col-span-2 space-y-10">
            
            <div class="rounded-2xl overflow-hidden border border-white/10 shadow-2xl">
                <img src="<?php echo htmlspecialchars($product['thumbnail']); ?>" class="w-full h-auto" alt="Preview">
            </div>

            <div class="bg-card border border-white/10 rounded-2xl p-8">
                <h3 class="text-xl font-bold text-white mb-6 border-b border-white/10 pb-4">Description</h3>
                
                <div class="prose prose-invert max-w-none text-muted leading-relaxed">
                    <p class="text-lg text-white mb-4"><?php echo htmlspecialchars($product['short_desc']); ?></p>
                    
                    <div class="bg-[#050505] border border-accent/20 rounded-xl p-6 mb-8 flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div>
                            <h4 class="font-bold text-white">Live Demo Available</h4>
                            <p class="text-xs text-muted">Check the app functionality before buying.</p>
                        </div>
                        <div class="flex gap-3">
                            <a href="#" class="px-5 py-2 bg-white/10 hover:bg-white/20 text-white text-xs font-bold uppercase rounded-lg transition">Admin Panel</a>
                            <a href="#" class="px-5 py-2 bg-accent text-black text-xs font-bold uppercase rounded-lg transition">App Preview</a>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-white font-bold text-lg">Key Features</h4>
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <?php foreach($features as $feat): ?>
                                <li class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <?php echo $feat; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div class="mt-8 p-4 bg-blue-900/20 border-l-4 border-blue-500 text-blue-200 text-sm">
                            <strong>Requirement:</strong> Needs PHP 8.1+ and MySQL database.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="sticky top-28 space-y-6">
                
                <div class="bg-card border border-white/10 rounded-2xl p-6 shadow-glow">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm font-bold text-muted">Regular License</span>
                        <span class="text-3xl font-display font-bold text-accent">$<?php echo number_format($product['price_basic'], 2); ?></span>
                    </div>
                    
                    <p class="text-xs text-muted mb-6">Use, by you or one client, in a single end product which end users <strong>are not</strong> charged for.</p>

                    <ul class="space-y-3 mb-6 text-sm text-white">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Quality Checked by Raj Agency</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Future Updates</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> 6 Months Support</li>
                    </ul>

                    <form action="index.php?page=cart_action" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['title']); ?>">
                        <input type="hidden" name="product_price" value="<?php echo $product['price_basic']; ?>">
                        
                        <button type="submit" class="w-full py-4 bg-accent text-black font-bold uppercase tracking-widest rounded-xl hover:bg-accent-hover transition shadow-lg shadow-accent/20 flex justify-center items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Add to Cart
                        </button>
                    </form>
                    
                    <button class="w-full mt-3 py-3 border border-white/10 text-white font-bold rounded-xl hover:bg-white hover:text-black transition">
                        Live Preview
                    </button>
                </div>

                <div class="bg-[#0a0a0a] border border-white/5 rounded-2xl p-6">
                    <h5 class="font-bold text-white mb-4">Information</h5>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-muted">Released</span> <span class="text-white">Jan 20, 2026</span></div>
                        <div class="flex justify-between"><span class="text-muted">Version</span> <span class="text-white">1.2.0</span></div>
                        <div class="flex justify-between"><span class="text-muted">Files</span> <span class="text-white">PHP, JS, JSON</span></div>
                        <div class="flex justify-between"><span class="text-muted">Framework</span> <span class="text-white">Laravel 10.x</span></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>