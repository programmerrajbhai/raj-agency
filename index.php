<?php
session_start(); // কার্ট সেশনের জন্য জরুরি

require_once 'config/db.php'; 

// ডিফল্ট পেজ 'feed' করে দেওয়া হলো, যাতে সাইটে ঢুকলেই নিউজফিড দেখা যায়
$page = isset($_GET['page']) ? $_GET['page'] : 'feed';

// কার্ট অ্যাকশন হ্যান্ডেল করা (Add to Cart)
if ($page == 'cart_action') {
    include 'api/cart_action.php';
    exit;
}

include 'includes/header.php';

// 'feed' পেজটি allowed_pages এর তালিকায় যুক্ত করা হয়েছে
$allowed_pages = ['home', 'feed', 'services', 'portfolio', 'checkout', 'contact', 'about', 'service-details'];

if (in_array($page, $allowed_pages)) {
    if (file_exists("pages/{$page}.php")) {
        include "pages/{$page}.php";
    } else {
        echo "<h1 class='text-center text-red-500 mt-32'>404 - Page File Not Found</h1>";
    }
} else {
    echo "<h1 class='text-center text-white mt-32'>404 - Page Not Found</h1>";
}

include 'includes/footer.php';
?>