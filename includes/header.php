<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raj Agency | Premium Digital Assets</title>

    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bg: '#050505',
                        card: '#101010', 
                        border: '#1F1F1F',
                        accent: '#F4B90B',
                        'accent-hover': '#FFD700',
                        text: '#FFFFFF',
                        muted: '#9CA3AF'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Space Grotesk', 'sans-serif'],
                    },
                    animation: {
                        'spin-slow': 'spin 10s linear infinite',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body { background-color: #050505; color: white; }
        .glass-nav { background: rgba(5, 5, 5, 0.85); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255,255,255,0.05); }
        .nav-link { position: relative; }
        .nav-link::after { content: ''; position: absolute; width: 0; height: 1px; bottom: -4px; left: 0; background: #F4B90B; transition: width 0.3s; }
        .nav-link:hover::after { width: 100%; }
    </style>
</head>
<body class="antialiased font-sans selection:bg-accent selection:text-black">

<nav class="fixed w-full z-50 top-0 left-0 glass-nav">
    <div class="max-w-7xl mx-auto px-6 h-24 flex justify-between items-center">
        
        <a href="index.php?page=home" class="text-2xl font-display font-bold uppercase tracking-tighter flex items-center gap-2">
            <span class="w-3 h-3 bg-accent rounded-full"></span>
            RAJ AGENCY
        </a>

        <ul class="hidden md:flex items-center gap-10 text-sm font-medium tracking-wide">
            <li><a href="index.php?page=home" class="nav-link hover:text-accent transition">HOME</a></li>
            <li><a href="index.php?page=portfolio" class="nav-link hover:text-accent transition">PORTFOLIO</a></li>
            <li><a href="index.php?page=about" class="nav-link hover:text-accent transition">ABOUT ME</a></li>
            <li><a href="index.php?page=contact" class="nav-link hover:text-accent transition">CONTACT</a></li>
        </ul>

        <a href="index.php?page=contact" class="hidden md:flex px-8 py-3 bg-white text-black text-xs font-bold uppercase tracking-widest rounded-full hover:bg-accent transition-all duration-300 shadow-[0_0_20px_rgba(255,255,255,0.2)] hover:shadow-[0_0_30px_rgba(244,185,11,0.4)]">
            Hire Me
        </a>

        <button id="mobile-menu-btn" class="md:hidden space-y-2 cursor-pointer group z-50 relative">
            <div class="w-8 h-[2px] bg-white group-hover:bg-accent transition"></div>
            <div class="w-5 h-[2px] bg-white ml-auto group-hover:w-8 group-hover:bg-accent transition-all"></div>
        </button>
    </div>
</nav>

<div id="mobile-menu" class="fixed inset-0 bg-black/95 z-40 hidden flex flex-col items-center justify-center space-y-8 transition-all duration-300 opacity-0 translate-y-10 backdrop-blur-xl">
    <a href="index.php?page=home" class="text-3xl font-display font-bold text-white hover:text-accent transition">HOME</a>
    <a href="index.php?page=portfolio" class="text-3xl font-display font-bold text-white hover:text-accent transition">PORTFOLIO</a>
    <a href="index.php?page=about" class="text-3xl font-display font-bold text-white hover:text-accent transition">ABOUT ME</a>
    <a href="index.php?page=contact" class="text-3xl font-display font-bold text-white hover:text-accent transition">CONTACT</a>
</div>