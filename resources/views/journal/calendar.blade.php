@extends('layouts.app')

@section('title', 'Journal Calendar')

@section('content')
{{-- 
    PERUBAHAN: 
    1. Menghapus class dynamic untuk blur/scale karena tidak ada modal lagi.
    2. Container sekarang statis dan bersih.
--}}
<div class="h-full flex flex-col relative z-0">
    
    {{-- Header --}}
    <header class="h-16 flex items-center justify-between px-8 z-10 sticky top-0 bg-white/40 backdrop-blur-md border-b border-slate-200/60">
        <div class="flex items-center gap-6">
            <h2 class="text-2xl font-bold tracking-tight text-slate-800">{{ $currentMonth }} {{ $currentYear }}</h2>
            <div class="flex items-center gap-1 bg-white/60 p-1 rounded-lg border border-white/60 shadow-sm backdrop-blur-md">
                <a href="{{ route('journal.index', ['month' => $prevDate->month, 'year' => $prevDate->year]) }}" class="p-1 hover:bg-black/5 rounded-md text-slate-600"><span class="material-symbols-outlined">chevron_left</span></a>
                <a href="{{ route('journal.index', ['month' => $nextDate->month, 'year' => $nextDate->year]) }}" class="p-1 hover:bg-black/5 rounded-md text-slate-600"><span class="material-symbols-outlined">chevron_right</span></a>
                <a href="{{ route('journal.index') }}" class="px-3 py-1 text-xs font-medium hover:bg-black/5 rounded-md text-slate-600">Today</a>
            </div>
        </div>
    </header>

    {{-- Calendar Grid --}}
    <div class="flex-1 overflow-y-auto p-8 pt-2">
        {{-- Days Header --}}
        <div class="grid grid-cols-7 gap-4 mb-4 text-center">
            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $day }}</div>
            @endforeach
        </div>

        {{-- Days Grid --}}
        <div class="grid grid-cols-7 grid-rows-5 gap-4 min-h-[1100px]">
            {{-- Empty slots for previous month --}}
            @for($i=0; $i < $startDayOfWeek; $i++)
                <div class="glass-card rounded-2xl p-3 opacity-30 bg-gray-50 border-transparent shadow-none"></div>
            @endfor

            {{-- Dates Loop --}}
            @for($day=1; $day <= $daysInMonth; $day++)
                @php 
                    $hasEntry = isset($entries[$day]);
                    $entry = $hasEntry ? $entries[$day] : null;
                    $isToday = ($day == now()->day && $monthInt == now()->month && $currentYear == now()->year);
                    $dateString = \Carbon\Carbon::createFromDate($currentYear, $monthInt, $day)->format('Y-m-d');
                    
                    // Logic animasi foto kecil di kalender
                    $photoCount = $hasEntry && $entry->photo_paths ? count($entry->photo_paths) : 0;
                    $slideAnimation = $photoCount > 1 ? 'animate-slide-' . $photoCount : '';
                    $widthClass = $photoCount > 0 ? 'width: ' . ($photoCount * 100) . '%' : '';
                @endphp

                {{-- 
                    LINK: Mengarah ke route journal.create dengan parameter tanggal.
                    Ini akan membuka halaman form Full Page yang baru.
                --}}
                <a href="{{ route('journal.create', ['date' => $dateString]) }}" 
                   class="glass-card rounded-2xl p-3 flex flex-col relative group cursor-pointer transition-all duration-300
                            {{ $isToday ? 'border-primary/50 bg-primary/5 shadow-md ring-1 ring-primary/20' : '' }}
                            {{ $hasEntry ? 'border-green-400/30 bg-white/80' : 'hover:bg-white/60' }}">
                    
                    {{-- Date & Mood Icon --}}
                    <div class="flex justify-between items-start z-10 relative">
                        <span class="text-lg font-medium {{ $isToday ? 'text-primary font-bold' : 'text-slate-700' }}">{{ $day }}</span>
                        @if($hasEntry)
                            <span class="text-xl transform group-hover:scale-110 transition-transform">
                                @if($entry->mood == 'happy') 🙂 
                                @elseif($entry->mood == 'sad') 😢 
                                @elseif($entry->mood == 'excited') 🤩 
                                @else 😐 @endif
                            </span>
                        @endif
                    </div>

                    {{-- Content Preview (Photos / Text) --}}
                    @if($hasEntry && $photoCount > 0)
                        <div class="mt-2 w-full h-40 rounded-lg overflow-hidden relative shadow-sm group-hover:shadow-md transition-all">
                             <div class="h-full flex {{ $slideAnimation }}" style="{{ $widthClass }}">
                                 @foreach($entry->photo_paths as $path)
                                     <div class="h-full w-full flex-shrink-0 bg-gray-100">
                                         <img src="{{ asset('storage/' . $path) }}" class="w-full h-full object-cover">
                                     </div>
                                 @endforeach
                             </div>
                             @if($photoCount > 1)
                                <div class="absolute bottom-1 left-0 right-0 flex justify-center gap-1 z-10">
                                    @for($k=0; $k<$photoCount; $k++)
                                        <div class="w-1 h-1 rounded-full bg-white/70 shadow-sm"></div>
                                    @endfor
                                </div>
                             @endif
                        </div>
                        <div class="mt-auto z-10 relative">
                            <p class="text-xs text-slate-500 line-clamp-2 mt-1 font-medium">{{ $entry->positive_highlight }}</p>
                        </div>
                    @elseif($isToday)
                        <div class="mt-auto"><span class="text-[10px] font-semibold text-primary uppercase tracking-wide">Today</span></div>
                    @else
                         <div class="mt-auto opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="text-[10px] text-slate-400 font-medium">Create</span>
                        </div>
                    @endif
                </a>
            @endfor
        </div>
    </div>
</div>

{{-- 
    CATATAN:
    Semua kode Modal (Preview & Create Form) serta Script JS yang ada di bagian bawah file ini sebelumnya 
    SUDAH DIHAPUS. Halaman ini sekarang murni hanya menampilkan kalender.
--}}
@endsection