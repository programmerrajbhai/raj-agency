<?php
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>window.location.href='index.php?page=portfolio';</script>";
    exit;
}

// Calculate Total
$total = 0;
foreach($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['qty'];
}
?>

<main class="pt-32 pb-20 min-h-screen bg-[#050505] flex items-center justify-center">
    <section class="max-w-6xl mx-auto px-6 w-full">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <div class="lg:col-span-8 space-y-8">
                
                <div class="bg-[#101010] border border-white/5 rounded-3xl p-8">
                    <h3 class="font-display text-2xl font-bold text-white mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-sm">1</span> 
                        Order Items
                    </h3>
                    
                    <div class="space-y-4">
                        <?php foreach($_SESSION['cart'] as $item): ?>
                            <div class="flex justify-between items-center bg-black/20 p-4 rounded-xl border border-white/5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-lg bg-accent/20 flex items-center justify-center text-accent">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white"><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <p class="text-xs text-muted">Standard License</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-white">$<?php echo $item['price']; ?></p>
                                    <p class="text-xs text-muted">Qty: <?php echo $item['qty']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-[#101010] border border-white/5 rounded-3xl p-8">
                    <h3 class="font-display text-2xl font-bold text-white mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-sm">2</span> 
                        Your Details
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs text-muted uppercase font-bold">Full Name</label>
                            <input type="text" class="w-full bg-[#050505] border border-white/10 rounded-xl px-4 py-3 text-white focus:border-accent focus:outline-none transition" placeholder="John Doe">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs text-muted uppercase font-bold">Email Address</label>
                            <input type="email" class="w-full bg-[#050505] border border-white/10 rounded-xl px-4 py-3 text-white focus:border-accent focus:outline-none transition" placeholder="john@example.com">
                        </div>
                    </div>
                </div>

                <div class="bg-[#101010] border border-white/5 rounded-3xl p-8">
                    <h3 class="font-display text-2xl font-bold text-white mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-sm">3</span> 
                        Payment
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <label class="cursor-pointer border border-accent bg-accent/10 p-4 rounded-xl flex items-center gap-3 transition">
                            <input type="radio" name="payment" checked class="accent-accent w-5 h-5">
                            <span class="font-bold text-sm text-white">Credit Card</span>
                        </label>
                        <label class="cursor-pointer border border-white/10 bg-[#050505] p-4 rounded-xl flex items-center gap-3 transition opacity-50 hover:opacity-100">
                            <input type="radio" name="payment" class="accent-accent w-5 h-5">
                            <span class="font-bold text-sm text-white">PayPal</span>
                        </label>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-xs text-muted uppercase font-bold">Card Number</label>
                            <div class="relative">
                                <input type="text" placeholder="0000 0000 0000 0000" class="w-full bg-[#050505] border border-white/10 rounded-xl px-4 py-3 text-white focus:border-accent focus:outline-none pl-12">
                                <svg class="w-5 h-5 absolute left-4 top-3.5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-xs text-muted uppercase font-bold">Expiry Date</label>
                                <input type="text" placeholder="MM/YY" class="w-full bg-[#050505] border border-white/10 rounded-xl px-4 py-3 text-white focus:border-accent focus:outline-none">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs text-muted uppercase font-bold">CVC / CVC2</label>
                                <input type="text" placeholder="123" class="w-full bg-[#050505] border border-white/10 rounded-xl px-4 py-3 text-white focus:border-accent focus:outline-none">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="lg:col-span-4">
                <div class="bg-card border border-white/10 rounded-3xl p-6 sticky top-28 shadow-2xl">
                    <h3 class="text-lg font-bold text-white mb-6 border-b border-white/10 pb-4">Order Summary</h3>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm text-muted">
                            <span>Subtotal</span>
                            <span class="text-white font-bold">$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-sm text-muted">
                            <span>Tax (VAT)</span>
                            <span class="text-white font-bold">$0.00</span>
                        </div>
                        <div class="flex justify-between text-sm text-muted">
                            <span>Discount</span>
                            <span class="text-accent">- $0.00</span>
                        </div>
                    </div>

                    <div class="border-t border-white/10 pt-4 flex justify-between items-center mb-8">
                        <span class="text-xl font-bold text-white">Total</span>
                        <span class="text-3xl font-display font-bold text-accent">$<?php echo number_format($total, 2); ?></span>
                    </div>

                    <button class="w-full py-4 bg-accent text-black font-bold uppercase tracking-widest rounded-xl hover:bg-[#FFD700] transition shadow-lg shadow-accent/20 flex items-center justify-center gap-2 group">
                        <svg class="w-5 h-5 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Pay $<?php echo number_format($total, 2); ?>
                    </button>
                    
                    <p class="text-center text-[10px] text-muted mt-4 flex justify-center items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Secure 256-bit SSL Encrypted
                    </p>
                </div>
            </div>

        </div>
    </section>
</main>