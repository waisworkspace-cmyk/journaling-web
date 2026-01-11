@extends('layouts.app')

@section('title', 'Journal Calendar')

@section('content')
<header class="h-16 flex items-center justify-between px-8 z-10 sticky top-0 bg-white/40 backdrop-blur-md border-b border-slate-200/60">
    <div class="flex items-center gap-6">
        <h2 class="text-2xl font-bold tracking-tight text-slate-800">January 2026</h2>
        <div class="flex items-center gap-1 bg-white/60 p-1 rounded-lg border border-white/60 shadow-sm backdrop-blur-md">
            <button class="p-1 hover:bg-black/5 rounded-md text-slate-600 transition-colors"><span class="material-symbols-outlined">chevron_left</span></button>
            <button class="p-1 hover:bg-black/5 rounded-md text-slate-600 transition-colors"><span class="material-symbols-outlined">chevron_right</span></button>
            <button class="px-3 py-1 text-xs font-medium hover:bg-black/5 rounded-md text-slate-600 transition-colors">Today</button>
        </div>
    </div>
    <div class="flex items-center gap-4">
        <a href="{{ route('journal.create') }}" class="flex items-center justify-center h-9 w-9 rounded-full bg-primary text-white shadow-lg hover:bg-primary/90 transition-all hover:scale-105">
            <span class="material-symbols-outlined text-[20px]">add</span>
        </a>
    </div>
</header>

<div class="flex-1 overflow-y-auto p-8 pt-2">
    <div class="grid grid-cols-7 gap-4 mb-4 text-center">
        @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $day }}</div>
        @endforeach
    </div>

    <div class="grid grid-cols-7 grid-rows-5 gap-4 h-[calc(100%-40px)] min-h-[600px]">
        {{-- Tanggal Kosong Awal Bulan --}}
        @for($i=0; $i<3; $i++)
            <div class="glass-card rounded-2xl p-3 flex flex-col opacity-60"><span class="text-sm font-medium text-slate-400">{{ 29 + $i }}</span></div>
        @endfor

        {{-- Tanggal 1 - 31 --}}
        @for($day=1; $day<=31; $day++)
            @php $isToday = ($day == 13); @endphp
            <div class="glass-card rounded-2xl p-3 flex flex-col relative group cursor-pointer {{ $isToday ? 'border-primary/30 bg-primary/5 shadow-[0_4px_15px_rgba(19,127,236,0.15)]' : '' }}">
                <span class="text-lg font-medium {{ $isToday ? 'text-primary font-bold' : 'text-slate-700' }}">{{ $day }}</span>
                
                @if($isToday)
                    <div class="mt-auto"><span class="text-[10px] font-semibold text-primary uppercase tracking-wide">Today</span></div>
                @endif

                {{-- Overlay Hover untuk Add Entry --}}
                <a href="{{ route('journal.create') }}" class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <div class="bg-primary/10 p-2 rounded-full text-primary">
                        <span class="material-symbols-outlined">add</span>
                    </div>
                </a>
            </div>
        @endfor
    </div>
</div>

<div class="absolute bottom-8 right-8 z-30">
    <a href="{{ route('journal.create') }}" class="flex items-center gap-2 px-4 py-3 bg-primary text-white rounded-full shadow-[0_4px_20px_rgba(19,127,236,0.3)] hover:shadow-[0_8px_25px_rgba(19,127,236,0.4)] hover:bg-primary/90 hover:scale-105 transition-all active:scale-95 group">
        <span class="material-symbols-outlined filled">edit_square</span>
        <span class="font-medium pr-1">New Entry</span>
    </a>
</div>
@endsection