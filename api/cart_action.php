<?php
// api/cart_action.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    
    $product_id = intval($_POST['product_id']);
    $name = $_POST['product_name'];
    $price = floatval($_POST['product_price']);

    // কার্ট না থাকলে তৈরি করো
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // আইটেম অ্যারে তৈরি
    $item = [
        'id' => $product_id,
        'name' => $name,
        'price' => $price,
        'qty' => 1
    ];

    // যদি প্রোডাক্ট অলরেডি থাকে, তাহলে কিছু করবেনা (অথবা কোয়ান্টিটি বাড়াতে পারেন)
    // আমরা এখানে সহজ রাখার জন্য রিপ্লেস/আপডেট করছি
    $_SESSION['cart'][$product_id] = $item;

    // চেকআউট পেজে পাঠান
    header("Location: ../index.php?page=checkout");
    exit;
}
?>