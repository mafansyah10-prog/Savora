@extends('layouts.app')
@section('title', \App\Models\Setting::getGlobal()->store_name . ' - ' . \App\Models\Setting::getGlobal()->hero_subtitle)

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-8 lg:px-10" x-data="{
    quickViewOpen: false,
    selectedProduct: { id: null, name: '', price: '', original_price: '', has_discount: false, description: '', image: '', category: '' },
    openQuickView(product) {
        this.selectedProduct = product;
        this.quickViewOpen = true;
        document.body.style.overflow = 'hidden';
    },
    closeQuickView() {
        this.quickViewOpen = false;
        document.body.style.overflow = '';
    }
}">
    <!-- Hero Section (Banner Carousel) -->
    <div class="relative w-full h-[55vh] sm:h-[65vh] lg:h-[70vh] xl:h-[75vh] min-h-[320px] max-h-[550px] lg:max-h-[700px] overflow-hidden rounded-[1.5rem] sm:rounded-[2.5rem] lg:rounded-[3rem] mb-10 md:mb-16 lg:mb-20 border border-gray-800/80 shadow-2xl lg:shadow-[0_30px_80px_rgba(0,0,0,0.5)] group/hero"
         x-data="{ 
            activeSlide: 0, 
            slidesCount: {{ count($banners) > 0 ? count($banners) : 3 }},
            autoPlay() {
                setInterval(() => {
                    this.activeSlide = (this.activeSlide + 1) % this.slidesCount;
                }, 5000);
            }
         }"
         x-init="autoPlay()">
         
        <div class="h-full w-full relative">
            @if(count($banners) > 0)
                @foreach($banners as $index => $banner)
                    <div class="absolute inset-0 transition-all duration-1000 ease-in-out transform"
                          :class="activeSlide === {{ $index }} ? 'opacity-100 scale-100 z-10' : 'opacity-0 z-0 pointer-events-none'">
                        <!-- Background Image with Ken Burns Zoom -->
                        <div class="absolute inset-0 bg-[#0f1115] overflow-hidden">
                            <img src="{{ asset('storage/' . $banner->image) }}" 
                                 class="w-full h-full object-cover object-center filter brightness-[0.4] transition-transform duration-[6000ms] ease-out transform" 
                                 :class="activeSlide === {{ $index }} ? 'scale-105' : 'scale-100'"
                                 alt="{{ $banner->title }}">
                            <!-- Gradient overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-brand-dark via-transparent to-black/40"></div>
                        </div>
                        
                        <div class="absolute inset-0 flex flex-col justify-center px-5 sm:px-12 md:px-20 lg:px-24 w-full md:w-3/4 lg:w-2/3 z-20">
                            <span class="text-gold-500 font-serif italic text-xs sm:text-sm md:text-lg lg:text-xl mb-2 lg:mb-3 tracking-wide">Pilihan Artisan Istimewa</span>
                            <h1 class="text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-black mb-3 md:mb-4 lg:mb-5 text-white leading-tight font-serif tracking-tight uppercase drop-shadow-lg">
                                {!! nl2br(e($banner->title)) !!}
                            </h1>
                            @php
                                $bannerUrl = null;
                                if ($banner->product_id && $banner->product) {
                                    $bannerUrl = route('product.show', $banner->product->slug);
                                } elseif ($banner->link) {
                                    $bannerUrl = $banner->link;
                                }
                            @endphp
                            @if($bannerUrl)
                            <div>
                                <a href="{{ $bannerUrl }}" class="inline-flex items-center px-6 sm:px-8 lg:px-10 py-2.5 sm:py-3 lg:py-3.5 bg-gradient-to-r from-orange-400 to-amber-500 hover:from-orange-500 hover:to-amber-600 text-black text-[10px] sm:text-xs lg:text-sm font-black rounded-full transition-all duration-300 transform hover:scale-105 hover:shadow-[0_0_30px_rgba(249,115,22,0.4)] tracking-widest uppercase">
                                    {{ $banner->button_text ?: 'Jelajahi Menu' }}
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 lg:w-4 lg:h-4 ml-2"></i>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <!-- Fallback premium slides if table is empty -->
                <!-- Slide 1 -->
                <div class="absolute inset-0 transition-all duration-1000 ease-in-out transform"
                     :class="activeSlide === 0 ? 'opacity-100 scale-100 z-10' : 'opacity-0 z-0 pointer-events-none'">
                    <div class="absolute inset-0 bg-[#0f1115] overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1549931319-a545dcf3bc73?q=80&w=1600&auto=format&fit=crop" 
                             class="w-full h-full object-cover object-center filter brightness-[0.35] transition-transform duration-[6000ms] ease-out transform" 
                             :class="activeSlide === 0 ? 'scale-105' : 'scale-100'"
                             alt="Sourdough Loaf">
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-dark via-transparent to-black/40"></div>
                    </div>
                    <div class="absolute inset-0 flex flex-col justify-center px-5 sm:px-12 md:px-20 lg:px-24 w-full md:w-3/4 lg:w-2/3 z-20">
                        <span class="text-gold-500 font-serif italic text-xs sm:text-sm md:text-lg lg:text-xl mb-2 lg:mb-3 tracking-wide">Pemanggangan Alami Setiap Pagi</span>
                        <h1 class="text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-black mb-3 md:mb-4 lg:mb-5 text-white leading-tight font-serif tracking-tight uppercase">
                            {{ \App\Models\Setting::getGlobal()->hero_title }}
                        </h1>
                        <p class="text-gray-300 text-xs md:text-sm lg:text-base mb-4 lg:mb-6 max-w-lg lg:max-w-xl leading-relaxed font-light hidden sm:block">
                            {{ \App\Models\Setting::getGlobal()->hero_subtitle }}
                        </p>
                        <div>
                            <a href="#menu-section" class="inline-flex items-center px-6 sm:px-8 lg:px-10 py-2.5 sm:py-3 lg:py-3.5 bg-gradient-to-r from-orange-400 to-amber-500 hover:from-orange-500 hover:to-amber-600 text-black text-[10px] sm:text-xs lg:text-sm font-black rounded-full transition-all duration-300 transform hover:scale-105 hover:shadow-[0_0_30px_rgba(249,115,22,0.4)] tracking-widest uppercase">
                                Lihat Koleksi Roti
                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5 lg:w-4 lg:h-4 ml-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="absolute inset-0 transition-all duration-1000 ease-in-out transform"
                     :class="activeSlide === 1 ? 'opacity-100 scale-100 z-10' : 'opacity-0 z-0 pointer-events-none'">
                    <div class="absolute inset-0 bg-[#0f1115] overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1467003909585-2f8a72700288?q=80&w=1600&auto=format&fit=crop" 
                             class="w-full h-full object-cover object-center filter brightness-[0.35] transition-transform duration-[6000ms] ease-out transform" 
                             :class="activeSlide === 1 ? 'scale-105' : 'scale-100'"
                             alt="Gourmet Salmon">
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-dark via-transparent to-black/40"></div>
                    </div>
                    <div class="absolute inset-0 flex flex-col justify-center px-5 sm:px-12 md:px-20 lg:px-24 w-full md:w-3/4 lg:w-2/3 z-20">
                        <span class="text-gold-500 font-serif italic text-xs sm:text-sm md:text-lg lg:text-xl mb-2 lg:mb-3 tracking-wide">Menu Sehat Kaya Nutrisi</span>
                        <h1 class="text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-black mb-3 md:mb-4 lg:mb-5 text-white leading-tight font-serif tracking-tight uppercase">
                            Mediterranean Salmon Bowl
                        </h1>
                        <p class="text-gray-300 text-xs md:text-sm lg:text-base mb-4 lg:mb-6 max-w-lg lg:max-w-xl leading-relaxed font-light hidden sm:block">
                            Salmon panggang segar dipadukan dengan sayuran organik pilihan dan saus racikan khas dapur Savora.
                        </p>
                        <div>
                            <a href="#menu-section" class="inline-flex items-center px-6 sm:px-8 lg:px-10 py-2.5 sm:py-3 lg:py-3.5 bg-gradient-to-r from-orange-400 to-amber-500 hover:from-orange-500 hover:to-amber-600 text-black text-[10px] sm:text-xs lg:text-sm font-black rounded-full transition-all duration-300 transform hover:scale-105 hover:shadow-[0_0_30px_rgba(249,115,22,0.4)] tracking-widest uppercase">
                                Pesan Sekarang
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 lg:w-4 lg:h-4 ml-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="absolute inset-0 transition-all duration-1000 ease-in-out transform"
                     :class="activeSlide === 2 ? 'opacity-100 scale-100 z-10' : 'opacity-0 z-0 pointer-events-none'">
                    <div class="absolute inset-0 bg-[#0f1115] overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1497935586351-b67a49e012bf?q=80&w=1600&auto=format&fit=crop" 
                             class="w-full h-full object-cover object-center filter brightness-[0.35] transition-transform duration-[6000ms] ease-out transform" 
                             :class="activeSlide === 2 ? 'scale-105' : 'scale-100'"
                             alt="Coffee Spritz">
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-dark via-transparent to-black/40"></div>
                    </div>
                    <div class="absolute inset-0 flex flex-col justify-center px-5 sm:px-12 md:px-20 lg:px-24 w-full md:w-3/4 lg:w-2/3 z-20">
                        <span class="text-gold-500 font-serif italic text-xs sm:text-sm md:text-lg lg:text-xl mb-2 lg:mb-3 tracking-wide">Kesegaran Alami Kreatif</span>
                        <h1 class="text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-black mb-3 md:mb-4 lg:mb-5 text-white leading-tight font-serif tracking-tight uppercase">
                            Minuman Racikan Artisan
                        </h1>
                        <p class="text-gray-300 text-xs md:text-sm lg:text-base mb-4 lg:mb-6 max-w-lg lg:max-w-xl leading-relaxed font-light hidden sm:block">
                            Segarkan harimu dengan Sparkling Citrus Spritz dan racikan minuman dingin botolan berkualitas tinggi tanpa pewarna buatan.
                        </p>
                        <div>
                            <a href="#menu-section" class="inline-flex items-center px-6 sm:px-8 lg:px-10 py-2.5 sm:py-3 lg:py-3.5 bg-gradient-to-r from-orange-400 to-amber-500 hover:from-orange-500 hover:to-amber-600 text-black text-[10px] sm:text-xs lg:text-sm font-black rounded-full transition-all duration-300 transform hover:scale-105 hover:shadow-[0_0_30px_rgba(249,115,22,0.4)] tracking-widest uppercase">
                                Jelajahi Minuman
                                <i data-lucide="coffee" class="w-3.5 h-3.5 lg:w-4 lg:h-4 ml-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Prev/Next Navigation Arrows (Desktop Only, Visible on Hover) -->
            <button @click="activeSlide = (activeSlide - 1 + slidesCount) % slidesCount" 
                    class="absolute left-4 sm:left-6 top-1/2 -translate-y-1/2 z-30 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-black/40 hover:bg-gold-500 hover:text-black border border-white/10 flex items-center justify-center text-white transition-all duration-300 opacity-0 group-hover/hero:opacity-100 hidden md:flex hover:scale-105 active:scale-95"
                    aria-label="Slide sebelumnya">
                <i data-lucide="chevron-left" class="w-5 h-5 sm:w-6 sm:h-6"></i>
            </button>
            <button @click="activeSlide = (activeSlide + 1) % slidesCount" 
                    class="absolute right-4 sm:right-6 top-1/2 -translate-y-1/2 z-30 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-black/40 hover:bg-gold-500 hover:text-black border border-white/10 flex items-center justify-center text-white transition-all duration-300 opacity-0 group-hover/hero:opacity-100 hidden md:flex hover:scale-105 active:scale-95"
                    aria-label="Slide berikutnya">
                <i data-lucide="chevron-right" class="w-5 h-5 sm:w-6 sm:h-6"></i>
            </button>
        </div>
        
        <!-- Pagination Dots -->
        <div class="absolute bottom-4 sm:bottom-6 left-1/2 -translate-x-1/2 flex space-x-2 sm:space-x-3 z-20">
            <template x-for="i in slidesCount" :key="i-1">
                <button @click="activeSlide = i-1" 
                        class="h-1.5 sm:h-2 rounded-full transition-all duration-300 focus:outline-none"
                        :class="activeSlide === i-1 ? 'bg-gold-500 w-5 sm:w-6' : 'bg-white/30 w-1.5 sm:w-2 hover:bg-white/60'"></button>
            </template>
        </div>
    </div>

    <!-- Section: Kelebihan Usaha Rumahan Savora (Values Grid) -->
    <div class="mb-14 md:mb-20 lg:mb-28">
        <div class="text-center mb-8 md:mb-10 lg:mb-14">
            <span class="text-brand-cyan text-[10px] lg:text-xs font-black uppercase tracking-[0.25em] lg:tracking-[0.3em]">Nilai Utama Kami</span>
            <h2 class="text-xl md:text-3xl lg:text-4xl font-bold font-serif text-white mt-2 lg:mt-3">Dibuat dari Dapur dengan Penuh Dedikasi</h2>
            <div class="text-gray-400 max-w-2xl lg:max-w-3xl mx-auto mt-4 lg:mt-6 text-sm lg:text-base leading-relaxed">
                {!! \App\Models\Setting::getGlobal()->about_text !!}
            </div>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 lg:gap-8">
            <!-- Card 1 -->
            <div class="card-shimmer bg-[#16181c]/60 backdrop-blur-sm border border-gray-800/80 hover:border-gold-500/30 p-4 sm:p-6 lg:p-8 rounded-2xl lg:rounded-3xl transition-all duration-300 transform hover:-translate-y-1 lg:hover:-translate-y-2 hover:shadow-[0_10px_30px_rgba(226,200,110,0.05)] lg:hover:shadow-[0_20px_50px_rgba(226,200,110,0.08)] group animate-fade-up pointer-events-none">
                <div class="w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 bg-gold-500/10 rounded-xl lg:rounded-2xl flex items-center justify-center text-gold-500 mb-3 sm:mb-4 lg:mb-5 group-hover:bg-gold-500 group-hover:text-black transition-all duration-500">
                    <i data-lucide="heart" class="w-5 h-5 sm:w-6 sm:h-6 lg:w-7 lg:h-7"></i>
                </div>
                <h3 class="text-xs sm:text-sm lg:text-base font-bold text-gray-100 mb-1 sm:mb-2">Dibuat Manual</h3>
                <p class="text-gray-400 text-[10px] sm:text-xs lg:text-sm leading-relaxed font-light hidden sm:block">Setiap porsi diolah secara teliti dengan resep keluarga rahasia demi rasa autentik.</p>
            </div>
            <!-- Card 2 -->
            <div class="card-shimmer bg-[#16181c]/60 backdrop-blur-sm border border-gray-800/80 hover:border-brand-cyan/30 p-4 sm:p-6 lg:p-8 rounded-2xl lg:rounded-3xl transition-all duration-300 transform hover:-translate-y-1 lg:hover:-translate-y-2 hover:shadow-[0_10px_30px_rgba(78,205,196,0.05)] lg:hover:shadow-[0_20px_50px_rgba(78,205,196,0.08)] group animate-fade-up pointer-events-none">
                <div class="w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 bg-brand-cyan/10 rounded-xl lg:rounded-2xl flex items-center justify-center text-brand-cyan mb-3 sm:mb-4 lg:mb-5 group-hover:bg-brand-cyan group-hover:text-black transition-all duration-500">
                    <i data-lucide="leaf" class="w-5 h-5 sm:w-6 sm:h-6 lg:w-7 lg:h-7"></i>
                </div>
                <h3 class="text-xs sm:text-sm lg:text-base font-bold text-gray-100 mb-1 sm:mb-2">Bahan Alami</h3>
                <p class="text-gray-400 text-[10px] sm:text-xs lg:text-sm leading-relaxed font-light hidden sm:block">Tanpa pewarna atau pengawet buatan. Kami hanya menggunakan bahan organik terbaik.</p>
            </div>
            <!-- Card 3 -->
            <div class="card-shimmer bg-[#16181c]/60 backdrop-blur-sm border border-gray-800/80 hover:border-gold-500/30 p-4 sm:p-6 lg:p-8 rounded-2xl lg:rounded-3xl transition-all duration-300 transform hover:-translate-y-1 lg:hover:-translate-y-2 hover:shadow-[0_10px_30px_rgba(226,200,110,0.05)] lg:hover:shadow-[0_20px_50px_rgba(226,200,110,0.08)] group animate-fade-up pointer-events-none">
                <div class="w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 bg-gold-500/10 rounded-xl lg:rounded-2xl flex items-center justify-center text-gold-500 mb-3 sm:mb-4 lg:mb-5 group-hover:bg-gold-500 group-hover:text-black transition-all duration-500">
                    <i data-lucide="chef-hat" class="w-5 h-5 sm:w-6 sm:h-6 lg:w-7 lg:h-7"></i>
                </div>
                <h3 class="text-xs sm:text-sm lg:text-base font-bold text-gray-100 mb-1 sm:mb-2">Selalu Segar</h3>
                <p class="text-gray-400 text-[10px] sm:text-xs lg:text-sm leading-relaxed font-light hidden sm:block">Roti dan hidangan dipanggang dan dimasak segar setiap harinya sesuai pesanan Anda.</p>
            </div>
            <!-- Card 4 -->
            <div class="card-shimmer bg-[#16181c]/60 backdrop-blur-sm border border-gray-800/80 hover:border-brand-cyan/30 p-4 sm:p-6 lg:p-8 rounded-2xl lg:rounded-3xl transition-all duration-300 transform hover:-translate-y-1 lg:hover:-translate-y-2 hover:shadow-[0_10px_30px_rgba(78,205,196,0.05)] lg:hover:shadow-[0_20px_50px_rgba(78,205,196,0.08)] group animate-fade-up pointer-events-none">
                <div class="w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 bg-brand-cyan/10 rounded-xl lg:rounded-2xl flex items-center justify-center text-brand-cyan mb-3 sm:mb-4 lg:mb-5 group-hover:bg-brand-cyan group-hover:text-black transition-all duration-500">
                    <i data-lucide="send" class="w-5 h-5 sm:w-6 sm:h-6 lg:w-7 lg:h-7"></i>
                </div>
                <h3 class="text-xs sm:text-sm lg:text-base font-bold text-gray-100 mb-1 sm:mb-2">Pengiriman Instan</h3>
                <p class="text-gray-400 text-[10px] sm:text-xs lg:text-sm leading-relaxed font-light hidden sm:block">Dikemas higienis dan dikirim instan agar hidangan sampai dalam keadaan hangat dan lezat.</p>
            </div>
        </div>
    </div>

    <!-- Shop by Category Section -->
    <div class="mb-12 md:mb-16 lg:mb-20 scroll-mt-24 md:scroll-mt-28" id="shop-by-category">
        <div class="flex items-center justify-between mb-4 md:mb-6 lg:mb-8">
            <div>
                <span class="text-brand-cyan text-[10px] lg:text-xs font-black uppercase tracking-[0.25em]">Navigasi Menu</span>
                <h2 class="text-lg md:text-2xl lg:text-3xl font-bold font-serif text-white mt-1">Kategori Kuliner</h2>
            </div>
            @if(request('category') || request('search') || request('filter'))
                <a href="{{ route('home') }}#shop-by-category" class="text-[10px] text-orange-400 hover:text-orange-500 transition-colors uppercase tracking-widest font-black flex items-center">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 mr-1"></i> Reset
                </a>
            @endif
        </div>
        
        <div class="flex gap-2 sm:gap-3 md:gap-4 lg:gap-5 overflow-x-auto px-3 sm:px-4 md:px-5 py-3 sm:py-4 scrollbar-hide">
            @foreach($categories as $index => $category)
            <a href="{{ route('home', ['category' => $category->slug]) }}#shop-by-category" 
               class="cat-pill-reveal flex-shrink-0 flex flex-col items-center justify-center w-20 min-h-[5.5rem] sm:w-28 sm:min-h-[6.5rem] lg:w-32 lg:min-h-[7.5rem] py-3 sm:py-4 rounded-xl sm:rounded-2xl lg:rounded-3xl border active:scale-95 group {{ request('category') == $category->slug ? 'category-active-card bg-gold-500 border-gold-400 text-black shadow-lg shadow-gold-500/20 active-category-pulse scale-[1.03]' : 'category-hover-card bg-[#16181c]/60 border-gray-800/80 text-gray-300' }}">
                <div class="flex items-center justify-center h-8 sm:h-10 lg:h-12 w-full category-icon-container mb-1.5 sm:mb-2">
                    <i data-lucide="{{ $category->icon ?? 'utensils' }}" 
                       class="w-6 h-6 sm:w-7 sm:h-7 lg:w-8 lg:h-8 transition-all duration-500 {{ request('category') == $category->slug ? 'text-black' : 'text-[#e2c86e]' }}"></i>
                </div>
                <span class="text-[9px] sm:text-[10px] lg:text-[11px] font-bold tracking-wider text-center px-1 sm:px-2 uppercase leading-tight break-words">{{ $category->name }}</span>
            </a>
            @endforeach
        </div>
    </div>

    <!-- Featured Delights (Menu Grid) -->
    <div class="mb-8 md:mb-12 lg:mb-16 scroll-mt-24 md:scroll-mt-28" id="menu-section">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-6 md:mb-8 lg:mb-10 gap-3">
            <div>
                <span class="text-brand-cyan text-[10px] lg:text-xs font-black uppercase tracking-[0.25em]">Menu Unggulan</span>
                <h2 class="text-xl md:text-3xl lg:text-4xl font-bold font-serif text-white mt-1">
                    @if(request('search'))
                        Hasil Pencarian: "{{ request('search') }}"
                    @elseif(request('filter') === 'new')
                        Koleksi Hidangan Baru
                    @elseif(request('filter') === 'popular')
                        Menu Paling Terlaris
                    @elseif(request('category'))
                        Kategori "{{ $categories->where('slug', request('category'))->first()->name ?? 'Kategori' }}"
                    @else
                        Menu Pilihan Terbaik
                    @endif
                </h2>
            </div>
            
            <!-- Quick filters bar -->
            <div class="flex space-x-2 lg:space-x-3 overflow-x-auto pb-1 scrollbar-hide flex-shrink-0">
                <a href="{{ route('home') }}#menu-section" class="flex-shrink-0 px-3 sm:px-4 lg:px-5 py-1.5 lg:py-2 rounded-full text-[10px] lg:text-[11px] uppercase font-bold tracking-wider transition-all duration-300 {{ !request('filter') ? 'bg-brand-cyan text-black shadow-[0_4px_15px_rgba(78,205,196,0.3)]' : 'bg-gray-800/50 text-gray-400 hover:text-white hover:bg-gray-700/50' }}">Semua</a>
                <a href="{{ route('home', ['filter' => 'new']) }}#menu-section" class="flex-shrink-0 px-3 sm:px-4 lg:px-5 py-1.5 lg:py-2 rounded-full text-[10px] lg:text-[11px] uppercase font-bold tracking-wider transition-all duration-300 {{ request('filter') === 'new' ? 'bg-brand-cyan text-black shadow-[0_4px_15px_rgba(78,205,196,0.3)]' : 'bg-gray-800/50 text-gray-400 hover:text-white hover:bg-gray-700/50' }}">Terbaru</a>
                <a href="{{ route('home', ['filter' => 'popular']) }}#menu-section" class="flex-shrink-0 px-3 sm:px-4 lg:px-5 py-1.5 lg:py-2 rounded-full text-[10px] lg:text-[11px] uppercase font-bold tracking-wider transition-all duration-300 {{ request('filter') === 'popular' ? 'bg-brand-cyan text-black shadow-[0_4px_15px_rgba(78,205,196,0.3)]' : 'bg-gray-800/50 text-gray-400 hover:text-white hover:bg-gray-700/50' }}">Terlaris</a>
            </div>
        </div>
        
        @if(count($products) > 0)
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 lg:gap-7">
                @foreach($products as $product)
                <div class="card-shimmer bg-[#16181c]/60 backdrop-blur-sm border border-gray-800/80 rounded-xl sm:rounded-2xl lg:rounded-3xl overflow-hidden shadow-lg p-2 sm:p-3 lg:p-4 transition-all duration-500 transform hover:-translate-y-2 lg:hover:-translate-y-3 hover:shadow-[0_15px_30px_rgba(0,0,0,0.4)] lg:hover:shadow-[0_25px_50px_rgba(0,0,0,0.5)] hover:border-gray-700/60 group animate-fade-up">
                    @php
                        $hasCustomizations = $product->enable_spiciness || $product->enable_toppings || $product->enable_sauces || $product->enable_additionals;
                        $qvData = json_encode([
                            'id'                 => $product->id,
                            'name'               => $product->name,
                            'price'              => 'Rp ' . number_format($product->selling_price, 0, ',', '.'),
                            'original_price'     => 'Rp ' . number_format($product->price, 0, ',', '.'),
                            'has_discount'       => $product->hasDiscount(),
                            'description'        => $product->description ?? '',
                            'image'              => $product->image ? asset('storage/' . $product->image) : '',
                            'category'           => $product->category->name,
                            'stock'              => $product->stock ?? 999,
                            'has_customizations' => $hasCustomizations,
                            'url'                => route('product.show', $product->slug),
                        ], JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_UNICODE);
                    @endphp
                    
                    <!-- Image container (Clickable to open Detail) -->
                    <div data-qv-data="{{ $qvData }}" @click="openQuickView({{ $qvData }})" class="rounded-lg sm:rounded-xl lg:rounded-2xl overflow-hidden h-36 sm:h-44 lg:h-52 mb-3 sm:mb-4 relative bg-gray-900 transition-all duration-500 shadow-inner cursor-pointer">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover transition-all duration-700 group-hover:scale-110 group-hover:brightness-110" alt="{{ $product->name }}">
                        @else
                            <div class="flex flex-col items-center justify-center h-full bg-[#1b3c37]/10 text-[#e2c86e] p-4 text-center">
                                <i data-lucide="utensils" class="w-8 h-8 sm:w-10 sm:h-10 mb-2 opacity-40"></i>
                                <span class="text-[9px] font-black uppercase tracking-widest opacity-60">{{ $product->category->name }}</span>
                            </div>
                        @endif

                        {{-- Stock badge --}}
                        @if($product->stock !== null)
                            @if($product->stock <= 0)
                                <span class="absolute top-2 left-2 z-10 bg-red-600 text-white text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full shadow">
                                    Stok Habis
                                </span>
                            @elseif($product->stock <= 5)
                                <span class="absolute top-2 left-2 z-10 bg-orange-500 text-black text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full shadow">
                                    Sisa {{ $product->stock }}
                                </span>
                            @endif
                        @endif
                    </div>
                    
                    <!-- Content -->
                    <div class="px-0.5 sm:px-1">
                        <!-- Heading & Category (Clickable to open Detail) -->
                        <div data-qv-data="{{ $qvData }}" @click="openQuickView({{ $qvData }})" class="cursor-pointer">
                            <span class="text-[9px] lg:text-[10px] font-black uppercase tracking-widest text-brand-cyan/80">{{ $product->category->name }}</span>
                            <h3 class="text-xs lg:text-sm font-bold text-white leading-tight mt-0.5 group-hover:text-gold-500 transition-colors line-clamp-2 mb-2 sm:mb-3">{{ $product->name }}</h3>
                        </div>
                        <div class="flex justify-between items-center pt-2 lg:pt-3 border-t border-gray-800/40">
                            @if($product->hasDiscount())
                                <div class="flex flex-col items-start">
                                    <span class="text-[9px] lg:text-[10px] text-gray-500 line-through">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                    <span class="text-xs lg:text-sm font-black text-gold-500">Rp {{ number_format($product->discount_price, 0, ',', '.') }}</span>
                                </div>
                            @else
                                <span class="text-xs lg:text-sm font-black text-gray-200">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            @endif
                            
                            @if(!\App\Models\Setting::getGlobal()->isStoreOpen())
                                <span class="w-8 h-8 sm:w-7 sm:h-7 bg-gray-800 text-gray-500 rounded-full flex items-center justify-center cursor-not-allowed" title="Toko Tutup">
                                    <i data-lucide="lock" class="w-4 h-4 sm:w-3.5 sm:h-3.5"></i>
                                </span>
                            @elseif($product->stock !== null && $product->stock <= 0)
                                <span class="w-8 h-8 sm:w-7 sm:h-7 bg-gray-700 text-gray-500 rounded-full flex items-center justify-center cursor-not-allowed" title="Stok Habis">
                                    <i data-lucide="ban" class="w-4 h-4 sm:w-3.5 sm:h-3.5"></i>
                                </span>
                            @else
                                @if($hasCustomizations)
                                    <a href="{{ route('product.show', $product->slug) }}" 
                                        class="w-8 h-8 sm:w-7 sm:h-7 bg-brand-cyan hover:bg-teal-400 text-black rounded-full flex items-center justify-center transition-all duration-300 transform hover:scale-110 shadow-md focus:outline-none active:scale-95" 
                                        title="Pilih Kustomisasi">
                                        <i data-lucide="sliders" class="w-4 h-4 sm:w-3.5 sm:h-3.5 stroke-[3]"></i>
                                    </a>
                                @else
                                    <form action="/keranjang/tambah" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" 
                                            class="w-8 h-8 sm:w-7 sm:h-7 bg-orange-400 hover:bg-orange-500 text-black rounded-full flex items-center justify-center transition-all duration-300 transform hover:scale-110 shadow-md focus:outline-none active:scale-95" 
                                            title="Tambah ke Keranjang">
                                            <i data-lucide="plus" class="w-4 h-4 sm:w-3.5 sm:h-3.5 stroke-[3]"></i>
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="bg-[#16181c]/40 border border-gray-800/80 rounded-2xl p-10 sm:p-12 text-center">
                <i data-lucide="inbox" class="w-12 h-12 text-gray-600 mx-auto mb-4"></i>
                <h3 class="text-sm font-bold text-gray-400 mb-1">Menu Tidak Ditemukan</h3>
                <p class="text-gray-500 text-xs">Coba cari kata kunci lain atau bersihkan filter di atas.</p>
            </div>
        @endif
    </div>



    <!-- Product Quick View Modal — Bottom Sheet on Mobile, Centered on Desktop -->
    <div class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center sm:px-4 sm:py-6" 
         x-show="quickViewOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
         
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="closeQuickView()"></div>
        
        <!-- Modal Content Box -->
        <div class="bg-[#16181c] border-t sm:border border-gray-800 rounded-t-3xl sm:rounded-3xl w-full sm:max-w-3xl lg:max-w-4xl overflow-hidden shadow-2xl lg:shadow-[0_40px_80px_rgba(0,0,0,0.6)] relative z-10 max-h-[88vh] sm:max-h-[85vh] overflow-y-auto"
             x-show="quickViewOpen"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95 sm:translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100 sm:translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100 sm:translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95 sm:translate-y-4">
             
            <!-- Mobile drag handle -->
            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="w-10 h-1 bg-gray-700 rounded-full"></div>
            </div>
            
            <!-- Close button -->
            <button @click="closeQuickView()" class="absolute top-4 right-4 text-gray-500 hover:text-white transition z-30 w-8 h-8 rounded-full bg-black/40 border border-gray-800 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
            
            <div class="grid grid-cols-1 sm:grid-cols-2">
                <!-- Product Image -->
                <div class="h-52 sm:h-auto sm:min-h-[280px] lg:min-h-[380px] relative bg-gray-900 flex items-center justify-center">
                    <template x-if="selectedProduct.image">
                        <img :src="selectedProduct.image" class="w-full h-full object-cover" :alt="selectedProduct.name">
                    </template>
                    <template x-if="!selectedProduct.image">
                        <div class="text-[#e2c86e] flex flex-col items-center">
                            <i data-lucide="utensils" class="w-12 h-12 mb-2 opacity-30"></i>
                            <span class="text-[10px] font-black uppercase tracking-wider opacity-50" x-text="selectedProduct.category"></span>
                        </div>
                    </template>
                </div>
                
                <!-- Product Details -->
                <div class="p-5 pb-8 sm:p-6 md:p-8 lg:p-10 flex flex-col justify-between">
                    <div>
                        <span class="text-[9px] lg:text-[10px] font-black uppercase tracking-widest text-brand-cyan" x-text="selectedProduct.category"></span>
                        <h2 class="text-base sm:text-lg md:text-xl lg:text-2xl font-bold font-serif text-white mt-1 mb-2 lg:mb-3" x-text="selectedProduct.name"></h2>
                        <div class="flex items-baseline gap-2 mb-2 lg:mb-3">
                            <template x-if="selectedProduct.has_discount">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-sm lg:text-lg font-black text-gold-500" x-text="selectedProduct.price"></span>
                                    <span class="text-xs lg:text-sm text-gray-500 line-through" x-text="selectedProduct.original_price"></span>
                                </div>
                            </template>
                            <template x-if="!selectedProduct.has_discount">
                                <span class="text-sm lg:text-lg font-black text-gold-500" x-text="selectedProduct.price"></span>
                            </template>
                        </div>

                        {{-- Stock badge in quick view --}}
                        <div class="mb-3 sm:mb-4">
                            <template x-if="selectedProduct.stock <= 0">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-600/20 border border-red-500/40 text-red-400 text-[9px] font-black uppercase tracking-widest rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                    Stok Habis
                                </span>
                            </template>
                            <template x-if="selectedProduct.stock > 0 && selectedProduct.stock <= 5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-orange-500/20 border border-orange-500/40 text-orange-400 text-[9px] font-black uppercase tracking-widest rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                                    Sisa <span x-text="selectedProduct.stock" class="mx-0.5"></span> Tersedia
                                </span>
                            </template>
                            <template x-if="selectedProduct.stock > 5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-brand-cyan/10 border border-brand-cyan/30 text-brand-cyan text-[9px] font-black uppercase tracking-widest rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-brand-cyan"></span>
                                    Stok: <span x-text="selectedProduct.stock" class="mx-0.5"></span> Tersedia
                                </span>
                            </template>
                        </div>

                        <div class="w-full h-px bg-gray-800 mb-3 sm:mb-4"></div>
                        <p class="text-xs text-gray-400 font-light leading-relaxed mb-4 sm:mb-6" x-text="selectedProduct.description || 'Kelezatan berkualitas buatan dapur rumahan Savora. Dibuat fresh setiap hari dengan bahan-bahan terpilih.'"></p>
                    </div>
                    
                    <div>
                        @if(!\App\Models\Setting::getGlobal()->isStoreOpen())
                            <button disabled class="w-full bg-gray-850 text-gray-500 py-3.5 rounded-xl text-xs font-black uppercase tracking-widest cursor-not-allowed flex items-center justify-center space-x-2">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                                <span>Toko Tutup</span>
                            </button>
                        @else
                            <template x-if="selectedProduct.stock <= 0">
                                <button disabled class="w-full bg-gray-800 text-gray-500 py-3.5 rounded-xl text-xs font-black uppercase tracking-widest cursor-not-allowed flex items-center justify-center space-x-2">
                                    <i data-lucide="ban" class="w-4 h-4"></i>
                                    <span>Stok Habis</span>
                                </button>
                            </template>
                            <template x-if="selectedProduct.stock > 0">
                                <div>
                                    <template x-if="selectedProduct.has_customizations">
                                        <a :href="selectedProduct.url" class="w-full bg-brand-cyan hover:bg-teal-400 text-black py-3.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-300 flex items-center justify-center space-x-2 active:scale-95">
                                            <i data-lucide="sliders" class="w-4 h-4"></i>
                                            <span>Pilih Opsi Kustomisasi</span>
                                        </a>
                                    </template>
                                    <template x-if="!selectedProduct.has_customizations">
                                        <form action="/keranjang/tambah" method="POST">
                                            @csrf
                                            <input type="hidden" name="product_id" :value="selectedProduct.id">
                                            <button type="submit" class="w-full bg-brand-cyan hover:bg-teal-400 text-black py-3.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-300 flex items-center justify-center space-x-2 active:scale-95">
                                                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                                                <span>Masukkan Keranjang</span>
                                            </button>
                                        </form>
                                    </template>
                                </div>
                            </template>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes category-active-glow {
        0%, 100% {
            box-shadow: 0 10px 20px -2px rgba(226, 200, 110, 0.25), 0 0 0 1px rgba(247, 225, 160, 0.2);
            transform: scale(1.02);
        }
        50% {
            box-shadow: 0 15px 30px 0px rgba(226, 200, 110, 0.45), 0 0 0 3px rgba(247, 225, 160, 0.45);
            transform: scale(1.04);
        }
    }
    .active-category-pulse {
        animation: category-active-glow 2s infinite ease-in-out;
        z-index: 5;
    }

    /* Mobile: slide-in from bottom for category pill row */
    @keyframes cat-pop {
        0%   { opacity: 0; transform: translateY(22px) scale(0.88); }
        65%  { opacity: 1; transform: translateY(-5px) scale(1.04); }
        100% { opacity: 1; transform: translateY(0)   scale(1); }
    }
    @media (max-width: 1023px) {
        .cat-pill-reveal {
            opacity: 0;
            transform: translateY(22px) scale(0.88);
            transition: opacity 0.45s cubic-bezier(0.22,1,0.36,1),
                        transform 0.45s cubic-bezier(0.22,1,0.36,1);
        }
        .cat-pill-reveal.revealed {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes loader-progress {
        0% { left: -40%; width: 30%; }
        50% { width: 40%; }
        100% { left: 110%; width: 30%; }
    }
    .ajax-loading-bar {
        position: relative;
        height: 3px;
        width: 100%;
        background: rgba(78, 205, 196, 0.1);
        overflow: hidden;
        border-radius: 999px;
        margin-bottom: 24px;
        margin-top: -12px;
    }
    .ajax-loading-bar::after {
        content: '';
        position: absolute;
        top: 0;
        left: -40%;
        height: 100%;
        width: 30%;
        background: linear-gradient(90deg, transparent, #4ecdc4, #e2c86e, transparent);
        animation: loader-progress 1.2s infinite linear;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // ── Mobile Scroll-Reveal untuk .cat-pill-reveal ──────────────────────────
        function revealPills() {
            if (window.innerWidth >= 1024) {
                // Desktop: tampilkan semua langsung
                document.querySelectorAll('.cat-pill-reveal').forEach(el => el.classList.add('revealed'));
                return;
            }
            const pills = document.querySelectorAll('.cat-pill-reveal:not(.revealed)');
            if (!pills.length) return;

            const pillObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const el = entry.target;
                        const idx = parseInt(el.dataset.pillIdx || 0);
                        setTimeout(() => el.classList.add('revealed'), idx * 70);
                        pillObserver.unobserve(el);
                    }
                });
            }, { threshold: 0.05, rootMargin: '0px 0px -10px 0px' });

            pills.forEach((pill, i) => {
                pill.dataset.pillIdx = i;
                pillObserver.observe(pill);
            });
        }
        revealPills(); // panggil saat halaman pertama load

        const shopByCategory = document.getElementById('shop-by-category');
        const menuSection = document.getElementById('menu-section');
        if (!shopByCategory || !menuSection) return;

        const loadContent = async (url) => {
            const productsGrid = menuSection.querySelector('.grid, .bg-\\[\\#16181c\\]\\/40');
            
            // Show dynamic loading bar inside menuSection
            let loadingBar = menuSection.querySelector('.ajax-loading-bar');
            if (!loadingBar) {
                loadingBar = document.createElement('div');
                loadingBar.className = 'ajax-loading-bar';
                const headerRow = menuSection.firstElementChild;
                if (headerRow) {
                    headerRow.after(loadingBar);
                } else {
                    menuSection.prepend(loadingBar);
                }
            }

            // Animate out current products grid
            if (productsGrid) {
                productsGrid.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                productsGrid.style.opacity = '0';
                productsGrid.style.transform = 'translateY(10px)';
            }

            // Small delay to let fade-out animate
            await new Promise(resolve => setTimeout(resolve, 150));

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Fetch failed');
                const html = await response.text();

                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newShopByCategory = doc.getElementById('shop-by-category');
                if (newShopByCategory && shopByCategory) {
                    const currentUrl = new URL(url, window.location.origin);
                    const categoryParam = currentUrl.searchParams.get('category');
                    const oldUrl = new URL(window.location.href);
                    const oldCategoryParam = oldUrl.searchParams.get('category');
                    
                    // Only update category bar classes/Reset button if the category filter actually changed
                    if (categoryParam !== oldCategoryParam) {
                        // Update the Reset button visibility
                        const headerFlex = shopByCategory.querySelector('.flex.items-center.justify-between');
                        const newReset = newShopByCategory.querySelector('a[href*="home"], a[href="/"]');
                        if (headerFlex) {
                            const existingReset = headerFlex.querySelector('a');
                            if (existingReset) existingReset.remove();
                            if (newReset) {
                                headerFlex.appendChild(newReset);
                            }
                        }
                        
                        // Update active states of existing category pills without replacing them in DOM
                        shopByCategory.querySelectorAll('.cat-pill-reveal').forEach(a => {
                            const aUrl = new URL(a.href);
                            const aCategory = aUrl.searchParams.get('category');
                            const icon = a.querySelector('i');
                            
                            if (aCategory === categoryParam) {
                                a.className = "cat-pill-reveal flex-shrink-0 flex flex-col items-center justify-center w-20 min-h-[5.5rem] sm:w-28 sm:min-h-[6.5rem] lg:w-32 lg:min-h-[7.5rem] py-3 sm:py-4 rounded-xl sm:rounded-2xl lg:rounded-3xl border active:scale-95 group category-active-card bg-gold-500 border-gold-400 text-black shadow-lg shadow-gold-500/20 active-category-pulse scale-[1.03] revealed";
                                if (icon) {
                                    icon.classList.remove('text-[#e2c86e]');
                                    icon.classList.add('text-black');
                                }
                            } else {
                                a.className = "cat-pill-reveal flex-shrink-0 flex flex-col items-center justify-center w-20 min-h-[5.5rem] sm:w-28 sm:min-h-[6.5rem] lg:w-32 lg:min-h-[7.5rem] py-3 sm:py-4 rounded-xl sm:rounded-2xl lg:rounded-3xl border active:scale-95 group category-hover-card bg-[#16181c]/60 border-gray-800/80 text-gray-300 revealed";
                                if (icon) {
                                    icon.classList.remove('text-black');
                                    icon.classList.add('text-[#e2c86e]');
                                }
                            }
                        });
                        requestAnimationFrame(() => revealPills());
                    }
                }

                const newMenuSection = doc.getElementById('menu-section');
                if (newMenuSection && menuSection) {
                    menuSection.innerHTML = newMenuSection.innerHTML;
                }

                window.history.pushState({ path: url }, '', url);

                // Update active state of desktop header navigation links dynamically
                const currentUrl = new URL(url, window.location.origin);
                const filterParam = currentUrl.searchParams.get('filter');
                const categoryParam = currentUrl.searchParams.get('category');

                document.querySelectorAll('.desktop-nav-link').forEach(link => {
                    link.classList.remove('active', 'text-white');
                    link.classList.add('text-gray-400');
                    
                    const linkUrl = new URL(link.getAttribute('href'), window.location.origin);
                    const linkFilter = linkUrl.searchParams.get('filter');
                    
                    if (linkUrl.pathname === '/' || linkUrl.pathname === '/index.php' || linkUrl.pathname === '/home') {
                        if (!linkFilter && !filterParam && !categoryParam) {
                            link.classList.add('active', 'text-white');
                            link.classList.remove('text-gray-400');
                        } else if (linkFilter && linkFilter === filterParam) {
                            link.classList.add('active', 'text-white');
                            link.classList.remove('text-gray-400');
                        }
                    } else if (window.location.pathname === linkUrl.pathname) {
                        link.classList.add('active', 'text-white');
                        link.classList.remove('text-gray-400');
                    }
                });

                if (window.lucide) {
                    window.lucide.createIcons();
                }

                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    window.Alpine.initTree(menuSection);
                }

                // Mobile: reveal produk baru via IntersectionObserver
                if (typeof window.revealCards === 'function') {
                    requestAnimationFrame(() => window.revealCards());
                }

                // Animate in new products grid
                const updatedProductsGrid = menuSection.querySelector('.grid, .bg-\\[\\#16181c\\]\\/40');
                if (updatedProductsGrid) {
                    updatedProductsGrid.style.opacity = '0';
                    updatedProductsGrid.style.transform = 'translateY(15px)';
                    updatedProductsGrid.style.transition = 'none';
                    
                    // Force reflow
                    updatedProductsGrid.offsetHeight;
                    
                    updatedProductsGrid.style.transition = 'opacity 0.4s cubic-bezier(0.16, 1, 0.3, 1), transform 0.4s cubic-bezier(0.16, 1, 0.3, 1)';
                    updatedProductsGrid.style.opacity = '1';
                    updatedProductsGrid.style.transform = 'translateY(0)';
                }
            } catch (e) {
                console.error('AJAX load error:', e);
                window.location.href = url;
            }
        };

        // Intercept clicks on links inside header navigation, shop-by-category and menu-section filters
        document.addEventListener('click', (e) => {
            const link = e.target.closest('.desktop-nav-link, #shop-by-category a, #menu-section a');
            if (!link) return;

            const url = new URL(link.href);
            
            // If it is the contact link pointing to #hubungi-kami, scroll to it smoothly
            if (url.hash === '#hubungi-kami') {
                e.preventDefault();
                const targetEl = document.getElementById('hubungi-kami');
                if (targetEl) {
                    targetEl.scrollIntoView({ behavior: 'smooth' });
                }
                history.pushState(null, null, url.pathname + url.search + url.hash);
                return;
            }

            if (url.origin === window.location.origin && (url.pathname === '/' || url.pathname === '/index.php')) {
                e.preventDefault();
                
                // Build the final target URL by preserving relevant query parameters
                const targetUrl = new URL(link.href);
                const currentUrl = new URL(window.location.href);
                
                // If clicking a filter inside #menu-section, preserve current category
                if (link.closest('#menu-section a')) {
                    const currentCategory = currentUrl.searchParams.get('category');
                    if (currentCategory) {
                        targetUrl.searchParams.set('category', currentCategory);
                    }
                }
                
                // If clicking a category link, preserve current filter
                if (link.closest('#shop-by-category a')) {
                    // Check if it is a reset link (does not have a category parameter)
                    if (targetUrl.searchParams.has('category')) {
                        const currentFilter = currentUrl.searchParams.get('filter');
                        if (currentFilter) {
                            targetUrl.searchParams.set('filter', currentFilter);
                        }
                    }
                }

                loadContent(targetUrl.toString());

                // Smooth scroll to menu-section if clicked from header
                if (link.classList.contains('desktop-nav-link')) {
                    const targetSection = document.getElementById('menu-section');
                    if (targetSection) {
                        targetSection.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            }
        });

        // Handle browser back/forward buttons
        window.addEventListener('popstate', () => {
            loadContent(window.location.href);
        });
    });
</script>
@endsection
