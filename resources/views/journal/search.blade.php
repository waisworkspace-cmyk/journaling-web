@extends('layouts.app')

@section('title', 'Search Journal')

@section('content')
<style>
    /* Custom Scrollbar & Glass Effect */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 3px; }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04);
    }
    
    /* Highlight text styling */
    .highlight {
        background-color: #fef08a; /* Yellow-200 */
        color: #854d0e; /* Yellow-800 */
        padding: 0 2px;
        border-radius: 2px;
        font-weight: 600;
    }
</style>

<div class="flex flex-col h-full w-full relative z-10 bg-[#F8F9FA]">

    {{-- Header --}}
    <header class="px-6 md:px-8 pt-8 pb-6 flex flex-col gap-4 bg-[#F8F9FA]/90 backdrop-blur-md sticky top-0 z-30 border-b border-slate-200/50">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Search Entries</h1>
        
        <form action="{{ route('journal.search') }}" method="GET" class="relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-slate-400 group-focus-within:text-primary transition-colors">search</span>
            </div>
            <input type="text" 
                   name="query" 
                   value="{{ $query ?? '' }}" 
                   placeholder="Search everything (goals, feelings, gratitude...)" 
                   class="w-full pl-12 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary shadow-sm transition-all"
                   autocomplete="off"
                   autofocus
            >
            @if($query)
                <a href="{{ route('journal.search') }}" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </a>
            @endif
        </form>
    </header>

    {{-- Content --}}
    <div class="flex-1 overflow-y-auto px-6 md:px-8 pb-12 scroll-smooth">
        <div class="max-w-4xl mx-auto">

            @if(isset($query) && $query != '')
                <div class="mb-6 flex items-center justify-between">
                    <p class="text-sm text-slate-500">
                        Found <span class="font-bold text-slate-800">{{ $results->count() }}</span> results for "<span class="text-primary font-semibold">{{ $query }}</span>"
                    </p>
                </div>

                @if($results->count() > 0)
                    <div class="space-y-6 animate-fade-in">
                        @foreach($results as $entry)
                            <a href="{{ route('journal.create', ['date' => $entry->entry_date->format('Y-m-d')]) }}" 
                               class="glass-card rounded-2xl p-6 flex flex-col md:flex-row gap-6 hover:shadow-md hover:scale-[1.01] transition-all duration-300 group">
                                
                                {{-- Date & Meta Column --}}
                                <div class="flex md:flex-col items-center md:items-start gap-4 md:w-32 shrink-0 border-b md:border-b-0 md:border-r border-slate-100 pb-4 md:pb-0 md:pr-4">
                                    <div class="text-center md:text-left">
                                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $entry->entry_date->format('M Y') }}</span>
                                        <span class="block text-2xl font-bold text-slate-800">{{ $entry->entry_date->format('d') }}</span>
                                        <span class="block text-sm font-medium text-slate-500">{{ $entry->entry_date->format('l') }}</span>
                                    </div>
                                    
                                    <div class="ml-auto md:ml-0 md:mt-auto flex flex-col gap-2">
                                        {{-- Mood Badge --}}
                                        <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-full ring-1 ring-black/5">
                                            <span class="text-lg">
                                                @if($entry->mood == 'happy') 🙂 
                                                @elseif($entry->mood == 'sad') 😢 
                                                @elseif($entry->mood == 'excited') 🤩 
                                                @else 😐 @endif
                                            </span>
                                            <span class="text-xs font-semibold capitalize text-slate-600">{{ $entry->mood ?? 'Neutral' }}</span>
                                        </div>
                                        {{-- Weather Badge (Jika ada) --}}
                                        @if($entry->weather)
                                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-full ring-1 ring-black/5">
                                                <span class="material-symbols-outlined text-[16px] text-slate-400">
                                                    @if($entry->weather == 'sunny') wb_sunny
                                                    @elseif($entry->weather == 'rainy') rainy
                                                    @else cloud @endif
                                                </span>
                                                <span class="text-xs font-semibold capitalize text-slate-600">{{ $entry->weather }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Content Column (Dynamic Highlights) --}}
                                <div class="flex-1 space-y-4">
                                    {{-- Positive --}}
                                    @if($entry->positive_highlight)
                                        <div class="relative pl-4 border-l-2 border-green-200">
                                            <h4 class="text-xs font-bold text-green-600 uppercase mb-1">Positivity</h4>
                                            <p class="text-sm text-slate-700 leading-relaxed line-clamp-2">
                                                {!! preg_replace('/(' . preg_quote($query, '/') . ')/i', '<span class="highlight">$1</span>', e($entry->positive_highlight)) !!}
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Negative --}}
                                    @if($entry->negative_reflection)
                                        <div class="relative pl-4 border-l-2 border-amber-200">
                                            <h4 class="text-xs font-bold text-amber-600 uppercase mb-1">Improvement</h4>
                                            <p class="text-sm text-slate-700 leading-relaxed line-clamp-2">
                                                {!! preg_replace('/(' . preg_quote($query, '/') . ')/i', '<span class="highlight">$1</span>', e($entry->negative_reflection)) !!}
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Gratitude --}}
                                    @if($entry->gratitude)
                                        <div class="relative pl-4 border-l-2 border-rose-200">
                                            <h4 class="text-xs font-bold text-rose-600 uppercase mb-1">Gratitude</h4>
                                            <p class="text-sm text-slate-700 leading-relaxed line-clamp-2">
                                                {!! preg_replace('/(' . preg_quote($query, '/') . ')/i', '<span class="highlight">$1</span>', e($entry->gratitude)) !!}
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Goals (NEW) --}}
                                    @if($entry->goals)
                                        <div class="relative pl-4 border-l-2 border-indigo-200">
                                            <h4 class="text-xs font-bold text-indigo-600 uppercase mb-1">Goals</h4>
                                            <p class="text-sm text-slate-700 leading-relaxed line-clamp-2">
                                                {!! preg_replace('/(' . preg_quote($query, '/') . ')/i', '<span class="highlight">$1</span>', e($entry->goals)) !!}
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Affirmations (NEW) --}}
                                    @if($entry->affirmations)
                                        <div class="relative pl-4 border-l-2 border-sky-200">
                                            <h4 class="text-xs font-bold text-sky-600 uppercase mb-1">Affirmations</h4>
                                            <p class="text-sm text-slate-700 leading-relaxed line-clamp-2">
                                                {!! preg_replace('/(' . preg_quote($query, '/') . ')/i', '<span class="highlight">$1</span>', e($entry->affirmations)) !!}
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Arrow Icon --}}
                                <div class="hidden md:flex items-center justify-center w-8 text-slate-300 group-hover:text-primary group-hover:translate-x-1 transition-all">
                                    <span class="material-symbols-outlined">chevron_right</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    {{-- No Results State --}}
                    <div class="flex flex-col items-center justify-center py-20 text-center animate-fade-in">
                        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-4xl text-slate-400">manage_search</span>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">No matches found</h3>
                        <p class="text-slate-500 max-w-xs mx-auto">We couldn't find any entries matching "<span class="font-medium text-slate-700">{{ $query }}</span>". Try searching for specific moods, weather, or goals.</p>
                    </div>
                @endif

            @else
                {{-- Empty Search State --}}
                <div class="flex flex-col items-center justify-center py-32 text-center opacity-60">
                    <span class="material-symbols-outlined text-6xl text-slate-300 mb-4">search</span>
                    <p class="text-slate-400 font-medium">Search across all your highlights, goals, affirmations, and moods.</p>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection