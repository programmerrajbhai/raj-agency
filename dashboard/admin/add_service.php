<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) header("Location: login.php");
require_once '../../config/db.php';

// ফোল্ডার না থাকলে তৈরি করে নিবে
$upload_dir = '../../uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $slug = strtolower(str_replace(' ', '-', $title));
    $price = $_POST['price'];
    $type = $_POST['file_type'];
    $short_desc = $_POST['short_desc'];
    
    $media_gallery = [];

    // ১. আপলোড করা ফাইলগুলো (Images & Videos) প্রসেস করা
    if (isset($_FILES['media_files']) && !empty($_FILES['media_files']['name'][0])) {
        foreach ($_FILES['media_files']['name'] as $key => $name) {
            $tmp_name = $_FILES['media_files']['tmp_name'][$key];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $new_name = uniqid() . '.' . $ext;
            $destination = $upload_dir . $new_name;

            if (move_uploaded_file($tmp_name, $destination)) {
                $media_type = in_array($ext, ['mp4', 'webm', 'ogg']) ? 'video' : 'image';
                $media_gallery[] = [
                    'type' => $media_type, 
                    'url' => 'uploads/' . $new_name // ফ্রন্টএন্ড থেকে লোড করার জন্য পাথ
                ];
            }
        }
    }

    // ২. URL বা Youtube লিংকগুলো প্রসেস করা
    if (isset($_POST['media_urls']) && is_array($_POST['media_urls'])) {
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

    // ৩. মেইন থাম্বনেল সিলেক্ট করা (পোর্টফোলিও পেজে দেখানোর জন্য)
    $thumbnail = '';
    foreach ($media_gallery as $media) {
        if ($media['type'] == 'image') {
            $thumbnail = $media['url']; // প্রথম ছবিটি থাম্বনেল হবে
            break;
        }
    }
    // যদি কোনো ছবি না থাকে, কিন্তু ভিডিও বা ইউটিউব থাকে, তার লিংকটাই ডিফল্ট করে দাও
    if (empty($thumbnail) && count($media_gallery) > 0) {
        $thumbnail = $media_gallery[0]['url'];
    }

    // ৪. ফিচারস এবং গ্যালারি JSON এ সেভ করা
    $features_json = json_encode([
        'top' => array_map('trim', explode(',', $_POST['feat_top'])),
        'admin' => array_map('trim', explode(',', $_POST['feat_admin'])),
        'tech' => array_map('trim', explode(',', $_POST['feat_tech'])),
        'media_gallery' => $media_gallery // সম্পূর্ণ মিডিয়া কালেকশন এখানে থাকবে
    ]);

    // ডাটাবেসে সেভ করা
    $sql = "INSERT INTO services (title, slug, price_basic, file_type, demo_url, thumbnail, short_desc, features) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $demo_url = $_POST['demo_url'] ?? '';
    
    $stmt->execute([$title, $slug, $price, $type, $demo_url, $thumbnail, $short_desc, $features_json]);

    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Create Product - Facebook Style</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { background-color: #050505; }
        .fb-card { background: #242526; border: 1px solid #3e4042; }
        .fb-input { background: transparent; border: none; font-size: 1.5rem; outline: none; resize: none; color: #e4e6eb; }
        .fb-input::placeholder { color: #828282; }
        .feature-box { border: 1px solid #3e4042; background: #18191a; transition: all 0.2s; }
        .feature-box:focus-within { border-color: #F4B90B; }
        .media-grid { display: grid; gap: 4px; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); }
        .media-item { position: relative; padding-top: 100%; background: #18191a; border-radius: 8px; overflow: hidden; border: 1px solid #3e4042; }
        .media-item img, .media-item video, .media-item iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body class="text-white flex justify-center py-10 px-4 font-sans selection:bg-yellow-500 selection:text-black">

    <div class="w-full max-w-[650px]">
        
        <div class="flex items-center justify-between mb-6">
            <a href="index.php" class="text-gray-400 hover:text-white transition flex items-center gap-1">
                <i class="ri-arrow-left-s-line text-2xl"></i> ড্যাশবোর্ডে ফিরুন
            </a>
            <h2 class="text-xl font-bold">নতুন প্রোডাক্ট</h2>
            <div class="w-8"></div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="fb-card rounded-xl shadow-2xl overflow-hidden relative">
            
            <div class="p-4 border-b border-[#3e4042] flex items-center justify-center relative bg-[#242526]">
                <span class="font-bold text-lg text-gray-200">Create post</span>
            </div>

            <div class="p-4 flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center font-black text-black shadow-lg">R</div>
                <div>
                    <h3 class="font-bold text-[15px]">Raj Agency</h3>
                    <div class="flex gap-1 items-center px-2 py-0.5 bg-[#3a3b3c] rounded-md text-[11px] font-semibold text-gray-300 w-max mt-0.5">
                        <i class="ri-global-line"></i> Public
                    </div>
                </div>
            </div>

            <div class="px-4 pb-4 max-h-[65vh] overflow-y-auto custom-scrollbar">
                
                <textarea name="title" required placeholder="প্রোডাক্টের নাম বা টাইটেল লিখুন..." 
                    class="fb-input w-full min-h-[80px] mb-2" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>

                <textarea name="short_desc" placeholder="বিস্তারিত বিবরণ..." rows="2" 
                    class="w-full bg-transparent outline-none text-[15px] text-gray-300 resize-none mb-4" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>

                <div id="media-preview-container" class="media-grid mb-4 hidden"></div>

                <div id="url-inputs-container" class="space-y-2 mb-4"></div>

                <div class="border border-[#3e4042] rounded-lg p-3 flex items-center justify-between shadow-sm mt-4 bg-[#18191a]">
                    <span class="font-semibold text-[15px] text-gray-300 pl-2">Add to your post</span>
                    <div class="flex items-center gap-1">
                        
                        <label class="w-10 h-10 rounded-full hover:bg-[#3a3b3c] flex items-center justify-center cursor-pointer transition" title="Photo/Video">
                            <i class="ri-image-add-fill text-[#45bd62] text-2xl"></i>
                            <input type="file" name="media_files[]" id="media_files" multiple accept="image/*,video/mp4,video/webm" class="hidden" onchange="previewLocalFiles(this)">
                        </label>
                        
                        <button type="button" onclick="addUrlInput()" class="w-10 h-10 rounded-full hover:bg-[#3a3b3c] flex items-center justify-center transition" title="Add Link / YouTube">
                            <i class="ri-link text-[#2d88ff] text-2xl"></i>
                        </button>
                        
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-4">
                    <div class="feature-box rounded-lg p-2.5">
                        <label class="text-[10px] text-gray-400 uppercase font-bold pl-1 block mb-1">মূল্য ($)</label>
                        <input type="number" step="0.01" name="price" required class="w-full bg-transparent border-none outline-none text-white font-bold text-lg p-1" placeholder="0.00">
                    </div>
                    <div class="feature-box rounded-lg p-2.5">
                        <label class="text-[10px] text-gray-400 uppercase font-bold pl-1 block mb-1">ধরণ (Type)</label>
                        <select name="file_type" class="w-full bg-transparent border-none outline-none text-white font-bold p-1">
                            <option value="web">PHP Script</option>
                            <option value="app">Mobile App</option>
                            <option value="ui">UI Kit</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 border border-[#3e4042] bg-[#18191a] rounded-xl p-4">
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-gray-400 uppercase font-bold pl-1 mb-1 block">Top Features</label>
                            <input type="text" name="feat_top" placeholder="Dark Mode, PWA, Stripe..." class="w-full bg-[#242526] text-sm p-2.5 rounded-lg outline-none border border-[#3e4042] focus:border-yellow-500 text-white">
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 uppercase font-bold pl-1 mb-1 block">Admin Features</label>
                            <input type="text" name="feat_admin" placeholder="Dashboard, Analytics..." class="w-full bg-[#242526] text-sm p-2.5 rounded-lg outline-none border border-[#3e4042] focus:border-yellow-500 text-white">
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 uppercase font-bold pl-1 mb-1 block">Tech Stack</label>
                            <input type="text" name="feat_tech" placeholder="PHP, Laravel, Flutter..." class="w-full bg-[#242526] text-sm p-2.5 rounded-lg outline-none border border-[#3e4042] focus:border-yellow-500 text-white">
                        </div>
                    </div>
                </div>

            </div>

            <div class="p-4 border-t border-[#3e4042] bg-[#242526]">
                <button type="submit" class="w-full py-2.5 bg-[#e4e6eb] hover:bg-white text-black font-bold rounded-lg transition-all active:scale-95 text-[15px]">
                    Post
                </button>
            </div>
        </form>
    </div>

    <script>
        const previewContainer = document.getElementById('media-preview-container');
        const urlContainer = document.getElementById('url-inputs-container');

        // ইউটিউব আইডি বের করার ফাংশন
        function getYouTubeId(url) {
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
            const match = url.match(regExp);
            return (match && match[2].length === 11) ? match[2] : null;
        }

        // লোকাল ফাইল প্রিভিউ (ছবি এবং ভিডিও)
        function previewLocalFiles(input) {
            if (input.files && input.files.length > 0) {
                previewContainer.classList.remove('hidden');
                previewContainer.innerHTML = ''; // আগের প্রিভিউ ক্লিয়ার করে নতুন গুলা দেখাবে

                Array.from(input.files).forEach(file => {
                    const fileUrl = URL.createObjectURL(file);
                    const div = document.createElement('div');
                    div.className = 'media-item';

                    if (file.type.startsWith('video/')) {
                        div.innerHTML = `<video src="${fileUrl}" autoplay muted loop></video>`;
                    } else {
                        div.innerHTML = `<img src="${fileUrl}" alt="preview">`;
                    }
                    previewContainer.appendChild(div);
                });
            }
        }

        // URL ইনপুট ফিল্ড যোগ করা এবং প্রিভিউ করা
        function addUrlInput() {
            const inputDiv = document.createElement('div');
            inputDiv.className = 'flex items-center gap-2';
            
            const input = document.createElement('input');
            input.type = 'url';
            input.name = 'media_urls[]';
            input.placeholder = 'https:// YouTube Video বা Image Link দিন...';
            input.className = 'flex-1 bg-[#18191a] text-sm p-3 rounded-lg outline-none border border-[#3e4042] focus:border-[#2d88ff] text-white';
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.innerHTML = '<i class="ri-close-circle-fill text-gray-500 hover:text-red-500 text-xl"></i>';
            removeBtn.onclick = () => { inputDiv.remove(); renderUrlPreviews(); };

            input.oninput = renderUrlPreviews;

            inputDiv.appendChild(input);
            inputDiv.appendChild(removeBtn);
            urlContainer.appendChild(inputDiv);
        }

        // URL প্রিভিউ রেন্ডার করা
        function renderUrlPreviews() {
            previewContainer.classList.remove('hidden');
            
            // শুধু URL গুলোর জন্য প্রিভিউ আপডেট করব, লোকাল ফাইল গুলো রেখে দিব
            const existingFiles = document.getElementById('media_files').files;
            previewContainer.innerHTML = ''; 
            
            // রি-রেন্ডার লোকাল ফাইলস
            if(existingFiles.length > 0) {
                Array.from(existingFiles).forEach(file => {
                    const fileUrl = URL.createObjectURL(file);
                    const div = document.createElement('div');
                    div.className = 'media-item';
                    div.innerHTML = file.type.startsWith('video/') ? `<video src="${fileUrl}" autoplay muted loop></video>` : `<img src="${fileUrl}">`;
                    previewContainer.appendChild(div);
                });
            }

            // রেন্ডার URL প্রিভিউ
            const urlInputs = document.querySelectorAll('input[name="media_urls[]"]');
            urlInputs.forEach(input => {
                const url = input.value.trim();
                if (!url) return;

                const div = document.createElement('div');
                div.className = 'media-item';

                const ytId = getYouTubeId(url);
                if (ytId) {
                    div.innerHTML = `<img src="https://img.youtube.com/vi/${ytId}/hqdefault.jpg"><i class="ri-play-circle-fill absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-5xl text-white opacity-80 shadow-black"></i>`;
                } else if (url.match(/\.(mp4|webm)$/i)) {
                    div.innerHTML = `<video src="${url}" autoplay muted loop></video>`;
                } else {
                    div.innerHTML = `<img src="${url}">`;
                }
                previewContainer.appendChild(div);
            });
        }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #3e4042; border-radius: 10px; }
    </style>
</body>
</html>