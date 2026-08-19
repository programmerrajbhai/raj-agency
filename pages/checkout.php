<?php
// ১. কার্ট অ্যাকশন লজিক (Increase, Decrease, Remove)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];

    if (isset($_SESSION['cart'][$id])) {
        if ($action === 'increase') {
            $_SESSION['cart'][$id]['qty'] += 1;
        } elseif ($action === 'decrease') {
            $_SESSION['cart'][$id]['qty'] -= 1;
            // যদি পরিমাণ ০ বা তার কম হয়, কার্ট থেকে মুছে ফেলবে
            if ($_SESSION['cart'][$id]['qty'] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
        } elseif ($action === 'remove') {
            unset($_SESSION['cart'][$id]);
        }
    }
    // পেজ রিলোড করে URL ক্লিন করা
    echo "<script>window.location.href='index.php?page=checkout';</script>";
    exit;
}

// ২. কার্ট চেক করা (কার্ট ফাঁকা থাকলে পোর্টফোলিওতে পাঠিয়ে দিবে)
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>window.location.href='index.php?page=portfolio';</script>";
    exit;
}

$cart_items = $_SESSION['cart'];
$total = 0;
?>

<main class="pt-32 pb-20 min-h-screen bg-[#050505] selection:bg-yellow-500 selection:text-black">
    <section class="max-w-6xl mx-auto px-6">
        
        <div class="mb-10">
            <h1 class="font-display text-4xl md:text-5xl font-bold text-white mb-3 flex items-center gap-3">
                <i class="ri-secure-payment-line text-accent"></i> Secure Checkout
            </h1>
            <p class="text-muted">Complete your order details and payment securely.</p>
        </div>

        <!-- Checkout Form (Submits to process_order.php) -->
        <form action="api/process_order.php" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <!-- Left Column (Billing & Payment) -->
            <div class="lg:col-span-7 space-y-8">
                
                <!-- Billing Details -->
                <div class="bg-[#111] border border-white/5 rounded-2xl p-6 md:p-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-white/5 pb-4">
                        <span class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-sm text-yellow-500">1</span> 
                        Billing Details
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs text-gray-400 uppercase font-bold tracking-wider">Full Name</label>
                            <input type="text" name="full_name" required class="w-full bg-[#0a0a0a] border border-white/10 rounded-xl px-4 py-3.5 text-white focus:border-yellow-500 focus:outline-none transition placeholder-gray-600" placeholder="John Doe">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs text-gray-400 uppercase font-bold tracking-wider">Email Address</label>
                            <input type="email" name="email" required class="w-full bg-[#0a0a0a] border border-white/10 rounded-xl px-4 py-3.5 text-white focus:border-yellow-500 focus:outline-none transition placeholder-gray-600" placeholder="john@example.com">
                        </div>
                        <div class="col-span-1 md:col-span-2 space-y-2">
                            <label class="text-xs text-gray-400 uppercase font-bold tracking-wider">Country</label>
                            <select name="country" class="w-full bg-[#0a0a0a] border border-white/10 rounded-xl px-4 py-3.5 text-white focus:border-yellow-500 focus:outline-none transition appearance-none">
                                <option value="Bangladesh">Bangladesh</option>
                                <option value="United States">United States</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="India">India</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-[#111] border border-white/5 rounded-2xl p-6 md:p-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-white/5 pb-4">
                        <span class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-sm text-yellow-500">2</span> 
                        Payment Method
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Credit Card -->
                        <label class="cursor-pointer group">
                            <input type="radio" name="payment_method" value="stripe" checked class="peer sr-only">
                            <div class="border border-white/10 bg-[#0a0a0a] p-5 rounded-xl flex items-center gap-4 transition peer-checked:border-yellow-500 peer-checked:bg-yellow-500/5 hover:bg-white/5">
                                <i class="ri-bank-card-line text-2xl text-gray-400 peer-checked:text-yellow-500 group-hover:text-white transition"></i>
                                <div>
                                    <h4 class="font-bold text-white text-sm">Credit Card</h4>
                                    <p class="text-[11px] text-gray-500">Stripe Secure</p>
                                </div>
                                <div class="ml-auto w-5 h-5 rounded-full border-2 border-gray-600 peer-checked:border-yellow-500 peer-checked:bg-yellow-500 flex items-center justify-center">
                                    <div class="w-2 h-2 bg-[#111] rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                            </div>
                        </label>

                        <!-- PayPal -->
                        <label class="cursor-pointer group">
                            <input type="radio" name="payment_method" value="paypal" class="peer sr-only">
                            <div class="border border-white/10 bg-[#0a0a0a] p-5 rounded-xl flex items-center gap-4 transition peer-checked:border-yellow-500 peer-checked:bg-yellow-500/5 hover:bg-white/5">
                                <i class="ri-paypal-fill text-2xl text-gray-400 peer-checked:text-blue-500 group-hover:text-white transition"></i>
                                <div>
                                    <h4 class="font-bold text-white text-sm">PayPal</h4>
                                    <p class="text-[11px] text-gray-500">International</p>
                                </div>
                                <div class="ml-auto w-5 h-5 rounded-full border-2 border-gray-600 peer-checked:border-yellow-500 peer-checked:bg-yellow-500 flex items-center justify-center">
                                    <div class="w-2 h-2 bg-[#111] rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Note -->
                    <div class="mt-6 bg-blue-500/10 border border-blue-500/20 p-4 rounded-xl flex gap-3 text-blue-400 text-sm">
                        <i class="ri-information-fill text-lg"></i>
                        <p>This is a demo checkout. Real payment gateway integration (Stripe/PayPal) needs to be configured in API.</p>
                    </div>
                </div>

            </div>

            <!-- Right Column (Order Summary) -->
            <div class="lg:col-span-5">
                <div class="bg-[#111] border border-white/5 rounded-2xl p-6 md:p-8 sticky top-28 shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-6 border-b border-white/5 pb-4">Order Summary</h3>
                    
                    <!-- Cart Items Loop -->
                    <div class="space-y-4 mb-6 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                        <?php foreach($cart_items as $id => $item): 
                            $item_total = $item['price'] * $item['qty'];
                            $total += $item_total;
                        ?>
                            <div class="flex justify-between items-center bg-[#0a0a0a] p-4 rounded-xl border border-white/5 group">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-yellow-500/10 flex items-center justify-center text-yellow-500">
                                        <i class="ri-code-box-line text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-white text-sm leading-tight line-clamp-1"><?php echo htmlspecialchars($item['name']); ?></p>
                                        
                                        <!-- Quantity Controller (+ / -) -->
                                        <div class="flex items-center gap-2 mt-1.5">
                                            <div class="flex items-center bg-[#111] rounded border border-white/10">
                                                <a href="index.php?page=checkout&action=decrease&id=<?php echo $id; ?>" class="px-2 py-0.5 text-gray-400 hover:text-white hover:bg-white/10 transition">-</a>
                                                <span class="px-2 text-xs font-bold text-white border-x border-white/10"><?php echo $item['qty']; ?></span>
                                                <a href="index.php?page=checkout&action=increase&id=<?php echo $id; ?>" class="px-2 py-0.5 text-gray-400 hover:text-white hover:bg-white/10 transition">+</a>
                                            </div>
                                            <span class="text-xs text-muted">&times; $<?php echo number_format($item['price'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <p class="font-bold text-white text-sm">$<?php echo number_format($item_total, 2); ?></p>
                                    <!-- Remove Item Button -->
                                    <a href="index.php?page=checkout&action=remove&id=<?php echo $id; ?>" class="text-xs text-red-500/50 hover:text-red-500 transition font-medium flex items-center gap-1">
                                        <i class="ri-delete-bin-line"></i> Remove
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Price Calculations -->
                    <div class="space-y-3 mb-6 pt-4 border-t border-white/5">
                        <div class="flex justify-between text-sm text-gray-400">
                            <span>Subtotal</span>
                            <span class="text-white font-bold">$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-400">
                            <span>License Type</span>
                            <span class="text-white">Regular License</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-400">
                            <span>Tax (VAT)</span>
                            <span class="text-white font-bold">$0.00</span>
                        </div>
                    </div>

                    <!-- Grand Total -->
                    <div class="border-t border-white/10 pt-4 flex justify-between items-center mb-8">
                        <span class="text-lg font-bold text-white">Total</span>
                        <span class="text-3xl font-display font-bold text-yellow-500">$<?php echo number_format($total, 2); ?></span>
                    </div>

                    <!-- Total Amount Hidden Input for Form -->
                    <input type="hidden" name="total_amount" value="<?php echo $total; ?>">

                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-yellow-500 to-yellow-600 text-black font-bold uppercase tracking-widest rounded-xl hover:shadow-[0_0_20px_rgba(244,185,11,0.3)] transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                        <i class="ri-lock-fill text-lg"></i> Complete Order
                    </button>
                    
                    <p class="text-center text-[11px] text-gray-500 mt-4 flex justify-center items-center gap-1.5">
                        <i class="ri-shield-check-fill text-green-500 text-sm"></i> Secure 256-bit SSL Encrypted
                    </p>
                </div>
            </div>

        </form>
    </section>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #3e4042; border-radius: 10px; }
    </style>
</main>