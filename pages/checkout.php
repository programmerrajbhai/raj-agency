<?php
// কার্ট চেক করা
if (!isset($_SESSION['cart'])) {
    echo "<script>window.location.href='index.php?page=portfolio';</script>";
    exit;
}

$cart = $_SESSION['cart'];
$total = $cart['price'];
?>

<main class="pt-32 pb-20 min-h-screen bg-bg">
    <section class="max-w-5xl mx-auto px-6">
        
        <h1 class="font-display text-4xl font-bold mb-2">Checkout</h1>
        <p class="text-muted mb-10">Complete your order for <span class="text-accent"><?php echo $cart['name']; ?></span></p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2 space-y-6">
                <div class="bg-card border border-white/10 p-6 rounded-2xl">
                     <h3 class="text-lg font-bold mb-4">Select Payment Method</h3>
                     <p class="text-sm text-muted">This is a demo checkout. No real payment will be processed.</p>
                </div>
            </div>

            <div class="md:col-span-1">
                <div class="bg-card border border-white/10 p-6 rounded-2xl sticky top-28">
                    <h3 class="text-lg font-bold mb-6 border-b border-white/10 pb-4">Summary</h3>
                    
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-2/3">
                            <p class="font-bold text-white text-sm"><?php echo $cart['name']; ?></p>
                            <p class="text-xs text-muted">Regular License</p>
                        </div>
                        <p class="font-bold text-accent">$<?php echo number_format($cart['price'], 2); ?></p>
                    </div>

                    <div class="border-t border-white/10 my-4 pt-4 flex justify-between items-center text-xl font-bold">
                        <p>Total</p>
                        <p>$<?php echo number_format($total, 2); ?></p>
                    </div>

                    <button class="w-full py-4 bg-accent text-black font-bold rounded-xl hover:bg-accent-hover transition shadow-lg">
                        Pay Securely
                    </button>
                </div>
            </div>
        </div>
    </section>
</main>