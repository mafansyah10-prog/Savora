<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Voucher;
use App\Models\SupportSession;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AiAssistantController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
            'session_token' => 'required|string|max:100',
        ]);

        $userMessage = trim($request->input('message'));
        $chatHistory = $request->input('history', []);
        $sessionToken = $request->input('session_token');

        // Check if there is an active support session for this token
        $activeSession = SupportSession::where('session_token', $sessionToken)
            ->whereIn('status', ['pending', 'active'])
            ->first();

        if ($activeSession) {
            // Already in live chat mode, save message directly
            $msg = SupportMessage::create([
                'support_session_id' => $activeSession->id,
                'sender' => 'user',
                'message' => $userMessage,
            ]);

            // Set status to pending to alert the admin
            $activeSession->update(['status' => 'pending']);

            return response()->json([
                'response' => null, // Frontend will understand it's in live chat mode and just wait for poll
                'live_chat' => true
            ]);
        }

        // Trigger Live Chat manually (Customer sends "9" or "Hubungi Admin")
        if ($userMessage === '9' || strtolower($userMessage) === 'hubungi admin') {
            if (!Auth::check()) {
                return response()->json([
                    'response' => "Mohon maaf, Anda harus [Masuk/Login](/login) ke akun Anda terlebih dahulu untuk dapat menghubungi Admin via Live Chat. Silakan [Masuk](/login) atau [Daftar](/register) terlebih dahulu. 😊",
                    'live_chat' => false
                ]);
            }

            $session = SupportSession::updateOrCreate(
                ['session_token' => $sessionToken],
                [
                    'user_id' => Auth::id(),
                    'status' => 'pending'
                ]
            );

            // Log the initial message
            SupportMessage::create([
                'support_session_id' => $session->id,
                'sender' => 'user',
                'message' => 'Pelanggan meminta bantuan Live Chat.',
            ]);

            return response()->json([
                'response' => "Menghubungkan Anda ke Admin Savora... 📞\n\nSilakan sampaikan keluhan atau kendala pemesanan Anda di sini. Admin kami akan segera membalas pesan Anda sesegera mungkin di jendela chat ini.",
                'live_chat' => true
            ]);
        }

        $apiKey = config('services.gemini.key');

        if (!$apiKey) {
            return response()->json([
                'response' => "Halo! Terima kasih telah menghubungi Savora. 😊\n\nSaat ini, asisten AI \"Savvy\" belum sepenuhnya dikonfigurasi oleh administrator karena kunci API Gemini belum dipasang di file lingkungan (`.env`).\n\nSilakan tambahkan `GEMINI_API_KEY` pada konfigurasi server agar Savvy dapat membantu Anda mencari menu terbaik!"
            ]);
        }

        // Build context from database
        try {
            $categories = Category::all()->pluck('name')->toArray();
            
            $products = Product::where('is_active', true)
                ->with('category')
                ->get()
                ->map(function ($product) {
                    $price = 'Rp ' . number_format($product->selling_price, 0, ',', '.');
                    $originalPrice = $product->hasDiscount() ? 'Rp ' . number_format($product->price, 0, ',', '.') : null;
                    $discountStr = $originalPrice ? " (Diskon dari {$originalPrice})" : "";
                    
                    return "- {$product->name} (Kategori: {$product->category->name}) - Harga: {$price}{$discountStr}. Stok: " . ($product->stock ?? 'Tersedia') . ". Tautan detail menu: [Lihat Menu](/produk/{$product->slug})";
                })->toArray();

            $vouchers = Voucher::where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->get()
                ->map(function ($voucher) {
                    $minSpend = 'Rp ' . number_format($voucher->min_spend, 0, ',', '.');
                    $discountStr = $voucher->type === 'percent' ? "{$voucher->discount}%" : 'Rp ' . number_format($voucher->discount, 0, ',', '.');
                    return "- Kode: `{$voucher->code}` - Diskon {$discountStr} (Minimal belanja {$minSpend}) - {$voucher->description}";
                })->toArray();

            $globalSetting = Setting::getGlobal();
            $storeName = $globalSetting->store_name ?? 'Savora';
            $isOpen = $globalSetting->isStoreOpen() ? 'Buka' : 'Tutup';
        } catch (\Exception $e) {
            Log::error('Error gathering context for AI Chat: ' . $e->getMessage());
            $categories = [];
            $products = [];
            $vouchers = [];
            $storeName = 'Savora';
            $isOpen = 'Buka';
        }

        // Build system instruction
        $systemInstruction = "Anda adalah \"Savvy\", asisten virtual pintar, ramah, dan solutif untuk Savora, sebuah toko kuliner artisan rumahan.
Tugas Anda adalah menjawab pertanyaan pelanggan seputar Savora secara ramah, ringkas, dan jelas menggunakan bahasa Indonesia yang santun.

INFORMASI TOKO SAVORA:
- Nama Toko: {$storeName}
- Status Toko Saat Ini: {$isOpen} (Beri tahu pelanggan mereka juga dapat mengecek jam operasional langsung di bilah status paling atas website Savora).
- Pengiriman: Hanya pengiriman instan/kurir lokal agar makanan/minuman sampai dalam kondisi segar, hangat, dan higienis.
- Keunggulan: Dibuat manual secara higienis (homemade), menggunakan bahan-bahan alami premium tanpa pengawet atau pewarna buatan.

SISTEM LOYALITAS & PANGKAT PELANGGAN (RANK):
Savora memiliki sistem pangkat pelanggan setia. Semakin sering pelanggan melakukan pemesanan, pangkat mereka akan naik dan mendapatkan keuntungan voucher khusus:
1. Reguler: Pangkat awal untuk semua pelanggan.
2. Emas (Gold): Mendapatkan akses voucher khusus pangkat emas.
3. Platinum: Mendapatkan akses voucher pangkat platinum dengan keuntungan lebih besar.
4. Diamond: Pangkat tertinggi dengan diskon voucher paling besar khusus pangkat diamond.
Pelanggan dapat memantau status pangkat mereka di [Dashboard Akun](/dashboard).

KATEGORI KULINER YANG TERSEDIA:
" . implode("\n", array_map(fn($c) => "- " . $c, $categories)) . "

DAFTAR MENU AKTIF & HARGA SAAT INI:
" . implode("\n", $products) . "

PROMO & VOUCHER DISKON AKTIF:
" . implode("\n", $vouchers) . "

PANDUAN PERILAKU & FORMAT JAWABAN:
1. Jawablah dengan singkat, padat, dan langsung pada inti jawaban agar mudah dibaca di layar chat kecil (widget). Hindari penjelasan bertele-tele.
2. Gunakan emoji secara ramah (misalnya 😊, 🍰, 🥤, 📦) untuk membuat suasana santai dan bersahabat.
3. Selalu rekomendasikan menu spesifik yang relevan dengan pertanyaan user beserta harganya.
4. Ketika mereferensikan produk, gunakan format tautan markdown standar yang tepat agar bisa diklik oleh user di UI chat: [Lihat Menu](/produk/slug-produk). Jangan gunakan URL lengkap. Contoh: [Lihat menu Sourdough Bread](/produk/sourdough-bread).
5. Ketika menyarankan navigasi umum, gunakan tautan markdown berikut jika relevan:
   - Halaman Utama: [Halaman Utama](/)
   - Keranjang Belanja: [Keranjang Belanja](/keranjang)
   - Menu Kategori: [Lihat Kategori](/kategori)
   - Riwayat Pesanan: [Pesanan Saya](/pesanan)
   - Dashboard & Pangkat: [Dashboard Akun](/dashboard)
   - Jurnal & Tips Resep Savora: [Jurnal Savora](/jurnal)
6. Jika pelanggan ingin membaca tips kuliner, info produk, atau resep buatan Savora, ajak mereka untuk membaca [Jurnal Savora](/jurnal).
7. Jika pelanggan mengalami kendala saat pemesanan atau pembayaran, atau secara eksplisit meminta berbicara dengan admin/manusia, beri tahu mereka untuk mengetik angka \"9\" atau mengetik \"Hubungi Admin\" untuk langsung terhubung dengan admin panel live chat kami.
8. Jika ditanya mengenai cara pembayaran atau checkout, jelaskan bahwa setelah memasukkan menu ke [Keranjang Belanja](/keranjang), mereka dapat melakukan checkout dan melakukan pembayaran online secara otomatis menggunakan Midtrans (Mendukung e-wallet, transfer bank, dll.).
9. Jangan membuat informasi fiktif tentang menu baru, harga baru, atau diskon yang tidak terdaftar di atas. Jika tidak tahu atau tidak tercantum, jawablah dengan jujur dan ramah.";

        // Format history for Gemini API
        $contents = [];

        // Add history
        foreach ($chatHistory as $msg) {
            $role = ($msg['sender'] === 'user') ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $msg['text']]
                ]
            ];
        }

        // Add current user message
        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $userMessage]
            ]
        ];

        // Call Gemini 3.6 Flash endpoint
        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-3.6-flash:generateContent?key=" . $apiKey;

        try {
            $response = Http::timeout(10)->post($url, [
                'contents' => $contents,
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $systemInstruction]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.4,
                    'maxOutputTokens' => 800,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? "Maaf, saya tidak dapat memahami respons tersebut. Ada hal lain yang bisa saya bantu?";
                return response()->json(['response' => trim($reply)]);
            }

            Log::error('Gemini API call failed: Status ' . $response->status() . ' - ' . $response->body());
            return response()->json([
                'response' => "Aduh, maaf ya. Sepertinya sistem asisten saya sedang sibuk atau mengalami kendala koneksi ke server AI. Mohon coba lagi beberapa saat lagi! 🙏"
            ]);
        } catch (\Exception $e) {
            Log::error('Error calling Gemini API: ' . $e->getMessage());
            return response()->json([
                'response' => "Aduh, maaf ya. Terjadi gangguan saat menghubungi asisten AI. Silakan coba lagi beberapa saat lagi! 🙏"
            ]);
        }
    }

    public function poll(Request $request)
    {
        $request->validate([
            'session_token' => 'required|string|max:100',
        ]);

        $sessionToken = $request->input('session_token');

        $session = SupportSession::where('session_token', $sessionToken)->first();

        if (!$session) {
            return response()->json([
                'live_chat' => false,
                'messages' => []
            ]);
        }

        $isLive = in_array($session->status, ['pending', 'active']);

        // Check if session has expired
        if ($isLive && $session->expires_at && now()->greaterThan($session->expires_at)) {
            $session->update(['status' => 'resolved']);
            $isLive = false;

            // Log final auto-closure message
            SupportMessage::create([
                'support_session_id' => $session->id,
                'sender' => 'admin',
                'message' => 'Sesi obrolan telah berakhir secara otomatis karena batas waktu kedaluwarsa.',
                'is_read' => true
            ]);
        }

        // Fetch all unread messages from admin
        $unreadMessages = SupportMessage::where('support_session_id', $session->id)
            ->where('sender', 'admin')
            ->where('is_read', false)
            ->get();

        // Mark them as read
        foreach ($unreadMessages as $msg) {
            $msg->update(['is_read' => true]);
        }

        return response()->json([
            'live_chat' => $isLive,
            'status' => $session->status,
            'expires_at' => $session->expires_at ? $session->expires_at->toIso8601String() : null,
            'messages' => $unreadMessages->map(function ($msg) {
                return [
                    'sender' => 'admin',
                    'text' => $msg->message,
                    'created_at' => $msg->created_at->toIso8601String()
                ];
            })
        ]);
    }

    public function sendLive(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_token' => 'required|string|max:100',
        ]);

        $userMessage = trim($request->input('message'));
        $sessionToken = $request->input('session_token');

        $session = SupportSession::where('session_token', $sessionToken)->first();

        // Check if session is resolved or expired
        if (!$session || $session->status === 'resolved' || ($session->expires_at && now()->greaterThan($session->expires_at))) {
            if ($session && $session->status !== 'resolved') {
                $session->update(['status' => 'resolved']);
            }
            return response()->json([
                'success' => false,
                'response' => 'Sesi chat Anda dengan admin telah selesai.'
            ]);
        }

        // Save message
        SupportMessage::create([
            'support_session_id' => $session->id,
            'sender' => 'user',
            'message' => $userMessage,
        ]);

        // Keep status pending (so admin sees it) or active
        $session->update(['status' => 'pending']);

        return response()->json([
            'success' => true
        ]);
    }
}
