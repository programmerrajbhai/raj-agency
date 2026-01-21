<main class="pt-32 pb-20 min-h-screen flex items-center justify-center relative">
    
    <div class="absolute top-1/2 left-1/4 w-96 h-96 bg-accent/10 rounded-full blur-[120px] -z-10"></div>

    <section class="max-w-6xl mx-auto px-6 w-full grid grid-cols-1 lg:grid-cols-2 gap-16">
        
        <div class="space-y-8">
            <h5 class="text-accent font-bold tracking-widest uppercase text-xs">Contact Support</h5>
            <h1 class="font-display text-5xl font-bold leading-tight">
                LET'S START A <br> NEW <span class="text-white">PROJECT.</span>
            </h1>
            <p class="text-muted text-lg">
                Have a project in mind? Fill out the form or send us a direct email. We typically reply within 2 hours.
            </p>

            <div class="space-y-4 pt-4">
                <div class="flex items-center gap-4 p-4 bg-white/5 border border-white/5 rounded-2xl hover:bg-white/10 transition">
                    <div class="w-12 h-12 bg-accent text-black rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs text-muted uppercase font-bold">Email Us</p>
                        <p class="text-white font-medium">hello@rajagency.com</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-4 bg-white/5 border border-white/5 rounded-2xl hover:bg-white/10 transition">
                    <div class="w-12 h-12 bg-white text-black rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs text-muted uppercase font-bold">Call Us</p>
                        <p class="text-white font-medium">+880 1XXX-XXXXXX</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-card border border-white/10 p-8 md:p-10 rounded-[2rem] relative overflow-hidden shadow-2xl">
            <form action="api/submit_contact.php" method="POST" class="space-y-6 relative z-10">
                
                <div class="relative group">
                    <input type="text" name="name" required class="peer w-full bg-transparent border-b border-white/20 py-3 text-white focus:outline-none focus:border-accent transition-colors placeholder-transparent" placeholder="Name">
                    <label class="absolute left-0 -top-3.5 text-xs text-accent transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-muted peer-placeholder-shown:top-3 peer-focus:-top-3.5 peer-focus:text-xs peer-focus:text-accent">Your Name</label>
                </div>

                <div class="relative group">
                    <input type="email" name="email" required class="peer w-full bg-transparent border-b border-white/20 py-3 text-white focus:outline-none focus:border-accent transition-colors placeholder-transparent" placeholder="Email">
                    <label class="absolute left-0 -top-3.5 text-xs text-accent transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-muted peer-placeholder-shown:top-3 peer-focus:-top-3.5 peer-focus:text-xs peer-focus:text-accent">Email Address</label>
                </div>

                <div class="relative group">
                    <select name="service" class="w-full bg-transparent border-b border-white/20 py-3 text-white focus:outline-none focus:border-accent transition-colors appearance-none">
                        <option value="" class="bg-card text-muted">Select Service</option>
                        <option value="web" class="bg-card">Web Development</option>
                        <option value="app" class="bg-card">App Development</option>
                        <option value="ui" class="bg-card">UI/UX Design</option>
                        <option value="other" class="bg-card">Other</option>
                    </select>
                </div>

                <div class="relative group">
                    <textarea name="message" rows="4" required class="peer w-full bg-transparent border-b border-white/20 py-3 text-white focus:outline-none focus:border-accent transition-colors placeholder-transparent" placeholder="Message"></textarea>
                    <label class="absolute left-0 -top-3.5 text-xs text-accent transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-muted peer-placeholder-shown:top-3 peer-focus:-top-3.5 peer-focus:text-xs peer-focus:text-accent">Project Details</label>
                </div>

                <button type="submit" class="w-full py-4 bg-white text-black font-bold uppercase tracking-widest rounded-xl hover:bg-accent transition-all duration-300 mt-4 shadow-lg hover:shadow-accent/25">
                    Send Message
                </button>
            </form>
        </div>

    </section>
</main>