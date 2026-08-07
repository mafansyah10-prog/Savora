@extends('layouts.app')
@section('title', $product->name . ' — ' . \App\Models\Setting::getGlobal()->store_name)

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-24 md:py-14">

    {{-- Breadcrumb --}}
    <nav class="mb-6 md:mb-8 flex items-center gap-2 text-[10px] md:text-xs text-gray-600 font-black uppercase tracking-widest">
        <a href="{{ route('home') }}" class="hover:text-white transition">Beranda</a>
        <i data-lucide="chevron-right" class="w-3 h-3 text-gray-700"></i>
        <a href="{{ route('categories.index') }}" class="hover:text-white transition">Kategori</a>
        <i data-lucide="chevron-right" class="w-3 h-3 text-gray-700"></i>
        <span class="text-gray-400 truncate max-w-[150px] md:max-w-xs">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 lg:gap-16">

        {{-- Product Image --}}
        <div class="group">
            <div class="relative bg-[#16181d] border border-gray-800 rounded-2xl md:rounded-[2rem] overflow-hidden aspect-[16/10] md:aspect-square max-h-[320px] md:max-h-none shadow-2xl">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}"
                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                         alt="{{ $product->name }}">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-700 gap-3">
                        <i data-lucide="utensils" class="w-16 h-16 opacity-20"></i>
                        <span class="text-xs font-black uppercase tracking-widest opacity-40">{{ $product->category->name }}</span>
                    </div>
                @endif

                {{-- Overlay gradient --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>

                {{-- Category badge --}}
                <div class="absolute top-4 left-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-black/60 backdrop-blur-sm border border-white/10 rounded-full text-[9px] md:text-[10px] font-black uppercase tracking-widest text-white">
                        <i data-lucide="{{ $product->category->icon ?? 'utensils' }}" class="w-3 h-3 text-brand-cyan"></i>
                        {{ $product->category->name }}
                    </span>
                </div>

                {{-- Stock badge --}}
                @if($product->stock !== null)
                    <div class="absolute top-4 right-4">
                        @if($product->stock <= 0)
                            <span class="px-3 py-1.5 bg-red-600 text-white text-[9px] font-black uppercase tracking-widest rounded-full shadow">Stok Habis</span>
                        @elseif($product->stock <= 5)
                            <span class="px-3 py-1.5 bg-orange-500 text-black text-[9px] font-black uppercase tracking-widest rounded-full shadow">Sisa {{ $product->stock }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Product Info --}}
        <div class="flex flex-col justify-center">

            {{-- Name & Price --}}
            <span class="text-[10px] md:text-xs font-black text-brand-cyan uppercase tracking-[0.3em] mb-2 md:mb-3">{{ $product->category->name }}</span>
            <h1 class="text-2xl md:text-4xl lg:text-5xl font-bold text-white mb-3 md:mb-4 tracking-tight font-serif leading-tight">{{ $product->name }}</h1>

            <div class="flex items-baseline gap-3 mb-5 md:mb-6">
                @if($product->hasDiscount())
                    <span class="text-2xl md:text-3xl font-black text-gold-500" id="dynamic-product-price" data-base-price="{{ $product->discount_price }}">Rp {{ number_format($product->discount_price, 0, ',', '.') }}</span>
                    <span class="text-sm text-gray-500 line-through">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                @else
                    <span class="text-2xl md:text-3xl font-black text-gold-500" id="dynamic-product-price" data-base-price="{{ $product->price }}">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                @endif
                <span class="text-xs text-gray-600 font-medium">per porsi</span>
            </div>

            {{-- Stock indicator --}}
            @if($product->stock !== null)
                <div class="mb-5 md:mb-6">
                    @if($product->stock <= 0)
                        <span class="inline-flex items-center gap-2 px-3 py-2 bg-red-600/15 border border-red-500/30 text-red-400 text-[10px] font-black uppercase tracking-widest rounded-full">
                            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span> Stok Habis
                        </span>
                    @elseif($product->stock <= 5)
                        <span class="inline-flex items-center gap-2 px-3 py-2 bg-orange-500/15 border border-orange-400/30 text-orange-400 text-[10px] font-black uppercase tracking-widest rounded-full">
                            <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span> Sisa {{ $product->stock }} Tersedia
                        </span>
                    @else
                        <span class="inline-flex items-center gap-2 px-3 py-2 bg-brand-cyan/10 border border-brand-cyan/30 text-brand-cyan text-[10px] font-black uppercase tracking-widest rounded-full">
                            <span class="w-2 h-2 rounded-full bg-brand-cyan"></span> Stok: {{ $product->stock }} Tersedia
                        </span>
                    @endif
                </div>
            @endif

            {{-- Divider --}}
            <div class="h-px bg-gradient-to-r from-gray-800 via-gray-700 to-transparent mb-5 md:mb-6"></div>

            {{-- Description --}}
            <p class="text-gray-400 text-sm md:text-base leading-relaxed mb-8 md:mb-10">
                {{ $product->description ?: 'Nikmati kelezatan menu pilihan dari Savora yang dibuat dengan bahan-bahan premium berkualitas tinggi untuk memberikan pengalaman kuliner terbaik bagi Anda.' }}
            </p>

            {{-- CTA --}}
            @if($product->stock !== null && $product->stock <= 0)
                <button disabled class="w-full bg-gray-800/60 border border-gray-700 text-gray-500 font-black text-sm py-4 rounded-2xl uppercase tracking-widest cursor-not-allowed flex items-center justify-center gap-2 mb-8">
                    <i data-lucide="ban" class="w-5 h-5"></i>
                    Stok Habis
                </button>
            @else
                <form action="{{ route('cart.add') }}" method="POST" class="mb-8" id="add-to-cart-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    {{-- ─── Spiciness Levels ─── --}}
                    @if($product->enable_spiciness && !empty($product->spiciness_levels))
                        <div class="mb-6">
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Tingkat Kepedasan</label>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach($product->spiciness_levels as $index => $level)
                                    @php
                                        $levelName = is_array($level) ? ($level['name'] ?? '') : $level;
                                        $levelPrice = is_array($level) ? ($level['price'] ?? 0) : 0;
                                    @endphp
                                    <label class="relative flex flex-col items-center justify-center p-3 border border-gray-800 rounded-xl bg-black/20 text-gray-400 cursor-pointer transition hover:border-brand-cyan/60 hover:text-white has-[:checked]:border-brand-cyan has-[:checked]:bg-brand-cyan/10 has-[:checked]:text-brand-cyan text-xs font-bold text-center">
                                        <input type="radio" name="spiciness_level" value="{{ $levelName }}" data-price="{{ $levelPrice }}" class="sr-only spiciness-option-input" {{ $index === 0 ? 'checked' : '' }}>
                                        <span>{{ $levelName }}</span>
                                        @if($levelPrice > 0)
                                            <span class="text-[10px] text-gray-400 mt-1 font-black">+Rp {{ number_format($levelPrice, 0, ',', '.') }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ─── Varian Saos ─── --}}
                    @if($product->enable_sauces && !empty($product->sauces))
                        @php
                            $activeSauces = collect($product->sauces)->filter(fn($s) => $s['is_active'] ?? true);
                        @endphp
                        @if($activeSauces->isNotEmpty())
                            <div class="mb-6">
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Pilihan Saos</label>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach($activeSauces as $index => $sauce)
                                        <label class="relative flex flex-col items-center justify-center p-3 border border-gray-800 rounded-xl bg-black/20 text-gray-400 cursor-pointer transition hover:border-brand-cyan/60 hover:text-white has-[:checked]:border-brand-cyan has-[:checked]:bg-brand-cyan/10 has-[:checked]:text-brand-cyan text-xs font-bold text-center">
                                            <input type="radio" name="sauce" value="{{ $sauce['name'] }}" data-price="{{ $sauce['price'] ?? 0 }}" class="sr-only sauce-option-input" {{ $index === 0 ? 'checked' : '' }}>
                                            <span>{{ $sauce['name'] }}</span>
                                            @if(($sauce['price'] ?? 0) > 0)
                                                <span class="text-[10px] text-gray-400 mt-1 font-black">+Rp {{ number_format($sauce['price'], 0, ',', '.') }}</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- ─── Toppings ─── --}}
                    @if($product->enable_toppings && !empty($product->toppings))
                        @php
                            $activeToppings = collect($product->toppings)->filter(fn($t) => $t['is_active'] ?? true);
                        @endphp
                        @if($activeToppings->isNotEmpty())
                            <div class="mb-6">
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Pilihan Topping</label>
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach($activeToppings as $topping)
                                        <label class="relative flex items-center justify-between p-3.5 border border-gray-800 rounded-xl bg-black/20 text-gray-400 cursor-pointer transition hover:border-brand-cyan/60 hover:text-white has-[:checked]:border-brand-cyan has-[:checked]:bg-brand-cyan/10 has-[:checked]:text-brand-cyan text-xs font-bold">
                                            <span class="flex items-center gap-2">
                                                <input type="checkbox" name="toppings[]" value="{{ $topping['name'] }}" data-price="{{ $topping['price'] }}" class="rounded border-gray-800 text-brand-cyan focus:ring-brand-cyan/20 bg-black/30 topping-option-input">
                                                <span>{{ $topping['name'] }}</span>
                                            </span>
                                            <span class="text-[10px] text-gold-550 font-black">+Rp {{ number_format($topping['price'], 0, ',', '.') }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- ─── Additionals ─── --}}
                    @if($product->enable_additionals && !empty($product->additionals))
                        @php
                            $activeAdditionals = collect($product->additionals)->filter(fn($a) => $a['is_active'] ?? true);
                        @endphp
                        @if($activeAdditionals->isNotEmpty())
                            <div class="mb-6">
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Menu Tambahan</label>
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach($activeAdditionals as $additional)
                                        <label class="relative flex items-center justify-between p-3.5 border border-gray-800 rounded-xl bg-black/20 text-gray-400 cursor-pointer transition hover:border-brand-cyan/60 hover:text-white has-[:checked]:border-brand-cyan has-[:checked]:bg-brand-cyan/10 has-[:checked]:text-brand-cyan text-xs font-bold">
                                            <span class="flex items-center gap-2">
                                                <input type="checkbox" name="additionals[]" value="{{ $additional['name'] }}" data-price="{{ $additional['price'] }}" class="rounded border-gray-800 text-brand-cyan focus:ring-brand-cyan/20 bg-black/30 additional-option-input">
                                                <span>{{ $additional['name'] }}</span>
                                            </span>
                                            <span class="text-[10px] text-gold-550 font-black">+Rp {{ number_format($additional['price'], 0, ',', '.') }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif

                    <button type="submit"
                        class="w-full bg-gradient-to-r from-brand-cyan to-teal-400 hover:from-teal-400 hover:to-brand-cyan text-black font-black text-sm md:text-base py-4 md:py-5 rounded-2xl shadow-[0_10px_30px_rgba(78,205,196,0.3)] hover:shadow-[0_15px_40px_rgba(78,205,196,0.45)] transition-all duration-300 transform hover:-translate-y-1 active:scale-95 uppercase tracking-widest flex items-center justify-center gap-2.5">
                        <i data-lucide="shopping-cart" class="w-5 h-5 stroke-[2.5]"></i>
                        Tambah ke Keranjang
                    </button>
                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const basePriceElement = document.getElementById('dynamic-product-price');
                        if (!basePriceElement) return;

                        const basePrice = parseFloat(basePriceElement.getAttribute('data-base-price') || 0);

                        function formatPrice(number) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
                        }

                        function updateDynamicPrice() {
                            let totalExtra = 0;

                            // Spiciness price
                            const selectedSpiciness = document.querySelector('.spiciness-option-input:checked');
                            if (selectedSpiciness) {
                                totalExtra += parseFloat(selectedSpiciness.getAttribute('data-price') || 0);
                            }

                            // Sauce price
                            const selectedSauce = document.querySelector('.sauce-option-input:checked');
                            if (selectedSauce) {
                                totalExtra += parseFloat(selectedSauce.getAttribute('data-price') || 0);
                            }

                            // Toppings price
                            document.querySelectorAll('.topping-option-input:checked').forEach(function(el) {
                                totalExtra += parseFloat(el.getAttribute('data-price') || 0);
                            });

                            // Additionals price
                            document.querySelectorAll('.additional-option-input:checked').forEach(function(el) {
                                totalExtra += parseFloat(el.getAttribute('data-price') || 0);
                            });

                            basePriceElement.textContent = formatPrice(basePrice + totalExtra);
                        }

                        // Listeners
                        document.querySelectorAll('.spiciness-option-input, .sauce-option-input, .topping-option-input, .additional-option-input').forEach(function(el) {
                            el.addEventListener('change', updateDynamicPrice);
                        });

                        updateDynamicPrice();
                    });
                </script>
            @endif

            {{-- Feature badges --}}
            <div class="grid grid-cols-2 gap-3 md:gap-4 border-t border-gray-800/60 pt-6 md:pt-8">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 md:w-10 md:h-10 bg-gold-500/10 border border-gold-500/20 rounded-xl flex items-center justify-center text-gold-500 flex-shrink-0">
                        <i data-lucide="sparkles" class="w-4 h-4 md:w-5 md:h-5"></i>
                    </div>
                    <div>
                        <p class="text-[10px] md:text-xs font-bold text-white">Premium Quality</p>
                        <p class="text-[9px] md:text-[10px] text-gray-600">Bahan pilihan terbaik</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 md:w-10 md:h-10 bg-brand-cyan/10 border border-brand-cyan/20 rounded-xl flex items-center justify-center text-brand-cyan flex-shrink-0">
                        <i data-lucide="zap" class="w-4 h-4 md:w-5 md:h-5"></i>
                    </div>
                    <div>
                        <p class="text-[10px] md:text-xs font-bold text-white">Fast Delivery</p>
                        <p class="text-[9px] md:text-[10px] text-gray-600">Dikirim dalam 30 menit</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 md:w-10 md:h-10 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center justify-center text-emerald-400 flex-shrink-0">
                        <i data-lucide="leaf" class="w-4 h-4 md:w-5 md:h-5"></i>
                    </div>
                    <div>
                        <p class="text-[10px] md:text-xs font-bold text-white">Bahan Alami</p>
                        <p class="text-[9px] md:text-[10px] text-gray-600">Tanpa pengawet buatan</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 md:w-10 md:h-10 bg-orange-400/10 border border-orange-400/20 rounded-xl flex items-center justify-center text-orange-400 flex-shrink-0">
                        <i data-lucide="chef-hat" class="w-4 h-4 md:w-5 md:h-5"></i>
                    </div>
                    <div>
                        <p class="text-[10px] md:text-xs font-bold text-white">Selalu Segar</p>
                        <p class="text-[9px] md:text-[10px] text-gray-600">Dimasak setiap hari</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
