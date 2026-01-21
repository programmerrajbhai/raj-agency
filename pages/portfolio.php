<?php
// ডাটাবেস থেকে সব সার্ভিস ফেচ করা
$stmt = $pdo->query("SELECT * FROM services");
$services = $stmt->fetchAll();
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php foreach($services as $svc): ?>
        
        <div class="group bg-card border border-border rounded-2xl overflow-hidden hover:border-accent/30 transition-all duration-300 hover:-translate-y-2 flex flex-col">
            <div class="relative h-60 bg-gray-800 overflow-hidden cursor-pointer" onclick="window.location.href='index.php?page=service-details&id=<?php echo $svc['id']; ?>'">
                <img src="<?php echo htmlspecialchars($svc['thumbnail']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" alt="Service">
                <span class="absolute top-3 right-3 bg-black/60 backdrop-blur text-white font-bold px-3 py-1 rounded-lg border border-white/10">$<?php echo $svc['price_basic']; ?></span>
            </div>
            
            <div class="p-6 flex-1 flex flex-col">
                <h3 class="text-xl font-display font-bold mb-2 group-hover:text-accent transition">
                    <a href="index.php?page=service-details&id=<?php echo $svc['id']; ?>"><?php echo htmlspecialchars($svc['title']); ?></a>
                </h3>
                <p class="text-muted text-sm line-clamp-2 mb-4"><?php echo htmlspecialchars($svc['short_desc']); ?></p>
                
                <div class="mt-auto grid grid-cols-2 gap-3">
                    <a href="index.php?page=service-details&id=<?php echo $svc['id']; ?>" class="flex items-center justify-center gap-2 py-2.5 border border-white/10 rounded-lg text-sm font-medium hover:bg-white hover:text-black transition">
                        View Details
                    </a>
                    
                    <form action="index.php?page=cart_action" method="POST" class="w-full">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $svc['id']; ?>">
                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($svc['title']); ?>">
                        <input type="hidden" name="product_price" value="<?php echo $svc['price_basic']; ?>">
                        
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 bg-accent text-black rounded-lg text-sm font-bold hover:bg-accent-hover transition">
                            Buy Now
                        </button>
                    </form>
                </div>
            </div>
        </div>

    <?php endforeach; ?>
</div>