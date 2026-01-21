<?php
session_start();
require_once '../../config/db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Incorrect Username or Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal | Raj Agency</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="bg-[#050505] h-screen w-full flex items-center justify-center relative overflow-hidden font-sans selection:bg-yellow-500 selection:text-black">

    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-yellow-500/10 rounded-full blur-[120px] animate-float"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-purple-600/10 rounded-full blur-[120px] animate-float" style="animation-delay: 2s;"></div>

    <div class="relative z-10 w-full max-w-md p-8 glass rounded-3xl shadow-2xl mx-4">
        
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-yellow-500/10 text-yellow-500 mb-4 border border-yellow-500/20">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h2 class="text-3xl font-bold text-white tracking-tight">Welcome Back</h2>
            <p class="text-gray-500 text-sm mt-2">Enter credentials to access dashboard</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm text-center font-medium animate-pulse">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Username</label>
                <div class="relative">
                    <input type="text" name="username" required 
                        class="w-full bg-black/50 border border-white/10 text-white px-5 py-4 rounded-xl outline-none focus:border-yellow-500/50 focus:bg-white/5 transition-all placeholder-gray-600" 
                        placeholder="Enter username">
                    <svg class="w-5 h-5 text-gray-500 absolute right-4 top-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Password</label>
                <div class="relative">
                    <input type="password" name="password" required 
                        class="w-full bg-black/50 border border-white/10 text-white px-5 py-4 rounded-xl outline-none focus:border-yellow-500/50 focus:bg-white/5 transition-all placeholder-gray-600" 
                        placeholder="••••••••">
                    <svg class="w-5 h-5 text-gray-500 absolute right-4 top-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
            </div>

            <button type="submit" class="w-full py-4 bg-gradient-to-r from-yellow-500 to-yellow-600 text-black font-bold rounded-xl hover:shadow-[0_0_20px_rgba(234,179,8,0.3)] hover:scale-[1.02] transition-all duration-300">
                Access Dashboard
            </button>
        </form>

        <div class="mt-8 text-center">
            <a href="../../index.php" class="text-xs text-gray-500 hover:text-white transition">← Back to Website</a>
        </div>
    </div>

</body>
</html>