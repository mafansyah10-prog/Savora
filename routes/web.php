<?php

use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PakasirWebhookController;
use App\Http\Controllers\ProfileController;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/search/live', function (Request $request) {
    $q = $request->input('q');
    if (! $q) {
        return response()->json([]);
    }
    $products = Product::where('is_active', true)
        ->where(function ($query) use ($q) {
            $query->where('name', 'like', '%'.$q.'%')
                ->orWhere('description', 'like', '%'.$q.'%')
                ->orWhereHas('category', function ($cq) use ($q) {
                    $cq->where('name', 'like', '%'.$q.'%');
                });
        })
        ->limit(6)
        ->get()
        ->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => number_format($product->price, 0, ',', '.'),
                'discount_price' => $product->discount_price ? number_format($product->discount_price, 0, ',', '.') : null,
                'image_url' => asset('storage/'.$product->image),
                'url' => route('product.show', $product->slug),
                'category' => $product->category?->name,
            ];
        });

    return response()->json($products);
});

Route::get('/', function (Request $request) {
    $search = $request->search;
    $categoryFilter = $request->category;
    $sortFilter = $request->filter; // new, popular

    $categories = Category::all();
    $banners = Banner::where('is_active', true)->get();

    $query = Product::where('is_active', true)->with('category');

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhereHas('category', function ($cq) use ($search) {
                    $cq->where('name', 'like', '%'.$search.'%');
                });
        });
    }

    if ($categoryFilter) {
        $query->whereHas('category', function ($q) use ($categoryFilter) {
            $q->where('slug', $categoryFilter);
        });
    }

    if ($sortFilter === 'new') {
        $query->where('is_new_manual', true)->latest();
    } elseif ($sortFilter === 'popular') {
        $query->where('is_popular_manual', true)->orderBy('sales_count', 'desc');
    } else {
        $query->latest();
    }

    $products = $query->get();

    return view('welcome', compact('categories', 'products', 'banners', 'search', 'sortFilter'));
})->name('home');

Route::get('/kategori', function () {
    $categories = Category::withCount('products')->get();

    return view('categories.index', compact('categories'));
})->name('categories.index');

Route::get('/jurnal', function () {
    $posts = Post::whereNotNull('published_at')->latest()->get();

    return view('posts.index', compact('posts'));
})->name('posts.index');

Route::get('/jurnal/{slug}', function ($slug) {
    $post = Post::where('slug', $slug)->firstOrFail();

    return view('posts.show', compact('post'));
})->name('posts.show');

Route::get('/produk/{slug}', function ($slug) {
    $product = Product::where('slug', $slug)->firstOrFail();

    return view('products.show', compact('product'));
})->name('product.show');

Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang/tambah', [CartController::class, 'add'])->name('cart.add');
Route::post('/keranjang/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/keranjang/hapus', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/keranjang/checkout', function () {
    return redirect()->route('cart.index');
});
Route::post('/keranjang/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::post('/keranjang/voucher/apply', [CartController::class, 'applyVoucher'])->name('cart.voucher.apply');
Route::post('/keranjang/voucher/remove', [CartController::class, 'removeVoucher'])->name('cart.voucher.remove');
Route::post('/keranjang/zona-kirim', [CartController::class, 'setShippingZone'])->name('cart.shipping.set');

Route::middleware('auth')->group(function () {
    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/pesanan/{order}/cetak', function (Order $order) {
        return view('orders.print', compact('order'));
    })->name('orders.print');
    Route::post('/pesanan/{order}/bukti-pembayaran', [OrderController::class, 'uploadPaymentProof'])->name('orders.upload_proof');
    Route::post('/pesanan/{order}/bukti-penerimaan', [OrderController::class, 'uploadDeliveryProof'])->name('orders.upload_delivery_proof');
    Route::get('/keranjang/sukses/{order}', [CartController::class, 'success'])->name('cart.success');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    // Dapatkan semua pangkat yang sama atau di bawah pangkat user sekarang
    $rankKeys = array_keys(User::$ranks);
    $userRankIndex = array_search($user->rank ?? 'reguler', $rankKeys);
    $eligibleRanks = array_slice($rankKeys, 0, $userRankIndex + 1);

    $vouchers = Voucher::where('is_active', true)
        ->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        })
        ->where(function ($q) use ($eligibleRanks) {
            $q->whereNull('rank')
                ->orWhereIn('rank', $eligibleRanks);
        })
        ->latest()
        ->get();

    return view('dashboard', [
        'rankInfo' => User::$ranks[$user->rank] ?? User::$ranks['reguler'],
        'allRanks' => User::$ranks,
        'nextRankInfo' => $user->next_rank_info,
        'progress' => $user->rank_progress,
        'remaining' => $user->remaining_for_next_rank,
        'vouchers' => $vouchers,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/webhook/pakasir', [PakasirWebhookController::class, 'handleNotification']);
Route::post('/webhook/midtrans', [MidtransWebhookController::class, 'handleNotification']);

Route::get('/logout-cepat', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect('/admin');
});

Route::get('/user/check-status', function (Request $request) {
    if (!Auth::check()) {
        return response()->json(['blocked' => false, 'authenticated' => false]);
    }
    return response()->json(['blocked' => false, 'authenticated' => true]);
})->name('user.check-status');

Route::post('/ai-chat', [AiAssistantController::class, 'chat'])->name('ai.chat');
Route::get('/ai-chat/poll', [AiAssistantController::class, 'poll'])->name('ai.chat.poll');
Route::post('/ai-chat/send', [AiAssistantController::class, 'sendLive'])->name('ai.chat.send');

require __DIR__.'/auth.php';
