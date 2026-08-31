<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingZone;
use App\Models\User;
use App\Models\Voucher;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Tampilkan halaman keranjang.
     */
    public function index()
    {
        $cart = session()->get('cart', []);

        $subtotal = 0;
        $originalSubtotal = 0;
        $productDiscount = 0;

        if (! empty($cart)) {
            $productIds = collect($cart)->map(function ($item, $key) {
                return $item['product_id'] ?? (is_numeric($key) ? (int) $key : null);
            })->filter()->unique()->toArray();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
            foreach ($cart as $cartKey => $item) {
                $pId = $item['product_id'] ?? (is_numeric($cartKey) ? (int) $cartKey : null);
                $product = $products->get($pId);
                if ($product) {
                    $qty = $item['quantity'];

                    // Hitung total extra dari opsi
                    $extraPrice = 0;
                    $options = $item['options'] ?? [];

                    // Spiciness extra price
                    if (! empty($options['spiciness_level']) && ! empty($product->spiciness_levels)) {
                        $spicinessObj = collect($product->spiciness_levels)->first(function ($level) use ($options) {
                            $levelName = is_array($level) ? ($level['name'] ?? '') : $level;

                            return $levelName === $options['spiciness_level'];
                        });
                        if ($spicinessObj && is_array($spicinessObj)) {
                            $extraPrice += (float) ($spicinessObj['price'] ?? 0);
                        }
                    }

                    // Sauce extra price
                    if (! empty($options['sauce']) && ! empty($product->sauces)) {
                        $sauceObj = collect($product->sauces)->firstWhere('name', $options['sauce']);
                        if ($sauceObj) {
                            $extraPrice += (float) ($sauceObj['price'] ?? 0);
                        }
                    }

                    // Toppings extra price
                    if (! empty($options['toppings']) && ! empty($product->toppings)) {
                        foreach ($options['toppings'] as $topName) {
                            $topObj = collect($product->toppings)->firstWhere('name', $topName);
                            if ($topObj) {
                                $extraPrice += (float) ($topObj['price'] ?? 0);
                            }
                        }
                    }

                    // Additionals extra price
                    if (! empty($options['additionals']) && ! empty($product->additionals)) {
                        foreach ($options['additionals'] as $addName) {
                            $addObj = collect($product->additionals)->firstWhere('name', $addName);
                            if ($addObj) {
                                $extraPrice += (float) ($addObj['price'] ?? 0);
                            }
                        }
                    }

                    $originalPrice = (float) $product->price + $extraPrice;
                    $sellingPrice = (float) $product->selling_price + $extraPrice;

                    $originalSubtotal += $originalPrice * $qty;
                    $subtotal += $sellingPrice * $qty;

                    if ($product->hasDiscount()) {
                        $productDiscount += ((float) $product->price - (float) $product->selling_price) * $qty;
                    }
                } else {
                    $subtotal += $item['price'] * $item['quantity'];
                    $originalSubtotal += $item['price'] * $item['quantity'];
                }
            }
        }

        // Shipping zone
        $shippingZones = ShippingZone::active()->orderBy('name')->get();
        $selectedZoneId = session()->get('shipping_zone_id');
        $selectedZone = $selectedZoneId ? ShippingZone::find($selectedZoneId) : null;
        $shippingCost = $selectedZone ? (float) $selectedZone->cost : 0;

        // Voucher
        $voucher = null;
        $discount = 0;

        if (session()->has('voucher')) {
            $voucherCode = session()->get('voucher');
            $voucher = Voucher::where('code', $voucherCode)->first();

            if ($voucher && $voucher->isValidForAmount($subtotal)) {
                $discount = $voucher->calculateDiscount($subtotal);
            } else {
                session()->forget('voucher');
                session()->flash('error', 'Voucher yang Anda gunakan sudah tidak valid atau tidak memenuhi minimal belanja.');
            }
        }

        $total = $subtotal - $discount + $shippingCost;

        $vouchers = collect();
        if (auth()->check()) {
            $user = auth()->user();
            $rankKeys = array_keys(User::$ranks);
            $userRankIndex = array_search($user->rank ?? 'reguler', $rankKeys);
            $eligibleRanks = array_slice($rankKeys, 0, $userRankIndex + 1);

            $vouchers = Voucher::where('is_active', true)
                ->where('is_hidden', false)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->where(function ($q) use ($eligibleRanks) {
                    $q->whereNull('rank')
                        ->orWhereIn('rank', $eligibleRanks);
                })
                ->where(function ($q) use ($user) {
                    $q->whereNull('user_id')
                        ->orWhere('user_id', $user->id);
                })
                ->latest()
                ->get();
        } else {
            $vouchers = Voucher::where('is_active', true)
                ->where('is_hidden', false)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->whereNull('rank')
                ->whereNull('user_id')
                ->latest()
                ->get();
        }

        // Filter out vouchers that have exceeded usage limits
        $vouchers = $vouchers->filter(function (Voucher $v) {
            if ($v->usage_limit !== null && $v->orders()->count() >= $v->usage_limit) {
                return false;
            }
            if (auth()->check()) {
                $limitPerUser = $v->limit_per_user;
                if ($limitPerUser === null && (str_starts_with($v->code, 'BARU-') || $v->user_id !== null)) {
                    $limitPerUser = 1;
                }
                if ($limitPerUser !== null) {
                    $userUsageCount = auth()->user()->orders()
                        ->where('voucher_code', $v->code)
                        ->where('status', '!=', 'cancelled')
                        ->count();
                    if ($userUsageCount >= $limitPerUser) {
                        return false;
                    }
                }
            }

            return true;
        });

        return view('cart.index', compact('cart', 'subtotal', 'originalSubtotal', 'productDiscount', 'total', 'voucher', 'discount', 'shippingZones', 'selectedZone', 'shippingCost', 'vouchers'));
    }

    /**
     * Tambah produk ke keranjang.
     */
    public function add(Request $request)
    {
        if (! Setting::getGlobal()->isStoreOpen()) {
            $msg = 'Maaf, toko kami saat ini sedang tutup. Pemesanan tidak dapat dilakukan.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        $product = Product::findOrFail($request->product_id);

        // --- Stock check ---
        if ($product->stock !== null && $product->stock <= 0) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => "Maaf, stok \"{$product->name}\" sudah habis."], 422);
            }

            return redirect()->back()->with('error', "Maaf, stok \"{$product->name}\" sudah habis.");
        }

        $selectedOptions = [
            'spiciness_level' => $request->input('spiciness_level'),
            'sauce' => $request->input('sauce'),
            'toppings' => $request->input('toppings', []),
            'additionals' => $request->input('additionals', []),
        ];

        // Hitung total extra dari opsi
        $extraPrice = 0;

        // Spiciness price
        if ($selectedOptions['spiciness_level'] && ! empty($product->spiciness_levels)) {
            $spicinessObj = collect($product->spiciness_levels)->first(function ($level) use ($selectedOptions) {
                $levelName = is_array($level) ? ($level['name'] ?? '') : $level;

                return $levelName === $selectedOptions['spiciness_level'];
            });
            if ($spicinessObj && is_array($spicinessObj)) {
                $extraPrice += (float) ($spicinessObj['price'] ?? 0);
            }
        }

        // Sauce price
        if ($selectedOptions['sauce'] && ! empty($product->sauces)) {
            $sauceObj = collect($product->sauces)->firstWhere('name', $selectedOptions['sauce']);
            if ($sauceObj) {
                $extraPrice += (float) ($sauceObj['price'] ?? 0);
            }
        }

        // Toppings price
        if (! empty($selectedOptions['toppings']) && ! empty($product->toppings)) {
            foreach ($selectedOptions['toppings'] as $topName) {
                $topObj = collect($product->toppings)->firstWhere('name', $topName);
                if ($topObj) {
                    $extraPrice += (float) ($topObj['price'] ?? 0);
                }
            }
        }

        // Additionals price
        if (! empty($selectedOptions['additionals']) && ! empty($product->additionals)) {
            foreach ($selectedOptions['additionals'] as $addName) {
                $addObj = collect($product->additionals)->firstWhere('name', $addName);
                if ($addObj) {
                    $extraPrice += (float) ($addObj['price'] ?? 0);
                }
            }
        }

        $itemPrice = (float) $product->selling_price + $extraPrice;
        $hasOptions = ! empty($selectedOptions['spiciness_level']) ||
                      ! empty($selectedOptions['sauce']) ||
                      ! empty($selectedOptions['toppings']) ||
                      ! empty($selectedOptions['additionals']);

        $cartKey = $hasOptions ? ($product->id.'_'.md5(serialize($selectedOptions))) : $product->id;

        $cart = session()->get('cart', []);
        $currentQty = collect($cart)->where('product_id', $product->id)->sum('quantity');

        // Prevent exceeding available stock
        if ($product->stock !== null && ($currentQty + 1) > $product->stock) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => "Stok \"{$product->name}\" tidak mencukupi. Tersisa {$product->stock} item."], 422);
            }

            return redirect()->back()->with('error', "Stok \"{$product->name}\" tidak mencukupi. Tersisa {$product->stock} item.");
        }

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity']++;
        } else {
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'price' => $itemPrice,
                'image' => $product->image,
                'stock' => $product->stock,
                'category' => $product->category->name,
                'options' => $selectedOptions,
            ];
        }

        session()->put('cart', $cart);
        $cartCount = count(session('cart', []));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke keranjang!',
                'cart_count' => $cartCount,
            ]);
        }

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    /**
     * Update jumlah produk di keranjang.
     */
    public function update(Request $request)
    {
        if ($request->id && isset($request->quantity)) {
            $cart = session()->get('cart');
            if (isset($cart[$request->id])) {
                $productId = $cart[$request->id]['product_id'] ?? (is_numeric($request->id) ? (int) $request->id : null);
                $product = Product::find($productId);

                // Hitung jumlah produk ini di keranjang dari item lain
                $otherQty = collect($cart)
                    ->forget($request->id)
                    ->filter(fn ($item, $key) => ($item['product_id'] ?? (is_numeric($key) ? (int) $key : null)) == $productId)
                    ->sum('quantity');

                if ($product && $product->stock !== null && ($otherQty + $request->quantity) > $product->stock) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Stok \"{$product->name}\" tidak mencukupi. Tersisa {$product->stock} item.",
                        ], 422);
                    }

                    return redirect()->back()->with('error', "Stok \"{$product->name}\" tidak mencukupi.");
                }

                $cart[$request->id]['quantity'] = max(1, (int) $request->quantity);
                session()->put('cart', $cart);
            }
        }
        if ($request->ajax() || $request->wantsJson()) {
            $cart = session()->get('cart', []);
            $subtotal = 0;
            $originalSubtotal = 0;
            $productDiscount = 0;
            $totalQty = 0;

            if (! empty($cart)) {
                $productIds = collect($cart)->map(function ($item, $key) {
                    return $item['product_id'] ?? (is_numeric($key) ? (int) $key : null);
                })->filter()->unique()->toArray();
                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
                foreach ($cart as $cartKey => $item) {
                    $totalQty += $item['quantity'];
                    $pId = $item['product_id'] ?? (is_numeric($cartKey) ? (int) $cartKey : null);
                    $product = $products->get($pId);
                    if ($product) {
                        $qty = $item['quantity'];

                        // Hitung total extra dari opsi
                        $extraPrice = 0;
                        $options = $item['options'] ?? [];

                        // Spiciness extra price
                        if (! empty($options['spiciness_level']) && ! empty($product->spiciness_levels)) {
                            $spicinessObj = collect($product->spiciness_levels)->first(function ($level) use ($options) {
                                $levelName = is_array($level) ? ($level['name'] ?? '') : $level;

                                return $levelName === $options['spiciness_level'];
                            });
                            if ($spicinessObj && is_array($spicinessObj)) {
                                $extraPrice += (float) ($spicinessObj['price'] ?? 0);
                            }
                        }

                        // Sauce
                        if (! empty($options['sauce']) && ! empty($product->sauces)) {
                            $sauceObj = collect($product->sauces)->firstWhere('name', $options['sauce']);
                            if ($sauceObj) {
                                $extraPrice += (float) ($sauceObj['price'] ?? 0);
                            }
                        }

                        // Toppings
                        if (! empty($options['toppings']) && ! empty($product->toppings)) {
                            foreach ($options['toppings'] as $topName) {
                                $topObj = collect($product->toppings)->firstWhere('name', $topName);
                                if ($topObj) {
                                    $extraPrice += (float) ($topObj['price'] ?? 0);
                                }
                            }
                        }

                        // Additionals
                        if (! empty($options['additionals']) && ! empty($product->additionals)) {
                            foreach ($options['additionals'] as $addName) {
                                $addObj = collect($product->additionals)->firstWhere('name', $addName);
                                if ($addObj) {
                                    $extraPrice += (float) ($addObj['price'] ?? 0);
                                }
                            }
                        }

                        $originalPrice = (float) $product->price + $extraPrice;
                        $sellingPrice = (float) $product->selling_price + $extraPrice;

                        $originalSubtotal += $originalPrice * $qty;
                        $subtotal += $sellingPrice * $qty;

                        if ($product->hasDiscount()) {
                            $productDiscount += ((float) $product->price - (float) $product->selling_price) * $qty;
                        }
                    } else {
                        $subtotal += $item['price'] * $item['quantity'];
                        $originalSubtotal += $item['price'] * $item['quantity'];
                    }
                }
            }

            // Shipping
            $selectedZoneId = session()->get('shipping_zone_id');
            $selectedZone = $selectedZoneId ? ShippingZone::find($selectedZoneId) : null;
            $shippingCost = $selectedZone ? (float) $selectedZone->cost : 0;

            // Voucher
            $discount = 0;
            $voucherApplied = false;
            $voucherCode = '';

            if (session()->has('voucher')) {
                $voucher = Voucher::where('code', session()->get('voucher'))->first();
                if ($voucher && $voucher->isValidForAmount($subtotal)) {
                    $discount = $voucher->calculateDiscount($subtotal);
                    $voucherApplied = true;
                    $voucherCode = $voucher->code;
                } else {
                    session()->forget('voucher');
                }
            }

            $finalTotal = $subtotal - $discount + $shippingCost;

            return response()->json([
                'success' => true,
                'subtotal' => $originalSubtotal,
                'subtotal_formatted' => 'Rp '.number_format($originalSubtotal, 0, ',', '.'),
                'total' => $originalSubtotal,
                'total_formatted' => 'Rp '.number_format($originalSubtotal, 0, ',', '.'),
                'product_discount' => $productDiscount,
                'product_discount_formatted' => 'Rp '.number_format($productDiscount, 0, ',', '.'),
                'total_qty' => $totalQty,
                'quantity' => isset($cart[$request->id]) ? $cart[$request->id]['quantity'] : 0,
                'voucher_applied' => $voucherApplied,
                'voucher_code' => $voucherCode,
                'voucher_name' => $voucherApplied && isset($voucher) ? $voucher->name : '',
                'discount' => $discount,
                'discount_formatted' => 'Rp '.number_format($discount, 0, ',', '.'),
                'shipping_cost' => $shippingCost,
                'shipping_formatted' => 'Rp '.number_format($shippingCost, 0, ',', '.'),
                'final_total' => $finalTotal,
                'final_total_formatted' => 'Rp '.number_format($finalTotal, 0, ',', '.'),
            ]);
        }

        return redirect()->back();
    }

    /**
     * Set zona pengiriman dari AJAX.
     */
    public function setShippingZone(Request $request)
    {
        $zoneId = $request->input('zone_id');

        if (! $zoneId) {
            session()->forget('shipping_zone_id');
            $cart = session()->get('cart', []);
            $subtotal = array_reduce($cart, fn ($c, $i) => $c + ($i['price'] * $i['quantity']), 0);
            $discount = 0;
            if (session()->has('voucher')) {
                $voucher = Voucher::where('code', session()->get('voucher'))->first();
                if ($voucher && $voucher->isValidForAmount($subtotal)) {
                    $discount = $voucher->calculateDiscount($subtotal);
                }
            }

            return response()->json([
                'success' => true,
                'shipping_cost' => 0,
                'shipping_formatted' => 'Gratis',
                'final_total' => $subtotal - $discount,
                'final_total_formatted' => 'Rp '.number_format($subtotal - $discount, 0, ',', '.'),
            ]);
        }

        $zone = ShippingZone::where('id', $zoneId)->where('is_active', true)->first();

        if (! $zone) {
            return response()->json(['success' => false, 'message' => 'Wilayah tidak ditemukan.'], 422);
        }

        session()->put('shipping_zone_id', $zone->id);

        $cart = session()->get('cart', []);
        $subtotal = array_reduce($cart, fn ($c, $i) => $c + ($i['price'] * $i['quantity']), 0);
        $discount = 0;

        if (session()->has('voucher')) {
            $voucher = Voucher::where('code', session()->get('voucher'))->first();
            if ($voucher && $voucher->isValidForAmount($subtotal)) {
                $discount = $voucher->calculateDiscount($subtotal);
            }
        }

        $shippingCost = (float) $zone->cost;
        $finalTotal = $subtotal - $discount + $shippingCost;

        return response()->json([
            'success' => true,
            'zone_name' => $zone->name,
            'shipping_cost' => $shippingCost,
            'shipping_formatted' => 'Rp '.number_format($shippingCost, 0, ',', '.'),
            'final_total' => $finalTotal,
            'final_total_formatted' => 'Rp '.number_format($finalTotal, 0, ',', '.'),
        ]);
    }

    /**
     * Hapus produk dari keranjang.
     */
    public function remove(Request $request)
    {
        if ($request->id) {
            $cart = session()->get('cart');
            if (isset($cart[$request->id])) {
                unset($cart[$request->id]);
                session()->put('cart', $cart);
            }
        }

        return redirect()->back();
    }

    /**
     * Proses checkout dan simpan ke database.
     */
    public function checkout(Request $request)
    {
        if (! Setting::getGlobal()->isStoreOpen()) {
            return redirect()->back()->with('error', 'Maaf, toko kami saat ini sedang tutup. Silakan melakukan checkout kembali setelah toko buka.');
        }

        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk melakukan pemesanan.');
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|regex:/^[0-9]+$/',
            'shipping_method' => 'required|in:delivery,pickup',
            'shipping_address' => 'required_if:shipping_method,delivery',
            'pickup_time' => [
                'required_if:shipping_method,pickup',
                'nullable',
                'date_format:H:i:s',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('shipping_method') === 'pickup' && $value) {
                        $currentTime = now()->format('H:i:s');
                        if ($value < $currentTime) {
                            $fail('Waktu pengambilan tidak boleh sudah terlewat (di masa lalu).');
                        }
                    }
                }
            ],
        ], [
            'customer_phone.regex' => 'Nomor telepon hanya boleh berisi angka.',
            'shipping_address.required_if' => 'Alamat pengiriman wajib diisi untuk metode Kirim (Delivery).',
            'pickup_time.required_if' => 'Waktu pengambilan wajib diisi untuk metode Pickup di Outlet.',
            'pickup_time.date_format' => 'Format waktu pengambilan harus jam:menit:detik (HH:MM:SS).',
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->back()->with('error', 'Keranjang Anda kosong!');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        // Shipping method and zone
        $shippingMethod = $request->input('shipping_method', 'delivery');
        $pickupTime = null;
        if ($shippingMethod === 'pickup') {
            $shippingCost = 0;
            $shippingZoneName = 'Pickup';
            $shippingAddress = 'Ambil di Toko (Pickup)';
            $pickupTime = $request->input('pickup_time');
        } else {
            $selectedZoneId = session()->get('shipping_zone_id');
            $selectedZone = $selectedZoneId ? ShippingZone::find($selectedZoneId) : null;
            $shippingCost = $selectedZone ? (float) $selectedZone->cost : 0;
            $shippingZoneName = $selectedZone ? $selectedZone->name : null;
            $shippingAddress = $request->shipping_address;
        }

        // Apply voucher if any
        $voucherCode = null;
        $discount = 0;

        if (session()->has('voucher')) {
            $voucher = Voucher::where('code', session()->get('voucher'))->first();
            if ($voucher && $voucher->isValidForAmount($subtotal)) {
                $voucherCode = $voucher->code;
                $discount = $voucher->calculateDiscount($subtotal);
            }
        }

        // Siapkan data items untuk disimpan ke order
        $items = [];
        foreach ($cart as $cartKey => $item) {
            $productId = $item['product_id'] ?? (is_numeric($cartKey) ? (int) $cartKey : null);
            $items[] = [
                'product_id' => $productId,
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'options' => $item['options'] ?? null,
            ];
        }

        $totalAmount = $subtotal - $discount + $shippingCost;

        $order = Order::create([
            'user_id' => auth()->id(),
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'shipping_method' => $shippingMethod,
            'shipping_address' => $shippingAddress,
            'pickup_time' => $pickupTime,
            'notes' => $request->notes,
            'shipping_zone_name' => $shippingZoneName,
            'shipping_cost' => $shippingCost,
            'payment_method' => $request->payment_method ?? 'transfer_bank',
            'voucher_code' => $voucherCode,
            'discount_amount' => $discount,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'items' => $items,
            'stock_deducted' => false,
        ]);

        // Increment sales_count for each product
        foreach ($cart as $cartKey => $item) {
            $productId = $item['product_id'] ?? (is_numeric($cartKey) ? (int) $cartKey : null);
            if ($productId) {
                Product::where('id', $productId)->increment('sales_count', $item['quantity']);
            }
        }

        session()->forget('cart');
        session()->forget('voucher');
        session()->forget('shipping_zone_id');

        return redirect()->route('cart.success', $order);
    }

    public function success(Order $order)
    {
        abort_if($order->user_id != auth()->id(), 403);
        $snapToken = MidtransService::getSnapToken($order);

        return view('cart.success', compact('order', 'snapToken'));
    }

    /**
     * Terapkan voucher belanja.
     */
    public function applyVoucher(Request $request)
    {
        $code = strtoupper($request->voucher_code);
        $voucher = Voucher::where('code', $code)->first();

        if (! $voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Kode voucher tidak ditemukan.',
            ], 422);
        }

        $cart = session()->get('cart', []);
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        if (! $voucher->isValidForAmount($subtotal)) {
            $limitPerUser = $voucher->limit_per_user;
            if ($limitPerUser === null && (str_starts_with($voucher->code, 'BARU-') || $voucher->user_id !== null)) {
                $limitPerUser = 1;
            }
            if (auth()->check() && $limitPerUser !== null && auth()->user()->orders()->where('voucher_code', $voucher->code)->where('status', '!=', 'cancelled')->count() >= $limitPerUser) {
                $errorMsg = 'Kamu sudah mencapai batas pemakaian voucher ini.';
            } elseif ($voucher->expires_at !== null && $voucher->expires_at->isPast()) {
                $errorMsg = 'Voucher ini sudah kadaluarsa. Cek S&K';
            } elseif ($subtotal < $voucher->min_order_amount) {
                $errorMsg = 'Kamu belum memenuhi minimum transaksi voucher ini. Cek S&K';
            } elseif ($voucher->rank !== null) {
                if (! auth()->check()) {
                    $errorMsg = 'Silakan login terlebih dahulu untuk menggunakan voucher ini. Cek S&K';
                } else {
                    $rankLabel = User::$ranks[$voucher->rank]['label'] ?? $voucher->rank;
                    $errorMsg = 'Pangkat loyalitas kamu belum memenuhi syarat untuk menggunakan voucher ini. Cek S&K';
                }
            } elseif ($voucher->usage_limit !== null && $voucher->orders()->count() >= $voucher->usage_limit) {
                $errorMsg = 'Batas maksimal penggunaan voucher ini sudah habis. Cek S&K';
            } elseif ($subtotal <= $voucher->calculateDiscount($subtotal)) {
                $errorMsg = 'Total belanja kamu harus di atas nilai potongan voucher ini. Cek S&K';
            } else {
                $errorMsg = 'Voucher tidak aktif saat ini. Cek S&K';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMsg,
            ], 422);
        }

        session()->put('voucher', $voucher->code);
        $discount = $voucher->calculateDiscount($subtotal);

        // Include shipping in final total
        $selectedZoneId = session()->get('shipping_zone_id');
        $selectedZone = $selectedZoneId ? ShippingZone::find($selectedZoneId) : null;
        $shippingCost = $selectedZone ? (float) $selectedZone->cost : 0;
        $finalTotal = $subtotal - $discount + $shippingCost;

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil digunakan!',
            'voucher_code' => $voucher->code,
            'voucher_name' => $voucher->name,
            'discount' => $discount,
            'discount_formatted' => 'Rp '.number_format($discount, 0, ',', '.'),
            'final_total' => $finalTotal,
            'final_total_formatted' => 'Rp '.number_format($finalTotal, 0, ',', '.'),
        ]);
    }

    /**
     * Hapus voucher belanja.
     */
    public function removeVoucher()
    {
        session()->forget('voucher');

        $cart = session()->get('cart', []);
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $selectedZoneId = session()->get('shipping_zone_id');
        $selectedZone = $selectedZoneId ? ShippingZone::find($selectedZoneId) : null;
        $shippingCost = $selectedZone ? (float) $selectedZone->cost : 0;
        $finalTotal = $subtotal + $shippingCost;

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil dihapus.',
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'total_formatted' => 'Rp '.number_format($subtotal, 0, ',', '.'),
            'shipping_cost' => $shippingCost,
            'shipping_formatted' => $shippingCost > 0 ? 'Rp '.number_format($shippingCost, 0, ',', '.') : 'Gratis',
            'final_total' => $finalTotal,
            'final_total_formatted' => 'Rp '.number_format($finalTotal, 0, ',', '.'),
        ]);
    }
}
