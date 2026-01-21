<?php
session_start(); // কার্ট সেশনের জন্য জরুরি

require_once 'config/db.php'; 

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// কার্ট অ্যাকশন হ্যান্ডেল করা (Add to Cart)
if ($page == 'cart_action') {
    include 'api/cart_action.php';
    exit;
}

include 'includes/header.php';

$allowed_pages = ['home', 'services', 'portfolio', 'checkout', 'contact', 'about', 'service-details']; // 'service-details' অ্যাড করা হয়েছে

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