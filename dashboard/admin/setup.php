<?php
// dashboard/admin/setup.php
require_once '../../config/db.php';

try {
    // 1. Create Admins Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL
    )");

    // 2. Remove Old Admin
    $pdo->exec("TRUNCATE TABLE admins");

    // 3. Create New Admin (User: admin, Pass: 123456)
    $pass = password_hash('123456', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES ('admin', ?)");
    $stmt->execute([$pass]);

    echo "<div style='font-family:sans-serif; text-align:center; padding:50px; background:#111; color:#0f0;'>
            <h1>✅ Admin Setup Complete!</h1>
            <p>Username: <strong>admin</strong></p>
            <p>Password: <strong>123456</strong></p>
            <br>
            <a href='login.php' style='color:#fff; text-decoration:underline;'>Go to Login Page</a>
          </div>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>