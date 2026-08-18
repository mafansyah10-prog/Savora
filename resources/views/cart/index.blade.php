@extends('layouts.app')
@section('title', 'Keranjang Belanja')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-12">

    {{-- Page header --}}
    <div class="mb-6 md:mb-8">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 md:w-12 md:h-12 bg-orange-400/10 border border-orange-400/20 rounded-2xl flex items-center justify-center text-orange-400 flex-shrink-0">
                <i data-lucide="shopping-cart" class="w-5 h-5 md:w-6 md:h-6"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-3xl font-bold text-white tracking-tight font-serif">Keranjang Belanja</h1>
                <p class="text-xs md:text-sm text-gray-400 mt-0.5">{{ collect($cart)->sum('quantity') }} item dalam keranjang Anda</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-brand-cyan/15 border border-brand-cyan/30 text-brand-cyan px-4 py-3 rounded-xl mb-5 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">

        {{-- ─── Cart Items (Left) ─────────────────────────────────────── --}}
        <div class="w-full lg:w-[60%] order-1 lg:order-1">
            <div class="bg-[#16181d] border border-gray-800 rounded-2xl md:rounded-3xl shadow-2xl overflow-hidden">

                @if(count($cart) > 0)
                    {{-- Item list --}}
                    <div class="divide-y divide-gray-800/50">
                        @foreach($cart as $id => $details)
                            @php
                                $product = \App\Models\Product::find($details['product_id'] ?? (is_numeric($id) ? (int)$id : null));
                            @endphp
                        <div class="flex gap-3 md:gap-5 p-4 md:p-6 relative group/item hover:bg-white/[0.015] transition-colors" data-item-id="{{ $id }}">

                            {{-- Product image --}}
                            <div class="w-20 h-20 md:w-24 md:h-24 bg-gray-800 rounded-xl md:rounded-2xl overflow-hidden flex-shrink-0 border border-gray-700/50">
                                @if($details['image'])
                                    <img src="{{ asset('storage/' . $details['image']) }}"
                                         class="w-full h-full object-cover transition-transform duration-500 group/item:hover:scale-105"
                                         alt="{{ $details['name'] }}">
                                @else
                                    <div class="flex flex-col items-center justify-center h-full text-gray-600 text-center">
                                        <i data-lucide="utensils" class="w-6 h-6 mb-1 opacity-40"></i>
                                        <span class="text-[9px] uppercase tracking-wider">{{ $details['category'] }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Product info --}}
                            <div class="flex-grow min-w-0 flex flex-col justify-between py-0.5">
                                <div class="pr-8">
                                    <span class="text-[9px] md:text-[10px] font-black text-brand-cyan/70 uppercase tracking-widest">{{ $details['category'] }}</span>
                                    <h3 class="text-sm md:text-base font-bold text-white leading-tight mt-0.5 line-clamp-2">{{ $details['name'] }}</h3>
                                    
                                    @php
                                        $itemExtraPrice = 0;
                                    @endphp
                                    @if(!empty($details['options']))
                                        @php
                                            $opts = [];
                                            if(!empty($details['options']['spiciness_level'])) {
                                                $spicinessName = $details['options']['spiciness_level'];
                                                $spicinessPrice = 0;
                                                if($product && !empty($product->spiciness_levels)) {
                                                    $spObj = collect($product->spiciness_levels)->first(function($level) use ($spicinessName) {
                                                        $levelName = is_array($level) ? ($level['name'] ?? '') : $level;
                                                        return $levelName === $spicinessName;
                                                    });
                                                    if ($spObj && is_array($spObj)) {
                                                        $spicinessPrice = $spObj['price'] ?? 0;
                                                    }
                                                }
                                                $itemExtraPrice += $spicinessPrice;
                                                $formattedSpiciness = (stripos($spicinessName, 'level') === false && is_numeric($spicinessName)) ? 'Level ' . $spicinessName : $spicinessName;
                                                $opts[] = 'Pedas: ' . $formattedSpiciness . ($spicinessPrice > 0 ? ' (+Rp ' . number_format($spicinessPrice, 0, ',', '.') . ')' : '');
                                            }
                                            if(!empty($details['options']['sauce'])) {
                                                $sauceName = $details['options']['sauce'];
                                                $saucePrice = 0;
                                                if($product && !empty($product->sauces)) {
                                                    $sObj = collect($product->sauces)->firstWhere('name', $sauceName);
                                                    $saucePrice = $sObj['price'] ?? 0;
                                                }
                                                $itemExtraPrice += $saucePrice;
                                                $opts[] = 'Saus: ' . $sauceName . ($saucePrice > 0 ? ' (+Rp ' . number_format($saucePrice, 0, ',', '.') . ')' : '');
                                            }
                                            if(!empty($details['options']['toppings'])) {
                                                $toppingsWithPrices = [];
                                                foreach($details['options']['toppings'] as $topName) {
                                                    $topPrice = 0;
                                                    if($product && !empty($product->toppings)) {
                                                        $tObj = collect($product->toppings)->firstWhere('name', $topName);
                                                        $topPrice = $tObj['price'] ?? 0;
                                                    }
                                                    $itemExtraPrice += $topPrice;
                                                    $toppingsWithPrices[] = $topName . ($topPrice > 0 ? ' (+Rp ' . number_format($topPrice, 0, ',', '.') . ')' : '');
                                                }
                                                $opts[] = 'Topping: ' . implode(', ', $toppingsWithPrices);
                                            }
                                            if(!empty($details['options']['additionals'])) {
                                                $additionalsWithPrices = [];
                                                foreach($details['options']['additionals'] as $addName) {
                                                    $addPrice = 0;
                                                    if($product && !empty($product->additionals)) {
                                                        $aObj = collect($product->additionals)->firstWhere('name', $addName);
                                                        $addPrice = $aObj['price'] ?? 0;
                                                    }
                                                    $itemExtraPrice += $addPrice;
                                                    $additionalsWithPrices[] = $addName . ($addPrice > 0 ? ' (+Rp ' . number_format($addPrice, 0, ',', '.') . ')' : '');
                                                }
                                                $opts[] = 'Tambahan: ' . implode(', ', $additionalsWithPrices);
                                            }
                                        @endphp
                                        @if(count($opts) > 0)
                                            <div class="mt-1.5">
                                                <p class="text-[10px] text-gray-300 italic font-medium leading-normal bg-black/10 border border-gray-800/40 rounded-lg px-2 py-1 inline-block">
                                                    {{ implode(' | ', $opts) }}
                                                </p>
                                            </div>
                                        @endif
                                    @endif
                                </div>

                                <div class="flex items-baseline justify-between mt-3 flex-wrap gap-2">
                                     <div class="flex items-baseline gap-2 flex-wrap">
                                         <span class="text-sm md:text-base font-black text-gold-500">Rp {{ number_format($product ? $product->selling_price : $details['price'], 0, ',', '.') }}</span>
                                         @if($product && $product->hasDiscount())
                                             <span class="text-[10px] text-gray-500 line-through">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                             <span class="px-1.5 py-0.5 bg-red-500/10 text-red-400 border border-red-500/20 text-[8px] font-black uppercase rounded">Promo</span>
                                         @endif
                                         @if($itemExtraPrice > 0)
                                             <span class="text-[10px] text-gray-500 font-bold bg-white/5 border border-white/10 px-2 py-0.5 rounded-lg">
                                                 + Opsi: Rp {{ number_format($itemExtraPrice, 0, ',', '.') }}
                                             </span>
                                         @endif
                                     </div>

                                    {{-- Quantity controls (AJAX) --}}
                                    <div class="flex items-center gap-0.5 bg-black/30 rounded-xl p-1 border border-gray-800/80">
                                        <form action="{{ route('cart.update') }}" method="POST" class="inline cart-update-form">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $id }}">
                                            <input type="hidden" name="quantity" class="qty-input-field" value="{{ $details['quantity'] - 1 }}">
                                            <button type="submit"
                                                class="minus-btn w-9 h-9 md:w-8 md:h-8 flex items-center justify-center text-gray-500 hover:text-white hover:bg-gray-700 rounded-lg transition font-black text-base leading-none"
                                                {{ $details['quantity'] <= 1 ? 'disabled' : '' }}>−</button>
                                        </form>

                                        <span class="text-xs md:text-sm w-8 text-center font-black text-white qty-display">{{ $details['quantity'] }}</span>

                                        <form action="{{ route('cart.update') }}" method="POST" class="inline cart-update-form">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $id }}">
                                            <input type="hidden" name="quantity" class="qty-input-field" value="{{ $details['quantity'] + 1 }}">
                                            <button type="submit"
                                                class="plus-btn w-9 h-9 md:w-8 md:h-8 flex items-center justify-center text-gold-500 hover:text-black hover:bg-gold-500 rounded-lg transition font-black text-base leading-none">+</button>
                                        </form>
                                    </div>
                                </div>

                                {{-- Subtotal per item --}}
                                <p class="text-[9px] md:text-[10px] text-gray-600 mt-1">
                                    Subtotal: <span class="text-gray-400 font-semibold item-subtotal-display" data-unit-price="{{ ($product ? $product->selling_price : $details['price']) + $itemExtraPrice }}">Rp {{ number_format((($product ? $product->selling_price : $details['price']) + $itemExtraPrice) * $details['quantity'], 0, ',', '.') }}</span>
                                </p>
                            </div>

                            {{-- Remove button --}}
                            <form action="{{ route('cart.remove') }}" method="POST" class="absolute top-4 right-4 md:top-5 md:right-5">
                                @csrf
                                <input type="hidden" name="id" value="{{ $id }}">
                                <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center text-gray-700 hover:text-red-400 transition-colors bg-black/30 hover:bg-red-500/10 rounded-xl border border-gray-800 hover:border-red-500/30"
                                    title="Hapus item">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>

                    {{-- Footer --}}
                    <div class="border-t border-gray-800/60 px-4 md:px-6 py-3 md:py-4 flex justify-between items-center bg-black/10">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-[10px] md:text-xs text-gray-500 hover:text-gold-400 transition font-black uppercase tracking-widest">
                            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                            Tambah Item
                        </a>
                        <span class="text-[10px] text-gray-600 font-medium">{{ count($cart) }} produk dipilih</span>
                    </div>

                @else
                    {{-- Empty state --}}
                    <div class="text-center py-16 md:py-24 px-6">
                        <div class="w-20 h-20 bg-gray-800/60 border border-gray-700 rounded-3xl flex items-center justify-center mx-auto mb-5">
                            <i data-lucide="shopping-cart" class="w-9 h-9 text-gray-600"></i>
                        </div>
                        <h3 class="text-base md:text-lg font-bold text-white mb-2">Keranjang Kosong</h3>
                        <p class="text-gray-500 text-sm mb-8 max-w-xs mx-auto leading-relaxed">Belum ada item yang dipilih. Yuk jelajahi menu lezat kami!</p>
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-8 py-3 bg-brand-cyan text-black font-black rounded-full text-xs uppercase tracking-widest transition hover:bg-teal-400 hover:scale-105 hover:shadow-[0_0_20px_rgba(78,205,196,0.35)]">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            Mulai Belanja
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- ─── Order Summary (Right) ─────────────────────────────────── --}}
        <div class="w-full lg:w-[40%] order-2 lg:order-2">
            <div class="bg-[#16181d] border border-gray-800 rounded-2xl md:rounded-3xl shadow-2xl overflow-hidden lg:sticky lg:top-24">

                {{-- Summary totals --}}
                <div class="p-4 md:p-6 border-b border-gray-800/60">
                    <h2 class="text-base md:text-lg font-bold text-white mb-5 tracking-tight">Ringkasan Pesanan</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-400">
                            <span>Total Item</span>
                            <span class="text-white font-semibold" id="summary-total-qty">{{ collect($cart)->sum('quantity') }} Item</span>
                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>Subtotal</span>
                            <span class="text-white font-semibold" id="summary-subtotal">Rp {{ number_format($originalSubtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-gray-400 {{ $productDiscount > 0 ? '' : 'hidden' }}" id="summary-product-discount-row">
                            <span>Potongan Harga Produk</span>
                            <span class="text-emerald-400 font-semibold" id="summary-product-discount">-Rp {{ number_format($productDiscount, 0, ',', '.') }}</span>
                        </div>
                        {{-- Dropdown Pilih Wilayah --}}
                        <div class="space-y-1.5 pt-1">
                            <label class="block text-[10px] text-gray-500 uppercase tracking-widest">Wilayah Pengiriman</label>
                            <select id="shipping-zone-select" class="w-full bg-black/30 border border-gray-800 hover:border-gray-700 focus:border-gold-500 focus:ring-1 focus:ring-gold-500/20 rounded-xl py-2 px-3 text-xs text-white outline-none transition">
                                <option value="" class="bg-[#16181d] text-gray-500">Pilih Wilayah...</option>
                                @foreach($shippingZones as $zone)
                                    <option value="{{ $zone->id }}" class="bg-[#16181d] text-white" {{ $selectedZone && $selectedZone->id === $zone->id ? 'selected' : '' }}>
                                        {{ $zone->name }} (Rp {{ number_format($zone->cost, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>Ongkos Kirim</span>
                            <span class="{{ $shippingCost > 0 ? 'text-white font-semibold' : 'text-emerald-400 font-bold text-xs uppercase tracking-wider' }}" id="summary-shipping">
                                {{ $shippingCost > 0 ? 'Rp ' . number_format($shippingCost, 0, ',', '.') : 'Pilih Wilayah' }}
                            </span>
                        </div>
                        <div class="flex justify-between text-gray-400 {{ $voucher ? '' : 'hidden' }}" id="summary-discount-row">
                            <span>Potongan Voucher (<span id="summary-voucher-code" class="uppercase">{{ $voucher?->code }}</span>)</span>
                            <span class="text-red-400 font-semibold" id="summary-discount">-Rp {{ number_format($discount, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-gray-800/60 pt-3 flex justify-between items-center">
                            <span class="font-bold text-white">Total Bayar</span>
                            <span class="text-xl md:text-2xl font-black text-gold-500" id="summary-total-pay">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Voucher section --}}
                <div class="p-4 md:p-6 border-b border-gray-800/60" id="voucher-section">
                    <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-[0.25em] mb-3">Voucher Belanja</h3>
                    
                    {{-- Row: Voucher Selector --}}
                    <div id="voucher-select-trigger" 
                         onclick="openVoucherModal()"
                         class="flex items-center justify-between p-3.5 bg-black/35 border border-gray-800 hover:border-gray-700 rounded-xl cursor-pointer transition group {{ $voucher ? 'hidden' : '' }}">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="ticket" class="w-5 h-5 text-gold-500"></i>
                            <div>
                                <p class="text-xs font-bold text-white">Voucher</p>
                                <p class="text-[10px] text-gray-400">Gunakan voucher untuk hemat belanjaan</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-[10px] font-black uppercase tracking-wider text-gold-500 group-hover:text-amber-400 transition-colors">Pilih</span>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-gray-500 group-hover:text-white transition-colors"></i>
                        </div>
                    </div>

                    {{-- Hidden Voucher form elements for AJAX --}}
                    <div class="hidden">
                        <input type="text" id="voucher-code-input">
                        <button type="button" id="apply-voucher-btn"></button>
                    </div>

                    {{-- Applied voucher display --}}
                    <div id="voucher-applied-container" class="flex justify-between items-center bg-gold-500/10 border border-gold-500/20 rounded-xl p-3.5 {{ $voucher ? '' : 'hidden' }}">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-gold-500/20 flex items-center justify-center text-gold-500">
                                <i data-lucide="ticket" class="w-4.5 h-4.5"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-white" id="applied-voucher-name">{{ $voucher?->name }}</p>
                                <p class="text-[9px] text-gold-500 font-mono uppercase tracking-wider" id="applied-voucher-code">Kode: {{ $voucher?->code }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5" id="applied-voucher-desc">Potongan Rp {{ number_format($discount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="openVoucherModal()" class="text-[10px] text-brand-cyan hover:underline uppercase tracking-wider font-black px-2 py-1">Ubah</button>
                            <button type="button" id="remove-voucher-btn" class="text-gray-500 hover:text-red-400 transition p-1">
                                <i data-lucide="x-circle" class="w-4.5 h-4.5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <style>
                    /* Voucher Selection Modal Custom CSS Transitions */
                    #voucher-modal .modal-backdrop {
                        transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    }
                    #voucher-modal .modal-body-content {
                        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    }
                    #voucher-modal.modal-visible .modal-backdrop {
                        opacity: 1 !important;
                    }
                    #voucher-modal.modal-visible .modal-body-content {
                        transform: translateY(0) scale(1) !important;
                        opacity: 1 !important;
                    }
                    /* Custom Scrollbar for Modal content */
                    #voucher-modal .custom-scrollbar::-webkit-scrollbar {
                        width: 4px;
                    }
                    #voucher-modal .custom-scrollbar::-webkit-scrollbar-track {
                        background: transparent;
                    }
                    #voucher-modal .custom-scrollbar::-webkit-scrollbar-thumb {
                        background: rgba(226, 200, 110, 0.2);
                        border-radius: 99px;
                    }
                    #voucher-modal .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                        background: rgba(226, 200, 110, 0.4);
                    }
                </style>

                {{-- Checkout form --}}
                <form action="{{ route('cart.checkout') }}" method="POST">
                    @csrf

                    {{-- Delivery & Payment info --}}
                    <div class="p-4 md:p-6 space-y-5">
                        {{-- Delivery info --}}
                        <div>
                            <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-[0.25em] mb-3.5">Informasi Pengiriman</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[10px] text-gray-500 uppercase tracking-widest mb-1.5">Nama Lengkap</label>
                                    <input type="text" name="customer_name" required
                                        class="w-full bg-black/30 border border-gray-800 hover:border-gray-700 focus:border-gold-500 focus:ring-1 focus:ring-gold-500/20 rounded-xl py-2.5 px-3.5 text-sm text-white transition placeholder-gray-600 outline-none"
                                        placeholder="Nama Anda">
                                </div>
                                <div>
                                    <label class="block text-[10px] text-gray-500 uppercase tracking-widest mb-1.5">Nomor Telepon</label>
                                    <input type="tel" name="customer_phone" required
                                        inputmode="numeric" pattern="[0-9]*"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        class="w-full bg-black/30 border border-gray-800 hover:border-gray-700 focus:border-gold-500 focus:ring-1 focus:ring-gold-500/20 rounded-xl py-2.5 px-3.5 text-sm text-white transition placeholder-gray-600 outline-none"
                                        placeholder="08xxxxxxxxxx">
                                </div>
                                <div>
                                    <label class="block text-[10px] text-gray-500 uppercase tracking-widest mb-1.5">Alamat Lengkap</label>
                                    <textarea name="shipping_address" required rows="3"
                                        class="w-full bg-black/30 border border-gray-800 hover:border-gray-700 focus:border-gold-500 focus:ring-1 focus:ring-gold-500/20 rounded-xl py-2.5 px-3.5 text-sm text-white transition placeholder-gray-600 outline-none resize-none"
                                        placeholder="Jl. ..."></textarea>
                                </div>
                                <div>
                                    <label class="block text-[10px] text-gray-500 uppercase tracking-widest mb-1.5">Catatan <span class="text-gray-700 normal-case">(opsional)</span></label>
                                    <textarea name="notes" rows="2"
                                        class="w-full bg-black/30 border border-gray-800 hover:border-gray-700 focus:border-gold-500 focus:ring-1 focus:ring-gold-500/20 rounded-xl py-2.5 px-3.5 text-sm text-white transition placeholder-gray-600 outline-none resize-none"
                                        placeholder="Contoh: Topping extra keju, jangan pakai kacang, dll."></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Payment method --}}
                        <div x-data="{ method: 'bca' }">
                            <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-[0.25em] mb-3.5">Metode Pembayaran</h3>
                            <input type="hidden" name="payment_method" :value="method">
                            <div class="grid grid-cols-1 gap-2.5">
                                <button type="button" @click="method = 'bca'"
                                    class="border border-brand-cyan/60 bg-brand-cyan/10 text-brand-cyan shadow-[0_0_15px_rgba(78,205,196,0.15)] rounded-xl py-3.5 px-4 text-xs font-black uppercase tracking-wider transition-all duration-200 flex items-center justify-between cursor-default">
                                    <div class="flex items-center gap-2.5">
                                        <i data-lucide="landmark" class="w-5 h-5 text-brand-cyan"></i>
                                        <span>Transfer / Virtual Account BCA</span>
                                    </div>
                                    <span class="text-[10px] bg-brand-cyan/20 px-2.5 py-1 rounded-full text-brand-cyan font-black tracking-widest">AKTIF</span>
                                </button>
                            </div>
                            @if(\App\Models\Setting::getGlobal()->pakasir_is_active && !\App\Models\Setting::getGlobal()->manual_payment_is_active)
                            <div class="mt-4 bg-brand-cyan/5 border border-brand-cyan/20 rounded-xl p-4 space-y-3">
                                <div class="flex items-center gap-2 text-brand-cyan">
                                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                                    <span class="text-[10px] font-black uppercase tracking-wider">Pembayaran Otomatis Pakasir</span>
                                </div>
                                <p class="text-xs text-gray-400 leading-relaxed">
                                    Pembayaran Anda akan diproses secara otomatis oleh Pakasir. Setelah checkout, silakan tekan tombol "Bayar Sekarang" untuk menyelesaikan transaksi.
                                </p>
                            </div>
                            @else
                            <div class="mt-4 bg-gold-500/5 border border-gold-500/20 rounded-xl p-4 space-y-3">
                                <div class="flex items-center gap-2 text-gold-400">
                                    <i data-lucide="landmark" class="w-4 h-4"></i>
                                    <span class="text-[10px] font-black uppercase tracking-wider">Transfer Manual / QRIS</span>
                                </div>
                                <p class="text-xs text-gray-400 leading-relaxed">
                                    Lakukan transfer manual ke rekening bank atau scan QRIS toko kami. Setelah checkout, silakan unggah bukti transfer untuk konfirmasi pesanan Anda.
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Form Footer (Action) --}}
                    <div class="border-t border-gray-800/60 px-4 md:px-6 py-5 bg-black/10 space-y-4">
                        {{-- Submit --}}
                        @auth
                            @if(\App\Models\Setting::getGlobal()->isStoreOpen())
                                <button type="submit"
                                    class="w-full bg-gradient-to-r from-[#e2c86e] to-[#f0d97a] hover:from-[#f0d97a] hover:to-[#e2c86e] text-[#0f1115] font-black text-xs md:text-sm py-4 rounded-2xl shadow-[0_8px_25px_rgba(226,200,110,0.35)] hover:shadow-[0_12px_35px_rgba(226,200,110,0.5)] transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 uppercase tracking-widest flex items-center justify-center gap-2"
                                    {{ count($cart) == 0 ? 'disabled' : '' }}>
                                    <i data-lucide="shopping-bag" class="w-4 h-4 pointer-events-none"></i>
                                    <span>Checkout Sekarang</span>
                                </button>
                            @else
                                <button type="button" disabled
                                    class="w-full bg-gray-800 text-gray-500 font-black text-xs md:text-sm py-4 rounded-2xl cursor-not-allowed transition-all duration-300 uppercase tracking-widest flex items-center justify-center gap-2">
                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                    <span>Toko Sedang Tutup</span>
                                </button>
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                                class="w-full bg-gradient-to-r from-brand-cyan to-teal-400 hover:from-teal-400 hover:to-brand-cyan text-[#0f1115] font-black text-xs md:text-sm py-4 rounded-2xl shadow-[0_8px_25px_rgba(78,205,196,0.35)] hover:shadow-[0_12px_35px_rgba(78,205,196,0.5)] transition-all duration-300 transform hover:-translate-y-0.5 active:scale-95 uppercase tracking-widest flex items-center justify-center gap-2">
                                <i data-lucide="log-in" class="w-4 h-4"></i>
                                <span>Login untuk Memesan</span>
                            </a>
                            <p class="text-center text-[10px] text-gray-500 mt-2 uppercase tracking-wider font-semibold">Anda harus login untuk melakukan pemesanan</p>
                        @endauth

                        <div class="flex items-center justify-center gap-2 text-gray-700 pt-1">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                            <span class="text-[10px] font-medium tracking-wider uppercase">Transaksi Aman & Terenkripsi</span>
                        </div>
                    </div>
                </form>


            </div>
        </div>
    </div>

    <!-- Voucher Selection Modal -->
    <div id="voucher-modal" class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center sm:px-4 sm:py-6" 
         style="display: none;">
         
         <!-- Backdrop -->
         <div class="fixed inset-0 bg-black/85 backdrop-blur-sm opacity-0 modal-backdrop" onclick="closeVoucherModal()"></div>
         
         <!-- Modal Content -->
         <div class="bg-[#121418] border border-gray-850 rounded-t-3xl sm:rounded-2xl w-[94vw] sm:max-w-md overflow-hidden shadow-2xl relative z-10 max-h-[80vh] flex flex-col mb-safe sm:mb-0 opacity-0 translate-y-8 sm:scale-95 sm:translate-y-4 modal-body-content">
              
              <!-- Mobile drag handle -->
              <div class="flex justify-center pt-3 pb-1 sm:hidden">
                  <div class="w-10 h-1 bg-gray-800 rounded-full"></div>
              </div>
              
              <!-- Modal Header -->
              <div class="px-5 py-4.5 border-b border-gray-900/60 flex items-center justify-between">
                  <h3 class="text-[11px] font-black text-white uppercase tracking-[0.2em] flex items-center gap-2">
                      <i data-lucide="ticket" class="w-4 h-4 text-gold-500"></i>
                      Voucher Belanja
                  </h3>
                  <button onclick="closeVoucherModal()" type="button" class="text-gray-500 hover:text-white transition p-1.5 hover:bg-white/5 rounded-lg">
                      <i data-lucide="x" class="w-4 h-4"></i>
                  </button>
              </div>

              <!-- Modal Body (Scrollable) -->
              <div class="p-5 overflow-y-auto space-y-5 flex-1 custom-scrollbar">
                  
                  {{-- Code Input inside Modal --}}
                  <div class="space-y-2">
                      <label class="block text-[8px] font-black text-gray-500 uppercase tracking-[0.2em]">Punya Kode Voucher Lain?</label>
                      <div class="flex gap-2">
                          <input type="text" id="modal-voucher-code-input" placeholder="Masukkan kode promo..." 
                              class="flex-grow bg-black/40 border border-gray-800/80 hover:border-gray-700 focus:border-gold-500/50 focus:ring-1 focus:ring-gold-500/20 rounded-xl py-2 px-3 text-xs text-white uppercase outline-none transition placeholder-gray-600">
                          <button type="button" 
                              onclick="const val = document.getElementById('modal-voucher-code-input').value.trim(); if(val){ document.getElementById('voucher-code-input').value = val; document.getElementById('apply-voucher-btn').click(); document.getElementById('modal-voucher-code-input').value = ''; closeVoucherModal(); }"
                              class="bg-gold-500 hover:bg-gold-600 text-black font-black text-xs px-4 rounded-xl transition active:scale-95">
                              Pakai
                          </button>
                      </div>
                  </div>

                  {{-- Available List --}}
                  <div class="space-y-3">
                      <p class="text-[8px] font-black text-gray-500 uppercase tracking-[0.2em]">Pilih Voucher Tersedia</p>
                      
                      @if(count($vouchers) > 0)
                          <div class="space-y-3">
                              @foreach($vouchers as $v)
                                  @php
                                      $isEligible = $subtotal >= $v->min_order_amount;
                                      $isActive = $voucher && $voucher->code === $v->code;
                                  @endphp
                                  <div data-voucher-item="{{ $v->code }}" data-min-amount="{{ $v->min_order_amount }}"
                                       class="flex items-center justify-between p-3 border rounded-xl transition group {{ $isActive ? 'bg-gold-500/5 border-gold-500/30' : 'bg-black/35 border-gray-850/80 hover:border-gold-500/20' }}">
                                      
                                      <!-- Info -->
                                      <div class="min-w-0 pr-2 flex-grow">
                                          <div class="flex items-center gap-2 flex-wrap">
                                              <span class="text-xs font-bold text-white tracking-wide">{{ $v->name }}</span>
                                              @if($v->rank)
                                                  @php
                                                      $vrInfo = \App\Models\User::$ranks[$v->rank] ?? null;
                                                  @endphp
                                                  @if($vrInfo)
                                                      <span class="px-1.5 py-0.5 bg-black/40 text-[7px] font-black uppercase rounded border" style="border-color: {{ $vrInfo['hex'] }}50; color: {{ $vrInfo['hex'] }};">
                                                          {{ $vrInfo['icon'] }}
                                                      </span>
                                                  @endif
                                              @endif
                                          </div>
                                          
                                          <div class="flex items-center gap-2 mt-1 text-[9px] flex-wrap">
                                              <span class="text-gold-500 font-mono font-bold uppercase tracking-wider select-all">KODE: {{ $v->code }}</span>
                                              <span class="text-gray-600">•</span>
                                              <span class="text-gray-400">Min. belanja Rp {{ number_format($v->min_order_amount, 0, ',', '.') }}</span>
                                          </div>
                                      </div>

                                      <!-- Action -->
                                      <div class="voucher-action-btn-container flex-shrink-0 ml-3">
                                          @if($isActive)
                                              <span class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[9px] font-black uppercase tracking-wider rounded-lg select-none">
                                                  <i data-lucide="check" class="w-3 h-3 stroke-[3]"></i> Terpasang
                                              </span>
                                          @else
                                              @if($isEligible)
                                                  <button type="button" 
                                                          onclick="document.getElementById('voucher-code-input').value = '{{ $v->code }}'; document.getElementById('apply-voucher-btn').click(); closeVoucherModal();"
                                                          class="px-3.5 py-1.5 bg-gold-500 hover:bg-gold-600 text-black text-[9px] font-black uppercase tracking-wider rounded-lg transition active:scale-95 cursor-pointer">
                                                      Gunakan
                                                  </button>
                                              @else
                                                  <button type="button" disabled
                                                          class="px-3.5 py-1.5 bg-gray-800/80 text-gray-500 text-[9px] font-bold uppercase tracking-wider rounded-lg cursor-not-allowed"
                                                          title="Minimal belanja belum terpenuhi">
                                                      Gunakan
                                                  </button>
                                              @endif
                                          @endif
                                      </div>
                                  </div>
                              @endforeach
                          </div>
                      @else
                          <div class="text-center py-8 border border-dashed border-gray-850 rounded-xl bg-black/10">
                              <i data-lucide="ticket" class="w-6 h-6 text-gray-650 mx-auto mb-2"></i>
                              <p class="text-xs text-gray-500">Belum ada voucher yang tersedia saat ini.</p>
                          </div>
                      @endif
                  </div>
                  {{-- Safe area and floating chat button spacer --}}
                  <div class="h-24 sm:h-8"></div>
              </div>
         </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Modal helpers
        window.openVoucherModal = function() {
            const modal = document.getElementById('voucher-modal');
            if (modal) {
                modal.style.display = 'flex';
                setTimeout(() => {
                    modal.classList.add('modal-visible');
                }, 10);
                document.body.style.overflow = 'hidden';
            }
        };
        window.closeVoucherModal = function() {
            const modal = document.getElementById('voucher-modal');
            if (modal) {
                modal.classList.remove('modal-visible');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
                document.body.style.overflow = '';
            }
        };
        // Update Voucher list UI active/inactive/disabled states dynamically
        function updateVoucherListUI(appliedCode, currentSubtotal) {
            document.querySelectorAll('[data-voucher-item]').forEach(item => {
                const code = item.getAttribute('data-voucher-item');
                const minAmount = parseFloat(item.getAttribute('data-min-amount') || 0);
                const btnContainer = item.querySelector('.voucher-action-btn-container');
                
                let isEligible = true;
                if (typeof currentSubtotal !== 'undefined') {
                    isEligible = currentSubtotal >= minAmount;
                } else {
                    const existingBtn = btnContainer.querySelector('button');
                    isEligible = existingBtn ? !existingBtn.disabled : true;
                }

                if (code === appliedCode) {
                    item.className = 'flex items-center justify-between p-3 border rounded-xl transition group bg-gold-500/5 border-gold-500/30';
                    if (btnContainer) {
                        btnContainer.innerHTML = `
                            <span class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[9px] font-black uppercase tracking-wider rounded-lg select-none">
                                <svg class="w-3 h-3 stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg> Terpasang
                            </span>
                        `;
                    }
                } else {
                    item.className = 'flex items-center justify-between p-3 border rounded-xl transition group bg-black/35 border-gray-850/80 hover:border-gold-500/20';
                    if (btnContainer) {
                        if (!isEligible) {
                            btnContainer.innerHTML = `
                                <button type="button" disabled
                                        class="px-3.5 py-1.5 bg-gray-800/80 text-gray-500 text-[9px] font-bold uppercase tracking-wider rounded-lg cursor-not-allowed"
                                        title="Minimal belanja belum terpenuhi">
                                    Gunakan
                                </button>
                            `;
                        } else {
                            btnContainer.innerHTML = `
                                <button type="button" 
                                        onclick="document.getElementById('voucher-code-input').value = '${code}'; document.getElementById('apply-voucher-btn').click(); closeVoucherModal();"
                                        class="px-3.5 py-1.5 bg-gold-500 hover:bg-gold-600 text-black text-[9px] font-black uppercase tracking-wider rounded-lg transition active:scale-95 cursor-pointer">
                                    Gunakan
                                </button>
                            `;
                        }
                    }
                }
            });
            if (window.lucide) window.lucide.createIcons();
        }

        // Initialize Voucher list UI on page load
        updateVoucherListUI('{{ $voucher?->code ?? "" }}', {{ $subtotal }});

        document.querySelectorAll('.cart-update-form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                const container = this.closest('[data-item-id]');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Visual feedback
                const btn = this.querySelector('button');
                if (btn) { btn.disabled = true; btn.style.opacity = '0.5'; }

                fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(res => {
                    if (!res.ok) return res.json().then(d => { throw new Error(d.message || 'Gagal mengubah kuantitas.'); });
                    return res.json();
                })
                .then(data => {
                    if (data.success && container) {
                        // Update qty display
                        const qtyDisplay = container.querySelector('.qty-display');
                        if (qtyDisplay) qtyDisplay.textContent = data.quantity;

                        // Update item subtotal
                        const subtotalDisplay = container.querySelector('.item-subtotal-display');
                        if (subtotalDisplay) {
                            const unitPrice = parseFloat(subtotalDisplay.getAttribute('data-unit-price') || 0);
                            const itemSubtotal = unitPrice * data.quantity;
                            subtotalDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(itemSubtotal);
                        }

                        // Update hidden inputs
                        const [minusForm, plusForm] = container.querySelectorAll('.cart-update-form');
                        if (minusForm) {
                            minusForm.querySelector('.qty-input-field').value = data.quantity - 1;
                            const minusBtn = minusForm.querySelector('.minus-btn');
                            if (minusBtn) minusBtn.disabled = data.quantity <= 1;
                        }
                        if (plusForm) {
                            plusForm.querySelector('.qty-input-field').value = data.quantity + 1;
                        }

                        // Update summaries
                        const el = (id) => document.getElementById(id);
                        if (el('summary-total-qty')) el('summary-total-qty').textContent = data.total_qty + ' Item';
                        if (el('summary-subtotal'))  el('summary-subtotal').textContent  = data.total_formatted;
                        
                        if (data.product_discount > 0) {
                            if (el('summary-product-discount-row')) el('summary-product-discount-row').classList.remove('hidden');
                            if (el('summary-product-discount')) el('summary-product-discount').textContent = '-' + data.product_discount_formatted;
                        } else {
                            if (el('summary-product-discount-row')) el('summary-product-discount-row').classList.add('hidden');
                        }
                        
                        if (data.voucher_applied) {
                            if (el('summary-discount-row')) el('summary-discount-row').classList.remove('hidden');
                            if (el('summary-voucher-code')) el('summary-voucher-code').textContent = data.voucher_code;
                            if (el('summary-discount')) el('summary-discount').textContent = '-' + data.discount_formatted;
                            if (el('applied-voucher-code')) el('applied-voucher-code').textContent = 'Kode: ' + data.voucher_code;
                            if (el('applied-voucher-name')) el('applied-voucher-name').textContent = data.voucher_name;
                            if (el('applied-voucher-desc')) el('applied-voucher-desc').textContent = 'Potongan ' + data.discount_formatted;
                            if (el('voucher-select-trigger')) el('voucher-select-trigger').classList.add('hidden');
                            if (el('voucher-applied-container')) el('voucher-applied-container').classList.remove('hidden');
                            if (el('summary-total-pay')) el('summary-total-pay').textContent = data.final_total_formatted;
                            
                            updateVoucherListUI(data.voucher_code, data.subtotal);
                        } else {
                            if (el('summary-discount-row')) el('summary-discount-row').classList.add('hidden');
                            if (el('voucher-select-trigger')) el('voucher-select-trigger').classList.remove('hidden');
                            if (el('voucher-applied-container')) el('voucher-applied-container').classList.add('hidden');
                            if (el('summary-total-pay')) el('summary-total-pay').textContent = data.total_formatted;
                            
                            updateVoucherListUI('', data.subtotal);
                        }

                        // Global cart badge
                        if (typeof updateCartBadges === 'function') updateCartBadges(data.total_qty);
                    }
                })
                .catch(error => {
                    if (typeof showCartToast === 'function') showCartToast(false, error.message);
                })
                .finally(() => {
                    if (btn) { btn.disabled = false; btn.style.opacity = ''; }
                });
            });
        });

        // Apply Voucher
        const applyBtn = document.getElementById('apply-voucher-btn');
        if (applyBtn) {
            applyBtn.addEventListener('click', function () {
                const codeInput = document.getElementById('voucher-code-input');
                const code = codeInput ? codeInput.value.trim() : '';
                if (!code) {
                    if (typeof showCartToast === 'function') showCartToast(false, 'Harap masukkan kode voucher.');
                    return;
                }

                applyBtn.disabled = true;
                applyBtn.style.opacity = '0.5';

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('{{ route("cart.voucher.apply") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ voucher_code: code })
                })
                .then(res => {
                    if (!res.ok) return res.json().then(d => { throw new Error(d.message || 'Gagal menggunakan voucher.'); });
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        const el = (id) => document.getElementById(id);
                        if (el('voucher-select-trigger')) el('voucher-select-trigger').classList.add('hidden');
                        
                        if (el('applied-voucher-code')) el('applied-voucher-code').textContent = 'Kode: ' + data.voucher_code;
                        if (el('applied-voucher-name')) el('applied-voucher-name').textContent = data.voucher_name;
                        if (el('applied-voucher-desc')) el('applied-voucher-desc').textContent = 'Potongan ' + data.discount_formatted;
                        if (el('voucher-applied-container')) el('voucher-applied-container').classList.remove('hidden');

                        if (el('summary-voucher-code')) el('summary-voucher-code').textContent = data.voucher_code;
                        if (el('summary-discount')) el('summary-discount').textContent = '-' + data.discount_formatted;
                        if (el('summary-discount-row')) el('summary-discount-row').classList.remove('hidden');

                        if (el('summary-total-pay')) el('summary-total-pay').textContent = data.final_total_formatted;

                        const currentSubtotal = parseFloat(document.getElementById('summary-subtotal').textContent.replace(/[^0-9]/g, '') || 0);
                        updateVoucherListUI(data.voucher_code, currentSubtotal);

                        if (typeof showCartToast === 'function') showCartToast(true, data.message);
                        if (codeInput) codeInput.value = '';
                        if (window.lucide) window.lucide.createIcons();
                    }
                })
                .catch(error => {
                    if (typeof showCartToast === 'function') showCartToast(false, error.message);
                })
                .finally(() => {
                    applyBtn.disabled = false;
                    applyBtn.style.opacity = '';
                });
            });
        }

        // Remove Voucher
        const removeBtn = document.getElementById('remove-voucher-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                removeBtn.disabled = true;
                removeBtn.style.opacity = '0.5';

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('{{ route("cart.voucher.remove") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(res => {
                    if (!res.ok) return res.json().then(d => { throw new Error(d.message || 'Gagal menghapus voucher.'); });
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        const el = (id) => document.getElementById(id);
                        if (el('voucher-select-trigger')) el('voucher-select-trigger').classList.remove('hidden');
                        if (el('voucher-applied-container')) el('voucher-applied-container').classList.add('hidden');
                        if (el('summary-discount-row')) el('summary-discount-row').classList.add('hidden');

                        if (el('summary-total-pay')) el('summary-total-pay').textContent = data.total_formatted;

                        updateVoucherListUI('', data.subtotal);

                        if (typeof showCartToast === 'function') showCartToast(true, data.message);
                    }
                })
                .catch(error => {
                    if (typeof showCartToast === 'function') showCartToast(false, error.message);
                })
                .finally(() => {
                    removeBtn.disabled = false;
                    removeBtn.style.opacity = '';
                });
            });
        }

        // Shipping Zone Change AJAX
        const zoneSelect = document.getElementById('shipping-zone-select');
        if (zoneSelect) {
            zoneSelect.addEventListener('change', function () {
                const zoneId = this.value;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                zoneSelect.disabled = true;
                zoneSelect.style.opacity = '0.6';

                fetch('{{ route("cart.shipping.set") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ zone_id: zoneId })
                })
                .then(res => {
                    if (!res.ok) throw new Error('Gagal memperbarui wilayah pengiriman.');
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        const el = (id) => document.getElementById(id);
                        
                        // Update shipping display text
                        const shippingEl = el('summary-shipping');
                        if (shippingEl) {
                            shippingEl.textContent = data.shipping_formatted;
                            if (data.shipping_cost > 0) {
                                shippingEl.className = 'text-white font-semibold';
                            } else {
                                shippingEl.className = 'text-emerald-400 font-bold text-xs uppercase tracking-wider';
                            }
                        }

                        // Update final total pay
                        if (el('summary-total-pay')) {
                            el('summary-total-pay').textContent = data.final_total_formatted;
                        }

                        if (typeof showCartToast === 'function') {
                            showCartToast(true, zoneId ? 'Wilayah pengiriman diperbarui ke ' + data.zone_name : 'Wilayah pengiriman dihapus.');
                        }
                    }
                })
                .catch(error => {
                    if (typeof showCartToast === 'function') showCartToast(false, error.message);
                })
                .finally(() => {
                    zoneSelect.disabled = false;
                    zoneSelect.style.opacity = '';
                });
            });
        }

        // Checkout Validation
        const checkoutForm = document.querySelector('form[action="{{ route("cart.checkout") }}"]');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function (e) {
                const zoneSelect = document.getElementById('shipping-zone-select');
                if (zoneSelect && !zoneSelect.value) {
                    e.preventDefault();
                    if (typeof showCartToast === 'function') {
                        showCartToast(false, 'Silakan pilih Wilayah Pengiriman terlebih dahulu.');
                    } else {
                        alert('Silakan pilih Wilayah Pengiriman terlebih dahulu.');
                    }
                    zoneSelect.focus();
                    // Scroll to zone select
                    zoneSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }
    });
</script>
@endsection
