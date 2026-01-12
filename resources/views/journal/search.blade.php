@extends('layouts.app')

@section('title', 'Search')

@section('content')
<div class="flex flex-col h-full w-full relative z-10 bg-slate-50/30">

    {{-- Search Bar Section --}}
    <div class="px-8 pt-8 pb-4 flex flex-col items-center justify-center shrink-0 z-20 sticky top-0 bg-slate-50/90 backdrop-blur-md">
        <form action="{{ route('journal.search') }}" method="GET" class="w-full max-w-2xl relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-slate-400 group-focus-within:text-primary transition-colors">search</span>
            </div>
            <input 
                type="text" 
                name="query" 
                value="{{ request('query') }}"
                class="block w-full pl-12 pr-4 py-3.5 bg-white/60 backdrop-blur-xl border-none rounded-xl text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500/20 shadow-[0_4px_20px_rgba(0,0,0,0.03)] ring-1 ring-black/5 transition-all outline-none" 
                placeholder="Search memories, moods (e.g., 'malas'), or notes..." 
                autocomplete="off"
            />
        </form>
    </div>

    {{-- Search Results --}}
    <div class="flex-1 overflow-y-auto px-6 md:px-8 pb-10 scroll-smooth">
        <div class="max-w-2xl mx-auto space-y-4 pt-2">
            
            @if(request('query'))
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider pl-1 mb-2">
                    {{ $results->count() }} Results found for "{{ request('query') }}"
                </p>

                @forelse($results as $entry)
                    <a href="{{ route('journal.create', ['date' => $entry->entry_date->format('Y-m-d')]) }}" class="block">
                        <div class="flex items-start gap-4 p-4 bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm ring-1 ring-black/5 hover:shadow-[0_8px_24px_rgba(0,0,0,0.06)] hover:bg-white transition-all duration-300 cursor-pointer group">
                            
                            {{-- Menampilkan Foto Pertama jika ada --}}
                            @php
                                $firstPhoto = (is_array($entry->photo_paths) && count($entry->photo_paths) > 0) ? $entry->photo_paths[0] : null;
                            @endphp
                            
                            @if($firstPhoto)
                                <div class="w-20 h-20 rounded-lg overflow-hidden shrink-0 ring-1 ring-black/5 bg-slate-100">
                                    <img src="{{ asset('storage/' . $firstPhoto) }}" 
                                         alt="Journal entry" 
                                         class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                                </div>
                            @else
                                {{-- Fallback jika tidak ada foto --}}
                                <div class="w-20 h-20 rounded-lg overflow-hidden shrink-0 ring-1 ring-black/5 bg-slate-50 flex items-center justify-center text-slate-300">
                                    <span class="material-symbols-outlined">description</span>
                                </div>
                            @endif

                            <div class="flex-1 min-w-0 flex flex-col h-full justify-center py-0.5">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-slate-900">{{ $entry->entry_date->format('F d, Y') }}</span>
                                        <span class="text-xs text-slate-400">•</span>
                                        {{-- Mood Badge --}}
                                        <span class="text-xs font-medium px-2 py-0.5 rounded-md capitalize 
                                            {{ $entry->mood == 'happy' ? 'bg-green-100 text-green-700' : 
                                              ($entry->mood == 'sad' ? 'bg-blue-100 text-blue-700' : 
                                              ($entry->mood == 'angry' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600')) }}">
                                            {{ $entry->mood ?? 'No Mood' }}
                                        </span>
                                    </div>
                                    <span class="material-symbols-outlined text-slate-300 text-[18px] group-hover:text-blue-500 transition-colors">chevron_right</span>
                                </div>
                                
                                {{-- Menampilkan Highlight / Reflection --}}
                                <p class="text-slate-600 text-[15px] leading-snug line-clamp-2">
                                    {{ $entry->positive_highlight ?? $entry->negative_reflection ?? 'No written entry.' }}
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    {{-- Empty State jika pencarian tidak ditemukan --}}
                    <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                        <span class="material-symbols-outlined text-5xl mb-2 opacity-20">search_off</span>
                        <p>No matches found for "{{ request('query') }}"</p>
                    </div>
                @endforelse

            @else
                {{-- State Awal (Belum mencari) --}}
                <div class="flex flex-col items-center justify-center py-20 text-slate-400 opacity-60">
                    <span class="material-symbols-outlined text-6xl mb-4 opacity-20">manage_search</span>
                    <p class="font-medium">Type something to search your journal</p>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection