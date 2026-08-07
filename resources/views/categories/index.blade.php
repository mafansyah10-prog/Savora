@extends('layouts.app')
@section('title', 'Kategori Menu — ' . \App\Models\Setting::getGlobal()->store_name)

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-14">

    {{-- Page Header --}}
    <div class="text-center mb-10 md:mb-14">
        <span class="text-brand-cyan text-[10px] md:text-xs font-black uppercase tracking-[0.3em] mb-3 block">Navigasi Menu</span>
        <h1 class="text-3xl md:text-5xl lg:text-6xl font-bold text-white font-serif italic mb-3 md:mb-4">Semua Kategori</h1>
        <p class="text-gray-500 max-w-lg mx-auto text-sm md:text-base leading-relaxed">
            Temukan berbagai pilihan hidangan terbaik kami yang dikelompokkan berdasarkan preferensi kuliner Anda.
        </p>
    </div>

    {{-- Category Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5 md:gap-6">
        @foreach($categories as $index => $category)
        <a href="{{ route('home', ['category' => $category->slug]) }}#menu-section"
           class="category-grid-card card-shimmer group relative overflow-hidden bg-gradient-to-br from-[#1a3c37]/60 to-[#16181c]/80 border border-gray-800/80 hover:border-brand-cyan/40 rounded-2xl md:rounded-3xl flex flex-col items-center justify-center p-5 sm:p-7 md:p-8 aspect-[4/5] sm:aspect-square md:aspect-[4/5] transition-all duration-500 transform hover:-translate-y-1 md:hover:-translate-y-3 hover:shadow-[0_25px_60px_rgba(78,205,196,0.1)] shadow-xl animate-fade-up">

            {{-- Background glow blob --}}
            <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full blur-3xl transition-all duration-700
                        {{ $index % 2 === 0 ? 'bg-gold-500/8 group-hover:bg-gold-500/15' : 'bg-brand-cyan/8 group-hover:bg-brand-cyan/15' }}">
            </div>
            <div class="absolute -left-8 -bottom-8 w-24 h-24 rounded-full blur-2xl transition-all duration-700
                        {{ $index % 2 === 0 ? 'bg-brand-cyan/5 group-hover:bg-brand-cyan/10' : 'bg-gold-500/5 group-hover:bg-gold-500/10' }}">
            </div>

            {{-- Icon --}}
            <div class="mb-4 md:mb-5 relative transition-transform duration-500 group-hover:scale-110 group-hover:-translate-y-1">
                <div class="w-14 h-14 md:w-16 md:h-16 rounded-2xl md:rounded-3xl flex items-center justify-center border transition-all duration-500
                            {{ $index % 2 === 0
                                ? 'bg-gold-500/10 border-gold-500/30 text-gold-500 group-hover:bg-gold-500 group-hover:border-gold-400 group-hover:text-black'
                                : 'bg-brand-cyan/10 border-brand-cyan/30 text-brand-cyan group-hover:bg-brand-cyan group-hover:border-brand-cyan group-hover:text-black' }}">
                    <i data-lucide="{{ $category->icon ?? 'utensils' }}" class="w-7 h-7 md:w-8 md:h-8 stroke-[2]"></i>
                </div>
            </div>

            {{-- Text --}}
            <div class="text-center relative z-10">
                <h3 class="text-white font-bold text-sm md:text-base lg:text-lg mb-1 group-hover:text-brand-cyan transition-colors duration-300 line-clamp-1">
                    {{ $category->name }}
                </h3>
                <p class="text-[9px] md:text-[10px] text-gray-500 uppercase tracking-widest font-black">
                    {{ $category->products_count }} Menu
                </p>
            </div>

            {{-- Arrow indicator (desktop) --}}
            <div class="hidden md:flex absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-400">
                <div class="w-7 h-7 bg-brand-cyan rounded-full flex items-center justify-center">
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-black stroke-[3]"></i>
                </div>
            </div>

            {{-- Product count pill (mobile) --}}
            <div class="md:hidden absolute top-3 right-3 px-2 py-0.5 bg-black/40 border border-white/10 rounded-full">
                <span class="text-[8px] text-gray-400 font-black">{{ $category->products_count }}</span>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Browse all CTA --}}
    <div class="mt-10 md:mt-14 text-center">
        <a href="{{ route('home') }}#menu-section"
           class="inline-flex items-center gap-2.5 px-8 py-3.5 bg-gradient-to-r from-orange-400 to-amber-500 hover:from-orange-500 hover:to-amber-600 text-black font-black text-xs uppercase tracking-widest rounded-full transition-all duration-300 hover:scale-105 hover:shadow-[0_0_30px_rgba(249,115,22,0.35)]">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            Lihat Semua Menu
        </a>
    </div>
</div>
@endsection
