@extends('layouts.app')
@section('title', 'Dashboard — ' . Auth::user()->name)

@section('content')
@php
    $user     = Auth::user();
    $rank     = $user->rank ?? 'reguler';
    $rankColors = [
        'reguler'  => ['from' => '#374151', 'to' => '#1f2937', 'border' => '#4b5563', 'text' => '#9ca3af', 'glow' => 'rgba(107,114,128,0.2)'],
        'perunggu' => ['from' => '#78350f', 'to' => '#1c0a00', 'border' => '#cd7f32', 'text' => '#cd7f32', 'glow' => 'rgba(205,127,50,0.25)'],
        'perak'    => ['from' => '#1f2937', 'to' => '#111827', 'border' => '#9ca3af', 'text' => '#d1d5db', 'glow' => 'rgba(156,163,175,0.25)'],
        'emas'     => ['from' => '#451a03', 'to' => '#1c1007', 'border' => '#e2c86e', 'text' => '#e2c86e', 'glow' => 'rgba(226,200,110,0.3)'],
        'platinum' => ['from' => '#0c4a6e', 'to' => '#042f4c', 'border' => '#22d3ee', 'text' => '#22d3ee', 'glow' => 'rgba(34,211,238,0.3)'],
        'diamond'  => ['from' => '#3b0764', 'to' => '#1a0329', 'border' => '#a855f7', 'text' => '#c084fc', 'glow' => 'rgba(168,85,247,0.35)'],
    ];
    $rc = $rankColors[$rank] ?? $rankColors['reguler'];
    
    // Fallback variables to prevent undefined variable errors
    $rankInfo     = $rankInfo ?? ($user->rank_info ?? \App\Models\User::$ranks['reguler']);
    $allRanks     = $allRanks ?? (\App\Models\User::$ranks);
    $nextRankInfo = $nextRankInfo ?? ($user->next_rank_info);
    $progress     = $progress ?? ($user->rank_progress);
    $remaining    = $remaining ?? ($user->remaining_for_next_rank);
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-14">

    {{-- Welcome Banner --}}
    <div class="relative rounded-2xl md:rounded-[2rem] overflow-hidden mb-8 md:mb-10 shadow-2xl border"
         style="background: linear-gradient(135deg, {{ $rc['from'] }}, #16181d, {{ $rc['to'] }}); border-color: {{ $rc['border'] }}40; box-shadow: 0 0 60px {{ $rc['glow'] }};">
        <div class="absolute -top-24 -right-24 w-64 h-64 rounded-full blur-3xl pointer-events-none" style="background: {{ $rc['glow'] }};"></div>
        <div class="absolute -bottom-20 -left-20 w-56 h-56 rounded-full blur-3xl pointer-events-none" style="background: {{ $rc['glow'] }};"></div>

        <div class="relative z-10 p-6 md:p-10 lg:p-12 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-4 md:gap-5">
                {{-- Avatar with rank ring --}}
                <div class="w-14 h-14 md:w-16 md:h-16 rounded-2xl md:rounded-3xl flex items-center justify-center font-black text-xl md:text-2xl flex-shrink-0 relative border-2"
                     style="background: {{ $rc['from'] }}; border-color: {{ $rc['border'] }}; color: {{ $rc['text'] }}; box-shadow: 0 0 20px {{ $rc['glow'] }};">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                    <span class="absolute -top-2 -right-2 text-base leading-none">{{ $rankInfo['icon'] }}</span>
                </div>

                <div>
                    <p class="text-[10px] md:text-xs font-black uppercase tracking-[0.25em] mb-1 flex items-center gap-1.5"
                       style="color: {{ $rc['text'] }};">
                        {{ $rankInfo['label'] }}
                    </p>
                    <h1 class="text-xl md:text-3xl lg:text-4xl font-bold text-white tracking-tight">{{ $user->name }}</h1>
                    <p class="text-xs md:text-sm text-gray-400 mt-1 flex items-center gap-1.5">
                        <i data-lucide="mail" class="w-3 h-3 text-gray-600"></i>
                        {{ $user->email }}
                    </p>
                </div>
            </div>

            <a href="{{ route('home') }}#menu-section" class="flex-shrink-0 self-start md:self-auto inline-flex items-center gap-2 px-6 md:px-8 py-3 md:py-3.5 bg-gradient-to-r from-orange-400 to-amber-500 hover:from-orange-500 hover:to-amber-600 text-black font-black text-[10px] md:text-xs uppercase tracking-widest rounded-full transition-all duration-300 hover:scale-105 hover:shadow-[0_0_25px_rgba(249,115,22,0.35)]">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                Jelajahi Menu
            </a>
        </div>
    </div>

    {{-- Quick Actions Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-5">
        <a href="{{ route('orders.index') }}"
           class="card-shimmer group relative bg-[#16181d] border border-gray-800 hover:border-brand-cyan/40 rounded-2xl md:rounded-3xl shadow-lg hover:shadow-[0_20px_50px_rgba(78,205,196,0.08)] p-5 md:p-7 transition-all duration-500 hover:-translate-y-1 md:hover:-translate-y-2 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-cyan/3 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10">
                <div class="w-12 h-12 md:w-14 md:h-14 bg-brand-cyan/10 border border-brand-cyan/20 rounded-xl md:rounded-2xl flex items-center justify-center text-brand-cyan group-hover:bg-brand-cyan group-hover:text-black group-hover:shadow-[0_0_20px_rgba(78,205,196,0.3)] transition-all duration-500 mb-4 md:mb-5">
                    <i data-lucide="package" class="w-6 h-6 md:w-7 md:h-7"></i>
                </div>
                <h4 class="font-bold text-white text-base md:text-lg mb-1">Pesanan Saya</h4>
                <p class="text-xs md:text-sm text-gray-500 leading-relaxed">Lihat riwayat dan status pesanan aktif</p>
                <div class="flex items-center gap-1.5 mt-4 text-[10px] md:text-xs font-black uppercase tracking-widest text-gray-600 group-hover:text-brand-cyan transition-colors">
                    Lihat Semua <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </a>

        <a href="/keranjang"
           class="card-shimmer group relative bg-[#16181d] border border-gray-800 hover:border-orange-400/40 rounded-2xl md:rounded-3xl shadow-lg hover:shadow-[0_20px_50px_rgba(251,146,60,0.08)] p-5 md:p-7 transition-all duration-500 hover:-translate-y-1 md:hover:-translate-y-2 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-orange-400/3 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10">
                <div class="w-12 h-12 md:w-14 md:h-14 bg-orange-400/10 border border-orange-400/20 rounded-xl md:rounded-2xl flex items-center justify-center text-orange-400 group-hover:bg-orange-400 group-hover:text-black group-hover:shadow-[0_0_20px_rgba(251,146,60,0.3)] transition-all duration-500 mb-4 md:mb-5 relative">
                    <i data-lucide="shopping-cart" class="w-6 h-6 md:w-7 md:h-7"></i>
                    @if(count(session('cart', [])) > 0)
                        <span class="absolute -top-1.5 -right-1.5 min-w-[1.25rem] h-5 bg-orange-400 text-black text-[9px] font-black rounded-full flex items-center justify-center px-1">
                            {{ count(session('cart', [])) }}
                        </span>
                    @endif
                </div>
                <h4 class="font-bold text-white text-base md:text-lg mb-1">Keranjang</h4>
                <p class="text-xs md:text-sm text-gray-500">{{ count(session('cart', [])) }} item sedang menunggu</p>
                <div class="flex items-center gap-1.5 mt-4 text-[10px] md:text-xs font-black uppercase tracking-widest text-gray-600 group-hover:text-orange-400 transition-colors">
                    Buka Keranjang <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('profile.edit') }}"
           class="card-shimmer group relative bg-[#16181d] border border-gray-800 hover:border-gold-500/40 rounded-2xl md:rounded-3xl shadow-lg hover:shadow-[0_20px_50px_rgba(226,200,110,0.08)] p-5 md:p-7 transition-all duration-500 hover:-translate-y-1 md:hover:-translate-y-2 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-gold-500/3 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10">
                <div class="w-12 h-12 md:w-14 md:h-14 bg-gold-500/10 border border-gold-500/20 rounded-xl md:rounded-2xl flex items-center justify-center text-gold-500 group-hover:bg-gold-500 group-hover:text-black group-hover:shadow-[0_0_20px_rgba(226,200,110,0.3)] transition-all duration-500 mb-4 md:mb-5">
                    <i data-lucide="user-circle" class="w-6 h-6 md:w-7 md:h-7"></i>
                </div>
                <h4 class="font-bold text-white text-base md:text-lg mb-1">Profil Saya</h4>
                <p class="text-xs md:text-sm text-gray-500">Ubah nama, email & password akun</p>
                <div class="flex items-center gap-1.5 mt-4 text-[10px] md:text-xs font-black uppercase tracking-widest text-gray-600 group-hover:text-gold-500 transition-colors">
                    Edit Profil <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>
        </a>
    </div>

    {{-- ═══ Loyalty Rank Card ═══ --}}
    <div class="mt-8 rounded-2xl md:rounded-3xl overflow-hidden shadow-2xl border"
         style="border-color: {{ $rc['border'] }}30; background: linear-gradient(135deg, #16181d, #111317);">

        {{-- Header --}}
        <div class="px-6 md:px-8 pt-6 md:pt-8 pb-4 border-b" style="border-color: {{ $rc['border'] }}20;">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg border"
                         style="background: {{ $rc['from'] }}; border-color: {{ $rc['border'] }}40; box-shadow: 0 0 15px {{ $rc['glow'] }};">
                        {{ $rankInfo['icon'] }}
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-500">Pangkat Loyalitas</p>
                        <h3 class="text-base md:text-lg font-bold" style="color: {{ $rc['text'] }};">{{ $rankInfo['label'] }}</h3>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-gray-500 uppercase tracking-widest">Total Belanja</p>
                    <p class="text-base md:text-lg font-black text-white">Rp {{ number_format($user->total_spent, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="px-6 md:px-8 py-5">
            @if($nextRankInfo)
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Progress ke {{ $nextRankInfo['label'] }}</span>
                    <span class="text-[10px] font-black" style="color: {{ $rc['text'] }};">{{ $progress }}%</span>
                </div>
                <div class="w-full h-2 bg-gray-800 rounded-full overflow-hidden mb-3">
                    <div class="h-full rounded-full transition-all duration-700"
                         style="width: {{ $progress }}%; background: linear-gradient(90deg, {{ $rc['border'] }}, {{ $rc['text'] }}); box-shadow: 0 0 10px {{ $rc['glow'] }};">
                    </div>
                </div>
                <p class="text-xs text-gray-500">
                    Belanja <strong class="text-white">Rp {{ number_format($remaining, 0, ',', '.') }}</strong> lagi untuk naik ke
                    <strong style="color: {{ $nextRankInfo['hex'] }};">{{ $nextRankInfo['icon'] }} {{ $nextRankInfo['label'] }}</strong>
                </p>
            @else
                <div class="flex items-center gap-2 text-sm font-bold" style="color: {{ $rc['text'] }};">
                    <span>{{ $rankInfo['icon'] }}</span>
                    <span>Anda telah mencapai pangkat tertinggi! Terima kasih atas kesetiaan Anda.</span>
                </div>
            @endif
        </div>

        {{-- All Ranks --}}
        <div class="px-6 md:px-8 pb-6 md:pb-8">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-600 mb-4">Semua Pangkat</p>
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                @foreach($allRanks as $key => $info)
                    @php
                        $rankKeys = array_keys(\App\Models\User::$ranks);
                        $currentIdx = array_search($user->rank, $rankKeys);
                        $thisIdx = array_search($key, $rankKeys);
                        $isAchieved = $thisIdx <= $currentIdx;
                        $isCurrent  = $key === $user->rank;
                    @endphp
                    <div class="flex flex-col items-center gap-1.5 p-2.5 rounded-xl border transition-all duration-300
                                {{ $isCurrent ? 'border-opacity-60' : ($isAchieved ? 'border-opacity-30' : 'border-gray-800 opacity-40') }}"
                         style="{{ $isCurrent ? 'border-color: ' . $info['hex'] . '; background: ' . $info['hex'] . '15; box-shadow: 0 0 15px ' . $info['hex'] . '30;' : ($isAchieved ? 'border-color: ' . $info['hex'] . '40;' : '') }}">
                        <span class="text-xl">{{ $info['icon'] }}</span>
                        <span class="text-[9px] font-black uppercase tracking-wider text-center leading-tight"
                              style="{{ $isCurrent ? 'color: ' . $info['hex'] : ($isAchieved ? 'color: ' . $info['hex'] . '; opacity: 0.7;' : 'color: #4b5563;') }}">
                            {{ $info['label'] }}
                        </span>
                        @if($isCurrent)
                            <span class="text-[7px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-md"
                                  style="background: {{ $info['hex'] }}; color: #000;">Anda</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Logout section --}}
    <div class="mt-8 md:mt-10 flex items-center justify-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 text-xs text-gray-600 hover:text-red-400 transition font-semibold uppercase tracking-widest">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                Keluar dari Akun
            </button>
        </form>
    </div>
</div>
@endsection
