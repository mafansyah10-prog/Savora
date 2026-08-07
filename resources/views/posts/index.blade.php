@extends('layouts.app')
@section('title', 'Jurnal Savora')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-24">
    <div class="max-w-3xl mb-24">
        <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 font-serif italic leading-none tracking-tighter">Jurnal Kuliner & <br><span class="text-gold-500">Inspirasi Rasa.</span></h1>
        <p class="text-gray-400 text-lg md:text-xl max-w-xl leading-relaxed">Dari dapur Savora ke meja Anda. Jelajahi cerita di balik setiap bahan, tips memasak, dan berita terbaru dari dunia kuliner kami.</p>
    </div>

    @if($posts->isEmpty())
        <div class="py-24 text-center border-t border-gray-800">
            <h2 class="text-2xl font-bold text-gray-700 font-serif italic mb-2 tracking-widest uppercase opacity-30">Belum ada jurnal baru.</h2>
            <p class="text-xs text-gray-500 uppercase tracking-widest font-black">Nantikan pembaruan kami selanjutnya.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 lg:gap-16">
            @foreach($posts as $post)
            <article class="group transition-all duration-500 transform hover:-translate-y-2 active:scale-95">
                <a href="{{ route('posts.show', $post->slug) }}" class="block mb-8 rounded-3xl overflow-hidden aspect-[16/10] bg-gray-800 border border-gray-800 shadow-2xl relative">
                    @if($post->image)
                        <img src="{{ asset('storage/' . $post->image) }}" class="w-full h-full object-cover transition duration-700 group-hover:scale-110">
                    @else
                        <div class="flex items-center justify-center h-full text-gray-500 font-serif italic text-2xl opacity-20">Savora Journal</div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </a>

                <div class="space-y-4">
                    <div class="flex items-center text-[10px] text-brand-cyan font-black uppercase tracking-[0.2em]">
                        <span>{{ $post->published_at->format('d M Y') }}</span>
                        <span class="mx-3 w-1 h-1 bg-gray-800 rounded-full"></span>
                        <span>Artikel Makanan</span>
                    </div>

                    <h2 class="text-2xl font-bold text-white group-hover:text-gold-400 transition-colors tracking-tight font-serif italic">
                        <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                    </h2>

                    <p class="text-gray-500 text-sm leading-relaxed line-clamp-3">
                        {{ $post->excerpt ?: substr(strip_tags($post->content), 0, 150) . '...' }}
                    </p>

                    <a href="{{ route('posts.show', $post->slug) }}" class="inline-flex items-center text-[11px] font-black text-white hover:text-brand-cyan transition-colors uppercase tracking-widest pb-1 border-b border-gray-800 hover:border-brand-cyan">
                        Baca Selengkapnya
                        <svg class="w-3 h-3 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                </div>
            </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
