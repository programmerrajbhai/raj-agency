<?php
session_start();
require_once '../../config/db.php';
if(isset($_SESSION['admin_logged_in']) && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: index.php");
?>