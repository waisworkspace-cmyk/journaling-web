@extends('layouts.app')

@section('title', 'Gallery')

@section('content')
{{-- Background Gradient tambahan khusus halaman ini (optional, agar sesuai desain HTML baru) --}}
<div class="absolute inset-0 z-0 pointer-events-none opacity-60" style="background-image: radial-gradient(circle at 15% 50%, rgba(0, 122, 255, 0.08), transparent 25%), radial-gradient(circle at 85% 30%, rgba(52, 199, 89, 0.08), transparent 25%);">
</div>

<div class="flex flex-col h-full w-full relative z-10 bg-slate-50/30">

    {{-- Header Section (Diambil dari desain HTML baru) --}}
    <header class="h-16 px-8 border-b border-slate-200/60 flex items-center justify-between bg-white/40 backdrop-blur-md shrink-0 sticky top-0 z-20">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Gallery</h1>
        
        {{-- Filter Tabs (Visual saja untuk saat ini) --}}
        <div class="bg-slate-200/50 p-0.5 rounded-lg flex text-[13px] font-medium shadow-inner ring-1 ring-black/5">
            <button class="px-5 py-1.5 bg-white text-slate-900 rounded-[6px] shadow-sm ring-1 ring-black/5 transition-all">Photos</button>
            <button class="px-5 py-1.5 text-slate-500 hover:text-slate-700 transition-colors cursor-not-allowed opacity-60">Albums</button>
        </div>
    </header>

    {{-- Content Grid --}}
    <div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
        
        @if($entriesWithPhotos->isEmpty())
            {{-- Tampilan jika belum ada foto --}}
            <div class="h-full flex flex-col items-center justify-center text-slate-400">
                <span class="material-symbols-outlined text-6xl mb-4 opacity-30">photo_library</span>
                <p class="text-lg font-medium text-slate-500">No photos yet</p>
                <p class="text-sm mb-6">Your journal memories will appear here.</p>
                <a href="{{ route('journal.index') }}" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                    Go to Journal
                </a>
            </div>
        @else
            {{-- Masonry Grid Layout --}}
            <div class="columns-1 sm:columns-2 md:columns-3 lg:columns-4 gap-6 space-y-6 mx-auto max-w-7xl pb-10">
                @foreach($entriesWithPhotos as $entry)
                    @if(is_array($entry->photo_paths))
                        @foreach($entry->photo_paths as $path)
                            {{-- Kartu Foto --}}
                            <a href="{{ route('journal.create', ['date' => $entry->entry_date->format('Y-m-d')]) }}" 
                               class="break-inside-avoid relative group rounded-2xl overflow-hidden shadow-[0_2px_8px_rgba(0,0,0,0.04)] hover:shadow-[0_8px_24px_rgba(0,0,0,0.08)] transition-all duration-300 ring-1 ring-black/5 bg-white cursor-pointer block">
                                
                                {{-- Gambar dari Storage --}}
                                <img src="{{ asset('storage/' . $path) }}" 
                                     alt="Journal entry from {{ $entry->entry_date->format('d M Y') }}" 
                                     class="w-full h-auto object-cover transform group-hover:scale-105 transition-transform duration-500" 
                                     loading="lazy">
                                
                                {{-- Overlay Hover dengan Tanggal --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4">
                                    <p class="text-white/90 text-xs font-medium backdrop-blur-sm bg-black/20 inline-block px-2 py-1 rounded-full w-fit">
                                        {{ $entry->entry_date->format('F d') }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection