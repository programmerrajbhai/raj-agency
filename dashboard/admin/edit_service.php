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

// ২. JSON ফিচারগুলো ডিকোড করা
$features = json_decode($service['features'], true);
$existing_media = isset($features['media_gallery']) ? $features['media_gallery'] : [];

// ডেমো লিংক কন্ট্রোলার ডেটা (আগে না থাকলে ডিফল্ট ভ্যালু তৈরি হবে)
$demo_links = isset($features['demo_links']) ? $features['demo_links'] : [
    'frontend' => ['url' => $service['demo_url'], 'show' => true],
    'admin' => ['url' => '', 'show' => false],
    'app' => ['url' => '', 'show' => false]
];

function arrayToCsv($arr) {
    return (is_array($arr)) ? implode(', ', $arr) : '';
}

function getPreviewUrl($url) {
    if (empty($url)) return '';
    if (strpos($url, 'http') === 0) return $url; 
    return '../../' . $url; 
}

$update_success = false;

// ৩. আপডেট রিকোয়েস্ট হ্যান্ডেল করা
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $slug = strtolower(str_replace(' ', '-', $title));
    $price = $_POST['price'];
    $type = $_POST['file_type'];
    $thumbnail = $_POST['thumbnail'];
    $short_desc = $_POST['short_desc'];

    // ডেমো লিংক এবং টগল সুইচ ডেটা ক্যাপচার
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
    // মেইন ডেমো URL আপডেট
    $demo = !empty($_POST['demo_frontend_url']) ? $_POST['demo_frontend_url'] : $service['demo_url'];

    $upload_dir = '../../uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    // ৪. এক্সিস্টিং মিডিয়া এডিট (রিমুভ বা রিপ্লেস)
    $final_media = [];
    foreach ($existing_media as $index => $media) {
        // যদি রিমুভ করার জন্য চেক করা থাকে, তবে স্কিপ করবে
        if (isset($_POST['remove_media']) && in_array($index, $_POST['remove_media'])) {
            continue; 
        }

        // যদি ওই পজিশনে নতুন ফাইল রিপ্লেস করা হয়
        if (isset($_FILES['replace_media']['name'][$index]) && !empty($_FILES['replace_media']['name'][$index])) {
            $tmp_name = $_FILES['replace_media']['tmp_name'][$index];
            $name = $_FILES['replace_media']['name'][$index];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'webm', 'gif'];
            
            if (in_array($ext, $allowed)) {
                $new_name = uniqid() . '.' . $ext;
                if (move_uploaded_file($tmp_name, $upload_dir . $new_name)) {
                    $media_type = in_array($ext, ['mp4', 'webm']) ? 'video' : 'image';
                    $media = ['type' => $media_type, 'url' => 'uploads/' . $new_name]; // আপডেট করা হলো
                }
            }
        }
        $final_media[] = $media; // ফাইনাল লিস্টে রাখা হলো
    }

    // ৫. নতুন মাল্টিপল ফাইল আপলোড
    if(isset($_FILES['media_files']) && !empty($_FILES['media_files']['name'][0])) {
        foreach($_FILES['media_files']['name'] as $key => $name) {
            $tmp_name = $_FILES['media_files']['tmp_name'][$key];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $new_name = uniqid() . '.' . $ext;
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'webm', 'gif'];
            if(in_array($ext, $allowed)) {
                if(move_uploaded_file($tmp_name, $upload_dir . $new_name)) {
                    $media_type = in_array($ext, ['mp4', 'webm']) ? 'video' : 'image';
                    $final_media[] = ['type' => $media_type, 'url' => 'uploads/' . $new_name];
                }
            }
        }
    }

    // ৬. ম্যানুয়াল URL বা YouTube প্রসেসিং
    if(isset($_POST['media_urls'])) {
        foreach($_POST['media_urls'] as $url) {
            if(empty(trim($url))) continue;
            if(strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
                $final_media[] = ['type' => 'youtube', 'url' => trim($url)];
            } else {
                $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                $media_type = in_array($ext, ['mp4', 'webm']) ? 'video' : 'image';
                $final_media[] = ['type' => $media_type, 'url' => trim($url)];
            }
        }
    }

    // থাম্বনেইল ফিক্স
    if(empty($thumbnail) && count($final_media) > 0) {
        foreach($final_media as $media) {
            if($media['type'] == 'image') {
                $thumbnail = $media['url'];
                break;
            }
        }
        if(empty($thumbnail)) $thumbnail = $final_media[0]['url'];
    }

    // ৭. ফিচারগুলো JSON এ কনভার্ট
    $features_json = json_encode([
        'top' => array_filter(array_map('trim', explode(',', $_POST['feat_top'] ?? ''))),
        'admin' => array_filter(array_map('trim', explode(',', $_POST['feat_admin'] ?? ''))),
        'user' => array_filter(array_map('trim', explode(',', $_POST['feat_user'] ?? ''))),
        'tech' => array_filter(array_map('trim', explode(',', $_POST['feat_tech'] ?? ''))),
        'files' => array_filter(array_map('trim', explode(',', $_POST['feat_files'] ?? ''))),
        'demo_links' => $demo_links_data, // নতুন ডেমো কন্ট্রোলস
        'media_gallery' => $final_media
    ]);

    // ডাটাবেস আপডেট কুয়েরি
    $sql = "UPDATE services SET 
            title=?, slug=?, price_basic=?, file_type=?, demo_url=?, thumbnail=?, short_desc=?, features=? 
            WHERE id=?";
    $stmt = $pdo->prepare($sql);
    if($stmt->execute([$title, $slug, $price, $type, $demo, $thumbnail, $short_desc, $features_json, $id])) {
        $update_success = true;
        // আপডেট হওয়ার পর লেটেস্ট ডাটা পেজে শো করানোর জন্য পুনরায় ফেচ করা হচ্ছে
        $features['demo_links'] = $demo_links_data;
        $existing_media = $final_media;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Service - Raj Admin</title>
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
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 border-b border-white/10 pb-4 gap-4">
            <div>
                <a href="index.php" class="text-gray-400 hover:text-white flex items-center gap-2 transition text-sm mb-2">
                    <i class="ri-arrow-left-line"></i> Back to Dashboard
                </a>
                <h2 class="text-2xl font-bold text-white">Edit Product: <span class="text-yellow-500"><?php echo htmlspecialchars($service['title']); ?></span></h2>
            </div>
            
            <!-- Live Preview Button -->
            <a href="../../index.php?page=service-details&id=<?php echo $id; ?>" target="_blank" 
               class="bg-blue-600/20 text-blue-400 border border-blue-500/30 px-6 py-2.5 rounded-lg font-bold hover:bg-blue-600 hover:text-white transition flex items-center gap-2">
                <i class="ri-eye-line"></i> Live Preview
            </a>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
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
                <label class="text-xs font-bold text-gray-500 uppercase">Short Description</label>
                <textarea name="short_desc" rows="1" 
                    class="w-full bg-black border border-white/20 p-3 rounded-lg focus:border-yellow-500 outline-none transition"><?php echo htmlspecialchars($service['short_desc']); ?></textarea>
            </div>

            <!-- ডেমো লিংক কন্ট্রোল সেকশন (Full Control) -->
            <div class="col-span-2 bg-[#18191a] p-6 rounded-xl border border-white/10 mt-4 shadow-inner">
                <h3 class="text-yellow-500 font-bold mb-4 flex items-center gap-2"><i class="ri-links-line"></i> Demo & Preview Controls</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Frontend Demo -->
                    <div class="space-y-3 bg-black p-4 rounded-lg border border-white/5">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-white uppercase">Frontend Demo</label>
                            <label class="flex items-center cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" name="demo_frontend_show" class="sr-only" <?php echo ($demo_links['frontend']['show'] ?? true) ? 'checked' : ''; ?>>
                                    <div class="bg-line block bg-gray-600 w-10 h-6 rounded-full transition"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition"></div>
                                </div>
                            </label>
                        </div>
                        <input type="url" name="demo_frontend_url" value="<?php echo htmlspecialchars($demo_links['frontend']['url'] ?? $service['demo_url']); ?>" placeholder="https://..." class="w-full bg-[#111] border border-white/10 p-2 rounded text-sm outline-none text-white focus:border-yellow-500">
                    </div>

                    <!-- Admin Demo -->
                    <div class="space-y-3 bg-black p-4 rounded-lg border border-white/5">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-white uppercase">Admin Panel</label>
                            <label class="flex items-center cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" name="demo_admin_show" class="sr-only" <?php echo ($demo_links['admin']['show'] ?? false) ? 'checked' : ''; ?>>
                                    <div class="bg-line block bg-gray-600 w-10 h-6 rounded-full transition"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition"></div>
                                </div>
                            </label>
                        </div>
                        <input type="url" name="demo_admin_url" value="<?php echo htmlspecialchars($demo_links['admin']['url'] ?? ''); ?>" placeholder="https://..." class="w-full bg-[#111] border border-white/10 p-2 rounded text-sm outline-none text-white focus:border-yellow-500">
                    </div>

                    <!-- App Demo -->
                    <div class="space-y-3 bg-black p-4 rounded-lg border border-white/5">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-white uppercase">Mobile App (APK)</label>
                            <label class="flex items-center cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" name="demo_app_show" class="sr-only" <?php echo ($demo_links['app']['show'] ?? false) ? 'checked' : ''; ?>>
                                    <div class="bg-line block bg-gray-600 w-10 h-6 rounded-full transition"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition"></div>
                                </div>
                            </label>
                        </div>
                        <input type="url" name="demo_app_url" value="<?php echo htmlspecialchars($demo_links['app']['url'] ?? ''); ?>" placeholder="https://..." class="w-full bg-[#111] border border-white/10 p-2 rounded text-sm outline-none text-white focus:border-yellow-500">
                    </div>

                </div>
            </div>

            <!-- মিডিয়া গ্যালারি কন্ট্রোল সেকশন (Individual Remove & Replace) -->
            <div class="col-span-2 bg-[#18191a] p-6 rounded-xl border border-white/10 mt-4 shadow-inner">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-yellow-500 font-bold flex items-center gap-2"><i class="ri-image-edit-line"></i> Media Gallery Manager</h3>
                </div>
                
                <?php if(count($existing_media) > 0): ?>
                <label class="text-xs text-gray-400 font-bold uppercase block mb-3 border-b border-white/5 pb-2">Existing Items (Edit / Remove)</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <?php foreach($existing_media as $index => $media): ?>
                        <div class="bg-black rounded-lg border border-white/10 overflow-hidden relative group">
                            
                            <!-- Preview -->
                            <div class="h-28 relative bg-[#111] flex items-center justify-center">
                                <?php if($media['type'] == 'youtube'): ?>
                                    <i class="ri-youtube-fill text-red-500 text-4xl"></i>
                                <?php elseif($media['type'] == 'video'): ?>
                                    <video src="<?php echo htmlspecialchars(getPreviewUrl($media['url'])); ?>" class="w-full h-full object-cover opacity-70"></video>
                                    <i class="ri-play-circle-fill text-white text-3xl absolute"></i>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars(getPreviewUrl($media['url'])); ?>" class="w-full h-full object-cover">
                                <?php endif; ?>
                                
                                <!-- View Full Button -->
                                <a href="<?php echo htmlspecialchars(getPreviewUrl($media['url'])); ?>" target="_blank" class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300">
                                    <i class="ri-eye-line text-white text-2xl"></i>
                                </a>
                            </div>

                            <!-- Actions -->
                            <div class="p-2 space-y-2">
                                <label class="flex items-center gap-2 text-xs text-red-400 font-bold cursor-pointer hover:bg-red-500/10 p-1.5 rounded transition">
                                    <input type="checkbox" name="remove_media[]" value="<?php echo $index; ?>" class="accent-red-500">
                                    <i class="ri-delete-bin-line"></i> Remove
                                </label>
                                
                                <div class="text-[10px] text-gray-400 px-1">Or Replace File:</div>
                                <input type="file" name="replace_media[<?php echo $index; ?>]" accept="image/*,video/mp4,video/webm" 
                                    class="w-full text-[10px] file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-white/10 file:text-white cursor-pointer hover:file:bg-white/20">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Add New Items -->
                <div class="border-t border-white/5 pt-4">
                    <label class="text-xs text-white font-bold uppercase block mb-3"><i class="ri-add-circle-fill text-green-500"></i> Add New Media Files</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-gray-400 mb-2">Upload Local Images/Videos</p>
                            <input type="file" name="media_files[]" multiple accept="image/*,video/mp4,video/webm" 
                                class="w-full bg-black border border-white/10 p-2 rounded text-sm outline-none text-gray-400 cursor-pointer file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:bg-yellow-500 file:text-black hover:file:bg-yellow-400">
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-2">Or Provide External URLs</p>
                            <div id="url_container" class="space-y-2"></div>
                            <button type="button" onclick="addUrlField()" class="mt-2 text-blue-400 text-xs font-bold hover:underline flex items-center gap-1">
                                + Add another link
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ফিচারস ম্যানেজার -->
            <div class="col-span-2 bg-white/5 p-6 rounded-xl border border-white/10 mt-2">
                <h3 class="text-yellow-500 font-bold mb-4 flex items-center gap-2"><i class="ri-list-settings-line"></i> Features Manager</h3>
                
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

            <!-- Submit Button -->
            <div class="col-span-2 flex gap-4 mt-4">
                <button type="submit" class="bg-yellow-500 text-black px-8 py-3.5 rounded-lg font-bold hover:bg-yellow-400 transition transform hover:-translate-y-1 shadow-lg shadow-yellow-500/20 flex items-center gap-2">
                    <i class="ri-save-3-fill text-xl"></i> Save & Update Product
                </button>
            </div>

        </form>
    </div>

    <!-- JavaScripts -->
    <script>
        // SweetAlert2 Success Logic
        <?php if($update_success): ?>
            Swal.fire({
                title: 'Excellent!',
                text: 'Product has been updated successfully!',
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
                    <input type="url" name="media_urls[]" placeholder="URL..." 
                        class="w-full bg-black border border-white/10 p-2 rounded text-sm focus:border-yellow-500 outline-none text-white">
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-400 p-2 transition">
                    <i class="ri-close-circle-fill text-lg"></i>
                </button>
            `;
            container.appendChild(inputDiv);
        }
        
        // Add one empty field by default
        addUrlField();
    </script>
</body>
</html>