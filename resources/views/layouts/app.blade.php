@php
    $authRank      = 'reguler';
    $authRankInfo  = \App\Models\User::$ranks['reguler'];
    $authIsVip     = false;

    if (auth()->check()) {
        $authUser     = auth()->user();
        $authRank     = $authUser->rank ?? 'reguler';
        $authRankInfo = \App\Models\User::$ranks[$authRank] ?? \App\Models\User::$ranks['reguler'];
        $authIsVip    = in_array($authRank, ['emas', 'platinum', 'diamond']);
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f1115">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="description" content="Savora - Kelezatan kuliner artisan rumahan. Dibuat dengan penuh dedikasi, dikirim segar ke pintu Anda.">

    <title>@yield('title', \App\Models\Setting::getGlobal()->store_name . ' Artisan Food')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-cyan': '#4ecdc4',
                        'brand-teal': '#1a3c37',
                        'brand-dark': '#0f1115',
                        'gold': {
                            400: '#f7e1a0',
                            500: '#e2c86e',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <style>
        
        :root {
            --color-brand-dark: #0f1115;
            --color-brand-cyan: #4ecdc4;
            --color-brand-teal: #1a3c37;
            --sat: env(safe-area-inset-top, 0px);
            --sar: env(safe-area-inset-right, 0px);
            --sab: env(safe-area-inset-bottom, 0px);
            --sal: env(safe-area-inset-left, 0px);
        }

        body { font-family: 'Outfit', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        
        .bg-brand-dark { background-color: var(--color-brand-dark); }
        .bg-brand-cyan { background-color: var(--color-brand-cyan); }
        .bg-brand-teal { background-color: var(--color-brand-teal); }
        .text-brand-cyan { color: var(--color-brand-cyan); }
        .border-brand-teal { border-color: var(--color-brand-teal); }
        .border-brand-cyan { border-color: var(--color-brand-cyan); }

        .shadow-text { text-shadow: 0 2px 4px rgba(0,0,0,0.5); }
        
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        .bg-gold-500 { background-color: #e2c86e; }
        .border-gold-400 { border-color: #f7e1a0; }
        .text-gold-500 { color: #e2c86e; }
        .focus\:border-gold-500:focus { border-color: #e2c86e; }
        .focus\:ring-gold-500:focus { --tw-ring-color: #e2c86e; }
        .shadow-gold-500\/20 { box-shadow: 0 10px 25px rgba(226,200,110,0.2); }

        /* Safe area insets for notched phones */
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 0px); }
        .bottom-nav-height { height: calc(4rem + env(safe-area-inset-bottom, 0px)); }
        .main-bottom-pad { padding-bottom: calc(5rem + env(safe-area-inset-bottom, 0px)); }

        /* Smooth scroll */
        html { scroll-behavior: smooth; }

        /* Tap highlight */
        * { -webkit-tap-highlight-color: transparent; }
        a, button { touch-action: manipulation; }

        /* Bottom nav active state */
        .bottom-nav-link { transition: all 0.15s ease; }
        .bottom-nav-link:active { transform: scale(0.92); }

        /* Cart badge pulse */
        @keyframes cart-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }
        .cart-badge { animation: cart-pulse 2s ease-in-out infinite; }

        /* ─── DESKTOP ENHANCEMENTS ────────────────────────────────── */

        /* Animated underline for desktop nav links */
        .desktop-nav-link {
            position: relative;
            padding-bottom: 4px;
        }
        .desktop-nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #4ecdc4, #e2c86e);
            border-radius: 999px;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(-50%);
        }
        .desktop-nav-link:hover::after,
        .desktop-nav-link.active::after {
            width: 100%;
        }
        .desktop-nav-link.active {
            color: white !important;
        }

        /* Ambient background glow for desktop */
        @keyframes ambient-drift {
            0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.03; }
            33% { transform: translate(30px, -20px) scale(1.1); opacity: 0.06; }
            66% { transform: translate(-20px, 15px) scale(0.95); opacity: 0.04; }
        }
        .ambient-glow {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
            z-index: 0;
            animation: ambient-drift 20s ease-in-out infinite;
        }

        /* Stagger animation for grid cards — desktop & mobile scroll-reveal */
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(28px) scale(0.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes pop-in {
            0%   { opacity: 0; transform: translateY(32px) scale(0.92); }
            60%  { opacity: 1; transform: translateY(-6px) scale(1.03); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Desktop: auto-play on load */
        @media (min-width: 1024px) {
            .animate-fade-up {
                animation: fade-up 0.6s ease-out both;
            }
            .animate-fade-up:nth-child(1) { animation-delay: 0.05s; }
            .animate-fade-up:nth-child(2) { animation-delay: 0.1s; }
            .animate-fade-up:nth-child(3) { animation-delay: 0.15s; }
            .animate-fade-up:nth-child(4) { animation-delay: 0.2s; }
            .animate-fade-up:nth-child(5) { animation-delay: 0.25s; }
            .animate-fade-up:nth-child(6) { animation-delay: 0.3s; }
            .animate-fade-up:nth-child(7) { animation-delay: 0.35s; }
            .animate-fade-up:nth-child(8) { animation-delay: 0.4s; }
        }

        /* Mobile: hidden until IntersectionObserver triggers .revealed */
        @media (max-width: 1023px) {
            .animate-fade-up {
                opacity: 0;
                transform: translateY(32px) scale(0.93);
                transition: opacity 0.55s cubic-bezier(0.22,1,0.36,1),
                            transform 0.55s cubic-bezier(0.22,1,0.36,1);
            }
            .animate-fade-up.revealed {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Mobile active press effect on category cards */
        @media (max-width: 1023px) {
            .category-grid-card:active {
                transform: scale(0.95) !important;
                transition: transform 0.12s ease !important;
            }
            .category-hover-card:active {
                transform: scale(0.94) !important;
                transition: transform 0.12s ease !important;
            }
        }

        /* Refined scrollbar for desktop */
        @media (min-width: 1024px) {
            ::-webkit-scrollbar {
                width: 8px;
            }
            ::-webkit-scrollbar-track {
                background: #0f1115;
            }
            ::-webkit-scrollbar-thumb {
                background: #2a2d35;
                border-radius: 4px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #3a3d45;
            }
        }

        /* Desktop card shimmer hover effect */
        @media (min-width: 1024px) {
            .card-shimmer {
                position: relative;
                overflow: hidden;
            }
            .card-shimmer::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.03), transparent);
                z-index: 1;
                transition: left 0.7s ease;
            }
            .card-shimmer:hover::before {
                left: 100%;
            }
        }

        /* ─── CUSTOM CATEGORY HOVER ANIMATIONS ────────────────────── */
        
        /* Category row item on Welcome page (inactive state) */
        .category-hover-card {
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1) !important;
            position: relative;
            z-index: 1;
        }
        
        .category-hover-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(135deg, rgba(78, 205, 196, 0.12) 0%, rgba(26, 60, 55, 0.35) 100%);
            opacity: 0;
            transition: opacity 0.5s ease;
            z-index: -1;
        }
        
        .category-hover-card:hover {
            transform: translateY(-6px) scale(1.04) !important;
            border-color: rgba(78, 205, 196, 0.6) !important;
            color: #ffffff !important;
            box-shadow: 0 16px 36px rgba(78, 205, 196, 0.16), 0 4px 12px rgba(0, 0, 0, 0.2) !important;
        }
        
        .category-hover-card:hover::before {
            opacity: 1;
        }
        
        /* Category row item on Welcome page (active state) */
        .category-active-card {
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        
        .category-active-card:hover {
            transform: translateY(-4px) scale(1.05) !important;
            box-shadow: 0 16px 36px rgba(226, 200, 110, 0.35), 0 4px 12px rgba(0, 0, 0, 0.2) !important;
        }
        
        /* Icon rotate and scale inside hover cards */
        .category-icon-container {
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        .category-hover-card:hover .category-icon-container {
            transform: scale(1.2) rotate(8deg);
        }
        
        .category-active-card:hover .category-icon-container {
            transform: scale(1.18) rotate(-6deg);
        }
        
        /* Category grid card on all-categories page */
        .category-grid-card {
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        
        .category-grid-card:hover {
            transform: translateY(-10px) scale(1.02) !important;
            border-color: rgba(78, 205, 196, 0.5) !important;
            box-shadow: 0 30px 60px rgba(78, 205, 196, 0.12), 0 10px 25px rgba(0, 0, 0, 0.25) !important;
        }
        
        .category-grid-card i[data-lucide] {
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        }
        
        .category-grid-card:hover i[data-lucide] {
            transform: scale(1.15) rotate(8deg);
        }
    </style>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-brand-dark text-white font-sans antialiased flex flex-col min-h-screen">
    @if(!\App\Models\Setting::getGlobal()->isStoreOpen())
    <div class="bg-gradient-to-r from-red-900/90 via-red-800/90 to-orange-950/95 backdrop-blur-md text-white text-[9px] sm:text-xs font-black uppercase tracking-widest py-2.5 px-4 text-center flex items-center justify-center gap-2 border-b border-red-500/20 z-[90]">
        <i data-lucide="lock" class="w-3.5 h-3.5 text-red-400"></i>
        <span>Toko Sedang Tutup. Anda tidak dapat melakukan pemesanan saat ini.</span>
    </div>
    @endif
    {{-- Global Toast Notification --}}
    <div x-data="{ 
             toastOpen: false, 
             toastMessage: '', 
             showToast(msg) { 
                 this.toastMessage = msg; 
                 this.toastOpen = true; 
                 setTimeout(() => this.toastOpen = false, 3500); 
             } 
         }"
         @show-toast.window="showToast($event.detail)"
         class="fixed top-5 left-1/2 -translate-x-1/2 z-[9999] pointer-events-none"
         style="display: none;"
         x-show="toastOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-4 scale-95">
        <div class="bg-red-500/10 backdrop-blur-xl border border-red-500/30 text-red-400 px-5 py-3 rounded-2xl shadow-[0_10px_30px_rgba(239,68,68,0.2)] text-[10px] md:text-xs font-black uppercase tracking-wider flex items-center gap-2 pointer-events-auto">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500"></i>
            <span x-text="toastMessage"></span>
        </div>
    </div>
    
    <!-- Navigation -->
    <header x-data="{ mobileMenuOpen: false, searchOpen: false }" class="bg-brand-dark/90 backdrop-blur-md sticky top-0 z-50 border-b border-gray-900/60 shadow-2xl transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 md:px-8 lg:px-10 py-3 lg:py-4 flex items-center justify-between gap-3 lg:gap-6">
            <!-- Brand (non-clickable) -->
            <span class="flex items-center space-x-2 flex-shrink-0 cursor-default select-none">
                <div class="w-8 h-8 rounded-full border-2 border-gold-500 flex items-center justify-center font-serif text-lg font-bold text-gold-500">{{ substr(\App\Models\Setting::getGlobal()->store_name, 0, 1) }}</div>
                <span class="text-xl font-bold tracking-tighter text-white font-serif italic">{{ \App\Models\Setting::getGlobal()->store_name }}</span>
            </span>

            <!-- Nav Links (Desktop) -->
            <nav class="hidden lg:flex items-center space-x-7 xl:space-x-9 text-[10px] font-black uppercase tracking-[0.2em]">
                <a href="{{ route('home') }}" class="desktop-nav-link text-gray-400 hover:text-white transition-colors duration-300 {{ Route::is('home') && !request('filter') && !request('category') ? 'active text-white' : '' }}">Belanja Semua</a>
                <a href="{{ route('home', ['filter' => 'new']) }}#menu-section" class="desktop-nav-link text-gray-400 hover:text-white transition-colors duration-300 {{ request('filter') === 'new' ? 'active text-white' : '' }}">Produk Baru</a>
                <a href="{{ route('home', ['filter' => 'popular']) }}#menu-section" class="desktop-nav-link text-gray-400 hover:text-white transition-colors duration-300 {{ request('filter') === 'popular' ? 'active text-white' : '' }}">Terlaris</a>
                <a href="{{ route('categories.index') }}" class="desktop-nav-link text-gray-400 hover:text-white transition-colors duration-300 {{ Route::is('categories.index') ? 'active text-white' : '' }}">Kategori</a>
                @auth
                    <a href="{{ route('orders.index') }}" class="desktop-nav-link text-gray-400 hover:text-white transition-colors duration-300 {{ Route::is('orders.index') ? 'active text-white' : '' }}">Pesanan Saya</a>
                @endauth
                <a href="#hubungi-kami" class="desktop-nav-link text-gray-400 hover:text-white transition-colors duration-300">Kontak</a>
            </nav>

            <!-- Right Icons -->
            <div class="flex items-center space-x-2 sm:space-x-3">
                <!-- Desktop Search -->
                <form action="{{ route('home') }}#menu-section" method="GET" class="relative group hidden sm:block"
                      x-data="{ 
                          query: '{{ request('search') }}', 
                          results: [], 
                          loading: false, 
                          showDropdown: false,
                          fetchResults() {
                              if (this.query.length < 2) {
                                  this.results = [];
                                  this.showDropdown = false;
                                  return;
                              }
                              this.loading = true;
                              this.showDropdown = true;
                              fetch(`/search/live?q=${encodeURIComponent(this.query)}`)
                                  .then(res => res.json())
                                  .then(data => {
                                      this.results = data;
                                      this.loading = false;
                                  })
                                  .catch(() => {
                                      this.loading = false;
                                  });
                          }
                      }"
                      @submit.prevent="
                          if (query.length >= 2 && results.length === 0 && !loading) {
                              $dispatch('show-toast', 'Menu \'' + query + '\' tidak ditemukan!');
                          } else {
                              $el.submit();
                          }
                      "
                      @click.away="showDropdown = false">
                    <input type="text" name="search" x-model="query" @input.debounce.300ms="fetchResults()" @focus="showDropdown = query.length >= 2" placeholder="Cari menu..." autocomplete="off"
                        class="bg-black/30 border border-gray-800 rounded-xl py-2 px-4 pl-9 text-[11px] text-gray-300 focus:outline-none focus:border-gold-500 focus:ring-1 focus:ring-gold-500/30 w-32 lg:w-40 focus:w-52 lg:focus:w-64 transition-all duration-500 placeholder-gray-600">
                    <i data-lucide="search" class="h-3.5 w-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 group-focus-within:text-gold-500 transition-colors duration-300"></i>
                    <button type="submit" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 hover:text-white transition">
                        <i data-lucide="arrow-right" class="h-3 w-3"></i>
                    </button>

                    <!-- Dropdown Suggestions -->
                    <div x-show="showDropdown" x-transition
                         class="absolute right-0 mt-2 w-72 md:w-80 bg-brand-dark/95 border border-gray-800 rounded-2xl shadow-2xl p-2.5 space-y-2 z-50 backdrop-blur-lg"
                         style="display: none;">
                         <!-- Loading indicator -->
                         <div x-show="loading" class="flex items-center justify-center py-4 text-gray-500 text-[10px] gap-2">
                             <div class="w-3.5 h-3.5 border-2 border-brand-cyan border-t-transparent rounded-full animate-spin"></div>
                             Mencari kuliner...
                         </div>
                         <!-- No results -->
                         <div x-show="!loading && results.length === 0" class="text-center py-4 text-gray-500 text-[10px]">
                             Menu tidak ditemukan
                         </div>
                         <!-- Results List -->
                         <div x-show="!loading && results.length > 0" class="max-h-60 overflow-y-auto space-y-1 pr-1 custom-scrollbar text-left">
                             <template x-for="item in results" :key="item.id">
                                 <a :href="item.url" class="flex items-center gap-3 p-2 hover:bg-white/5 rounded-xl transition group/item">
                                     <img :src="item.image_url" alt="" class="w-10 h-10 object-cover rounded-lg bg-gray-800 flex-shrink-0 border border-gray-800/80">
                                     <div class="flex-1 min-w-0">
                                         <p class="text-[11px] font-black text-gray-200 truncate group-hover/item:text-brand-cyan transition-colors" x-text="item.name"></p>
                                         <p class="text-[9px] text-gray-500 truncate" x-text="item.category"></p>
                                     </div>
                                     <div class="text-right">
                                         <template x-if="item.discount_price">
                                             <div>
                                                 <p class="text-[10px] font-black text-gold-400" x-text="'Rp ' + item.discount_price"></p>
                                                 <p class="text-[8px] text-gray-500 line-through" x-text="'Rp ' + item.price"></p>
                                             </div>
                                         </template>
                                         <template x-if="!item.discount_price">
                                             <p class="text-[10px] font-black text-gray-300" x-text="'Rp ' + item.price"></p>
                                         </template>
                                     </div>
                                 </a>
                             </template>
                         </div>
                    </div>
                </form>

                <!-- Mobile Search Toggle -->
                <button @click="searchOpen = !searchOpen" 
                    class="sm:hidden text-gray-400 hover:text-white transition p-1.5 rounded-lg hover:bg-white/5"
                    :class="searchOpen ? 'text-brand-cyan' : ''"
                    aria-label="Cari">
                    <i data-lucide="search" class="h-5 w-5"></i>
                </button>

                <!-- User Profile Dropdown (desktop only) -->
                <div class="relative hidden lg:block" x-data="{ open: false }">
                    <button @click="open = !open" class="text-gray-300 hover:text-white transition flex items-center focus:outline-none p-1.5 rounded-lg hover:bg-white/5">
                        @auth
                            <span class="text-[10px] font-bold mr-2 uppercase tracking-widest hidden md:inline flex items-center gap-1.5" style="color: {{ $authRankInfo['hex'] }};">
                                {{ Auth::user()->name }}
                                @if($authRank !== 'reguler')
                                    <span class="px-1.5 py-0.5 text-black text-[7px] font-black uppercase rounded-md tracking-wider" style="background: {{ $authRankInfo['hex'] }}; box-shadow: 0 0 10px {{ $authRankInfo['hex'] }}40;">{{ $authRankInfo['icon'] }}</span>
                                @endif
                            </span>
                        @endauth
                        <i data-lucide="user" class="h-4 w-4"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="transform opacity-0 scale-95 translate-y-1"
                         x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="transform opacity-0 scale-95 translate-y-1"
                         class="absolute right-0 mt-3 w-56 bg-[#16181c]/95 backdrop-blur-xl border border-gray-800/80 rounded-2xl shadow-[0_20px_60px_rgba(0,0,0,0.6)] z-50 overflow-hidden"
                         style="display: none;">
                        @auth
                            <div class="px-4 py-3 border-b border-gray-800/60 bg-black/10 flex items-center justify-between">
                                <div>
                                    <p class="text-[9px] text-gray-500 uppercase tracking-widest mb-0.5">Akun Saya</p>
                                    <p class="text-xs font-bold text-white truncate max-w-[130px]">{{ Auth::user()->email }}</p>
                                </div>
                                @if($authRank !== 'reguler')
                                    <span class="px-2 py-0.5 text-black text-[8px] font-black uppercase rounded-lg tracking-wider flex items-center gap-0.5"
                                          style="background: {{ $authRankInfo['hex'] }}; box-shadow: 0 0 10px {{ $authRankInfo['hex'] }}30;">
                                        {{ $authRankInfo['icon'] }} {{ $authRankInfo['label'] }}
                                    </span>
                                @endif
                            </div>
                            <div class="p-1.5 space-y-0.5">
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs text-gray-300 hover:bg-white/5 hover:text-white rounded-xl transition font-medium">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-brand-cyan"></i>
                                    <span>Dashboard Akun</span>
                                </a>
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs text-gray-300 hover:bg-white/5 hover:text-white rounded-xl transition font-medium">
                                    <i data-lucide="user-circle" class="w-4 h-4 text-gray-500"></i>
                                    <span>Profil Saya</span>
                                </a>
                                <a href="{{ route('orders.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs text-gray-300 hover:bg-white/5 hover:text-white rounded-xl transition font-medium">
                                    <i data-lucide="package" class="w-4 h-4 text-gold-500"></i>
                                    <span>Pesanan Saya</span>
                                </a>
                                <div class="h-px bg-gray-800/60 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-red-400 hover:bg-red-500/10 hover:text-red-300 rounded-xl transition font-medium text-left">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="p-1.5 space-y-0.5">
                                <a href="{{ route('login') }}" class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-300 hover:bg-white/5 hover:text-white rounded-xl transition font-medium">
                                    <i data-lucide="log-in" class="w-4 h-4 text-brand-cyan"></i>
                                    <span>Masuk</span>
                                </a>
                                <a href="{{ route('register') }}" class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-300 hover:bg-white/5 hover:text-white rounded-xl transition font-medium">
                                    <i data-lucide="user-plus" class="w-4 h-4 text-gold-500"></i>
                                    <span>Daftar Akun</span>
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>

                <!-- Cart Button -->
                <a href="/keranjang" class="bg-brand-cyan hover:bg-teal-400 text-black px-3.5 lg:px-5 py-1.5 lg:py-2 rounded-lg lg:rounded-xl text-xs font-bold transition-all duration-300 flex items-center space-x-1.5 lg:space-x-2 relative flex-shrink-0 hover:shadow-[0_4px_20px_rgba(78,205,196,0.35)] hover:scale-[1.03] active:scale-95">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span data-cart-count>{{ count(session('cart', [])) }}</span>
                </a>

                <!-- Mobile Hamburger Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" 
                    class="lg:hidden text-gray-300 hover:text-white focus:outline-none transition p-1.5 rounded-lg hover:bg-white/5"
                    aria-label="Menu">
                    <i data-lucide="menu" class="h-5 w-5" x-show="!mobileMenuOpen"></i>
                    <i data-lucide="x" class="h-5 w-5" x-show="mobileMenuOpen" style="display: none;"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Search Bar -->
        <div x-show="searchOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="sm:hidden bg-brand-dark border-b border-gray-800/60 px-4 pb-3"
             style="display: none;">
            <form action="{{ route('home') }}#menu-section" method="GET" class="relative"
                  x-data="{ 
                      query: '{{ request('search') }}', 
                      results: [], 
                      loading: false, 
                      showDropdown: false,
                      fetchResults() {
                          if (this.query.length < 2) {
                              this.results = [];
                              this.showDropdown = false;
                              return;
                          }
                          this.loading = true;
                          this.showDropdown = true;
                          fetch(`/search/live?q=${encodeURIComponent(this.query)}`)
                              .then(res => res.json())
                              .then(data => {
                                  this.results = data;
                                  this.loading = false;
                              })
                              .catch(() => {
                                  this.loading = false;
                              });
                      }
                  }"
                  @submit.prevent="
                      if (query.length >= 2 && results.length === 0 && !loading) {
                          $dispatch('show-toast', 'Menu \'' + query + '\' tidak ditemukan!');
                      } else {
                          $el.submit();
                      }
                  "
                  @click.away="showDropdown = false">
                <input type="text" name="search" x-model="query" @input.debounce.300ms="fetchResults()" @focus="showDropdown = query.length >= 2" placeholder="Cari kuliner favorit Anda..." autocomplete="off"
                    class="w-full bg-black/40 border border-gray-700 rounded-xl py-2.5 pl-4 pr-11 text-sm text-gray-200 focus:outline-none focus:border-brand-cyan transition-all placeholder-gray-600">
                <button type="submit" class="absolute right-3 top-2.5 text-gray-400 hover:text-brand-cyan transition">
                    <i data-lucide="search" class="h-5 w-5"></i>
                </button>

                <!-- Dropdown Suggestions for Mobile -->
                <div x-show="showDropdown" x-transition
                     class="absolute left-0 right-0 mt-2 bg-brand-dark/95 border border-gray-800 rounded-2xl shadow-2xl p-2.5 space-y-2 z-50 backdrop-blur-lg"
                     style="display: none;">
                     <!-- Loading indicator -->
                     <div x-show="loading" class="flex items-center justify-center py-4 text-gray-500 text-[10px] gap-2">
                         <div class="w-3.5 h-3.5 border-2 border-brand-cyan border-t-transparent rounded-full animate-spin"></div>
                         Mencari kuliner...
                     </div>
                     <!-- No results -->
                     <div x-show="!loading && results.length === 0" class="text-center py-4 text-gray-500 text-[10px]">
                         Menu tidak ditemukan
                     </div>
                     <!-- Results List -->
                     <div x-show="!loading && results.length > 0" class="max-h-60 overflow-y-auto space-y-1 pr-1 custom-scrollbar text-left">
                         <template x-for="item in results" :key="item.id">
                             <a :href="item.url" class="flex items-center gap-3 p-2 hover:bg-white/5 rounded-xl transition group/item">
                                 <img :src="item.image_url" alt="" class="w-9 h-9 object-cover rounded-lg bg-gray-800 flex-shrink-0 border border-gray-800/80">
                                 <div class="flex-1 min-w-0">
                                     <p class="text-[11px] font-black text-gray-200 truncate group-hover/item:text-brand-cyan transition-colors" x-text="item.name"></p>
                                     <p class="text-[9px] text-gray-500 truncate" x-text="item.category"></p>
                                 </div>
                                 <div class="text-right">
                                     <template x-if="item.discount_price">
                                         <div>
                                             <p class="text-[10px] font-black text-gold-400" x-text="'Rp ' + item.discount_price"></p>
                                             <p class="text-[8px] text-gray-500 line-through" x-text="'Rp ' + item.price"></p>
                                         </div>
                                     </template>
                                     <template x-if="!item.discount_price">
                                         <p class="text-[10px] font-black text-gray-300" x-text="'Rp ' + item.price"></p>
                                     </template>
                                 </div>
                             </a>
                         </template>
                     </div>
                </div>
            </form>
        </div>

        <!-- Mobile Drawer Menu -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-3"
             class="lg:hidden bg-[#0f1115] border-b border-gray-800 absolute top-full left-0 w-full z-40 shadow-2xl" 
             style="display: none;">
             
            <div class="px-3 py-3 space-y-0.5">
                <a href="{{ route('home') }}" @click="mobileMenuOpen = false"
                    class="flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-white/5 py-2.5 px-3 rounded-xl transition text-sm font-semibold uppercase tracking-wider">
                    <i data-lucide="home" class="w-4 h-4 flex-shrink-0 text-brand-cyan"></i>
                    <span>Belanja Semua</span>
                </a>
                <a href="{{ route('home', ['filter' => 'new']) }}#menu-section" @click="mobileMenuOpen = false"
                    class="flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-white/5 py-2.5 px-3 rounded-xl transition text-sm font-semibold uppercase tracking-wider">
                    <i data-lucide="sparkles" class="w-4 h-4 flex-shrink-0 text-gold-500"></i>
                    <span>Produk Baru</span>
                </a>
                <a href="{{ route('home', ['filter' => 'popular']) }}#menu-section" @click="mobileMenuOpen = false"
                    class="flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-white/5 py-2.5 px-3 rounded-xl transition text-sm font-semibold uppercase tracking-wider">
                    <i data-lucide="trending-up" class="w-4 h-4 flex-shrink-0 text-orange-400"></i>
                    <span>Terlaris</span>
                </a>
                <a href="{{ route('categories.index') }}" @click="mobileMenuOpen = false"
                    class="flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-white/5 py-2.5 px-3 rounded-xl transition text-sm font-semibold uppercase tracking-wider">
                    <i data-lucide="grid-3x3" class="w-4 h-4 flex-shrink-0 text-purple-400"></i>
                    <span>Kategori</span>
                </a>
                @auth
                    <a href="{{ route('orders.index') }}" @click="mobileMenuOpen = false"
                        class="flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-white/5 py-2.5 px-3 rounded-xl transition text-sm font-semibold uppercase tracking-wider">
                        <i data-lucide="package" class="w-4 h-4 flex-shrink-0 text-sky-400"></i>
                        <span>Pesanan Saya</span>
                    </a>
                    <a href="{{ route('dashboard') }}" @click="mobileMenuOpen = false"
                        class="flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-white/5 py-2.5 px-3 rounded-xl transition text-sm font-semibold uppercase tracking-wider">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 flex-shrink-0 text-brand-cyan"></i>
                        <span>Dashboard Akun</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" @click="mobileMenuOpen = false"
                        class="flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-white/5 py-2.5 px-3 rounded-xl transition text-sm font-semibold uppercase tracking-wider">
                        <i data-lucide="user-circle" class="w-4 h-4 flex-shrink-0 text-emerald-400"></i>
                        <span>Profil Saya</span>
                    </a>
                    <div class="pt-2 mt-2 border-t border-gray-800/60">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center space-x-3 text-red-400 hover:text-red-300 hover:bg-red-500/5 py-2.5 px-3 rounded-xl transition w-full text-sm font-semibold uppercase tracking-wider">
                                <i data-lucide="log-out" class="w-4 h-4 flex-shrink-0"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="pt-2 mt-2 border-t border-gray-800/60 flex gap-2 px-3">
                        <a href="{{ route('login') }}" @click="mobileMenuOpen = false"
                            class="flex-1 text-center py-2.5 bg-brand-cyan text-black rounded-xl text-xs font-black uppercase tracking-widest transition hover:bg-teal-400">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" @click="mobileMenuOpen = false"
                            class="flex-1 text-center py-2.5 bg-white/5 border border-gray-700 text-gray-300 rounded-xl text-xs font-black uppercase tracking-widest transition hover:bg-white/10">
                            Daftar
                        </a>
                    </div>
                @endauth
                <a href="#hubungi-kami" @click="mobileMenuOpen = false"
                    class="flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-white/5 py-2.5 px-3 rounded-xl transition text-sm font-semibold uppercase tracking-wider">
                    <i data-lucide="phone" class="w-4 h-4 flex-shrink-0 text-gray-500"></i>
                    <span>Kontak</span>
                </a>
            </div>
        </div>
    </header>

    <main class="flex-grow lg:pb-8 relative">

    <!-- Ambient background glow (desktop only) -->
    <div class="hidden lg:block">
        <div class="ambient-glow" style="width:600px;height:600px;top:10%;left:-5%;background:rgba(78,205,196,0.07);"></div>
        <div class="ambient-glow" style="width:500px;height:500px;top:40%;right:-8%;background:rgba(226,200,110,0.05);animation-delay:-7s;"></div>
        <div class="ambient-glow" style="width:400px;height:400px;bottom:10%;left:30%;background:rgba(78,205,196,0.04);animation-delay:-14s;"></div>
    </div>

    <!-- Toast Notifications -->
    @if(session('success') || session('error') || session('warning') || session('info'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 5000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-[-1rem]"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-[-1rem]"
        class="fixed top-20 left-1/2 -translate-x-1/2 z-[9999] w-[92vw] max-w-md"
        style="display: none;"
    >
        @if(session('success'))
        <div class="flex items-start gap-3 bg-[#0d1a18] border border-brand-cyan/40 text-white px-4 py-3.5 rounded-2xl shadow-[0_8px_32px_rgba(78,205,196,0.2)] backdrop-blur-xl">
            <div class="flex-shrink-0 w-8 h-8 bg-brand-cyan/20 border border-brand-cyan/30 rounded-xl flex items-center justify-center">
                <i data-lucide="check-circle" class="w-4 h-4 text-brand-cyan"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-black uppercase tracking-widest text-brand-cyan mb-0.5">Berhasil</p>
                <p class="text-xs text-gray-300 leading-relaxed">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="flex-shrink-0 text-gray-600 hover:text-white transition mt-0.5">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
        @elseif(session('error'))
        <div class="flex items-start gap-3 bg-[#1a0d0d] border border-red-500/40 text-white px-4 py-3.5 rounded-2xl shadow-[0_8px_32px_rgba(239,68,68,0.2)] backdrop-blur-xl">
            <div class="flex-shrink-0 w-8 h-8 bg-red-500/20 border border-red-500/30 rounded-xl flex items-center justify-center">
                <i data-lucide="x-circle" class="w-4 h-4 text-red-400"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-black uppercase tracking-widest text-red-400 mb-0.5">Gagal</p>
                <p class="text-xs text-gray-300 leading-relaxed">{{ session('error') }}</p>
            </div>
            <button @click="show = false" class="flex-shrink-0 text-gray-600 hover:text-white transition mt-0.5">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
        @elseif(session('warning'))
        <div class="flex items-start gap-3 bg-[#1a1505] border border-yellow-500/40 text-white px-4 py-3.5 rounded-2xl shadow-[0_8px_32px_rgba(234,179,8,0.2)] backdrop-blur-xl">
            <div class="flex-shrink-0 w-8 h-8 bg-yellow-500/20 border border-yellow-500/30 rounded-xl flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-yellow-400"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-black uppercase tracking-widest text-yellow-400 mb-0.5">Perhatian</p>
                <p class="text-xs text-gray-300 leading-relaxed">{{ session('warning') }}</p>
            </div>
            <button @click="show = false" class="flex-shrink-0 text-gray-600 hover:text-white transition mt-0.5">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
        @elseif(session('info'))
        <div class="flex items-start gap-3 bg-[#0d1220] border border-blue-500/40 text-white px-4 py-3.5 rounded-2xl shadow-[0_8px_32px_rgba(59,130,246,0.2)] backdrop-blur-xl">
            <div class="flex-shrink-0 w-8 h-8 bg-blue-500/20 border border-blue-500/30 rounded-xl flex items-center justify-center">
                <i data-lucide="info" class="w-4 h-4 text-blue-400"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-black uppercase tracking-widest text-blue-400 mb-0.5">Info</p>
                <p class="text-xs text-gray-300 leading-relaxed">{{ session('info') }}</p>
            </div>
            <button @click="show = false" class="flex-shrink-0 text-gray-600 hover:text-white transition mt-0.5">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
        @endif
    </div>
    @endif
        
        @if(isset($slot))
            {{ $slot }}
        @else
            @yield('content')
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-black/90 py-10 md:py-14 lg:py-16 border-t border-gray-900 shadow-inner mt-10 md:mt-16 mb-[4.5rem] lg:mb-0 relative z-[1]">
        <div class="max-w-7xl mx-auto px-6 md:px-8 lg:px-10 grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12 text-sm text-gray-500">
            <div class="flex flex-col space-y-4 col-span-2 lg:col-span-1">
                <a href="{{ route('home') }}" class="flex items-center space-x-2.5 group">
                    <div class="w-9 h-9 lg:w-10 lg:h-10 rounded-full border-2 border-gold-500 flex items-center justify-center font-serif text-lg lg:text-xl font-bold text-gold-500 group-hover:bg-gold-500 group-hover:text-black transition-all duration-500">{{ substr(\App\Models\Setting::getGlobal()->store_name, 0, 1) }}</div>
                    <span class="text-xl lg:text-2xl font-bold tracking-tighter text-white font-serif italic">{{ \App\Models\Setting::getGlobal()->store_name }}</span>
                </a>
                <p class="text-gray-500 text-xs lg:text-sm leading-relaxed max-w-xs">Kelezatan kuliner rumahan kelas artisan. Dibuat dengan penuh dedikasi dari dapur kami untuk keluarga Anda.</p>
            </div>
            <div class="flex flex-col space-y-3 col-span-1">
                <h4 class="font-bold text-gold-500 text-xs uppercase tracking-widest mb-1">Tautan</h4>
                <div class="flex flex-col space-y-2 text-xs lg:text-sm text-gray-400">
                    <a href="{{ route('home') }}#menu-section" class="hover:text-white hover:translate-x-1 transition-all duration-200 inline-flex items-center gap-1.5">Daftar Menu</a>
                    <a href="{{ route('home', ['filter' => 'new']) }}#menu-section" class="hover:text-white hover:translate-x-1 transition-all duration-200">Produk Baru</a>
                    <a href="{{ route('home', ['filter' => 'popular']) }}#menu-section" class="hover:text-white hover:translate-x-1 transition-all duration-200">Menu Terlaris</a>
                    <a href="{{ route('categories.index') }}" class="hover:text-white hover:translate-x-1 transition-all duration-200">Kategori</a>
                </div>
            </div>
            <div class="flex flex-col space-y-3 col-span-1">
                <h4 class="font-bold text-gold-500 text-xs uppercase tracking-widest mb-1">Akun</h4>
                <div class="flex flex-col space-y-2 text-xs lg:text-sm text-gray-400">
                    @auth
                        <a href="{{ route('profile.edit') }}" class="hover:text-white hover:translate-x-1 transition-all duration-200">Profil Saya</a>
                        <a href="{{ route('orders.index') }}" class="hover:text-white hover:translate-x-1 transition-all duration-200">Pesanan Saya</a>
                        <a href="/keranjang" class="hover:text-white hover:translate-x-1 transition-all duration-200">Keranjang</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-white hover:translate-x-1 transition-all duration-200">Masuk</a>
                        <a href="{{ route('register') }}" class="hover:text-white hover:translate-x-1 transition-all duration-200">Daftar</a>
                    @endauth
                </div>
            </div>
            <div id="hubungi-kami" class="flex flex-col space-y-3 col-span-2 lg:col-span-1 scroll-mt-24 md:scroll-mt-28">
                <h4 class="font-bold text-gold-500 text-xs uppercase tracking-widest mb-1">Hubungi Kami</h4>
                <p class="text-xs lg:text-sm text-gray-400 leading-relaxed">{{ \App\Models\Setting::getGlobal()->store_address }}</p>
                <p class="text-xs lg:text-sm text-gray-400">WhatsApp: <a href="https://wa.me/{{ \App\Models\Setting::getGlobal()->whatsapp_number }}" target="_blank" class="text-brand-cyan hover:underline hover:text-teal-300 transition-colors">+{{ \App\Models\Setting::getGlobal()->whatsapp_number }}</a></p>
                @if(\App\Models\Setting::getGlobal()->instagram_url)
                    <p class="text-xs lg:text-sm text-gray-400">Instagram: <a href="{{ \App\Models\Setting::getGlobal()->instagram_url }}" target="_blank" class="text-brand-cyan hover:underline hover:text-teal-300 transition-colors">@ {{ \App\Models\Setting::getGlobal()->store_name }}</a></p>
                @endif
            </div>
        </div>

    </footer>

    <!-- ===== BOTTOM MOBILE NAVIGATION BAR ===== -->
    <nav class="fixed bottom-0 left-0 right-0 z-50 lg:hidden bg-[#0d0f13] border-t border-gray-800/70 pb-safe">
        <div class="flex items-stretch h-16">

            <!-- Beranda -->
            <a href="{{ route('home') }}" 
                class="bottom-nav-link flex-1 flex flex-col items-center justify-center space-y-0.5 {{ Route::is('home') ? 'text-brand-cyan' : 'text-gray-500 hover:text-gray-300' }}">
                <i data-lucide="home" class="w-5 h-5"></i>
                <span class="text-[9px] font-bold uppercase tracking-wide">Beranda</span>
            </a>

            <!-- Kategori -->
            <a href="{{ route('categories.index') }}" 
                class="bottom-nav-link flex-1 flex flex-col items-center justify-center space-y-0.5 {{ Route::is('categories.index') ? 'text-brand-cyan' : 'text-gray-500 hover:text-gray-300' }}">
                <i data-lucide="grid-3x3" class="w-5 h-5"></i>
                <span class="text-[9px] font-bold uppercase tracking-wide">Kategori</span>
            </a>

            <!-- Keranjang (center elevated) -->
            <a href="/keranjang" class="bottom-nav-link flex-1 flex flex-col items-center justify-center -mt-5">
                <div class="relative w-14 h-14 bg-gradient-to-br from-[#4ecdc4] to-[#0d9488] rounded-2xl flex items-center justify-center shadow-[0_6px_20px_rgba(78,205,196,0.45)] border-2 border-[#0d0f13] transition-transform active:scale-95">
                    <i data-lucide="shopping-cart" class="w-6 h-6 text-black stroke-[2.5]"></i>
                    <span class="cart-badge absolute -top-1.5 -right-1.5 min-w-[1.2rem] h-5 bg-orange-400 text-black text-[9px] font-black rounded-full flex items-center justify-center px-1 shadow-lg" 
                          data-cart-count 
                          style="{{ count(session('cart', [])) > 0 ? '' : 'display: none;' }}">
                        {{ count(session('cart', [])) }}
                    </span>
                </div>
                <span class="text-[9px] font-bold uppercase tracking-wide mt-1 text-gray-200">Keranjang</span>
            </a>

            <!-- Pesanan -->
            @auth
                <a href="{{ route('orders.index') }}"
                    class="bottom-nav-link flex-1 flex flex-col items-center justify-center space-y-0.5 {{ Route::is('orders.*') ? 'text-brand-cyan' : 'text-gray-500 hover:text-gray-300' }}">
                    <i data-lucide="package" class="w-5 h-5"></i>
                    <span class="text-[9px] font-bold uppercase tracking-wide">Pesanan</span>
                </a>
            @else
                <a href="{{ route('login') }}"
                    class="bottom-nav-link flex-1 flex flex-col items-center justify-center space-y-0.5 text-gray-500 hover:text-gray-300">
                    <i data-lucide="package" class="w-5 h-5"></i>
                    <span class="text-[9px] font-bold uppercase tracking-wide">Pesanan</span>
                </a>
            @endauth

            <!-- Akun -->
            @auth
                <a href="{{ route('dashboard') }}"
                    class="bottom-nav-link flex-1 flex flex-col items-center justify-center space-y-0.5 {{ Route::is('dashboard') ? 'text-white' : 'text-gray-500 hover:text-gray-300' }}">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black uppercase relative border"
                         style="background: {{ $authRankInfo['hex'] }}20; border-color: {{ $authRankInfo['hex'] }}60; color: {{ $authRankInfo['hex'] }}; {{ $authRank !== 'reguler' ? 'box-shadow: 0 0 10px ' . $authRankInfo['hex'] . '40;' : '' }}">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <span class="text-[9px] font-bold uppercase tracking-wide">Akun</span>
                </a>
            @else
                <a href="{{ route('login') }}"
                    class="bottom-nav-link flex-1 flex flex-col items-center justify-center space-y-0.5 text-gray-500 hover:text-gray-300">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    <span class="text-[9px] font-bold uppercase tracking-wide">Masuk</span>
                </a>
            @endauth
        </div>
    </nav>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/{{ \App\Models\Setting::getGlobal()->whatsapp_number }}?text=Halo%20{{ urlencode(\App\Models\Setting::getGlobal()->store_name) }}%2C%20saya%20ingin%20bertanya%20mengenai%20pemesanan..." 
       target="_blank" 
       class="fixed bottom-[5.5rem] right-4 lg:bottom-6 lg:right-6 z-40 bg-emerald-500 hover:bg-emerald-600 text-white w-12 h-12 lg:w-14 lg:h-14 rounded-full flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110 hover:shadow-[0_0_20px_rgba(16,185,129,0.4)] group"
       title="Hubungi Kami via WhatsApp">
        <svg class="w-6 h-6 lg:w-7 lg:h-7 stroke-current fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M12.004 2C6.51 2 2.014 6.5 2.014 12c0 2.13.67 4.11 1.81 5.73L2.03 22l4.43-1.76c1.55.93 3.37 1.47 5.3 1.47 5.49-.01 9.99-4.5 9.99-10S17.49 2 12.004 2zm5.72 13.91c-.24.67-1.18 1.25-1.92 1.41-.53.11-1.22.2-3.57-.77-3-1.24-4.94-4.28-5.09-4.48-.15-.2-1.21-1.61-1.21-3.07s.76-2.17 1.03-2.47c.27-.3.59-.38.79-.38.2 0 .4 0 .57.01.18.01.41-.01.63.5.24.57.81 1.97.88 2.12.07.15.12.32.02.52-.1.2-.15.32-.3.49-.15.17-.32.39-.46.52-.15.15-.31.32-.13.62.18.3.79 1.3 1.69 2.1 1.16 1.03 2.14 1.35 2.44 1.5.3.15.48.12.66-.08.18-.2.78-.9.99-1.21.2-.3.42-.25.7-.15.28.1 1.77.84 2.07.99.3.15.5.22.57.35.07.12.07.72-.17 1.39z"/>
        </svg>
    </a>

    <script>
        lucide.createIcons();

        // ── Mobile Scroll-Reveal for .animate-fade-up (global agar bisa dipanggil ulang) ──
        window.revealCards = function () {
            if (window.innerWidth >= 1024) return; // desktop uses CSS-only animation

            const cards = document.querySelectorAll('.animate-fade-up:not(.revealed)');
            if (!cards.length) return;

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const el = entry.target;
                        const idx = parseInt(el.dataset.revealIdx || 0);
                        setTimeout(() => {
                            el.classList.add('revealed');
                        }, idx * 80); // 80ms stagger per card
                        observer.unobserve(el);
                    }
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

            cards.forEach((card, i) => {
                card.dataset.revealIdx = i;
                observer.observe(card);
            });
        };
        window.revealCards();

        // ── AJAX Add to Cart ─────────────────────────────────────────────────────
        // Intercept every "add-to-cart" form submission so the page doesn't reload
        document.addEventListener('submit', function (e) {
            const form = e.target.closest('form[action*="keranjang/tambah"], form[action*="cart/add"]');
            if (!form) return;
            e.preventDefault();

            const formData = new FormData(form);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
            })
            .then(async res => {
                const data = await res.json();
                showCartToast(data.success, data.message);
                if (data.success) {
                    updateCartBadges(data.cart_count);
                }
            })
            .catch(() => showCartToast(false, 'Terjadi kesalahan, silakan coba lagi.'));
        });

        // Update semua badge counter keranjang di navbar & bottom nav
        function updateCartBadges(count) {
            document.querySelectorAll('[data-cart-count]').forEach(el => {
                el.textContent = count;
            });
            // bottom nav badge
            document.querySelectorAll('.cart-badge').forEach(el => {
                el.textContent = count;
                el.style.display = count > 0 ? '' : 'none';
            });
        }

        // Toast notifikasi ringan
        function showCartToast(success, message) {
            const existing = document.getElementById('cart-toast');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.id = 'cart-toast';
            const isDesktop = window.innerWidth >= 1024;
            toast.style.cssText = [
                'position:fixed',
                isDesktop ? 'bottom:2rem' : 'bottom: calc(5.5rem + env(safe-area-inset-bottom, 0px))',
                'left:50%',
                'transform:translateX(-50%) translateY(20px)',
                'opacity:0',
                'z-index:9999',
                isDesktop ? 'padding:12px 28px' : 'padding:12px 20px',
                'border-radius:16px',
                isDesktop ? 'font-size:13px' : 'font-size:12px',
                'font-weight:700',
                'letter-spacing:0.05em',
                'text-transform:uppercase',
                'white-space:normal',
                'max-width:calc(100% - 2rem)',
                'text-align:center',
                'pointer-events:none',
                'transition:all 0.4s cubic-bezier(0.4,0,0.2,1)',
                isDesktop ? 'box-shadow:0 8px 30px rgba(0,0,0,0.5)' : 'box-shadow:0 4px 20px rgba(0,0,0,0.4)',
                isDesktop ? 'backdrop-filter:blur(12px)' : '',
                success
                    ? 'background:rgba(78,205,196,0.95);color:#000;border:1px solid #3ab8af'
                    : 'background:rgba(220,38,38,0.95);color:#fff;border:1px solid #b91c1c',
            ].join(';');
            toast.textContent = message;
            document.body.appendChild(toast);

            // Animate in
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    toast.style.transform = 'translateX(-50%) translateY(0)';
                    toast.style.opacity = '1';
                });
            });

            // Auto dismiss
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-50%) translateY(10px)';
                setTimeout(() => toast.remove(), 300);
            }, 2500);
        }

        // Global event delegation for Quick View modal triggers (works with AJAX-replaced content)
        document.addEventListener('DOMContentLoaded', () => {
            document.body.addEventListener('click', (e) => {
                const trigger = e.target.closest('[data-qv-data]');
                if (trigger) {
                    e.preventDefault();
                    e.stopPropagation();
                    try {
                        const productData = JSON.parse(trigger.getAttribute('data-qv-data'));
                        if (typeof Alpine !== 'undefined') {
                            const alpineRoot = document.querySelector('[x-data]');
                            if (alpineRoot && Alpine.$data) {
                                Alpine.$data(alpineRoot).openQuickView(productData);
                            }
                        }
                    } catch (err) {
                        console.error('Quick view error:', err);
                    }
                }
            });
        });
    </script>
</body>
</html>
