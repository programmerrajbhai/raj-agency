<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) header("Location: login.php");
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $slug = strtolower(str_replace(' ', '-', $title));
    $price = $_POST['price'];
    $type = $_POST['file_type'];
    $demo = $_POST['demo_url'];
    $thumbnail = $_POST['thumbnail'];
    $short_desc = $_POST['short_desc'];

    // Construct JSON Features
    $features_json = json_encode([
        'top' => explode(',', $_POST['feat_top']),
        'admin' => explode(',', $_POST['feat_admin']),
        'user' => explode(',', $_POST['feat_user']),
        'tech' => explode(',', $_POST['feat_tech']),
        'files' => explode(',', $_POST['feat_files'])
    ]);

    $sql = "INSERT INTO services (title, slug, price_basic, file_type, demo_url, thumbnail, short_desc, features) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $slug, $price, $type, $demo, $thumbnail, $short_desc, $features_json]);

    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Service - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#050505] text-white p-10">
    <div class="max-w-4xl mx-auto bg-[#111] p-8 rounded-2xl border border-white/10">
        <h2 class="text-2xl font-bold mb-6">Add New Product</h2>
        
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="col-span-2 md:col-span-1">
                <label class="block text-gray-400 mb-2">Product Title</label>
                <input type="text" name="title" required class="w-full bg-black border border-white/20 p-3 rounded-lg focus:border-yellow-500 outline-none">
            </div>
            <div class="col-span-2 md:col-span-1">
                <label class="block text-gray-400 mb-2">Price ($)</label>
                <input type="number" name="price" step="0.01" required class="w-full bg-black border border-white/20 p-3 rounded-lg focus:border-yellow-500 outline-none">
            </div>

            <div class="col-span-2 md:col-span-1">
                <label class="block text-gray-400 mb-2">Product Type</label>
                <select name="file_type" class="w-full bg-black border border-white/20 p-3 rounded-lg text-white">
                    <option value="web">PHP Script / Web</option>
                    <option value="app">Mobile App</option>
                    <option value="ui">UI Kit</option>
                </select>
            </div>
            <div class="col-span-2 md:col-span-1">
                <label class="block text-gray-400 mb-2">Demo / APK URL</label>
                <input type="url" name="demo_url" class="w-full bg-black border border-white/20 p-3 rounded-lg outline-none">
            </div>

            <div class="col-span-2">
                <label class="block text-gray-400 mb-2">Thumbnail Image URL</label>
                <input type="url" name="thumbnail" required class="w-full bg-black border border-white/20 p-3 rounded-lg outline-none" placeholder="https://...">
            </div>

            <div class="col-span-2">
                <label class="block text-gray-400 mb-2">Short Description</label>
                <textarea name="short_desc" rows="3" class="w-full bg-black border border-white/20 p-3 rounded-lg outline-none"></textarea>
            </div>

            <div class="col-span-2 border-t border-white/10 pt-6 mt-4">
                <h3 class="text-yellow-500 font-bold mb-4">Product Features (Separate by comma)</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-xs text-gray-500 uppercase">Top Features</label>
                        <input type="text" name="feat_top" class="w-full bg-black border border-white/10 p-3 rounded text-sm" placeholder="AI Support, Dark Mode, Stripe...">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase">Admin Features</label>
                        <input type="text" name="feat_admin" class="w-full bg-black border border-white/10 p-3 rounded text-sm" placeholder="User Management, Analytics...">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase">User Features</label>
                        <input type="text" name="feat_user" class="w-full bg-black border border-white/10 p-3 rounded text-sm" placeholder="Profile, Orders...">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase">Tech Stack</label>
                        <input type="text" name="feat_tech" class="w-full bg-black border border-white/10 p-3 rounded text-sm" placeholder="Laravel, MySQL, Flutter...">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase">Files Included</label>
                        <input type="text" name="feat_files" class="w-full bg-black border border-white/10 p-3 rounded text-sm" placeholder="Source Code, Documentation...">
                    </div>
                </div>
            </div>

            <div class="col-span-2 flex gap-4 mt-4">
                <button type="submit" class="bg-yellow-500 text-black px-8 py-3 rounded-lg font-bold hover:bg-yellow-400">Save Product</button>
                <a href="index.php" class="bg-white/10 px-8 py-3 rounded-lg font-bold hover:bg-white/20">Cancel</a>
            </div>

        </form>
    </div>
</body>
</html>