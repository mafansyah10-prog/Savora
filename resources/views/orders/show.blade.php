@extends('layouts.app')
@section('title', 'Detail Pesanan #' . str_pad($order->id, 5, '0', STR_PAD_LEFT))

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-14">

    {{-- Back link --}}
    <div class="mb-6 md:mb-8">
        <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-2 text-xs md:text-sm text-gray-500 hover:text-white transition font-semibold uppercase tracking-widest">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali ke Pesanan Saya
        </a>
    </div>

    @if(session('success'))
        <div class="bg-brand-cyan/15 border border-brand-cyan/30 text-brand-cyan px-4 py-3 rounded-xl mb-5 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-500/15 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl mb-5 text-sm flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
            {{ session('error') }}
        </div>
    @endif

    @php
        $statusLabels = [
            'pending'   => 'Menunggu Konfirmasi',
            'paid'      => 'Lunas',
            'shipped'   => 'Sedang Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
        $statusBadgeClasses = [
            'pending'   => 'bg-amber-400/10 border-amber-400/30 text-amber-400',
            'paid'      => 'bg-emerald-400/10 border-emerald-400/30 text-emerald-400',
            'shipped'   => 'bg-sky-400/10 border-sky-400/30 text-sky-400',
            'completed' => 'bg-violet-400/10 border-violet-400/30 text-violet-400',
            'cancelled' => 'bg-red-400/10 border-red-400/30 text-red-400',
        ];
        $statusIcons = [
            'pending'   => 'clock',
            'paid'      => 'check-circle',
            'shipped'   => 'truck',
            'completed' => 'badge-check',
            'cancelled' => 'x-circle',
        ];
        $statusLabel = $statusLabels[$order->status] ?? ucfirst($order->status);
        $statusClass = $statusBadgeClasses[$order->status] ?? 'bg-gray-500/10 border-gray-500/30 text-gray-300';
        $statusIcon  = $statusIcons[$order->status] ?? 'circle';

        // Progress steps for order timeline
        $steps = ['pending', 'paid', 'shipped', 'completed'];
        $currentStepIndex = array_search($order->status, $steps);
        if ($currentStepIndex === false) $currentStepIndex = -1;

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
                            $spObj = collect($product->spiciness_levels)->firstWhere('name', $spicinessName);
                            $spicinessPrice = $spObj['price'] ?? 0;
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
        
        $subtotal = $order->total_amount - $order->shipping_cost + $order->discount_amount;
        
        $waText = "Halo *{$storeName}*, saya ingin mengonfirmasi pesanan *#" . str_pad($order->id, 5, '0', STR_PAD_LEFT) . "*.\n\n"
                . "*Detail Transaksi:*\n"
                . "Nama: {$order->customer_name}\n"
                . "Telepon: {$order->customer_phone}\n\n"
                . "*Menu yang Dipesan:*\n"
                . "{$itemsList}\n"
                . "*Subtotal:* Rp " . number_format($subtotal, 0, ',', '.') . "\n";
                
        if ($order->shipping_zone_name) {
            $waText .= "*Ongkos Kirim ({$order->shipping_zone_name}):* Rp " . number_format($order->shipping_cost, 0, ',', '.') . "\n";
        }
                
        if ($order->voucher_code) {
            $waText .= "*Voucher ({$order->voucher_code}):* -Rp " . number_format($order->discount_amount, 0, ',', '.') . "\n";
        }
        
        $waText .= "*Total Pembayaran:* Rp " . number_format($order->total_amount, 0, ',', '.') . "\n"
                . "*Metode Pembayaran:* " . strtoupper($paymentMethodLabel) . "\n"
                . "*Status Pesanan:* " . strtoupper($statusLabel) . "\n\n"
                . "*Alamat Pengiriman:*\n"
                . "{$order->shipping_address}\n";
        
        if ($order->notes) {
            $waText .= "\n*Catatan:*\n{$order->notes}\n";
        }
        
        $waText .= "\nMohon segera diproses ya. Terima kasih! 😊";
                
        $waUrl = "https://wa.me/{$waNumber}?text=" . urlencode($waText);
    @endphp

    {{-- Main card --}}
    <div class="bg-[#16181d] border border-gray-800 rounded-2xl md:rounded-[2rem] shadow-2xl overflow-hidden">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-[#111317] to-[#13161a] border-b border-gray-800 px-5 md:px-8 py-5 md:py-7">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.3em] mb-1">Nomor Pesanan</p>
                    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">
                        #<span class="text-brand-cyan">{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">{{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                </div>
                <div class="flex flex-col items-start sm:items-end gap-2">
                    <span id="main-order-status-badge"
                          class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-[10px] md:text-xs font-black uppercase tracking-widest border {{ $statusClass }}">
                        <i data-lucide="{{ $statusIcon }}" class="w-3.5 h-3.5"></i>
                        <span id="main-order-status-text">{{ $statusLabel }}</span>
                    </span>
                    <p class="text-[10px] text-gray-600 italic hidden sm:block">Diperbarui otomatis secara real-time</p>
                </div>
            </div>
        </div>

        {{-- Order Progress Timeline (hidden for cancelled) --}}
        @if($order->status !== 'cancelled')
        <div class="border-b border-gray-800/60 px-5 md:px-8 py-5 md:py-6">
            <div class="flex items-center justify-between relative">
                {{-- Connector line --}}
                <div class="absolute left-0 right-0 top-4 md:top-5 h-0.5 bg-gray-800 mx-[2rem] md:mx-[2.5rem]"></div>
                <div id="progress-line" class="absolute left-0 top-4 md:top-5 h-0.5 bg-brand-cyan mx-[2rem] md:mx-[2.5rem] transition-all duration-700"
                     style="width: calc({{ $currentStepIndex >= 0 ? ($currentStepIndex / 3 * 100) : 0 }}% - 0px);"></div>

                @foreach([
                    ['pending',   'Dipesan',  'clock'],
                    ['paid',      'Lunas',    'check-circle'],
                    ['shipped',   'Dikirim',  'truck'],
                    ['completed', 'Selesai',  'badge-check'],
                ] as $i => [$step, $label, $icon])
                    @php
                        $isDone   = $currentStepIndex >= $i;
                        $isCurr   = $currentStepIndex === $i;
                    @endphp
                    <div class="flex flex-col items-center gap-1.5 md:gap-2 relative z-10">
                        <div id="step-circle-{{ $step }}" class="w-8 h-8 md:w-10 md:h-10 rounded-full border-2 flex items-center justify-center transition-all duration-500
                                    {{ $isDone ? 'bg-brand-cyan border-brand-cyan text-black shadow-[0_0_15px_rgba(78,205,196,0.4)]' : 'bg-[#16181d] border-gray-700 text-gray-600' }}
                                    {{ $isCurr ? 'ring-4 ring-brand-cyan/20' : '' }}">
                            <i data-lucide="{{ $icon }}" class="w-4 h-4 md:w-4.5 md:h-4.5 stroke-[2.5]"></i>
                        </div>
                        <span id="step-label-{{ $step }}" class="text-[8px] md:text-[10px] font-bold uppercase tracking-wide text-center {{ $isDone ? 'text-white' : 'text-gray-600' }}">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Body: 2-column grid on desktop --}}
        <div class="grid md:grid-cols-2 gap-0 md:divide-x md:divide-gray-800/60">

            {{-- Left: Order Info --}}
            <div class="p-5 md:p-8 space-y-5 md:space-y-6 border-b md:border-b-0 border-gray-800/60">

                {{-- Customer Info --}}
                <div>
                    <p class="text-[9px] md:text-[10px] font-black text-gray-500 uppercase tracking-[0.3em] mb-2 md:mb-3">Data Customer</p>
                    <div class="bg-black/20 border border-gray-800/60 rounded-xl md:rounded-2xl p-4 md:p-5 flex items-center gap-3.5">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-brand-cyan/10 border border-brand-cyan/20 rounded-xl md:rounded-2xl flex items-center justify-center text-brand-cyan font-black text-sm flex-shrink-0">
                            {{ strtoupper(substr($order->customer_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm md:text-base font-bold text-white leading-tight">{{ $order->customer_name }}</p>
                            <p class="text-xs md:text-sm text-gray-400 mt-0.5 flex items-center gap-1.5">
                                <i data-lucide="phone" class="w-3 h-3 text-gray-600"></i>
                                {{ $order->customer_phone }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Shipping --}}
                <div>
                    <p class="text-[9px] md:text-[10px] font-black text-gray-500 uppercase tracking-[0.3em] mb-2 md:mb-3">Alamat Pengiriman</p>
                    <div class="bg-black/20 border border-gray-800/60 rounded-xl md:rounded-2xl p-4 md:p-5 flex gap-3">
                        <i data-lucide="map-pin" class="w-4 h-4 text-gray-600 mt-0.5 flex-shrink-0"></i>
                        <p class="text-sm text-gray-300 leading-relaxed">{{ $order->shipping_address }}</p>
                    </div>
                </div>

                {{-- Customer Notes --}}
                @if($order->notes)
                <div>
                    <p class="text-[9px] md:text-[10px] font-black text-gray-500 uppercase tracking-[0.3em] mb-2 md:mb-3">Catatan Pelanggan</p>
                    <div class="bg-black/20 border border-gray-800/60 rounded-xl md:rounded-2xl p-4 md:p-5 flex gap-3">
                        <i data-lucide="message-square-text" class="w-4 h-4 text-gold-400 mt-0.5 flex-shrink-0"></i>
                        <p class="text-sm text-gray-300 leading-relaxed italic">{{ $order->notes }}</p>
                    </div>
                </div>
                @endif

                {{-- Payment + Amount --}}
                <div class="grid grid-cols-2 gap-3 md:gap-4">
                    <div class="bg-black/20 border border-gray-800/60 rounded-xl md:rounded-2xl p-3.5 md:p-5">
                        <p class="text-[9px] md:text-[10px] text-gray-500 uppercase tracking-[0.2em] mb-1">Metode Bayar</p>
                        <p class="text-xs md:text-sm font-bold text-white capitalize">{{ str_replace('_', ' ', $order->payment_method ?? 'transfer_bank') }}</p>
                    </div>
                    <div class="bg-black/20 border border-gray-800/60 rounded-xl md:rounded-2xl p-3.5 md:p-5">
                        <p class="text-[9px] md:text-[10px] text-gray-500 uppercase tracking-[0.2em] mb-1">Total Bayar</p>
                        <p class="text-sm md:text-base font-black text-gold-500">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                        @if($order->shipping_zone_name)
                            <span class="text-[9px] text-gray-400 block mt-1">Ongkir: Rp {{ number_format($order->shipping_cost, 0, ',', '.') }} ({{ $order->shipping_zone_name }})</span>
                        @endif
                        @if($order->voucher_code)
                            <span class="text-[9px] text-red-400 block mt-1 font-bold">Hemat Rp {{ number_format($order->discount_amount, 0, ',', '.') }} via {{ $order->voucher_code }}</span>
                        @endif
                    </div>
                </div>

                @if(isset($snapToken) && $snapToken)
                @php
                    $setting = \App\Models\Setting::getGlobal();
                @endphp
                <div id="midtrans-payment-container" class="bg-brand-cyan/5 border border-brand-cyan/20 rounded-xl md:rounded-2xl p-4 md:p-5 space-y-4">
                    <div class="flex items-center gap-2 text-brand-cyan">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                        <p class="text-xs font-black uppercase tracking-wider">Menunggu Pembayaran Otomatis</p>
                    </div>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Silakan selesaikan pembayaran Anda secara otomatis dengan aman menggunakan DANA, QRIS, E-Wallet, atau Transfer Bank. Nominal pembayaran akan terisi otomatis.
                    </p>
                    <button id="pay-button"
                       class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-gradient-to-r from-brand-cyan to-teal-400 hover:from-teal-400 hover:to-brand-cyan text-[#0f1115] font-black text-xs uppercase tracking-widest rounded-lg transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_6px_15px_rgba(78,205,196,0.3)] active:scale-95 text-center cursor-pointer">
                        <i data-lucide="wallet" class="w-4 h-4"></i>
                        Bayar Sekarang
                    </button>
                </div>
                @elseif(\App\Models\Setting::getGlobal()->pakasir_is_active && !\App\Models\Setting::getGlobal()->manual_payment_is_active && in_array($order->payment_method, ['pakasir', 'bca', 'mandiri', 'qris']) && $order->status === 'pending')
                @php
                    $setting = \App\Models\Setting::getGlobal();
                    $qrisOnlyParam = $order->payment_method === 'qris' ? '&qris_only=1' : '';
                    $pakasirUrl = "https://app.pakasir.com/pay/{$setting->pakasir_project}/" . (int)$order->total_amount . "?order_id={$order->id}{$qrisOnlyParam}&redirect=" . urlencode(route('orders.show', $order));
                @endphp
                <div id="payment-instruction-container" class="bg-brand-cyan/5 border border-brand-cyan/20 rounded-xl md:rounded-2xl p-4 md:p-5 space-y-4">
                    <div class="flex items-center gap-2 text-brand-cyan">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                        <p class="text-xs font-black uppercase tracking-wider">Menunggu Pembayaran Pakasir</p>
                    </div>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Silakan selesaikan pembayaran Anda menggunakan link aman Pakasir di bawah ini dengan Virtual Account / Transfer Bank BCA.
                    </p>
                    <a href="{{ $pakasirUrl }}" target="_blank"
                       class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-gradient-to-r from-brand-cyan to-teal-400 hover:from-teal-400 hover:to-brand-cyan text-[#0f1115] font-black text-xs uppercase tracking-widest rounded-lg transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_6px_15px_rgba(78,205,196,0.3)] active:scale-95 text-center">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        Bayar Sekarang
                    </a>
                    @if(app()->environment('local'))
                    <button onclick="simulatePakasirPayment({{ $order->id }}, {{ $order->total_amount }}, '{{ $setting->pakasir_project }}')"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-black font-black text-xs uppercase tracking-widest rounded-lg transition-all duration-300 active:scale-95 text-center cursor-pointer">
                        <i data-lucide="shield-alert" class="w-4 h-4"></i>
                        Simulasikan Pembayaran Sukses (Lokal)
                    </button>
                    @endif
                </div>
                @elseif($order->status === 'pending')
                {{-- Manual Payment Instructions & Upload Form --}}
                <div id="manual-payment-container" class="bg-[#16181d] border border-gray-800 rounded-xl md:rounded-2xl p-4 md:p-5 space-y-4">
                    <div class="flex items-center gap-2 text-gold-400">
                        <i data-lucide="landmark" class="w-4 h-4"></i>
                        <p class="text-xs font-black uppercase tracking-wider">Instruksi Pembayaran Manual</p>
                    </div>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Silakan lakukan pembayaran dengan mentransfer ke rekening bank kami atau memindai kode QRIS di bawah ini.
                    </p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @php
                            $methods = \App\Models\Setting::getGlobal()->manual_payment_methods ?? [];
                        @endphp
                        @forelse($methods as $method)
                        <div class="bg-black/25 border border-gray-850 rounded-xl p-4 space-y-3 flex flex-col justify-between">
                            <div class="space-y-2">
                                <h4 class="text-[10px] font-black text-gold-500 uppercase tracking-wider">{{ $method['name'] ?? 'Metode Pembayaran' }}</h4>
                                <div class="space-y-1.5 text-xs text-left">
                                    <p class="text-gray-400">No. Rekening / HP: 
                                        <span class="text-white font-bold font-mono select-all">{{ $method['account_number'] ?? '' }}</span>
                                        <button onclick="copyBankNumber('{{ $method['account_number'] ?? '' }}')" class="ml-1 text-gold-500 hover:text-amber-400 text-[10px] font-bold uppercase transition">Salin</button>
                                    </p>
                                    <p class="text-gray-400">Atas Nama: <span class="text-white font-bold">{{ $method['account_name'] ?? '' }}</span></p>
                                </div>
                            </div>
                            @if(!empty($method['qris_image']))
                            <div class="pt-3 border-t border-gray-850 flex flex-col items-center justify-center space-y-1.5">
                                <p class="text-[8px] font-black text-gray-500 uppercase tracking-wider">Scan QRIS</p>
                                <img src="{{ asset('storage/' . $method['qris_image']) }}" alt="QRIS" class="w-24 h-24 object-contain rounded-lg border border-gray-800 bg-white p-1">
                            </div>
                            @endif
                        </div>
                        @empty
                        <div class="col-span-2 text-center py-4 text-xs text-gray-500 bg-black/25 border border-gray-850 rounded-xl">
                            Belum ada metode pembayaran manual yang dikonfigurasi oleh admin.
                        </div>
                        @endforelse
                    </div>

                    <div class="border-t border-gray-850 pt-4 space-y-3">
                        <div class="flex items-center gap-2 text-gold-400">
                            <i data-lucide="upload" class="w-4 h-4"></i>
                            <p class="text-xs font-black uppercase tracking-wider">Unggah Bukti Pembayaran</p>
                        </div>
                        <p class="text-[11px] text-gray-500">Unggah foto atau screenshot bukti transfer Anda untuk verifikasi AI otomatis.</p>
                        
                        <form action="{{ route('orders.upload_proof', $order) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <input type="file" name="payment_proof" required accept="image/*"
                                   class="block w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-black file:uppercase file:bg-gray-800 file:text-gold-500 hover:file:bg-gray-700 file:cursor-pointer bg-black/25 border border-gray-850 rounded-lg p-2">
                            <button type="submit" class="w-full bg-gold-500 hover:bg-gold-600 text-black font-black text-xs py-3 rounded-lg transition active:scale-95 uppercase tracking-widest text-center cursor-pointer">
                                Kirim & Verifikasi Pembayaran
                            </button>
                        </form>
                    </div>
                </div>
                @endif



                {{-- Info note --}}
                <div class="bg-brand-cyan/5 border border-brand-cyan/15 rounded-xl md:rounded-2xl p-4 flex gap-3">
                    <i data-lucide="info" class="w-4 h-4 text-brand-cyan mt-0.5 flex-shrink-0"></i>
                    <p class="text-xs text-gray-400 leading-relaxed">Tim Savora akan menghubungi Anda jika ada perubahan status atau konfirmasi pesanan melalui WhatsApp.</p>
                </div>

                {{-- WhatsApp button --}}
                <div class="mt-4">
                    <a href="{{ $waUrl }}" target="_blank"
                       class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#25D366] hover:bg-[#1dbc5a] text-white font-black text-[10px] uppercase tracking-widest rounded-lg transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_6px_15px_rgba(37,211,102,0.2)] active:scale-95">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12.004 2C6.51 2 2.014 6.5 2.014 12c0 2.13.67 4.11 1.81 5.73L2.03 22l4.43-1.76c1.55.93 3.37 1.47 5.3 1.47 5.49-.01 9.99-4.5 9.99-10S17.49 2 12.004 2zm5.72 13.91c-.24.67-1.18 1.25-1.92 1.41-.53.11-1.22.2-3.57-.77-3-1.24-4.94-4.28-5.09-4.48-.15-.2-1.21-1.61-1.21-3.07s.76-2.17 1.03-2.47c.27-.3.59-.38.79-.38.2 0 .4 0 .57.01.18.01.41-.01.63.5.24.57.81 1.97.88 2.12.07.15.12.32.02.52-.1.2-.15.32-.3.49-.15.17-.32.39-.46.52-.15.15-.31.32-.13.62.18.3.79 1.3 1.69 2.1 1.16 1.03 2.14 1.35 2.44 1.5.3.15.48.12.66-.08.18-.2.78-.9.99-1.21.2-.3.42-.25.7-.15.28.1 1.77.84 2.07.99.3.15.5.22.57.35.07.12.07.72-.17 1.39z"/></svg>
                        Konfirmasi via WhatsApp
                    </a>
                </div>
            </div>

            {{-- Right: Virtual Receipt --}}
            <div class="p-5 md:p-8">
                <div class="flex items-center justify-between mb-3 md:mb-4">
                    <p class="text-[9px] md:text-[10px] font-black text-gray-500 uppercase tracking-[0.3em]">Struk Transaksi Virtual</p>
                    <button id="btn-download-receipt"
                       class="inline-flex items-center gap-1.5 px-3 py-1 bg-gold-500 hover:bg-[#f0d97a] text-[#0f1115] text-[10px] font-black rounded-lg uppercase tracking-wider transition-all hover:scale-[1.03] active:scale-95 shadow-sm">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        Unduh Struk
                    </button>
                </div>

                {{-- Thermal receipt --}}
                <div id="thermal-receipt" class="bg-white text-black font-mono rounded-xl md:rounded-2xl shadow-2xl overflow-hidden border border-gray-100 relative max-w-[360px] mx-auto w-full">
                    {{-- Torn top edge --}}
                    <div class="w-full h-2.5 bg-gradient-to-r from-gray-100 via-white to-gray-100" style="background-image: repeating-linear-gradient(90deg, transparent, transparent 8px, #f3f4f6 8px, #f3f4f6 10px);"></div>

                    <div class="px-5 py-4 md:px-6 md:py-5">
                        {{-- Header --}}
                        <div class="text-center mb-4 pb-4 border-b-2 border-dashed border-gray-300">
                            <div class="w-10 h-10 rounded-full border-2 border-amber-500 bg-[#0f1115] mb-2 mx-auto" style="display: table; box-sizing: border-box; width: 40px; height: 40px;">
                                <div style="display: table-cell; vertical-align: middle; text-align: center;">
                                    <span class="font-serif text-xl font-black text-amber-500" style="line-height: 1; font-family: Georgia, Cambria, 'Times New Roman', Times, serif; display: inline-block;">{{ substr(\App\Models\Setting::getGlobal()->store_name, 0, 1) }}</span>
                                </div>
                            </div>
                            <h3 class="font-black text-sm uppercase tracking-widest">{{ \App\Models\Setting::getGlobal()->store_name }}</h3>
                            <p class="text-[9px] text-gray-500 mt-0.5">{{ \App\Models\Setting::getGlobal()->store_address }}</p>
                            <p class="text-[9px] text-gray-500">WA: +{{ \App\Models\Setting::getGlobal()->whatsapp_number }}</p>
                        </div>

                        {{-- Order info --}}
                        <div class="text-[11px] space-y-1.5 mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-500">No. Struk</span>
                                <span class="font-bold">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tanggal</span>
                                <span id="show-date-time" data-utc-time="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Status</span>
                                <span id="receipt-order-status" class="font-black text-[10px] uppercase
                                    {{ $order->status === 'completed' || $order->status === 'paid' ? 'text-emerald-600' :
                                       ($order->status === 'shipped' ? 'text-sky-600' :
                                       ($order->status === 'cancelled' ? 'text-red-600' : 'text-amber-600')) }}">
                                    {{ $statusLabel }}
                                </span>
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
                        <div class="border-t-2 border-b-2 border-dashed border-gray-300 py-3 mb-4 space-y-2.5">
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
                                                    $formattedSpiciness = (stripos($spicinessName, 'level') === false && is_numeric($spicinessName)) ? 'Level ' . $spicinessName : $spicinessName;
                                                    $opts[] = 'Pedas: ' . $formattedSpiciness . ($spicinessPrice > 0 ? ' (+Rp ' . number_format($spicinessPrice, 0, ',', '.') . ')' : '');
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
                                <p class="text-[10px] text-center text-gray-400 italic py-2">Tidak ada detail item</p>
                            @endif
                        </div>

                        {{-- Totals --}}
                        <div class="space-y-1.5 mb-4 text-[11px]">
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
                            <p class="text-[8px]">Disimpan: <span id="download-time">{{ now()->format('d/m/Y H:i') }}</span></p>
                        </div>
                    </div>

                    {{-- Torn bottom edge --}}
                    <div class="w-full h-2.5" style="background-image: repeating-linear-gradient(90deg, transparent, transparent 8px, #f3f4f6 8px, #f3f4f6 10px);"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Html2Canvas Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    function copyBankNumber(text) {
        text = text.trim();
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Nomor rekening berhasil disalin!');
            }).catch(err => {
                fallbackCopyText(text);
            });
        } else {
            fallbackCopyText(text);
        }
    }

    function fallbackCopyText(text) {
        const el = document.createElement('textarea');
        el.value = text;
        el.setAttribute('readonly', '');
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        
        const selected = document.getSelection().rangeCount > 0 
            ? document.getSelection().getRangeAt(0) 
            : false;
        el.select();
        el.setSelectionRange(0, 99999);
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                alert('Nomor rekening berhasil disalin!');
            } else {
                throw new Error('Gagal mengeksekusi perintah salin');
            }
        } catch (err) {
            console.error('Fallback gagal menyalin teks: ', err);
            window.prompt("Gagal menyalin otomatis. Silakan salin nomor rekening di bawah ini:", text);
        }
        
        document.body.removeChild(el);
        if (selected) {
            document.getSelection().removeAllRanges();
            document.getSelection().addRange(selected);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Handle Download Struk
        const btnDownload = document.getElementById('btn-download-receipt');
        if (btnDownload) {
            btnDownload.addEventListener('click', function () {
                const receiptElement = document.getElementById('thermal-receipt');
                
                // Animasi visual loading pada button
                const originalContent = btnDownload.innerHTML;
                btnDownload.disabled = true;
                btnDownload.innerHTML = '<i class="w-3.5 h-3.5 animate-spin border-2 border-current border-t-transparent rounded-full"></i> Mengunduh...';

                // Gunakan html2canvas untuk menangkap gambar struk
                html2canvas(receiptElement, {
                    scale: 2.5, // Meningkatkan kualitas gambar unduhan
                    useCORS: true,
                    backgroundColor: null, // Transparan agar rounded corners & shadow sama persis dengan website
                    logging: false,
                    onclone: (clonedDoc) => {
                        const clonedReceipt = clonedDoc.getElementById('thermal-receipt');
                        if (clonedReceipt) {
                            clonedReceipt.style.width = '360px';
                            clonedReceipt.style.maxWidth = '360px';
                            clonedReceipt.style.margin = '0 auto';
                        }
                    }
                }).then(canvas => {
                    const link = document.createElement('a');
                    link.download = 'Struk_Savora_#' + '{{ str_pad($order->id, 5, "0", STR_PAD_LEFT) }}' + '.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                    
                    // Kembalikan tombol seperti semula
                    btnDownload.disabled = false;
                    btnDownload.innerHTML = originalContent;
                }).catch(err => {
                    console.error('Gagal mengunduh struk:', err);
                    alert('Gagal mengunduh struk, silakan coba lagi.');
                    btnDownload.disabled = false;
                    btnDownload.innerHTML = originalContent;
                });
            });
        }

        // Real-time ticking date & time on receipt (Tanggal Transaksi)
        function updateReceiptTime() {
            const el = document.getElementById('show-date-time');
            if (!el) return;
            const now = new Date();
            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            el.textContent = `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
        }
        updateReceiptTime();
        setInterval(updateReceiptTime, 1000);

        // Real-time ticking current time for download footer
        function updateDownloadTime() {
            const el = document.getElementById('download-time');
            if (!el) return;
            const now = new Date();
            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            el.textContent = `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
        }
        updateDownloadTime();
        setInterval(updateDownloadTime, 1000);

        const fetchUrl = "{{ route('orders.show', $order->id) }}";
        let currentStatus = "{{ $order->status }}";

        const statusBadgeClasses = {
            pending:   'bg-amber-400/10 border border-amber-400/30 text-amber-400',
            paid:      'bg-emerald-400/10 border border-emerald-400/30 text-emerald-400',
            shipped:   'bg-sky-400/10 border border-sky-400/30 text-sky-400',
            completed: 'bg-violet-400/10 border border-violet-400/30 text-violet-400',
            cancelled: 'bg-red-400/10 border border-red-400/30 text-red-400',
        };

        // Load Confetti Library dynamically
        if (!window.confetti) {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js';
            document.head.appendChild(script);
        }

        // Web Audio Success Chime
        function playSuccessSound() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                
                // Note 1 (E5)
                const osc1 = audioCtx.createOscillator();
                const gain1 = audioCtx.createGain();
                osc1.connect(gain1);
                gain1.connect(audioCtx.destination);
                osc1.type = 'sine';
                osc1.frequency.value = 659.25;
                gain1.gain.setValueAtTime(0, audioCtx.currentTime);
                gain1.gain.linearRampToValueAtTime(0.15, audioCtx.currentTime + 0.05);
                gain1.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.3);
                osc1.start(audioCtx.currentTime);
                osc1.stop(audioCtx.currentTime + 0.35);

                // Note 2 (A5) after 100ms
                setTimeout(() => {
                    const osc2 = audioCtx.createOscillator();
                    const gain2 = audioCtx.createGain();
                    osc2.connect(gain2);
                    gain2.connect(audioCtx.destination);
                    osc2.type = 'sine';
                    osc2.frequency.value = 880.00;
                    gain2.gain.setValueAtTime(0, audioCtx.currentTime);
                    gain2.gain.linearRampToValueAtTime(0.2, audioCtx.currentTime + 0.05);
                    gain2.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);
                    osc2.start(audioCtx.currentTime);
                    osc2.stop(audioCtx.currentTime + 0.55);
                }, 100);
            } catch (e) {
                console.log('Audio Context failed or blocked:', e);
            }
        }

        // Fast Polling (every 1.5 seconds)
        setInterval(function () {
            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                // Check if status changed
                if (data.status !== currentStatus) {
                    // Trigger interactive celebration if transitioning to paid or completed
                    if ((data.status === 'paid' || data.status === 'completed') && (currentStatus === 'pending')) {
                        playSuccessSound();
                        if (window.confetti) {
                            confetti({
                                particleCount: 150,
                                spread: 80,
                                origin: { y: 0.6 }
                            });
                        }
                        // Hide payment instruction box dynamically
                        const paymentBox = document.getElementById('payment-instruction-container');
                        if (paymentBox) {
                            paymentBox.style.transition = 'all 0.5s ease';
                            paymentBox.style.opacity = '0';
                            setTimeout(() => paymentBox.remove(), 500);
                        }
                    }
                    currentStatus = data.status;
                }

                const badge     = document.getElementById('main-order-status-badge');
                const badgeText = document.getElementById('main-order-status-text');
                const receipt   = document.getElementById('receipt-order-status');

                if (badgeText) badgeText.textContent = data.status_label;
                if (badge) {
                    const base = 'inline-flex items-center gap-2 px-4 py-2 rounded-full text-[10px] md:text-xs font-black uppercase tracking-widest border ';
                    badge.className = base + (statusBadgeClasses[data.status] || 'bg-gray-500/10 border-gray-500/30 text-gray-300');
                }
                if (receipt) {
                    receipt.textContent = data.status_label;
                    if (data.status === 'cancelled')         receipt.className = 'font-black text-[10px] uppercase text-red-600';
                    else if (data.status === 'completed' || data.status === 'paid') receipt.className = 'font-black text-[10px] uppercase text-emerald-600';
                    else if (data.status === 'shipped')      receipt.className = 'font-black text-[10px] uppercase text-sky-600';
                    else                                     receipt.className = 'font-black text-[10px] uppercase text-amber-600';
                }

                // Dynamically update timeline progress
                const steps = ['pending', 'paid', 'shipped', 'completed'];
                const idx = steps.indexOf(data.status);
                if (idx !== -1) {
                    const progressLine = document.getElementById('progress-line');
                    if (progressLine) {
                        progressLine.style.width = `calc(${(idx / 3) * 100}% - 0px)`;
                    }
                    steps.forEach((stepName, stepIdx) => {
                        const circle = document.getElementById(`step-circle-${stepName}`);
                        const labelSpan = document.getElementById(`step-label-${stepName}`);
                        if (circle) {
                            if (stepIdx <= idx) {
                                circle.className = "w-8 h-8 md:w-10 md:h-10 rounded-full border-2 flex items-center justify-center transition-all duration-500 bg-brand-cyan border-brand-cyan text-black shadow-[0_0_15px_rgba(78,205,196,0.4)]";
                                if (labelSpan) labelSpan.className = "text-[8px] md:text-[10px] font-bold uppercase tracking-wide text-center text-white";
                            } else {
                                circle.className = "w-8 h-8 md:w-10 md:h-10 rounded-full border-2 flex items-center justify-center transition-all duration-500 bg-[#16181d] border-gray-700 text-gray-600";
                                if (labelSpan) labelSpan.className = "text-[8px] md:text-[10px] font-bold uppercase tracking-wide text-center text-gray-600";
                            }
                        }
                    });
                }
            })
            .catch(err => console.error('Gagal memperbarui status:', err));
        }, 1500);
    });

    function updateShowDateTime() {
        const now = new Date();
        const day = String(now.getDate()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const year = now.getFullYear();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        const el = document.getElementById('show-date-time');
        if (el) {
            el.textContent = `${day}/${month}/${year} ${hours}:${minutes}`;
        }
    }
    updateShowDateTime();

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
                alert('Simulasi pembayaran berhasil! Status pesanan akan terupdate dalam 1-2 detik.');
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

@if(isset($snapToken) && $snapToken)
@php
    $snapJsUrl = \App\Models\Setting::getGlobal()->midtrans_is_production 
        ? 'https://app.midtrans.com/snap/snap.js' 
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
    $clientKey = \App\Models\Setting::getGlobal()->midtrans_client_key ?: config('services.midtrans.client_key');
@endphp
<script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
<script>
    const payButton = document.getElementById('pay-button');
    if (payButton) {
        payButton.addEventListener('click', function () {
            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function(result){
                    alert("Pembayaran berhasil!");
                    window.location.reload();
                },
                onPending: function(result){
                    alert("Menunggu pembayaran!");
                    window.location.reload();
                },
                onError: function(result){
                    alert("Pembayaran gagal!");
                },
                onClose: function(){
                    alert('Anda menutup popup tanpa menyelesaikan pembayaran.');
                }
            });
        });
    }
</script>
@endif

@endsection
