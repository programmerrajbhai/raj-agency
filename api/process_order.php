<?php
// api/process_order.php
session_start();
require_once '../config/db.php';

// ১. চেক করা হচ্ছে রিকোয়েস্টটি POST কি না এবং কার্টে কিছু আছে কি না
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['cart'])) {
    
    // ২. ডাটা স্যানিটাইজেশন (XSS বা ক্ষতিকর কোড থেকে বাঁচতে)
    $full_name = htmlspecialchars(strip_tags($_POST['full_name'] ?? ''));
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $country = htmlspecialchars(strip_tags($_POST['country'] ?? ''));
    $payment_method = htmlspecialchars(strip_tags($_POST['payment_method'] ?? ''));

    // ৩. সিকিউরিটির জন্য টোটাল প্রাইস আবার ব্যাকএন্ডে ক্যালকুলেট করা
    $total_amount = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_amount += $item['price'] * $item['qty'];
    }
    
    // কার্টের আইটেমগুলো JSON ফরম্যাটে ডাটাবেসে সেভ করার জন্য
    $order_data = json_encode($_SESSION['cart']);

    try {
        // ৪. Orders টেবিল না থাকলে অটোমেটিক তৈরি করে নিবে (সেফটির জন্য)
        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            country VARCHAR(50) NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            order_data TEXT NOT NULL,
            status VARCHAR(20) DEFAULT 'Completed',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // ৫. ডাটাবেসে অর্ডার ইনসার্ট করা
        $stmt = $pdo->prepare("INSERT INTO orders (full_name, email, country, payment_method, total_amount, order_data) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $email, $country, $payment_method, $total_amount, $order_data]);

        // ৬. অর্ডার সাকসেসফুল হলে কার্ট ক্লিয়ার করা
        unset($_SESSION['cart']);
        
        $success = true;

    } catch(PDOException $e) {
        $success = false;
        $error_message = $e->getMessage();
    }
} else {
    // যদি কেউ ডিরেক্ট এই লিংকে হিট করে, তাকে হোমপেজে পাঠিয়ে দিবে
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Order... | Raj Agency</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #050505; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

<!-- প্রসেসিং অ্যানিমেশন এবং অ্যালার্ট -->
<script>
    <?php if (isset($success) && $success): ?>
        Swal.fire({
            title: 'Payment Successful!',
            text: 'Thank you for your order. We will contact you shortly.',
            icon: 'success',
            confirmButtonColor: '#F4B90B',
            background: '#111',
            color: '#fff',
            iconColor: '#45bd62',
            allowOutsideClick: false,
            timer: 3500,
            timerProgressBar: true,
            showConfirmButton: false
        }).then((result) => {
            window.location.href = '../index.php?page=portfolio'; // সাকসেস হলে পোর্টফোলিওতে রিডাইরেক্ট
        });
    <?php else: ?>
        Swal.fire({
            title: 'Order Failed!',
            text: 'Something went wrong while processing your order. Please try again.',
            icon: 'error',
            confirmButtonColor: '#f3425f',
            background: '#111',
            color: '#fff',
            iconColor: '#f3425f'
        }).then((result) => {
            window.location.href = '../index.php?page=checkout'; // ফেইল হলে আবার চেকআউটে ফেরত পাঠাবে
        });
    <?php endif; ?>
</script>

</body>
</html>