<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f1115">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="description" content="Savora - Login untuk menikmati kemudahan belanja kuliner artisan terbaik.">

    <title>{{ config('app.name', 'Savora') }} — Masuk</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap');

        :root {
            --cyan:  #4ecdc4;
            --gold:  #e2c86e;
            --dark:  #0f1115;
        }

        * { -webkit-tap-highlight-color: transparent; box-sizing: border-box; }
        a, button { touch-action: manipulation; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--dark);
            min-height: 100vh;
        }
        .font-serif { font-family: 'Playfair Display', serif; }

        /* Colour helpers */
        .bg-gold-500   { background-color: var(--gold); }
        .text-gold-500 { color: var(--gold); }
        .border-gold-500 { border-color: var(--gold); }
        .text-brand-cyan { color: var(--cyan); }
        .bg-brand-cyan   { background-color: var(--cyan); }
        .border-brand-cyan { border-color: var(--cyan); }
        .focus\:border-gold-500:focus { border-color: var(--gold); }
        .focus\:ring-gold-500:focus   { --tw-ring-color: var(--gold); }

        /* ── Ambient background blobs ─────────────────────── */
        @keyframes drift {
            0%, 100% { transform: translate(0, 0) scale(1);   }
            33%       { transform: translate(25px,-18px) scale(1.08); }
            66%       { transform: translate(-15px,12px) scale(0.95); }
        }
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
            animation: drift 18s ease-in-out infinite;
        }

        /* ── Card entrance ───────────────────────────────── */
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(24px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .auth-card { animation: cardIn 0.5s cubic-bezier(0.22,1,0.36,1) both; }

        /* ── Logo bounce ─────────────────────────────────── */
        @keyframes logoBounce {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-6px); }
        }
        .logo-icon { animation: logoBounce 3s ease-in-out infinite; }

        /* ── Scrollbar ───────────────────────────────────── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--dark); }
        ::-webkit-scrollbar-thumb { background: #2a2d35; border-radius: 4px; }
    </style>
</head>
<body class="antialiased text-white flex flex-col min-h-screen">

    <!-- Ambient blobs -->
    <div class="blob" style="width:500px;height:500px;top:-10%;left:-10%;background:rgba(78,205,196,0.05);animation-delay:0s;"></div>
    <div class="blob" style="width:400px;height:400px;bottom:-5%;right:-5%;background:rgba(226,200,110,0.05);animation-delay:-9s;"></div>

    <div class="relative z-10 flex flex-col min-h-screen items-center justify-between py-10 px-4">

        <!-- Brand logo -->
        <div class="flex flex-col items-center gap-2 mb-2">
            <a href="/" class="flex flex-col items-center gap-2 group">
                <div class="logo-icon w-14 h-14 rounded-full border-2 border-gold-500 flex items-center justify-center font-serif text-2xl font-bold text-gold-500 group-hover:bg-gold-500 group-hover:text-black transition-all duration-500 shadow-[0_0_30px_rgba(226,200,110,0.2)] group-hover:shadow-[0_0_40px_rgba(226,200,110,0.4)]">
                    {{ substr(\App\Models\Setting::getGlobal()->store_name, 0, 1) }}
                </div>
                <span class="text-2xl font-bold tracking-tighter text-white font-serif italic group-hover:text-gold-500 transition-colors duration-300">
                    {{ \App\Models\Setting::getGlobal()->store_name }}
                </span>
                <span class="text-[9px] text-gray-600 uppercase tracking-[0.3em] font-black -mt-1">Artisan Food</span>
            </a>
        </div>

        <!-- Auth form card -->
        <div class="auth-card w-full max-w-sm sm:max-w-md mx-auto my-4">
            <div class="bg-[#16181d]/95 backdrop-blur-xl border border-gray-800/80 shadow-[0_30px_80px_rgba(0,0,0,0.6)] rounded-2xl sm:rounded-3xl overflow-hidden">

                <!-- Top accent line -->
                <div class="h-px bg-gradient-to-r from-transparent via-brand-cyan to-transparent opacity-60"></div>

                <div class="px-6 py-8 sm:px-8 sm:py-10">
                    {{ $slot }}
                </div>

                <!-- Bottom accent line -->
                <div class="h-px bg-gradient-to-r from-transparent via-gold-500 to-transparent opacity-30"></div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex flex-col items-center gap-2 mt-2">
            <a href="/" class="inline-flex items-center gap-1.5 text-[10px] text-gray-600 hover:text-gold-500 transition-colors uppercase tracking-widest font-black">
                <i data-lucide="arrow-left" class="w-3 h-3"></i>
                Kembali ke Beranda
            </a>
            <p class="text-[9px] text-gray-700 tracking-wider">© {{ date('Y') }} {{ \App\Models\Setting::getGlobal()->store_name }}. All rights reserved.</p>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
