<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) header("Location: login.php");
require_once '../../config/db.php';

$upload_dir = '../../uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$insert_success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $slug = strtolower(str_replace(' ', '-', $title));
    $price = $_POST['price'];
    $type = $_POST['file_type'];
    $thumbnail = $_POST['thumbnail'];
    $short_desc = $_POST['short_desc'];

    // ১. ডেমো লিংক এবং টগল সুইচ ডেটা ক্যাপচার
    $demo_links_data = [
        'frontend' => [
            'url' => $_POST['demo_frontend_url'] ?? '',
            'show' => isset($_POST['demo_frontend_show']) ? true : false
        ],
        'admin' => [
            'url' => $_POST['demo_admin_url'] ?? '',
            'show' => isset($_POST['demo_admin_show']) ? true : false
        ],
        'app' => [
            'url' => $_POST['demo_app_url'] ?? '',
            'show' => isset($_POST['demo_app_show']) ? true : false
        ]
    ];
    $demo_url = $_POST['demo_frontend_url'] ?? ''; // মেইন কলামের জন্য

    $media_gallery = [];

    // ২. লোকাল মাল্টিপল ফাইল আপলোড প্রসেসিং (সিকিউরড)
    if (isset($_FILES['media_files']) && !empty($_FILES['media_files']['name'][0])) {
        foreach ($_FILES['media_files']['name'] as $key => $name) {
            $tmp_name = $_FILES['media_files']['tmp_name'][$key];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $new_name = uniqid() . '.' . $ext;

            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'webm', 'gif'];
            if (in_array($ext, $allowed)) {
                if (move_uploaded_file($tmp_name, $upload_dir . $new_name)) {
                    $media_type = in_array($ext, ['mp4', 'webm']) ? 'video' : 'image';
                    $media_gallery[] = ['type' => $media_type, 'url' => 'uploads/' . $new_name];
                }
            }
        }
    }

    // ৩. ম্যানুয়াল URL বা YouTube প্রসেসিং
    if (isset($_POST['media_urls'])) {
        foreach ($_POST['media_urls'] as $url) {
            if (empty(trim($url))) continue;
            
            if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
                $media_gallery[] = ['type' => 'youtube', 'url' => trim($url)];
            } else {
                $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                $media_type = in_array($ext, ['mp4', 'webm']) ? 'video' : 'image';
                $media_gallery[] = ['type' => $media_type, 'url' => trim($url)];
            }
        }
    }

    // ৪. থাম্বনেইল ফিক্স (ফাঁকা থাকলে গ্যালারির প্রথম ছবি থেকে অটোমেটিক নিবে)
    if (empty($thumbnail) && count($media_gallery) > 0) {
        foreach ($media_gallery as $media) {
            if ($media['type'] == 'image') {
                $thumbnail = $media['url'];
                break;
            }
        }
        if (empty($thumbnail)) $thumbnail = $media_gallery[0]['url']; // Fallback
    }

    // ৫. ফিচারগুলো JSON এ কনভার্ট করা
    $features_json = json_encode([
        'top' => array_filter(array_map('trim', explode(',', $_POST['feat_top'] ?? ''))),
        'admin' => array_filter(array_map('trim', explode(',', $_POST['feat_admin'] ?? ''))),
        'user' => array_filter(array_map('trim', explode(',', $_POST['feat_user'] ?? ''))),
        'tech' => array_filter(array_map('trim', explode(',', $_POST['feat_tech'] ?? ''))),
        'files' => array_filter(array_map('trim', explode(',', $_POST['feat_files'] ?? ''))),
        'demo_links' => $demo_links_data, // ডেমো কন্ট্রোলস
        'media_gallery' => $media_gallery // মিডিয়া গ্যালারি
    ]);

    // ৬. ডাটাবেসে সেভ করা
    $sql = "INSERT INTO services (title, slug, price_basic, file_type, demo_url, thumbnail, short_desc, features) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$title, $slug, $price, $type, $demo_url, $thumbnail, $short_desc, $features_json])) {
        $insert_success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Product - Raj Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px;}
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #3e4042; border-radius: 10px; }
        
        /* Tailwind Toggle Custom Colors */
        input:checked ~ .dot { transform: translateX(100%); }
        input:checked ~ .bg-line { background-color: #F4B90B; }
    </style>
</head>
<body class="bg-[#050505] text-white min-h-screen pb-20 pt-10 px-4 font-sans selection:bg-yellow-500 selection:text-black">
    
    <div class="max-w-5xl mx-auto bg-[#111] p-8 rounded-2xl border border-white/10 shadow-2xl">
        
        <div class="flex justify-between items-center mb-8 border-b border-white/10 pb-4">
            <div>
                <a href="index.php" class="text-gray-400 hover:text-white flex items-center gap-2 transition text-sm mb-2">
                    <i class="ri-arrow-left-line"></i> Back to Dashboard
                </a>
                <h2 class="text-2xl font-bold text-white">Add New Product</h2>
            </div>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="col-span-2 md:col-span-1 space-y-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Product Title</label>
                <input type="text" name="title" required placeholder="E.g. E-Commerce App"
                    class="w-full bg-black border border-white/20 p-3 rounded-lg focus:border-yellow-500 outline-none transition text-white">
            </div>

            <div class="col-span-2 md:col-span-1 space-y-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Price ($)</label>
                <input type="number" name="price" step="0.01" required placeholder="0.00"
                    class="w-full bg-black border border-white/20 p-3 rounded-lg focus:border-yellow-500 outline-none transition text-white">
            </div>

            <div class="col-span-2 md:col-span-1 space-y-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Product Type</label>
                <select name="file_type" class="w-full bg-black border border-white/20 p-3 rounded-lg text-white outline-none">
                    <option value="web">PHP Script / Web</option>
                    <option value="app">Mobile App</option>
                    <option value="ui">UI Kit</option>
                </select>
            </div>

            <div class="col-span-2 md:col-span-1 space-y-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Thumbnail Image URL</label>
                <input type="url" name="thumbnail" placeholder="Leave blank to auto-detect from gallery"
                    class="w-full bg-black border border-white/20 p-3 rounded-lg focus:border-yellow-500 outline-none transition text-white">
            </div>

            <div class="col-span-2 space-y-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Short Description</label>
                <textarea name="short_desc" rows="2" placeholder="Brief overview of the product..."
                    class="w-full bg-black border border-white/20 p-3 rounded-lg focus:border-yellow-500 outline-none transition text-white"></textarea>
            </div>

            <!-- ডেমো লিংক কন্ট্রোল সেকশন -->
            <div class="col-span-2 bg-[#18191a] p-6 rounded-xl border border-white/10 mt-4 shadow-inner">
                <h3 class="text-yellow-500 font-bold mb-4 flex items-center gap-2"><i class="ri-links-line"></i> Demo & Preview Controls</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Frontend Demo -->
                    <div class="space-y-3 bg-black p-4 rounded-lg border border-white/5">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-white uppercase">Frontend Demo</label>
                            <label class="flex items-center cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" name="demo_frontend_show" class="sr-only" checked>
                                    <div class="bg-line block bg-gray-600 w-10 h-6 rounded-full transition"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition"></div>
                                </div>
                            </label>
                        </div>
                        <input type="url" name="demo_frontend_url" placeholder="https://..." class="w-full bg-[#111] border border-white/10 p-2 rounded text-sm outline-none text-white focus:border-yellow-500">
                    </div>

                    <!-- Admin Demo -->
                    <div class="space-y-3 bg-black p-4 rounded-lg border border-white/5">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-white uppercase">Admin Panel</label>
                            <label class="flex items-center cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" name="demo_admin_show" class="sr-only">
                                    <div class="bg-line block bg-gray-600 w-10 h-6 rounded-full transition"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition"></div>
                                </div>
                            </label>
                        </div>
                        <input type="url" name="demo_admin_url" placeholder="https://..." class="w-full bg-[#111] border border-white/10 p-2 rounded text-sm outline-none text-white focus:border-yellow-500">
                    </div>

                    <!-- App Demo -->
                    <div class="space-y-3 bg-black p-4 rounded-lg border border-white/5">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-white uppercase">Mobile App (APK)</label>
                            <label class="flex items-center cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" name="demo_app_show" class="sr-only">
                                    <div class="bg-line block bg-gray-600 w-10 h-6 rounded-full transition"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition"></div>
                                </div>
                            </label>
                        </div>
                        <input type="url" name="demo_app_url" placeholder="https://..." class="w-full bg-[#111] border border-white/10 p-2 rounded text-sm outline-none text-white focus:border-yellow-500">
                    </div>

                </div>
            </div>

            <!-- মিডিয়া গ্যালারি সেকশন -->
            <div class="col-span-2 bg-[#18191a] p-6 rounded-xl border border-white/10 mt-4 shadow-inner">
                <div class="flex justify-between items-center mb-4 border-b border-white/5 pb-3">
                    <h3 class="text-yellow-500 font-bold flex items-center gap-2"><i class="ri-image-add-fill"></i> Media Gallery Manager</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-white font-bold uppercase mb-2">1. Upload Local Images/Videos</p>
                        <input type="file" name="media_files[]" multiple accept="image/*,video/mp4,video/webm" 
                            class="w-full bg-black border border-white/10 p-2 rounded text-sm outline-none text-gray-400 cursor-pointer file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-yellow-500 file:text-black hover:file:bg-yellow-400">
                        <p class="text-[10px] text-gray-500 mt-2">Select multiple files (Hold CTRL/CMD). Supported: JPG, PNG, MP4.</p>
                    </div>
                    <div>
                        <p class="text-xs text-white font-bold uppercase mb-2">2. Or Provide External URLs</p>
                        <div id="url_container" class="space-y-2"></div>
                        <button type="button" onclick="addUrlField()" class="mt-2 text-blue-400 text-xs font-bold hover:underline flex items-center gap-1">
                            <i class="ri-add-line"></i> Add URL Link
                        </button>
                    </div>
                </div>
            </div>

            <!-- ফিচারস ম্যানেজার -->
            <div class="col-span-2 bg-white/5 p-6 rounded-xl border border-white/10 mt-2">
                <h3 class="text-yellow-500 font-bold mb-4 flex items-center gap-2"><i class="ri-list-settings-line"></i> Features Manager</h3>
                <p class="text-xs text-gray-400 mb-6">Separate items with a comma (e.g. Dark Mode, PWA, Stripe)</p>
                
                <div class="space-y-5">
                    <div class="space-y-1">
                        <label class="text-xs text-white font-bold uppercase">🔥 Top Features</label>
                        <input type="text" name="feat_top" placeholder="E.g. Fully Responsive, Clean Code..." 
                            class="w-full bg-black border border-white/10 p-3 rounded text-sm focus:border-yellow-500 outline-none text-white">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs text-white font-bold uppercase">🛡️ Admin Features</label>
                        <input type="text" name="feat_admin" placeholder="E.g. Dashboard, Analytics, User Management..." 
                            class="w-full bg-black border border-white/10 p-3 rounded text-sm focus:border-yellow-500 outline-none text-white">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs text-white font-bold uppercase">👤 User Features</label>
                        <input type="text" name="feat_user" placeholder="E.g. Profile, Wishlist, Order Tracking..." 
                            class="w-full bg-black border border-white/10 p-3 rounded text-sm focus:border-yellow-500 outline-none text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs text-white font-bold uppercase">💻 Tech Stack</label>
                            <input type="text" name="feat_tech" placeholder="E.g. PHP, Laravel, Flutter..." 
                                class="w-full bg-black border border-white/10 p-3 rounded text-sm focus:border-yellow-500 outline-none text-white">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs text-white font-bold uppercase">📦 Files Included</label>
                            <input type="text" name="feat_files" placeholder="E.g. Source Code, SQL File, Documentation..." 
                                class="w-full bg-black border border-white/10 p-3 rounded text-sm focus:border-yellow-500 outline-none text-white">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="col-span-2 flex gap-4 mt-4 border-t border-white/10 pt-6">
                <button type="submit" class="bg-yellow-500 text-black px-8 py-3.5 rounded-lg font-bold hover:bg-yellow-400 transition transform hover:-translate-y-1 shadow-lg shadow-yellow-500/20 flex items-center gap-2">
                    <i class="ri-add-circle-fill text-xl"></i> Save & Publish Product
                </button>
            </div>

        </form>
    </div>

    <!-- JavaScripts -->
    <script>
        // SweetAlert2 Success Logic
        <?php if($insert_success): ?>
            Swal.fire({
                title: 'Published!',
                text: 'New product has been added successfully!',
                icon: 'success',
                confirmButtonColor: '#F4B90B',
                background: '#18191a',
                color: '#fff',
                iconColor: '#45bd62'
            }).then((result) => {
                window.location.href = 'index.php'; // রিডাইরেক্ট হবে
            });
        <?php endif; ?>

        // Add Dynamic URL Field
        function addUrlField() {
            const container = document.getElementById('url_container');
            const inputDiv = document.createElement('div');
            inputDiv.className = 'flex gap-2 items-center';
            
            inputDiv.innerHTML = `
                <div class="flex-1 relative">
                    <input type="url" name="media_urls[]" placeholder="https://youtube.com/..." 
                        class="w-full bg-black border border-white/10 p-2.5 rounded text-sm focus:border-yellow-500 outline-none text-white">
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-400 bg-red-500/10 hover:bg-red-500/20 p-2 rounded transition">
                    <i class="ri-delete-bin-line text-lg"></i>
                </button>
            `;
            container.appendChild(inputDiv);
        }
        
        // Add one empty field by default
        addUrlField();
    </script>
</body>
</html>