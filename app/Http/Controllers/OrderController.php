<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        abort_if($order->user_id != auth()->id(), 403);

        if (request()->wantsJson() || request()->ajax()) {
            $statusLabels = [
                'pending' => 'Pending',
                'paid' => 'Lunas',
                'shipped' => 'Dikirim',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
            ];
            $statusClasses = [
                'pending' => 'bg-gold-500/10 border border-gold-500/30 text-gold-500',
                'paid' => 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-500',
                'shipped' => 'bg-sky-500/10 border border-sky-500/30 text-sky-500',
                'completed' => 'bg-violet-500/10 border border-violet-500/30 text-violet-500',
                'cancelled' => 'bg-red-500/10 border border-red-500/30 text-red-500',
            ];

            return response()->json([
                'status' => $order->status,
                'status_label' => $statusLabels[$order->status] ?? ucfirst($order->status),
                'status_class' => $statusClasses[$order->status] ?? 'bg-gray-500/10 border border-gray-500/30 text-gray-300',
            ]);
        }

        $snapToken = MidtransService::getSnapToken($order);

        return view('orders.show', compact('order', 'snapToken'));
    }

    public function uploadPaymentProof(Request $request, Order $order)
    {
        abort_if($order->user_id != auth()->id(), 403);
        abort_if($order->status !== 'pending', 400);

        $request->validate([
            'payment_proof' => 'required|image|max:3072', // Max 3MB
        ]);

        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');

            // Hapus file lama jika ada
            if ($order->payment_proof && Storage::disk('public')->exists($order->payment_proof)) {
                Storage::disk('public')->delete($order->payment_proof);
            }

            $path = $file->store('payment_proofs', 'public');

            // Konfirmasi Pembayaran: Update status pesanan ke 'paid' (Lunas / Diproses) tanpa AI
            $order->update([
                'payment_proof' => $path,
                'payment_proof_analysis' => null,
                'status' => 'paid',
            ]);
        }

        return back()->with('success', 'Bukti pembayaran berhasil dikirim! Pembayaran Anda akan segera dikonfirmasi dan pesanan diproses.');
    }
}
