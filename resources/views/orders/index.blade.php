@extends('layouts.app')
@section('title', 'Pesanan Saya')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-14">

    {{-- Page Header --}}
    <div class="mb-8 md:mb-10">
        <div class="flex items-center gap-3 md:gap-4 mb-1">
            <div class="w-10 h-10 md:w-12 md:h-12 bg-brand-cyan/10 border border-brand-cyan/20 rounded-2xl flex items-center justify-center text-brand-cyan flex-shrink-0">
                <i data-lucide="package" class="w-5 h-5 md:w-6 md:h-6"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-3xl font-bold text-white tracking-tight">Pesanan Saya</h1>
                <p class="text-xs md:text-sm text-gray-400 mt-0.5">Riwayat dan status pesanan Anda</p>
            </div>
        </div>
    </div>

    @if($orders->isEmpty())
        {{-- Empty State --}}
        <div class="bg-[#16181d] border border-gray-800 rounded-3xl p-12 md:p-20 text-center shadow-2xl">
            <div class="w-20 h-20 bg-gray-800/60 border border-gray-700 rounded-3xl flex items-center justify-center mx-auto mb-5">
                <i data-lucide="package-open" class="w-9 h-9 text-gray-500"></i>
            </div>
            <h2 class="text-lg md:text-xl font-bold text-white mb-2">Belum Ada Pesanan</h2>
            <p class="text-gray-400 text-sm mb-8 max-w-xs mx-auto leading-relaxed">Anda belum pernah melakukan pemesanan. Yuk mulai jelajahi menu kami!</p>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-orange-400 to-amber-500 text-black font-black text-xs uppercase tracking-widest rounded-full transition hover:scale-105 hover:shadow-[0_0_25px_rgba(249,115,22,0.35)]">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                Jelajahi Menu
            </a>
        </div>
    @else
        <div class="space-y-4 md:space-y-5">
            @foreach($orders as $order)
                @php
                    $statusLabels = [
                        'pending'   => ['label' => 'Menunggu', 'icon' => 'clock', 'color' => 'text-amber-400', 'bg' => 'bg-amber-400/10 border-amber-400/30'],
                        'paid'      => ['label' => 'Lunas',    'icon' => 'check-circle', 'color' => 'text-emerald-400', 'bg' => 'bg-emerald-400/10 border-emerald-400/30'],
                        'shipped'   => ['label' => 'Dikirim',  'icon' => 'truck',  'color' => 'text-sky-400', 'bg' => 'bg-sky-400/10 border-sky-400/30'],
                        'completed' => ['label' => 'Selesai',  'icon' => 'badge-check', 'color' => 'text-violet-400', 'bg' => 'bg-violet-400/10 border-violet-400/30'],
                        'cancelled' => ['label' => 'Dibatalkan','icon' => 'x-circle','color' => 'text-red-400', 'bg' => 'bg-red-400/10 border-red-400/30'],
                    ];
                    $s = $statusLabels[$order->status] ?? ['label' => ucfirst($order->status), 'icon' => 'circle', 'color' => 'text-gray-400', 'bg' => 'bg-gray-400/10 border-gray-400/30'];
                @endphp

                <a href="{{ route('orders.show', $order) }}"
                   class="group block bg-[#16181d] border border-gray-800 hover:border-gray-600 rounded-2xl md:rounded-3xl shadow-lg hover:shadow-[0_20px_50px_rgba(0,0,0,0.4)] transition-all duration-300 overflow-hidden hover:-translate-y-0.5 md:hover:-translate-y-1">

                    {{-- Top bar with order ID + status --}}
                    <div class="flex items-center justify-between px-4 md:px-6 py-3 md:py-4 bg-black/20 border-b border-gray-800/60">
                        <div class="flex items-center gap-2.5">
                            <span class="text-[9px] md:text-[10px] font-black text-gray-500 uppercase tracking-[0.25em]">Pesanan</span>
                            <span class="text-sm md:text-base font-black text-white">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 md:px-3 py-1 md:py-1.5 rounded-full text-[9px] md:text-[10px] font-black uppercase tracking-widest border {{ $s['bg'] }} {{ $s['color'] }}">
                            <i data-lucide="{{ $s['icon'] }}" class="w-3 h-3"></i>
                            {{ $s['label'] }}
                        </span>
                    </div>

                    {{-- Main content --}}
                    <div class="p-4 md:p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6">
                            {{-- Total --}}
                            <div>
                                <p class="text-[9px] md:text-[10px] text-gray-500 uppercase tracking-[0.2em] mb-0.5 md:mb-1">Total Bayar</p>
                                <p class="font-black text-gold-500 text-sm md:text-base">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                            </div>
                            {{-- Date --}}
                            <div>
                                <p class="text-[9px] md:text-[10px] text-gray-500 uppercase tracking-[0.2em] mb-0.5 md:mb-1">Tanggal</p>
                                <p class="font-semibold text-white text-xs md:text-sm">{{ $order->created_at->translatedFormat('d M Y') }}</p>
                                <p class="text-[9px] md:text-[10px] text-gray-500">{{ $order->created_at->format('H:i') }} WIB</p>
                            </div>
                            {{-- Payment method --}}
                            <div>
                                <p class="text-[9px] md:text-[10px] text-gray-500 uppercase tracking-[0.2em] mb-0.5 md:mb-1">Pembayaran</p>
                                <p class="font-semibold text-white text-xs md:text-sm capitalize">{{ str_replace('_', ' ', $order->payment_method ?? 'Transfer Bank') }}</p>
                            </div>
                            {{-- Shipping --}}
                            <div class="col-span-2 md:col-span-1">
                                <p class="text-[9px] md:text-[10px] text-gray-500 uppercase tracking-[0.2em] mb-0.5 md:mb-1">Alamat</p>
                                <p class="font-semibold text-white text-xs md:text-sm line-clamp-2">{{ \Illuminate\Support\Str::limit($order->shipping_address, 50) }}</p>
                            </div>
                        </div>

                        {{-- Bottom row --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 mt-4 pt-4 border-t border-gray-800/40">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                                <span class="inline-flex items-center gap-1.5">
                                    <i data-lucide="user" class="w-3.5 h-3.5 flex-shrink-0"></i>
                                    <span class="font-bold text-gray-300 break-words max-w-[250px] sm:max-w-none">{{ $order->customer_name }}</span>
                                </span>
                                <span class="text-gray-700 hidden sm:inline">·</span>
                                <span class="text-gray-400">{{ $order->customer_phone }}</span>
                            </div>
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-brand-cyan/80 group-hover:text-brand-cyan transition-colors self-end sm:self-auto">
                                Lihat Detail
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Browse more CTA --}}
        <div class="mt-8 md:mt-12 text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-xs text-gray-500 hover:text-white transition font-semibold uppercase tracking-widest">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Pesan Lagi
            </a>
        </div>
    @endif
</div>
@endsection
