<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once '../../config/db.php';

// Fetch Stats
$total_services = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_msgs = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();

// Fetch Services
$services = $pdo->query("SELECT * FROM services ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Raj Agency</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="bg-[#050505] text-white font-sans flex">

    <aside class="w-64 h-screen bg-[#111] border-r border-white/5 flex flex-col fixed">
        <div class="p-6 text-2xl font-bold text-yellow-500">RAJ ADMIN.</div>
        <nav class="flex-1 px-4 space-y-2">
            <a href="index.php" class="flex items-center gap-3 p-3 bg-white/5 rounded-lg text-white"><i class="ri-dashboard-line"></i> Dashboard</a>
            <a href="add_service.php" class="flex items-center gap-3 p-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"><i class="ri-add-circle-line"></i> Add Service</a>
            <a href="#" class="flex items-center gap-3 p-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"><i class="ri-shopping-cart-line"></i> Orders</a>
            <a href="#" class="flex items-center gap-3 p-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"><i class="ri-message-2-line"></i> Messages</a>
        </nav>
        <a href="logout.php" class="p-4 text-red-500 hover:bg-white/5 flex items-center gap-2"><i class="ri-logout-box-line"></i> Logout</a>
    </aside>

    <main class="ml-64 flex-1 p-8">
        
        <div class="grid grid-cols-3 gap-6 mb-10">
            <div class="bg-[#111] p-6 rounded-2xl border border-white/5">
                <h3 class="text-gray-400">Total Services</h3>
                <p class="text-4xl font-bold mt-2"><?php echo $total_services; ?></p>
            </div>
            <div class="bg-[#111] p-6 rounded-2xl border border-white/5">
                <h3 class="text-gray-400">Total Orders</h3>
                <p class="text-4xl font-bold mt-2 text-green-400"><?php echo $total_orders; ?></p>
            </div>
            <div class="bg-[#111] p-6 rounded-2xl border border-white/5">
                <h3 class="text-gray-400">Inquiries</h3>
                <p class="text-4xl font-bold mt-2 text-blue-400"><?php echo $total_msgs; ?></p>
            </div>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">All Services</h2>
            <a href="add_service.php" class="bg-yellow-500 text-black px-6 py-2 rounded-lg font-bold hover:bg-yellow-400">+ New Product</a>
        </div>

        <div class="bg-[#111] rounded-2xl border border-white/5 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-white/5 text-gray-400">
                    <tr>
                        <th class="p-4">ID</th>
                        <th class="p-4">Thumbnail</th>
                        <th class="p-4">Title</th>
                        <th class="p-4">Price</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php foreach($services as $svc): ?>
                    <tr class="hover:bg-white/5 transition">
                        <td class="p-4">#<?php echo $svc['id']; ?></td>
                        <td class="p-4"><img src="<?php echo $svc['thumbnail']; ?>" class="w-12 h-12 rounded object-cover"></td>
                        <td class="p-4 font-bold"><?php echo $svc['title']; ?></td>
                        <td class="p-4 text-yellow-500">$<?php echo $svc['price_basic']; ?></td>
                        <td class="p-4 uppercase text-xs">
                            <span class="bg-white/10 px-2 py-1 rounded"><?php echo $svc['file_type'] ?? 'web'; ?></span>
                        </td>
                        <td class="p-4 flex gap-2">
                            <a href="edit_service.php?id=<?php echo $svc['id']; ?>" class="text-blue-400 hover:text-white"><i class="ri-edit-line"></i></a>
                            <a href="delete_service.php?id=<?php echo $svc['id']; ?>" class="text-red-500 hover:text-red-300" onclick="return confirm('Are you sure?')"><i class="ri-delete-bin-line"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>
</body>
</html>