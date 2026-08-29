<style>
    /* Professional Smooth Reveal Animations */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .reveal-1 { animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; animation-delay: 100ms; }
    .reveal-2 { animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; animation-delay: 200ms; }
    .reveal-3 { animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; animation-delay: 300ms; }
    .reveal-4 { animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; animation-delay: 400ms; }
    
    /* Graph Bar Animations */
    @keyframes growBar {
        from { height: 0%; opacity: 0.5; }
        to { height: var(--h); opacity: 1; }
    }
    .graph-bar { animation: growBar 1s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; animation-delay: 600ms; transform-origin: bottom; }
</style>

<section class="min-h-screen w-full flex items-center justify-center relative pt-28 pb-16 lg:pt-20 bg-[#050505] overflow-hidden">
    
    <!-- Premium Golden Abstract Background -->
    <div class="absolute top-0 right-0 w-full h-full bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-[#1a1405] via-[#050505] to-[#050505] z-0 pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-6 w-full flex flex-col lg:flex-row items-center justify-between gap-10 lg:gap-20 relative z-10">
        
        <!-- TEXT SECTION -->
        <div class="w-full lg:w-[55%] flex flex-col items-start text-left order-2 lg:order-1 mt-6 lg:mt-0">
            
            <p class="reveal-1 text-gray-400 text-sm sm:text-base lg:text-lg font-medium tracking-wider mb-2 lg:mb-3 flex items-center gap-2">
                Hello, I'm Raj <span class="animate-bounce origin-bottom">👋</span>
            </p>
            
            <h1 class="reveal-2 font-display text-[40px] sm:text-5xl lg:text-[72px] leading-[1.1] lg:leading-[1.05] font-bold tracking-tight text-white mb-4 lg:mb-6 uppercase">
                I Build <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#F4B90B] to-yellow-500 italic pr-2">Premium</span> <br>
                Software
            </h1>
            
            <p class="reveal-3 text-gray-400 text-[14px] sm:text-[15px] lg:text-lg leading-relaxed max-w-[95%] lg:max-w-[480px] font-light mb-8 lg:mb-10">
                With over <strong class="text-white font-medium">5 years of experience</strong> in Software Development, I transform complex ideas into high-end web & mobile solutions tailored for business growth.
            </p>

            <!-- 100% Side-by-Side Stats Box (Fixed for Mobile) -->
            <div class="reveal-4 flex flex-row items-stretch gap-3 lg:gap-6 mb-8 lg:mb-10 w-full sm:w-auto">
                
                <!-- Experience & Graph Card -->
                <div class="flex-1 sm:flex-none border border-[#F4B90B]/30 bg-[#111]/80 backdrop-blur-md rounded-[16px] lg:rounded-[20px] p-3 lg:px-6 lg:py-4 flex items-center justify-between gap-3 lg:gap-6 shadow-[0_0_15px_rgba(244,185,11,0.05)]">
                    <div class="flex flex-col">
                        <span class="text-2xl lg:text-4xl font-display font-bold text-white drop-shadow-[0_0_10px_rgba(244,185,11,0.3)]">5+</span>
                        <span class="text-[9px] lg:text-xs text-[#F4B90B] font-semibold uppercase tracking-[0.1em] lg:tracking-[0.15em] leading-tight">Years<br>Experience</span>
                    </div>
                    
                    <div class="h-8 lg:h-10 w-px bg-white/10"></div>
                    
                    <!-- Animated Mini Growth Graph -->
                    <div class="flex items-end gap-1 lg:gap-1.5 h-8 lg:h-10 w-10 lg:w-16">
                        <div class="w-1.5 lg:w-2.5 bg-[#F4B90B]/30 rounded-t-sm graph-bar" style="--h: 30%;"></div>
                        <div class="w-1.5 lg:w-2.5 bg-[#F4B90B]/50 rounded-t-sm graph-bar" style="--h: 50%;"></div>
                        <div class="w-1.5 lg:w-2.5 bg-[#F4B90B]/70 rounded-t-sm graph-bar" style="--h: 75%;"></div>
                        <div class="w-1.5 lg:w-2.5 bg-[#F4B90B] rounded-t-sm relative graph-bar shadow-[0_0_10px_rgba(244,185,11,0.5)]" style="--h: 100%;">
                            <div class="absolute -top-1 -right-1 lg:-top-1.5 lg:-right-1 w-2 h-2 lg:w-2.5 lg:h-2.5 bg-white rounded-full shadow-[0_0_5px_white] animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <!-- Projects Completed Card -->
                <div class="flex-1 sm:flex-none border border-white/10 bg-[#0a0a0a]/50 backdrop-blur-md rounded-[16px] lg:rounded-[20px] p-3 lg:px-6 lg:py-4 flex items-center justify-center sm:justify-start gap-4">
                    <div class="flex flex-col">
                        <span class="text-2xl lg:text-4xl font-display font-bold text-white">150+</span>
                        <span class="text-[9px] lg:text-xs text-gray-400 font-medium uppercase tracking-[0.1em] lg:tracking-[0.15em] leading-tight">Projects<br>Delivered</span>
                    </div>
                </div>
                
            </div>

            <!-- Buttons -->
            <div class="reveal-4 w-full max-w-[320px] lg:max-w-none flex gap-3 lg:gap-4 justify-start">
                <a href="#newsfeed-section" class="flex-1 lg:flex-none lg:px-10 flex justify-center items-center bg-[#F4B90B] text-black hover:bg-yellow-400 text-[14px] lg:text-[16px] font-bold py-3.5 lg:py-4 rounded-xl lg:rounded-[20px] transition-all duration-300 shadow-[0_0_20px_rgba(244,185,11,0.25)] hover:shadow-[0_0_30px_rgba(244,185,11,0.4)]">
                    Explore Work
                </a>
                <a href="index.php?page=contact" class="w-12 h-12 lg:w-[56px] lg:h-[56px] flex-shrink-0 flex justify-center items-center bg-transparent border border-[#F4B90B]/30 hover:border-[#F4B90B] hover:bg-[#F4B90B]/10 text-[#F4B90B] rounded-xl lg:rounded-[20px] transition-all duration-300" title="Contact Me">
                    <i class="ri-mail-send-line text-lg lg:text-xl"></i>
                </a>
            </div>

        </div>

        <!-- IMAGE SECTION -->
        <div class="w-full lg:w-[45%] flex justify-center lg:justify-end order-1 lg:order-2 relative">
            
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[220px] lg:w-[350px] h-[220px] lg:h-[350px] bg-[#F4B90B]/15 rounded-full blur-[70px] z-0 pointer-events-none"></div>

            <div class="relative w-[280px] sm:w-[320px] lg:w-[400px] group z-10 reveal-2">
                
                <div class="absolute -top-4 right-0 lg:-top-6 lg:right-0 border border-[#F4B90B]/40 bg-[#0a0a0a]/90 rounded-full px-4 py-1.5 lg:px-5 lg:py-2 backdrop-blur-md z-30 shadow-[0_0_15px_rgba(244,185,11,0.2)]">
                    <span class="text-[#F4B90B] text-[10px] uppercase tracking-widest font-bold flex items-center gap-2">
                        <span class="w-1.5 h-1.5 lg:w-2 lg:h-2 rounded-full bg-[#F4B90B] animate-pulse"></span> Available
                    </span>
                </div>

                <div class="overflow-hidden rounded-t-[140px] rounded-b-[20px] lg:rounded-t-[200px] lg:rounded-b-[40px] bg-transparent aspect-[4/4.8] relative transition-transform duration-700 group-hover:-translate-y-2">
                    <!-- === আপনার প্রোফাইল ছবি === -->
                    <img src="assets/profile.png" 
                         alt="Raj - Software Developer" 
                         class="w-full h-full object-cover object-center opacity-95 transition-all duration-700 group-hover:scale-105 filter contrast-110 brightness-105">
                    
                    <div class="absolute inset-x-0 bottom-0 h-2/5 bg-gradient-to-t from-[#050505] via-[#050505]/80 to-transparent z-10 pointer-events-none"></div>
                </div>

                <div class="absolute bottom-2 -right-2 lg:bottom-6 lg:-right-8 text-[#F4B90B] font-display font-semibold tracking-wider text-xs lg:text-xl bg-[#111] backdrop-blur-md px-4 py-2 lg:px-6 lg:py-3 rounded-lg lg:rounded-xl border border-[#F4B90B]/20 shadow-2xl z-30">
                    software dev
                </div>
            </div>
        </div>

    </div>
</section>