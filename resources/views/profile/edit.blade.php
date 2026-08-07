@extends('layouts.app')
@section('title', 'Profil Saya — ' . Auth::user()->name)

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-14">

    {{-- Page Header --}}
    <div class="mb-8 md:mb-10">
        <div class="flex items-center gap-3 md:gap-4">
            <div class="w-10 h-10 md:w-12 md:h-12 bg-gold-500/10 border border-gold-500/20 rounded-2xl flex items-center justify-center text-gold-500 flex-shrink-0">
                <i data-lucide="user-circle" class="w-5 h-5 md:w-6 md:h-6"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-3xl font-bold text-white tracking-tight">Profil Saya</h1>
                <p class="text-xs md:text-sm text-gray-400 mt-0.5">Kelola informasi akun dan keamanan Anda</p>
            </div>
        </div>
    </div>

    <div class="space-y-5 md:space-y-6">

        {{-- Update Profile Info --}}
        <div class="bg-[#16181d] border border-gray-800 rounded-2xl md:rounded-3xl shadow-xl overflow-hidden">
            <div class="bg-black/20 border-b border-gray-800/60 px-5 md:px-7 py-3.5 flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4 text-brand-cyan"></i>
                <span class="text-[10px] font-black text-gray-300 uppercase tracking-[0.25em]">Informasi Akun</span>
            </div>
            <div class="p-5 md:p-7">
                <div class="max-w-lg">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        {{-- Update Password --}}
        <div class="bg-[#16181d] border border-gray-800 rounded-2xl md:rounded-3xl shadow-xl overflow-hidden">
            <div class="bg-black/20 border-b border-gray-800/60 px-5 md:px-7 py-3.5 flex items-center gap-2">
                <i data-lucide="lock" class="w-4 h-4 text-gold-500"></i>
                <span class="text-[10px] font-black text-gray-300 uppercase tracking-[0.25em]">Ubah Password</span>
            </div>
            <div class="p-5 md:p-7">
                <div class="max-w-lg">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        {{-- Delete Account --}}
        <div class="bg-red-950/20 border border-red-900/30 rounded-2xl md:rounded-3xl shadow-xl overflow-hidden">
            <div class="bg-red-950/20 border-b border-red-900/20 px-5 md:px-7 py-3.5 flex items-center gap-2">
                <i data-lucide="triangle-alert" class="w-4 h-4 text-red-400"></i>
                <span class="text-[10px] font-black text-red-400/80 uppercase tracking-[0.25em]">Zona Berbahaya</span>
            </div>
            <div class="p-5 md:p-7">
                <div class="max-w-lg">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    /* ─── Dark Theme Override for Breeze Partials ─────────────────────── */
    .bg-white { background-color: transparent !important; }
    .dark\:bg-gray-800 { background-color: transparent !important; }

    /* Section headings */
    h2.text-lg { color: white !important; font-weight: 700; font-size: 1rem !important; }
    p.text-gray-600, .dark\:text-gray-400 { color: #6b7280 !important; font-size: 0.8125rem !important; }
    .text-gray-900, .dark\:text-gray-100 { color: white !important; }

    /* Labels */
    label.block.font-medium, label.block.text-sm {
        font-size: 0.625rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.12em !important;
        color: #6b7280 !important;
        font-weight: 900 !important;
        margin-bottom: 0.375rem !important;
    }

    /* Inputs */
    input[type="text"],
    input[type="email"],
    input[type="password"] {
        background-color: rgba(0,0,0,0.25) !important;
        border-color: #1f2937 !important;
        color: white !important;
        border-radius: 0.75rem !important;
        padding: 0.625rem 0.875rem !important;
        font-size: 0.875rem !important;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus {
        border-color: #e2c86e !important;
        box-shadow: 0 0 0 2px rgba(226,200,110,0.15) !important;
        outline: none !important;
    }

    /* Save / Submit buttons */
    .fi-btn, button[type="submit"].inline-flex,
    button.inline-flex:not(.text-red-600):not(.text-red-500) {
        background: linear-gradient(135deg, #4ecdc4, #38b2ac) !important;
        color: black !important;
        font-weight: 900 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.1em !important;
        font-size: 0.6875rem !important;
        border-radius: 0.75rem !important;
        padding: 0.625rem 1.25rem !important;
        border: none !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 4px 15px rgba(78,205,196,0.25) !important;
    }
    button[type="submit"].inline-flex:hover,
    button.inline-flex:not(.text-red-600):hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 20px rgba(78,205,196,0.4) !important;
    }

    /* Delete Account danger button */
    button.inline-flex.text-red-600,
    button.inline-flex.text-red-500,
    section button.inline-flex[x-data] {
        background: rgba(239,68,68,0.1) !important;
        border: 1px solid rgba(239,68,68,0.3) !important;
        color: #f87171 !important;
        box-shadow: none !important;
    }
    button.inline-flex.text-red-600:hover { background: rgba(239,68,68,0.2) !important; }

    /* Error messages */
    .text-red-600 { color: #f87171 !important; }

    /* Success message */
    .text-green-600 { color: #34d399 !important; }

    /* Spacing fixes */
    section.space-y-6 > header { margin-bottom: 1rem; }
    .mt-6, .mt-4 { margin-top: 1rem !important; }
</style>
@endsection
