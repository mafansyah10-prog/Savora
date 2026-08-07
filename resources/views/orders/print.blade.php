<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                background: white;
                color: black;
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
            .receipt-container {
                width: 80mm;
                margin: 0;
                box-shadow: none;
                border: none;
                padding: 10px;
            }
        }
        body {
            background-color: #0f1115;
            color: #ffffff;
            font-family: 'Courier New', Courier, monospace;
        }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen py-6 px-4">
    <div class="no-print mb-6 flex gap-3">
        <button onclick="window.print()" class="px-6 py-2.5 bg-brand-cyan hover:bg-teal-400 text-black rounded-xl shadow-lg transition font-sans text-xs font-bold uppercase tracking-wider">
            Cetak Struk
        </button>
        <button onclick="window.close()" class="px-6 py-2.5 bg-gray-800 hover:bg-gray-700 text-white rounded-xl shadow-lg transition font-sans text-xs font-bold uppercase tracking-wider">
            Tutup Halaman
        </button>
    </div>

    <div class="receipt-container bg-white text-black p-6 rounded-2xl shadow-2xl border border-gray-200 w-full max-w-[80mm] overflow-hidden">
        <div class="text-center mb-4">
            <h2 class="font-bold text-lg uppercase tracking-wider">{{ \App\Models\Setting::getGlobal()->store_name }}</h2>
            <p class="text-[9px] text-gray-500 mt-0.5">{{ \App\Models\Setting::getGlobal()->store_address }}</p>
            <p class="text-[9px] text-gray-500">WhatsApp: +{{ \App\Models\Setting::getGlobal()->whatsapp_number }}</p>
            <div class="border-b border-dashed border-gray-300 my-3"></div>
        </div>
        
        <div class="text-[11px] space-y-1 mb-4">
            <div class="flex justify-between">
                <span>No. Struk:</span>
                <span class="font-bold">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tanggal:</span>
                <span id="print-date-time">{{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pelanggan:</span>
                <span class="font-semibold text-right truncate max-w-[150px]">{{ $order->customer_name }}</span>
            </div>
            <div class="flex justify-between">
                <span>Telepon:</span>
                <span>{{ $order->customer_phone }}</span>
            </div>
            <div class="flex justify-between">
                <span>Metode Bayar:</span>
                <span class="uppercase font-semibold">{{ str_replace('_', ' ', $order->payment_method ?? 'transfer_bank') }}</span>
            </div>
            @if($order->notes)
                <div class="text-[10px] mt-1.5 text-gray-600">
                    <span class="font-semibold">Catatan:</span> {{ $order->notes }}
                </div>
            @endif
        </div>
        
        <div class="border-b border-dashed border-gray-300 my-3"></div>
        
        <!-- Item List -->
        <div class="text-[11px] space-y-2.5 mb-4">
            @if($order->items && count($order->items) > 0)
                @foreach($order->items as $item)
                    @php
                        $product = \App\Models\Product::find($item['product_id']);
                        $hasDiscount = $product && $item['price'] < $product->price;
                    @endphp
                    <div>
                        <div class="flex justify-between font-semibold">
                            <span class="truncate max-w-[190px]">{{ $item['name'] }}</span>
                            <span>Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                        </div>
                        <div class="text-[9px] text-gray-500">
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
                <div class="text-center py-2 text-gray-400 italic">Tidak ada detail item</div>
            @endif
        </div>
        
        <div class="border-b border-dashed border-gray-300 my-3"></div>
        
        <div class="text-[11px] space-y-1.5 mb-4">
            <div class="flex justify-between">
                <span>Total Item:</span>
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
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            @if($totalProductDiscount > 0)
                <div class="flex justify-between text-emerald-600 font-semibold">
                    <span>Diskon Produk:</span>
                    <span>-Rp {{ number_format($totalProductDiscount, 0, ',', '.') }}</span>
                </div>
            @endif
            @if($order->shipping_zone_name)
                <div class="flex justify-between">
                    <span>Ongkir ({{ $order->shipping_zone_name }}):</span>
                    <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
            @endif
            @if($order->voucher_code)
                <div class="flex justify-between text-red-600 font-semibold">
                    <span>Voucher ({{ $order->voucher_code }}):</span>
                    <span>-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between text-xs font-bold border-t border-dashed border-gray-300 pt-1.5 mt-1.5">
                <span>TOTAL BAYAR:</span>
                <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>
        
        <div class="border-b border-dashed border-gray-300 my-3"></div>
        
        <div class="text-center text-[9px] text-gray-500 space-y-1 mt-4">
            <p class="font-semibold text-black">TERIMA KASIH ATAS KUNJUNGAN ANDA</p>
            <p>Savora - Artisan Bakery & Food</p>
        </div>
    </div>

    <script>
        function updateDateTime() {
            const now = new Date();
            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            
            const el = document.getElementById('print-date-time');
            if (el) {
                el.textContent = `${day}/${month}/${year} ${hours}:${minutes}`;
            }
        }
        updateDateTime();

        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
