@extends('layouts.app')
@section('title', $post->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-24">
    <div class="mb-12">
        <a href="{{ route('posts.index') }}" class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] hover:text-brand-cyan transition-colors flex items-center mb-8">
            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Jurnal
        </a>

        <div class="flex items-center text-[10px] text-brand-cyan font-black uppercase tracking-[0.2em] mb-4">
            <span>{{ $post->published_at->format('d M Y') }}</span>
            <span class="mx-3 w-1 h-1 bg-gray-800 rounded-full"></span>
            <span>Berita Savora</span>
        </div>

        <h1 class="text-4xl md:text-6xl font-bold text-white mb-8 font-serif italic leading-tight tracking-tight">{{ $post->title }}</h1>
        
        @if($post->excerpt)
            <p class="text-xl text-gray-500 font-medium leading-relaxed italic border-l-4 border-gold-500/50 pl-6 mb-12">
                {{ $post->excerpt }}
            </p>
        @endif
    </div>

    <!-- Cover Image -->
    <div class="mb-16 rounded-[2.5rem] overflow-hidden aspect-[16/9] shadow-2xl border border-gray-800 relative bg-gray-900 group">
        @if($post->image)
            <img src="{{ asset('storage/' . $post->image) }}" class="w-full h-full object-cover transition duration-1000 group-hover:scale-105">
        @else
            <div class="flex items-center justify-center h-full text-gold-500 font-serif italic text-4xl opacity-10">Savora Journal</div>
        @endif
    </div>

    <!-- Article Content -->
    <div class="prose prose-invert prose-brand lg:prose-xl max-w-none prose-p:text-gray-400 prose-p:leading-[1.8] prose-headings:text-white prose-headings:font-serif prose-headings:italic prose-a:text-brand-cyan prose-blockquote:border-gold-500 prose-blockquote:bg-gold-500/5 prose-blockquote:rounded-2xl prose-blockquote:p-8 prose-blockquote:not-italic prose-strong:text-white">
        {!! nl2br($post->content) !!}
    </div>

    <!-- Footer of the article -->
    <div class="mt-24 pt-12 border-t border-gray-800 flex flex-col items-center text-center">
        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-6">Bagikan Jurnal Ini</h4>
        <div class="flex space-x-4">
            <button class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center hover:bg-brand-cyan hover:text-black transition-all">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
            </button>
            <button class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center hover:bg-brand-cyan hover:text-black transition-all">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.761 0 5-2.239 5-5v-14c0-2.761-2.239-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
            </button>
        </div>
    </div>
</div>
@endsection
