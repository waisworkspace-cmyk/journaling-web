@extends('layouts.app')

@section('title', 'Mood Analytics')

@section('content')
<style>
    /* Custom Animations for Chart */
    @keyframes drawLine {
        from { stroke-dashoffset: 2000; }
        to { stroke-dashoffset: 0; }
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .chart-animate {
        stroke-dasharray: 2000;
        stroke-dashoffset: 0;
        animation: drawLine 2.5s ease-out forwards;
    }
    .fade-in-delayed {
        opacity: 0;
        animation: fadeIn 0.8s ease-out 0.5s forwards;
    }
    .group:hover circle {
        r: 8px;
        stroke-width: 4px;
    }
</style>

<div class="flex flex-col h-full w-full relative z-10 bg-slate-50/30">

    {{-- Header --}}
    <header class="h-16 px-8 border-b border-slate-200/60 flex items-center justify-between bg-white/40 backdrop-blur-md shrink-0 sticky top-0 z-20">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Mood Analytics</h1>
        </div>
        <div class="bg-slate-200/50 p-0.5 rounded-lg flex text-[13px] font-medium shadow-inner ring-1 ring-black/5">
    @php
        $activeClass = 'bg-white text-slate-900 shadow-sm ring-1 ring-black/5';
        $inactiveClass = 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/50';
    @endphp

    <a href="{{ route('journal.mood', ['range' => 'week']) }}" 
       class="px-5 py-1.5 rounded-[6px] transition-all {{ $range == 'week' ? $activeClass : $inactiveClass }}">
       Week
    </a>
    <a href="{{ route('journal.mood', ['range' => 'month']) }}" 
       class="px-5 py-1.5 rounded-[6px] transition-all {{ $range == 'month' ? $activeClass : $inactiveClass }}">
       Month
    </a>
    <a href="{{ route('journal.mood', ['range' => 'year']) }}" 
       class="px-5 py-1.5 rounded-[6px] transition-all {{ $range == 'year' ? $activeClass : $inactiveClass }}">
       Year
    </a>
</div>
    </header>

    {{-- Scrollable Content --}}
    <div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
        <div class="max-w-7xl mx-auto space-y-8 pb-10">

            {{-- 1. SECTION: LINE CHART (MOOD TRENDS) --}}
            <div class="bg-white/75 backdrop-blur-2xl rounded-3xl p-6 md:p-8 shadow-[0_8px_30px_rgba(0,0,0,0.04)] ring-1 ring-white/60 relative overflow-hidden group/card">
                {{-- Decorative Top Bar --}}
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 via-purple-400 to-orange-400 opacity-50"></div>

                <div class="flex items-center justify-between mb-8 relative z-10">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Mood Trends</h2>
                        <p class="text-sm text-slate-500 mt-1">Emotional state overview for {{ date('F') }}</p>
                    </div>
                    @if($totalEntries > 1)
                        <div class="flex items-center gap-2">
                            <span class="flex items-center gap-1.5 text-xs font-medium text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full ring-1 ring-emerald-100 shadow-sm">
                                <span class="material-symbols-outlined text-[14px]">show_chart</span>
                                {{ $totalEntries }} Entries
                            </span>
                        </div>
                    @endif
                </div>

                <div class="relative h-80 w-full pl-6">
                    @if($totalEntries < 2)
                        <div class="absolute inset-0 flex items-center justify-center text-slate-400">
                            <div class="text-center">
                                <span class="material-symbols-outlined text-4xl mb-2 opacity-50">ssid_chart</span>
                                <p>Not enough data yet. Start journaling!</p>
                            </div>
                        </div>
                    @else
                        {{-- Legend --}}
                        <div class="absolute top-0 right-0 z-20 flex gap-4 text-xs font-medium text-slate-600 bg-white/80 backdrop-blur-sm px-4 py-2 rounded-full ring-1 ring-black/5 shadow-sm">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px] text-amber-500 filled">wb_sunny</span>
                                <span>High ({{ $maxRating }})</span>
                            </div>
                            <div class="w-px h-3.5 bg-slate-300"></div>
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px] text-indigo-400 filled">bedtime</span>
                                <span>Low ({{ $minRating }})</span>
                            </div>
                        </div>

                        {{-- SVG CHART --}}
                        {{-- Ubah viewBox dimulai dari -60 dan lebar ditambah 60 (jadi 1060) agar label di kiri terlihat --}}
<svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="-60 0 1060 300">
                            <defs>
                                <linearGradient id="gradientMood" x1="0%" x2="100%" y1="0%" y2="0%">
                                    <stop offset="0%" style="stop-color:#5856D6;stop-opacity:1"></stop>
                                    <stop offset="50%" style="stop-color:#007AFF;stop-opacity:1"></stop>
                                    <stop offset="100%" style="stop-color:#FF9500;stop-opacity:1"></stop>
                                </linearGradient>
                                <filter height="140%" id="glow" width="140%" x="-20%" y="-20%">
                                    <feGaussianBlur result="coloredBlur" stdDeviation="3"></feGaussianBlur>
                                    <feMerge>
                                        <feMergeNode in="coloredBlur"></feMergeNode>
                                        <feMergeNode in="SourceGraphic"></feMergeNode>
                                    </feMerge>
                                </filter>
                            </defs>

                            {{-- Grid Lines --}}
                            <g class="stroke-slate-100" stroke-width="1">
                                <line x1="0" x2="1000" y1="50" y2="50"></line>   {{-- Rating 10 --}}
                                <line x1="0" x2="1000" y1="150" y2="150"></line> {{-- Rating 5 --}}
                                <line x1="0" x2="1000" y1="250" y2="250"></line> {{-- Rating 1 --}}
                            </g>

                            {{-- Y Axis Labels --}}
                            <g class="fill-slate-400 text-[11px] font-medium tracking-wide" style="font-family: 'Inter', sans-serif;" text-anchor="end">
                                <text x="-12" y="54">Great</text>
                                <text class="material-symbols-outlined filled" font-size="14" style="fill:#34C759; opacity: 0.8;" x="-52" y="55">sentiment_satisfied</text>
                                <text x="-12" y="154">Okay</text>
                                <text class="material-symbols-outlined" font-size="14" style="fill:#5AC8FA; opacity: 0.8;" x="-50" y="155">sentiment_neutral</text>
                                <text x="-12" y="254">Low</text>
                                <text class="material-symbols-outlined" font-size="14" style="fill:#5856D6; opacity: 0.8;" x="-42" y="255">sentiment_dissatisfied</text>
                            </g>

                            {{-- The Data Line (Dynamic) --}}
                            <path class="chart-animate drop-shadow-sm"
                                  d="{{ $svgPath }}"
                                  fill="none"
                                  filter="url(#glow)"
                                  stroke="url(#gradientMood)"
                                  stroke-linecap="round"
                                  stroke-width="5"
                                  stroke-linejoin="round">
                            </path>

                            {{-- Data Points & Tooltips --}}
                            @foreach($chartPoints as $point)
                                <g class="group cursor-pointer" transform="translate({{ $point['x'] }}, {{ $point['y'] }})">
                                    <circle class="transition-all duration-300 ease-out shadow-sm"
                                            cx="0" cy="0" r="6" fill="white" stroke="{{ $point['color'] }}" stroke-width="3"></circle>

                                    {{-- Tooltip Group --}}
                                    <g class="opacity-0 group-hover:opacity-100 transition-all duration-300 ease-out transform translate-y-2 group-hover:translate-y-0" style="pointer-events: none;">
                                        <path d="M-6,-15 L0,-9 L6,-15" fill="#1e293b"></path>
                                        <rect class="shadow-xl" fill="#1e293b" height="30" rx="8" width="90" x="-45" y="-45"></rect>
                                        <text fill="white" font-size="11" font-weight="600" text-anchor="middle" x="0" y="-26">{{ $point['mood'] }}: {{ $point['rating'] }}</text>
                                    </g>
                                </g>
                            @endforeach
                        </svg>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                {{-- 2. SECTION: DONUT CHART (MOOD DISTRIBUTION) --}}
                <div class="bg-white/75 backdrop-blur-2xl rounded-3xl p-6 md:p-8 shadow-[0_8px_30px_rgba(0,0,0,0.04)] ring-1 ring-white/60 flex flex-col items-center justify-between">
                    <div class="w-full mb-4">
                        <h3 class="text-lg font-semibold text-slate-900">Mood Distribution</h3>
                        <p class="text-sm text-slate-500">Breakdown by category</p>
                    </div>

                    @if($totalEntries > 0)
                        <div class="flex items-center gap-8 w-full justify-center">
                            <div class="relative w-48 h-48 shrink-0">
                                <svg class="w-full h-full -rotate-90 transform" viewBox="0 0 100 100">
                                    {{-- Background Circle --}}
                                    <circle cx="50" cy="50" fill="transparent" r="40" stroke="#F2F2F7" stroke-width="12"></circle>

                                    {{-- Dynamic Segments --}}
                                    @php
                                        $circumference = 251.2;
                                        $offset = 0;
                                    @endphp

                                    @foreach($moodPercentages as $mood => $data)
                                        @if($data['count'] > 0)
                                            @php
                                                $dashArray = ($data['percent'] / 100) * $circumference;
                                            @endphp
                                            <circle cx="50" cy="50" fill="transparent" r="40"
                                                    stroke="{{ $data['color'] }}"
                                                    stroke-dasharray="{{ $dashArray }} {{ $circumference }}"
                                                    stroke-dashoffset="-{{ $offset }}"
                                                    stroke-linecap="round"
                                                    stroke-width="12"></circle>
                                            @php $offset += $dashArray; @endphp
                                        @endif
                                    @endforeach
                                </svg>

                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-2xl font-bold text-slate-800">{{ $totalEntries }}</span>
                                    <span class="text-xs text-slate-500 uppercase tracking-wide font-semibold">Entries</span>
                                </div>
                            </div>

                            {{-- Legend --}}
                            <div class="space-y-3 text-sm">
                                @foreach($moodPercentages as $mood => $data)
                                    @if($data['count'] > 0)
                                        <div class="flex items-center gap-3">
                                            <span class="w-3 h-3 rounded-full shadow-sm" style="background-color: {{ $data['color'] }}"></span>
                                            <span class="text-slate-700 font-medium capitalize">{{ $mood }}</span>
                                            <span class="text-slate-400 ml-auto">{{ $data['percent'] }}%</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="py-12 text-slate-400 flex flex-col items-center">
                            <span class="material-symbols-outlined text-4xl mb-2 opacity-30">pie_chart</span>
                            <span>No data available</span>
                        </div>
                    @endif
                </div>

                {{-- 3. SECTION: GAUGE CHART (AVERAGE RATING) --}}
                <div class="bg-white/75 backdrop-blur-2xl rounded-3xl p-6 md:p-8 shadow-[0_8px_30px_rgba(0,0,0,0.04)] ring-1 ring-white/60 flex flex-col items-center justify-between">
                    <div class="w-full mb-4">
                        <h3 class="text-lg font-semibold text-slate-900">Average Daily Rating</h3>
                        <p class="text-sm text-slate-500">Based on overall wellbeing</p>
                    </div>

                    <div class="relative w-56 h-48 flex items-end justify-center pb-4">
                        <svg class="w-full h-full" viewBox="0 0 200 120">
                            {{-- Background Arc --}}
                            <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="#E5E7EB" stroke-linecap="round" stroke-width="16"></path>

                            {{-- Value Arc (Dynamic) --}}
                            @php
                                $gaugeMax = 251;
                                $gaugeValue = ($avgRating / 10) * $gaugeMax;
                            @endphp
                            <path class="drop-shadow-sm transition-all duration-1000 ease-out"
                                  d="M 20 100 A 80 80 0 0 1 180 100"
                                  fill="none"
                                  stroke="url(#gaugeGradient)"
                                  stroke-linecap="round"
                                  stroke-width="16"
                                  stroke-dasharray="{{ $gaugeValue }} {{ $gaugeMax }}"></path>

                            <defs>
                                <linearGradient id="gaugeGradient" x1="0%" x2="100%" y1="0%" y2="0%">
                                    <stop offset="0%" style="stop-color:#FF9500"></stop>
                                    <stop offset="50%" style="stop-color:#FFCC00"></stop>
                                    <stop offset="100%" style="stop-color:#34C759"></stop>
                                </linearGradient>
                            </defs>
                            <g class="text-[10px] fill-slate-400 font-medium">
                                <text x="15" y="115">1</text>
                                <text x="180" y="115">10</text>
                            </g>
                        </svg>

                        <div class="absolute inset-0 flex flex-col items-center justify-end pb-8">
                            <div class="text-5xl font-bold tracking-tight text-slate-800">{{ $avgRating }}</div>
                            <div class="flex items-center gap-1 mt-1">
                                <span class="material-symbols-outlined text-yellow-500 text-lg filled">star</span>
                                <span class="text-sm font-medium text-slate-500">
                                    @if($avgRating >= 8) Fantastic
                                    @elseif($avgRating >= 6) Good
                                    @elseif($avgRating >= 4) Okay
                                    @else Tough Time
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="w-full grid grid-cols-2 gap-4 mt-2">
                        <div class="bg-white/50 rounded-xl p-3 flex flex-col items-center ring-1 ring-black/5">
                            <span class="text-xs text-slate-500 font-medium uppercase">Highest</span>
                            <span class="text-lg font-bold text-slate-800">{{ $maxRating }}</span>
                        </div>
                        <div class="bg-white/50 rounded-xl p-3 flex flex-col items-center ring-1 ring-black/5">
                            <span class="text-xs text-slate-500 font-medium uppercase">Lowest</span>
                            <span class="text-lg font-bold text-slate-800">{{ $minRating }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. SECTION: INSIGHTS CARD --}}
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl p-6 md:p-8 shadow-lg text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-10">
                    <span class="material-symbols-outlined text-[120px] filled">auto_awesome</span>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg">
                            <span class="material-symbols-outlined text-white">lightbulb</span>
                        </div>
                        <h3 class="font-semibold text-lg">Weekly Insight</h3>
                    </div>
                    <p class="text-indigo-50 text-lg leading-relaxed max-w-2xl">
                        {!! $insight !!}
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection