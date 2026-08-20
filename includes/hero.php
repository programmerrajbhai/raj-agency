<section class="min-h-screen w-full flex items-center justify-center relative pt-24 pb-12 md:pt-20 border-b border-white/5 overflow-hidden">
    
    <!-- Background Gradients -->
    <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_50%_50%,rgba(20,20,20,1)_0%,rgba(5,5,5,1)_100%)] z-0 pointer-events-none"></div>
    <div class="absolute top-1/4 right-0 md:right-1/4 w-72 h-72 md:w-96 md:h-96 bg-accent/5 rounded-full blur-[100px] md:blur-[150px] z-0 animate-pulse pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 w-full grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-16 items-center relative z-10">
        
        <!-- Left Side: Texts & Buttons -->
        <div class="space-y-6 md:space-y-8 flex flex-col items-center md:items-start text-center md:text-left mt-10 md:mt-0">
            
            <div class="inline-flex items-center gap-2 px-3 py-1.5 md:px-4 md:py-2 rounded-full border border-white/10 bg-white/5 backdrop-blur-md">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                <span class="text-[10px] md:text-xs font-bold tracking-widest uppercase text-muted">Available for New Projects</span>
            </div>

            <h1 class="font-display text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-bold leading-[1.05] md:leading-[0.9] tracking-tight text-white">
                WE BUILD <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-accent to-yellow-600 italic pr-1 md:pr-2">PREMIUM</span> <br>
                PRODUCTS.
            </h1>
            
            <p class="text-muted text-sm sm:text-base md:text-lg max-w-sm md:max-w-md leading-relaxed">
                Transforming complex ideas into premium digital assets. We specialize in high-end web & mobile solutions.
            </p>

            <div class="flex flex-col sm:flex-row flex-wrap gap-4 pt-2 md:pt-4 w-full sm:w-auto justify-center md:justify-start">
                <a href="#newsfeed-section" class="w-full sm:w-auto px-8 py-3.5 md:py-4 bg-white text-black font-bold rounded-full hover:bg-accent transition-all duration-300 flex items-center justify-center gap-2 group shadow-lg shadow-white/5">
                    See Updates
                    <svg class="w-4 h-4 group-hover:translate-y-1 transition-transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </a>
                <a href="index.php?page=about" class="w-full sm:w-auto px-8 py-3.5 md:py-4 border border-white/20 rounded-full text-white font-medium hover:border-accent hover:text-accent transition-colors duration-300 flex items-center justify-center">
                    About Agency
                </a>
            </div>
        </div>

        <!-- Right Side: Real Image & Floating Animation -->
        <div class="relative flex justify-center items-center h-[350px] sm:h-[400px] md:h-[500px]">
            <div class="relative w-64 h-64 sm:w-80 sm:h-80 md:w-[450px] md:h-[450px] animate-float flex justify-center items-center">
                
                <!-- Glowing Aura behind Image -->
                <div class="absolute inset-0 bg-accent/20 rounded-full blur-[60px] md:blur-[100px] z-0 pointer-events-none"></div>
                
                <!-- Rotating Rings (Premium Touch) -->
                <div class="absolute inset-4 md:inset-8 border border-white/10 rounded-full animate-spin-slow z-0 pointer-events-none"></div>
                <div class="absolute inset-8 md:inset-12 border border-accent/20 rounded-full animate-spin-slow z-0 pointer-events-none" style="animation-direction: reverse;"></div>

                <!-- === এখানে আপনার হিরো ইমেজ দিন (Transparent PNG / 3D Graphic হলে সবচেয়ে ভালো লাগবে) === -->
                <img src="https://cdni.iconscout.com/illustration/premium/thumb/web-development-4439222-3728469.png" 
                     alt="Premium Digital Assets" 
                     class="relative z-10 w-[80%] h-[80%] object-contain drop-shadow-[0_0_30px_rgba(244,185,11,0.3)]">
                <!-- ========================================================================= -->

                <!-- Floating Tiny Elements -->
                <div class="absolute top-0 right-0 md:right-4 bg-[#111]/80 backdrop-blur-md border border-white/10 p-2 md:p-3 rounded-lg md:rounded-xl shadow-xl animate-bounce z-20" style="animation-duration: 3s;">
                    <div class="w-6 md:w-8 h-1 bg-white/20 rounded mb-1"></div>
                    <div class="w-4 md:w-5 h-1 bg-white/20 rounded"></div>
                </div>
                <div class="absolute bottom-4 md:bottom-10 left-0 md:left-4 bg-[#111]/80 backdrop-blur-md border border-white/10 p-2 md:p-3 rounded-full shadow-xl animate-bounce z-20" style="animation-duration: 4s;">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                
            </div>
        </div>

    </div>
</section>