<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) header("Location: login.php");
require_once '../../config/db.php';

// ১. আইডি চেক করা এবং ডাটা ফেচ করা
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$id]);
$service = $stmt->fetch();

if (!$service) {
    echo "Service not found!";
    exit;
}

// ২. JSON ফিচারগুলো ডিকোড করা (যাতে ইনপুট ফিল্ডে দেখানো যায়)
$features = json_decode($service['features'], true);

// হেল্পার ফাংশন: অ্যারে থেকে কমা-সেপারেটেড স্ট্রিং বানানোর জন্য
function arrayToCsv($arr) {
    return (is_array($arr)) ? implode(', ', $arr) : '';
}

// ৩. আপডেট রিকোয়েস্ট হ্যান্ডেল করা
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $slug = strtolower(str_replace(' ', '-', $title));
    $price = $_POST['price'];
    $type = $_POST['file_type'];
    $demo = $_POST['demo_url'];
    $thumbnail = $_POST['thumbnail'];
    $short_desc = $_POST['short_desc'];

    // ফিচারগুলো আবার JSON এ কনভার্ট করা
    // array_map('trim', ...) ব্যবহার করা হয়েছে যাতে কমার আগে-পিছে স্পেস রিমুভ হয়
    $features_json = json_encode([
        'top' => array_map('trim', explode(',', $_POST['feat_top'])),
        'admin' => array_map('trim', explode(',', $_POST['feat_admin'])),
        'user' => array_map('trim', explode(',', $_POST['feat_user'])),
        'tech' => array_map('trim', explode(',', $_POST['feat_tech'])),
        'files' => array_map('trim', explode(',', $_POST['feat_files']))
    ]);

    // ডাটাবেস আপডেট কুয়েরি
    $sql = "UPDATE services SET 
            title=?, slug=?, price_basic=?, file_type=?, demo_url=?, thumbnail=?, short_desc=?, features=? 
            WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $slug, $price, $type, $demo, $thumbnail, $short_desc, $features_json, $id]);

    // সাকসেস হলে রিডাইরেক্ট
    echo "<script>alert('Product updated successfully!'); window.location.href='index.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Service - Raj Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="bg-[#050505] text-white min-h-screen p-10">
    
    <div class="max-w-4xl mx-auto bg-[#111] p-8 rounded-2xl border border-white/10 shadow-2xl">
        
        <div class="flex justify-between items-center mb-8 border-b border-white/10 pb-4">
            <h2 class="text-2xl font-bold text-white">Edit Product: <span class="text-yellow-500"><?php echo htmlspecialchars($service['title']); ?></span></h2>
            <a href="index.php" class="text-gray-400 hover:text-white flex items-center gap-2 transition">
                <i class="ri-arrow-left-line"></i> Back to Dashboard
            </a>
        </div>
        
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="col-span-2 md:col-span-1 space-y-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Product Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($service['title']); ?>" required 
                    class="w-full bg-black border border-white/20 p-3 rounded-lg focus:border-yellow-500 outline-none transition">
            </div>

            <div class="col-span-2 md:col-span-1 space-y-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Price ($)</label>
                <input type="number" name="price" step="0.01" value="<?php echo $service['price_basic']; ?>" required 
                    class="w-full bg-black border border-white/20 p-3 rounded-lg focus:border-yellow-500 outline-none transition">
            </div>

            <div class="col-span-2 md:col-span-1 space-y-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Product Type</label>
                <select name="file_type" class="w-full bg-black border border-white/20 p-3 rounded-lg text-white outline-none">
                    <option value="web" <?php echo ($service['file_type'] == 'web') ? 'selected' : ''; ?>>PHP Script / Web</option>
                    <option value="app" <?php echo ($service['file_type'] == 'app') ? 'selected' : ''; ?>>Mobile App</option>
                    <option value="ui" <?php echo ($service['file_type'] == 'ui') ? 'selected' : ''; ?>>UI Kit</option>
                </select>
            </div>

            <div class="col-span-2 md:col-span-1 space-y-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Demo / APK URL</label>
                <input type="url" name="demo_url" value="<?php echo htmlspecialchars($service['demo_url']); ?>" 
                    class="w-full bg-black border border-white/20 p-3 rounded-lg focus:border-yellow-500 outline-none transition">
            </div>

            <div class="col-span-2 space-y-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Thumbnail Image URL</label>
                <input type="url" name="thumbnail" value="<?php echo htmlspecialchars($service['thumbnail']); ?>" required 
                    class="w-full bg-black border border-white/20 p-3 rounded-lg focus:border-yellow-500 outline-none transition">
                <div class="mt-2">
                    <img src="<?php echo htmlspecialchars($service['thumbnail']); ?>" class="h-20 rounded border border-white/10" alt="Preview">
                </div>
            </div>

            <div class="col-span-2 space-y-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Short Description</label>
                <textarea name="short_desc" rows="3" 
                    class="w-full bg-black border border-white/20 p-3 rounded-lg focus:border-yellow-500 outline-none transition"><?php echo htmlspecialchars($service['short_desc']); ?></textarea>
            </div>

            <div class="col-span-2 bg-white/5 p-6 rounded-xl border border-white/10 mt-4">
                <h3 class="text-yellow-500 font-bold mb-4 flex items-center gap-2"><i class="ri-list-settings-line"></i> Features Manager</h3>
                <p class="text-xs text-gray-400 mb-6">Separate items with a comma (e.g. Feature 1, Feature 2, Feature 3)</p>
                
                <div class="space-y-5">
                    <div class="space-y-1">
                        <label class="text-xs text-white font-bold uppercase">🔥 Top Features</label>
                        <input type="text" name="feat_top" value="<?php echo arrayToCsv($features['top'] ?? []); ?>" 
                            class="w-full bg-black border border-white/10 p-3 rounded text-sm focus:border-yellow-500 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs text-white font-bold uppercase">🛡️ Admin Features</label>
                        <input type="text" name="feat_admin" value="<?php echo arrayToCsv($features['admin'] ?? []); ?>" 
                            class="w-full bg-black border border-white/10 p-3 rounded text-sm focus:border-yellow-500 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs text-white font-bold uppercase">👤 User Features</label>
                        <input type="text" name="feat_user" value="<?php echo arrayToCsv($features['user'] ?? []); ?>" 
                            class="w-full bg-black border border-white/10 p-3 rounded text-sm focus:border-yellow-500 outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs text-white font-bold uppercase">💻 Tech Stack</label>
                            <input type="text" name="feat_tech" value="<?php echo arrayToCsv($features['tech'] ?? []); ?>" 
                                class="w-full bg-black border border-white/10 p-3 rounded text-sm focus:border-yellow-500 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs text-white font-bold uppercase">📦 Files Included</label>
                            <input type="text" name="feat_files" value="<?php echo arrayToCsv($features['files'] ?? []); ?>" 
                                class="w-full bg-black border border-white/10 p-3 rounded text-sm focus:border-yellow-500 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-2 flex gap-4 mt-6 pt-6 border-t border-white/10">
                <button type="submit" class="bg-yellow-500 text-black px-8 py-3 rounded-lg font-bold hover:bg-yellow-400 transition transform hover:scale-105 shadow-lg shadow-yellow-500/20">
                    <i class="ri-save-line mr-1"></i> Update Product
                </button>
                <a href="index.php" class="bg-white/10 px-8 py-3 rounded-lg font-bold hover:bg-white/20 transition flex items-center">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</body>
</html>