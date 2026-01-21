<?php
// api/submit_contact.php
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ইনপুট স্যানিটাইজ করা
    $name = htmlspecialchars(strip_tags($_POST['name']));
    $email = htmlspecialchars(strip_tags($_POST['email']));
    $service = htmlspecialchars(strip_tags($_POST['service']));
    $message = htmlspecialchars(strip_tags($_POST['message']));

    // ডাটাবেস টেবিল 'messages' এ সেভ করা (যদি টেবিল থাকে)
    // টেবিল না থাকলে আপনি এই অংশটি কমেন্ট করে রাখতে পারেন
    /*
    try {
        $stmt = $pdo->prepare("INSERT INTO messages (name, email, service, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $service, $message]);
    } catch(PDOException $e) {
        // Error handling
    }
    */

    // সিম্পল অ্যালার্ট দিয়ে রিডাইরেক্ট
    echo "<script>
        alert('Thank you, $name! We have received your message.');
        window.location.href = '../index.php?page=contact';
    </script>";
} else {
    header("Location: ../index.php");
}
?>