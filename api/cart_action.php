<?php
// api/cart_action.php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    
    $product_id = $_POST['product_id'];
    $name = $_POST['product_name'];
    $price = floatval($_POST['product_price']);

    // কার্ট ইনিশিয়ালাইজ করা
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // কার্টে আইটেম যোগ করা (যদি আগে থেকে থাকে, তাহলে আপডেট না করে নতুন হিসেবে ধরছি সিম্পল রাখার জন্য)
    // প্রফেশনাল কার্টে সাধারণত ডুপ্লিকেট চেক করা হয়, এখানে আমরা সরাসরি রিপ্লেস করছি "Buy Now" ফ্লো এর জন্য
    $_SESSION['cart'] = [
        'id' => $product_id,
        'name' => $name,
        'price' => $price,
        'tax' => 0 // ফিউচার ট্যাক্স লজিকের জন্য
    ];

    // চেকআউট পেজে রিডাইরেক্ট
    header("Location: index.php?page=checkout");
    exit;
}
?>