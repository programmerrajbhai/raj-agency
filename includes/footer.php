<footer class="border-t border-white/5 bg-black py-12 mt-20">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center">
            <div class="mb-4 md:mb-0">
                <h2 class="text-2xl font-display font-bold">RAJ AGENCY.</h2>
                <p class="text-muted text-sm mt-1">© 2026 All rights reserved.</p>
            </div>
            <div class="flex gap-6 text-muted text-sm">
                <a href="#" class="hover:text-accent transition">Dribbble</a>
                <a href="#" class="hover:text-accent transition">GitHub</a>
                <a href="#" class="hover:text-accent transition">Twitter</a>
            </div>
        </div>
    </footer>

    <div id="preview-modal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/90 backdrop-blur-sm" onclick="closePreview()"></div>
        <div class="absolute inset-4 md:inset-10 bg-card rounded-2xl overflow-hidden border border-white/10 flex flex-col shadow-2xl">
            <div class="h-14 bg-black border-b border-white/10 flex justify-between items-center px-6">
                <h3 id="modal-title" class="font-bold text-white">Live Preview</h3>
                <div class="flex items-center gap-4">
                    <button onclick="closePreview()" class="text-white hover:text-red-500 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
            <iframe id="modal-iframe" src="" class="w-full flex-1 bg-white" frameborder="0"></iframe>
        </div>
    </div>

    <script>
        // 1. Mobile Menu Logic
        const menuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuLinks = mobileMenu.querySelectorAll('a');
        let isMenuOpen = false;

        if(menuBtn){
            menuBtn.addEventListener('click', () => {
                isMenuOpen = !isMenuOpen;
                if (isMenuOpen) {
                    mobileMenu.classList.remove('hidden');
                    gsap.to(mobileMenu, { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' });
                    // Burger Animation
                    gsap.to(menuBtn.children[0], { rotation: 45, y: 6 });
                    gsap.to(menuBtn.children[1], { rotation: -45, y: -6, width: '2rem' });
                } else {
                    closeMenu();
                }
            });
        }

        function closeMenu() {
            isMenuOpen = false;
            gsap.to(mobileMenu, { opacity: 0, y: 20, duration: 0.3, onComplete: () => mobileMenu.classList.add('hidden') });
            gsap.to(menuBtn.children[0], { rotation: 0, y: 0 });
            gsap.to(menuBtn.children[1], { rotation: 0, y: 0, width: '1.25rem' });
        }

        // Close menu when link clicked
        menuLinks.forEach(link => link.addEventListener('click', closeMenu));

        // 2. Modal Logic
        function openPreview(title, url) {
            const modal = document.getElementById('preview-modal');
            const iframe = document.getElementById('modal-iframe');
            const modalTitle = document.getElementById('modal-title');
            
            if(!url || url === '#') url = 'https://dribbble.com'; 
            
            modalTitle.innerText = "Preview: " + title;
            iframe.src = url;
            
            modal.classList.remove('hidden');
            gsap.fromTo(modal.children[1], {scale: 0.95, opacity: 0}, {scale: 1, opacity: 1, duration: 0.3, ease: 'power2.out'});
        }

        function closePreview() {
            const modal = document.getElementById('preview-modal');
            const iframe = document.getElementById('modal-iframe');
            
            gsap.to(modal.children[1], {scale: 0.95, opacity: 0, duration: 0.2, onComplete: () => {
                modal.classList.add('hidden');
                iframe.src = ''; 
            }});
        }
    </script>
</body>
</html>