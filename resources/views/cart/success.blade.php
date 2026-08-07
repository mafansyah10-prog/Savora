@extends('layouts.app')
@section('title', 'Pesanan Berhasil — #' . str_pad($order->id, 5, '0', STR_PAD_LEFT))

@section('content')
@php
    $status = $order->status ?? 'pending';
    $statusLabels = [
        'pending'   => 'Menunggu Konfirmasi',
        'paid'      => 'Lunas',
        'shipped'   => 'Sedang Dikirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];
    $statusLabel = $statusLabels[$status] ?? ucfirst($status);

    $waNumber = '6289601905406';
    $storeName = \App\Models\Setting::getGlobal()->store_name;
    
    // Bangun item list teks
    $itemsList = "";
    if ($order->items && count($order->items) > 0) {
        foreach ($order->items as $item) {
            $product = \App\Models\Product::find($item['product_id']);
            $basePrice = $product ? $product->selling_price : $item['price'];
            $itemsList .= "- " . $item['name'] . " (" . $item['quantity'] . "x) @ Rp " . number_format($basePrice, 0, ',', '.');
            if ($product && $item['price'] > $product->selling_price) {
                $itemsList .= " [+ Opsi: Rp " . number_format($item['price'] - $product->selling_price, 0, ',', '.') . "]";
            }
            if (!empty($item['options'])) {
                $opts = [];
                if (!empty($item['options']['spiciness_level'])) {
                    $spicinessName = $item['options']['spiciness_level'];
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
                    $formattedSpiciness = (stripos($spicinessName, 'level') === false && is_numeric($spicinessName)) ? 'Level ' . $spicinessName : $spicinessName;
                    $opts[] = 'Pedas: ' . $formattedSpiciness . ($spicinessPrice > 0 ? ' (+Rp ' . number_format($spicinessPrice, 0, ',', '.') . ')' : '');
                }
                if (!empty($item['options']['sauce'])) {
                    $sauceName = $item['options']['sauce'];
                    $saucePrice = 0;
                    if($product && !empty($product->sauces)) {
                        $sObj = collect($product->sauces)->firstWhere('name', $sauceName);
                        $saucePrice = $sObj['price'] ?? 0;
                    }
                    $opts[] = 'Saus: ' . $sauceName . ($saucePrice > 0 ? ' (+Rp ' . number_format($saucePrice, 0, ',', '.') . ')' : '');
                }
                if (!empty($item['options']['toppings'])) {
                    $toppingsWithPrices = [];
                    foreach($item['options']['toppings'] as $topName) {
                        $topPrice = 0;
                        if($product && !empty($product->toppings)) {
                            $tObj = collect($product->toppings)->firstWhere('name', $topName);
                            $topPrice = $tObj['price'] ?? 0;
                        }
                        $toppingsWithPrices[] = $topName . ($topPrice > 0 ? ' (+Rp ' . number_format($topPrice, 0, ',', '.') . ')' : '');
                    }
                    $opts[] = 'Topping: ' . implode(', ', $toppingsWithPrices);
                }
                if (!empty($item['options']['additionals'])) {
                    $additionalsWithPrices = [];
                    foreach($item['options']['additionals'] as $addName) {
                        $addPrice = 0;
                        if($product && !empty($product->additionals)) {
                            $aObj = collect($product->additionals)->firstWhere('name', $addName);
                            $addPrice = $aObj['price'] ?? 0;
                        }
                        $additionalsWithPrices[] = $addName . ($addPrice > 0 ? ' (+Rp ' . number_format($addPrice, 0, ',', '.') . ')' : '');
                    }
                    $opts[] = 'Tambahan: ' . implode(', ', $additionalsWithPrices);
                }
                if (count($opts) > 0) {
                    $itemsList .= " [" . implode(' | ', $opts) . "]";
                }
            }
            $itemsList .= "\n";
        }
    }
    
    $paymentMethodLabel = str_replace('_', ' ', $order->payment_method ?? 'transfer_bank');
    
    $waText = "Halo *{$storeName}*, saya ingin mengonfirmasi pesanan *#" . str_pad($order->id, 5, '0', STR_PAD_LEFT) . "*.\n\n"
            . "*Detail Transaksi:*\n"
            . "Nama: {$order->customer_name}\n"
            . "WhatsApp: {$order->customer_phone}\n\n"
            . "*Menu yang Dipesan:*\n"
            . "{$itemsList}\n"
            . "*Subtotal:* Rp " . number_format($order->total_amount + $order->discount_amount, 0, ',', '.') . "\n";
            
    if ($order->voucher_code) {
        $waText .= "*Voucher ({$order->voucher_code}):* -Rp " . number_format($order->discount_amount, 0, ',', '.') . "\n";
    }
    
    $waText .= "*Total Pembayaran:* Rp " . number_format($order->total_amount, 0, ',', '.') . "\n"
            . "*Metode Pembayaran:* " . strtoupper($paymentMethodLabel) . "\n\n"
            . "*Alamat Pengiriman:*\n"
            . "{$order->shipping_address}\n\n"
            . "Mohon segera diproses ya. Terima kasih! 😊";
            
    $waUrl = "https://wa.me/{$waNumber}?text=" . urlencode($waText);
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-16">

    {{-- Success banner --}}
    <div class="text-center mb-10 md:mb-12">
        {{-- Animated check icon --}}
        <div class="relative inline-flex items-center justify-center mb-6">
            <div class="w-20 h-20 md:w-24 md:h-24 bg-brand-cyan/10 border-2 border-brand-cyan/30 rounded-full flex items-center justify-center shadow-[0_0_40px_rgba(78,205,196,0.25)] animate-pulse-once">
                <i data-lucide="check" class="w-10 h-10 md:w-12 md:h-12 text-brand-cyan stroke-[3]"></i>
            </div>
            <div class="absolute inset-0 rounded-full border-2 border-brand-cyan/10 animate-ping" style="animation-duration:2s;animation-iteration-count:2;"></div>
        </div>

        <h1 class="text-2xl md:text-4xl font-black text-white tracking-tight font-serif mb-3">
            Pesanan Berhasil! 🎉
        </h1>
        <p class="text-gray-400 text-sm md:text-base max-w-md mx-auto leading-relaxed">
            Terima kasih, <span class="text-white font-bold">{{ $order->customer_name }}</span>! Pesanan Anda sudah diterima dengan status <span class="text-brand-cyan font-bold">{{ $statusLabel }}</span>. Tim kami akan segera menghubungi Anda.
        </p>

        {{-- Order ID pill --}}
        <div class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-white/5 border border-gray-800 rounded-full">
            <i data-lucide="hash" class="w-3.5 h-3.5 text-gray-500"></i>
            <span class="text-xs font-black text-gray-300 tracking-widest">Pesanan {{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>
    </div>

    {{-- Content grid --}}
    <div class="grid md:grid-cols-2 gap-5 md:gap-6 mb-8 md:mb-10">

        {{-- Left Column: Delivery Info & Payment Instructions --}}
        <div class="space-y-5 md:space-y-6">
            {{-- Delivery info card --}}
            <div class="bg-[#16181d] border border-gray-800 rounded-2xl md:rounded-3xl shadow-xl overflow-hidden">
                <div class="bg-black/20 border-b border-gray-800/60 px-5 md:px-6 py-3.5 flex items-center gap-2">
                    <i data-lucide="truck" class="w-4 h-4 text-brand-cyan"></i>
                    <h3 class="text-[10px] font-black text-gray-300 uppercase tracking-[0.25em]">Detail Pengiriman</h3>
                </div>
                <div class="p-5 md:p-6 space-y-4">
                    <div class="flex gap-3">
                        <div class="w-10 h-10 bg-brand-cyan/10 border border-brand-cyan/20 rounded-xl flex items-center justify-center text-brand-cyan font-black text-sm flex-shrink-0">
                            {{ strtoupper(substr($order->customer_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-0.5">Penerima</p>
                            <p class="text-sm font-bold text-white">{{ $order->customer_name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $order->customer_phone }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="w-10 h-10 bg-gray-800 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="map-pin" class="w-4 h-4 text-gray-500"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-0.5">Alamat</p>
                            <p class="text-sm text-gray-300 leading-relaxed">{{ $order->shipping_address }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="w-10 h-10 bg-gray-800 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="credit-card" class="w-4 h-4 text-gray-500"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-0.5">Metode Bayar</p>
                            <p class="text-sm font-semibold text-white capitalize">{{ str_replace('_', ' ', $order->payment_method ?? 'transfer_bank') }}</p>
                        </div>
                    </div>

                    <div class="bg-brand-cyan/5 border border-brand-cyan/15 rounded-xl p-3.5 flex gap-2.5 mt-1">
                        <i data-lucide="info" class="w-4 h-4 text-brand-cyan flex-shrink-0 mt-0.5"></i>
                        <p class="text-xs text-gray-400 leading-relaxed">Tim Savora akan memproses pesanan dan menghubungi Anda melalui WhatsApp.</p>
                    </div>
                </div>
            </div>

            {{-- Payment instructions & proof upload --}}
            @if(\App\Models\Setting::getGlobal()->pakasir_is_active && in_array($order->payment_method, ['pakasir', 'bca', 'mandiri', 'qris']) && $order->status === 'pending')
            <div class="bg-[#16181d] border border-gray-800 rounded-2xl md:rounded-3xl shadow-xl overflow-hidden">
                <div class="bg-black/20 border-b border-gray-800/60 px-5 md:px-6 py-3.5 flex items-center gap-2">
                    <i data-lucide="credit-card" class="w-4 h-4 text-brand-cyan"></i>
                    <h3 class="text-[10px] font-black text-gray-300 uppercase tracking-[0.25em]">Instruksi Pembayaran Pakasir</h3>
                </div>
                <div class="p-5 md:p-6 space-y-4">
                    @php
                        $setting = \App\Models\Setting::getGlobal();
                        $qrisOnlyParam = $order->payment_method === 'qris' ? '&qris_only=1' : '';
                        $pakasirUrl = "https://app.pakasir.com/pay/{$setting->pakasir_project}/" . (int)$order->total_amount . "?order_id={$order->id}{$qrisOnlyParam}&redirect=" . urlencode(route('orders.show', $order));
                    @endphp
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Silakan klik tombol di bawah ini untuk membayar pesanan Anda dengan aman menggunakan Virtual Account / Transfer Bank BCA via Pakasir.
                    </p>
                    <a href="{{ $pakasirUrl }}" target="_blank"
                       class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-gradient-to-r from-brand-cyan to-teal-400 hover:from-teal-400 hover:to-brand-cyan text-[#0f1115] font-black text-xs uppercase tracking-widest rounded-xl transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_6px_15px_rgba(78,205,196,0.3)] active:scale-95 text-center">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        Bayar Sekarang
                    </a>
                    @if(app()->environment('local'))
                    <button onclick="simulatePakasirPayment({{ $order->id }}, {{ $order->total_amount }}, '{{ $setting->pakasir_project }}')"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-black font-black text-xs uppercase tracking-widest rounded-xl transition-all duration-300 active:scale-95 text-center mt-2 cursor-pointer">
                        <i data-lucide="shield-alert" class="w-4 h-4"></i>
                        Simulasikan Pembayaran Sukses (Lokal)
                    </button>
                    @endif
                </div>
            </div>
            @endif


        </div>

        {{-- Virtual Receipt --}}
        <div>
            <div class="bg-white text-black font-mono rounded-2xl shadow-2xl overflow-hidden border border-gray-100 max-w-[360px] mx-auto w-full">
                {{-- Torn top --}}
                <div class="w-full h-3" style="background-image: repeating-linear-gradient(90deg, transparent, transparent 9px, #f3f4f6 9px, #f3f4f6 11px);"></div>

                <div class="px-5 py-4 md:px-6 md:py-5">
                    {{-- Header --}}
                    <div class="text-center mb-4 pb-3 border-b-2 border-dashed border-gray-300">
                        <svg width="40" height="40" viewBox="0 0 40 40" class="mx-auto mb-2" style="display: block;">
                            <circle cx="20" cy="20" r="18" fill="#0f1115" stroke="#f59e0b" stroke-width="2" />
                            <text x="20" y="21.5" font-family="Georgia, Cambria, serif" font-size="22" font-weight="900" fill="#f59e0b" text-anchor="middle" dominant-baseline="central">{{ substr(\App\Models\Setting::getGlobal()->store_name, 0, 1) }}</text>
                        </svg>
                        <h3 class="font-black text-sm uppercase tracking-widest">{{ \App\Models\Setting::getGlobal()->store_name }}</h3>
                        <p class="text-[9px] text-gray-500 mt-0.5">{{ \App\Models\Setting::getGlobal()->store_address }}</p>
                        <p class="text-[9px] text-gray-500">WA: +{{ \App\Models\Setting::getGlobal()->whatsapp_number }}</p>
                    </div>

                    {{-- Order meta --}}
                    <div class="text-[11px] space-y-1.5 mb-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500">No. Struk</span>
                            <span class="font-bold">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tanggal</span>
                            <span id="success-date-time" data-utc-time="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Status</span>
                            <span class="font-black text-emerald-600 text-[10px] uppercase">{{ $statusLabel }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Pembayaran</span>
                            <span class="font-semibold uppercase text-[10px]">{{ str_replace('_', ' ', $order->payment_method ?? 'transfer_bank') }}</span>
                        </div>
                        @if($order->notes)
                            <div class="text-[10px] mt-1.5 text-gray-600">
                                <span class="font-semibold text-gray-500">Catatan:</span> {{ $order->notes }}
                            </div>
                        @endif
                    </div>

                    {{-- Items --}}
                    <div class="border-t-2 border-b-2 border-dashed border-gray-300 py-3 mb-3 space-y-2">
                        @if($order->items && count($order->items) > 0)
                            @foreach($order->items as $item)
                                @php
                                    $product = \App\Models\Product::find($item['product_id']);
                                    $hasDiscount = $product && $item['price'] < $product->price;
                                @endphp
                                <div>
                                    <div class="flex justify-between text-[11px] font-semibold gap-4">
                                        <span class="break-words flex-1">{{ $item['name'] }}</span>
                                        <span class="flex-shrink-0">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="text-[9px] text-gray-500 mt-0.5">
                                        {{ $item['quantity'] }} x 
                                        @if($hasDiscount)
                                            <span class="line-through">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                        @endif
                                        Rp {{ number_format($product ? $product->selling_price : $item['price'], 0, ',', '.') }}
                                        @if($product && $item['price'] > $product->selling_price)
                                            (+ Opsi: Rp {{ number_format($item['price'] - $product->selling_price, 0, ',', '.') }})
                                        @endif
                                    </div>
                                    @if(!empty($item['options']))
                                        @php
                                            $opts = [];
                                            if(!empty($item['options']['spiciness_level'])) {
                                                $spicinessName = $item['options']['spiciness_level'];
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
                                                $opts[] = 'Pedas: ' . $spicinessName . ($spicinessPrice > 0 ? ' (+Rp ' . number_format($spicinessPrice, 0, ',', '.') . ')' : '');
                                            }
                                            if(!empty($item['options']['sauce'])) {
                                                $sauceName = $item['options']['sauce'];
                                                $saucePrice = 0;
                                                if($product && !empty($product->sauces)) {
                                                    $sObj = collect($product->sauces)->firstWhere('name', $sauceName);
                                                    $saucePrice = $sObj['price'] ?? 0;
                                                }
                                                $opts[] = 'Saus: ' . $sauceName . ($saucePrice > 0 ? ' (+Rp ' . number_format($saucePrice, 0, ',', '.') . ')' : '');
                                            }
                                            if(!empty($item['options']['toppings'])) {
                                                $toppingsWithPrices = [];
                                                foreach($item['options']['toppings'] as $topName) {
                                                    $topPrice = 0;
                                                    if($product && !empty($product->toppings)) {
                                                        $tObj = collect($product->toppings)->firstWhere('name', $topName);
                                                        $topPrice = $tObj['price'] ?? 0;
                                                    }
                                                    $toppingsWithPrices[] = $topName . ($topPrice > 0 ? ' (+Rp ' . number_format($topPrice, 0, ',', '.') . ')' : '');
                                                }
                                                $opts[] = 'Topping: ' . implode(', ', $toppingsWithPrices);
                                            }
                                            if(!empty($item['options']['additionals'])) {
                                                $additionalsWithPrices = [];
                                                foreach($item['options']['additionals'] as $addName) {
                                                    $addPrice = 0;
                                                    if($product && !empty($product->additionals)) {
                                                        $aObj = collect($product->additionals)->firstWhere('name', $addName);
                                                        $addPrice = $aObj['price'] ?? 0;
                                                    }
                                                    $additionalsWithPrices[] = $addName . ($addPrice > 0 ? ' (+Rp ' . number_format($addPrice, 0, ',', '.') . ')' : '');
                                                }
                                                $opts[] = 'Tambahan: ' . implode(', ', $additionalsWithPrices);
                                            }
                                        @endphp
                                        @if(count($opts) > 0)
                                            <div class="text-[9px] text-gray-500 italic mt-0.5 leading-tight">
                                                ({{ implode(' | ', $opts) }})
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <p class="text-[10px] text-center text-gray-400 italic py-1">Tidak ada detail item</p>
                        @endif
                    </div>

                    {{-- Totals --}}
                    <div class="space-y-1.5 mb-3 text-[11px]">
                        <div class="flex justify-between text-gray-500">
                            <span>Total Item</span>
                            <span>{{ $order->items ? collect($order->items)->sum('quantity') : 0 }}</span>
                        </div>
                        @php
                            $totalOriginalSubtotal = 0;
                            $totalProductDiscount = 0;
                            
                            if ($order->items && count($order->items) > 0) {
                                foreach ($order->items as $item) {
                                    $product = \App\Models\Product::find($item['product_id']);
                                    if ($product) {
                                        $originalPrice = (float) $product->price;
                                        $sellingPrice = (float) $item['price'];
                                        
                                        if ($sellingPrice < $originalPrice) {
                                            $totalOriginalSubtotal += $originalPrice * $item['quantity'];
                                            $totalProductDiscount += ($originalPrice - $sellingPrice) * $item['quantity'];
                                        } else {
                                            $totalOriginalSubtotal += $sellingPrice * $item['quantity'];
                                        }
                                    } else {
                                        $totalOriginalSubtotal += $item['price'] * $item['quantity'];
                                    }
                                }
                            }
                            
                            if ($totalProductDiscount == 0) {
                                $subtotal = $order->total_amount - $order->shipping_cost + $order->discount_amount;
                            } else {
                                $subtotal = $totalOriginalSubtotal;
                            }
                        @endphp
                        <div class="flex justify-between text-gray-500">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($totalProductDiscount > 0)
                            <div class="flex justify-between text-emerald-600 font-semibold">
                                <span>Diskon Produk</span>
                                <span>-Rp {{ number_format($totalProductDiscount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if($order->shipping_zone_name)
                            <div class="flex justify-between text-gray-500">
                                <span>Ongkir ({{ $order->shipping_zone_name }})</span>
                                <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if($order->voucher_code)
                            <div class="flex justify-between text-red-600 font-semibold">
                                <span>Voucher ({{ $order->voucher_code }})</span>
                                <span>-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-black text-base border-t border-gray-200 pt-2 mt-2">
                            <span>TOTAL</span>
                            <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="border-t-2 border-dashed border-gray-300 pt-3 text-center text-[9px] text-gray-500 space-y-1">
                        <p class="font-black text-black text-[10px] uppercase tracking-widest">Terima Kasih!</p>
                        <p>{{ \App\Models\Setting::getGlobal()->store_name }} — Made Fresh Daily</p>
                    </div>
                </div>

                {{-- Torn bottom --}}
                <div class="w-full h-3" style="background-image: repeating-linear-gradient(90deg, transparent, transparent 9px, #f3f4f6 9px, #f3f4f6 11px);"></div>
            </div>
        </div>
    </div>

    <!-- CTA buttons -->
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ $waUrl }}"
           target="_blank"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#25D366] hover:bg-[#1dbc5a] text-white font-black text-[10px] uppercase tracking-widest rounded-xl transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_6px_15px_rgba(37,211,102,0.25)] active:scale-95">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12.004 2C6.51 2 2.014 6.5 2.014 12c0 2.13.67 4.11 1.81 5.73L2.03 22l4.43-1.76c1.55.93 3.37 1.47 5.3 1.47 5.49-.01 9.99-4.5 9.99-10S17.49 2 12.004 2zm5.72 13.91c-.24.67-1.18 1.25-1.92 1.41-.53.11-1.22.2-3.57-.77-3-1.24-4.94-4.28-5.09-4.48-.15-.2-1.21-1.61-1.21-3.07s.76-2.17 1.03-2.47c.27-.3.59-.38.79-.38.2 0 .4 0 .57.01.18.01.41-.01.63.5.24.57.81 1.97.88 2.12.07.15.12.32.02.52-.1.2-.15.32-.3.49-.15.17-.32.39-.46.52-.15.15-.31.32-.13.62.18.3.79 1.3 1.69 2.1 1.16 1.03 2.14 1.35 2.44 1.5.3.15.48.12.66-.08.18-.2.78-.9.99-1.21.2-.3.42-.25.7-.15.28.1 1.77.84 2.07.99.3.15.5.22.57.35.07.12.07.72-.17 1.39z"/></svg>
            Konfirmasi via WhatsApp
        </a>

        <a href="{{ route('orders.show', $order) }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-brand-cyan/10 hover:bg-brand-cyan hover:text-black border border-brand-cyan/30 text-brand-cyan font-black text-[10px] uppercase tracking-widest rounded-xl transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
            <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
            Lihat Detail Pesanan
        </a>

        <a href="{{ route('home') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white/5 hover:bg-white/10 border border-gray-700 text-gray-300 hover:text-white font-black text-[10px] uppercase tracking-widest rounded-xl transition-all duration-300 active:scale-95">
            <i data-lucide="home" class="w-3.5 h-3.5"></i>
            Kembali ke Beranda
        </a>
    </div>
</div>

<style>
    @keyframes pulse-once {
        0%, 100% { box-shadow: 0 0 40px rgba(78,205,196,0.25); }
        50%       { box-shadow: 0 0 60px rgba(78,205,196,0.5); }
    }
    .animate-pulse-once { animation: pulse-once 1.5s ease-in-out 2; }
</style>
<script>
    function formatSuccessDateTime() {
        const el = document.getElementById('success-date-time');
        if (!el) return;
        const utcTime = el.getAttribute('data-utc-time');
        if (!utcTime) return;
        
        const localDate = new Date(utcTime);
        const day = String(localDate.getDate()).padStart(2, '0');
        const month = String(localDate.getMonth() + 1).padStart(2, '0');
        const year = localDate.getFullYear();
        const hours = String(localDate.getHours()).padStart(2, '0');
        const minutes = String(localDate.getMinutes()).padStart(2, '0');
        const seconds = String(localDate.getSeconds()).padStart(2, '0');
        
        el.textContent = `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
    }
    formatSuccessDateTime();

    function simulatePakasirPayment(orderId, amount, project) {
        if (!confirm('Simulasikan pembayaran sukses untuk pesanan ini?')) return;
        
        fetch('/webhook/pakasir', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                project: project,
                order_id: orderId,
                amount: amount,
                status: 'completed',
                payment_method: 'qris',
                completed_at: new Date().toISOString()
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Simulasi pembayaran berhasil!');
                window.location.href = "{{ route('orders.show', $order) }}";
            } else {
                alert('Gagal mensimulasikan pembayaran: ' + (data.message || 'Error'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan.');
        });
    }
</script>
@endsection
