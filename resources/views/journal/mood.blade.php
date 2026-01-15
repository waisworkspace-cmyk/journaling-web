@extends('layouts.app')

@section('title', 'Mood Analytics')

@section('content')
<style>
    /* Custom Animations */
    @keyframes drawLine { from { stroke-dashoffset: 2000; } to { stroke-dashoffset: 0; } }
    .chart-animate { stroke-dasharray: 2000; stroke-dashoffset: 0; animation: drawLine 2.5s ease-out forwards; }
    
    /* Custom Scrollbar & Glass Effect */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 3px; }
    .glass-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04);
    }
    .group:hover circle { r: 8px; stroke-width: 4px; }
</style>

<div class="flex flex-col h-full w-full relative z-10 bg-[#F8F9FA]">

    {{-- Header --}}
    <header class="h-16 px-6 md:px-8 border-b border-slate-200/60 flex items-center justify-between bg-white/60 backdrop-blur-md shrink-0 sticky top-0 z-30">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Analytics & Tracker</h1>
        
        {{-- Range Selector --}}
        <div class="bg-slate-200/50 p-1 rounded-xl flex text-[13px] font-medium shadow-inner ring-1 ring-black/5">
            @php
                $activeClass = 'bg-white text-slate-900 shadow-sm ring-1 ring-black/5';
                $inactiveClass = 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/50';
            @endphp
            @foreach(['week' => 'Week', 'month' => 'Month', 'year' => 'Year'] as $key => $label)
                <a href="{{ route('journal.mood', ['range' => $key]) }}" 
                   class="px-4 py-1.5 rounded-lg transition-all {{ $range == $key ? $activeClass : $inactiveClass }}">
                   {{ $label }}
                </a>
            @endforeach
        </div>
    </header>

    {{-- Content --}}
    <div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
        <div class="max-w-7xl mx-auto space-y-6 pb-12 animate-fade-in">

            {{-- TOP ROW: Line Chart --}}
            <div class="glass-card rounded-3xl p-6 md:p-8 relative overflow-hidden group/card">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 via-purple-400 to-orange-400 opacity-50"></div>
                <div class="flex items-center justify-between mb-8 relative z-10">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Mood Trends</h2>
                        <p class="text-sm text-slate-500">Emotional flow over time</p>
                    </div>
                    @if($totalEntries > 1)
                        <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full ring-1 ring-emerald-100">
                            {{ $totalEntries }} Entries
                        </span>
                    @endif
                </div>

                <div class="relative h-72 w-full pl-2">
                    @if($totalEntries < 2)
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                            <span class="material-symbols-outlined text-4xl mb-2 opacity-50">ssid_chart</span>
                            <p>Start journaling to see your trends!</p>
                        </div>
                    @else
                        {{-- Chart SVG (Sama seperti sebelumnya tapi disesuaikan stylenya) --}}
                        <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="-60 0 1060 300">
                            <defs>
                                <linearGradient id="gradientMood" x1="0%" x2="100%" y1="0%" y2="0%">
                                    <stop offset="0%" style="stop-color:#5856D6;stop-opacity:1"></stop>
                                    <stop offset="50%" style="stop-color:#007AFF;stop-opacity:1"></stop>
                                    <stop offset="100%" style="stop-color:#FF9500;stop-opacity:1"></stop>
                                </linearGradient>
                            </defs>
                            {{-- Grid & Labels --}}
                            <g class="stroke-slate-100" stroke-width="1">
                                <line x1="0" x2="1000" y1="50" y2="50"></line>
                                <line x1="0" x2="1000" y1="150" y2="150"></line>
                                <line x1="0" x2="1000" y1="250" y2="250"></line>
                            </g>
                            <g class="fill-slate-400 text-[11px] font-medium" text-anchor="end">
                                <text x="-15" y="55">Great</text>
                                <text x="-15" y="155">Okay</text>
                                <text x="-15" y="255">Low</text>
                            </g>
                            {{-- Line --}}
                            <path class="chart-animate drop-shadow-md" d="{{ $svgPath }}" fill="none" stroke="url(#gradientMood)" stroke-linecap="round" stroke-width="4" stroke-linejoin="round"></path>
                            {{-- Points --}}
                            @foreach($chartPoints as $point)
                                <g class="group cursor-pointer" transform="translate({{ $point['x'] }}, {{ $point['y'] }})">
                                    <circle class="transition-all duration-300 shadow-sm" cx="0" cy="0" r="5" fill="white" stroke="{{ $point['color'] }}" stroke-width="3"></circle>
                                    <title>{{ $point['date'] }}: Rating {{ $point['rating'] }}</title>
                                </g>
                            @endforeach
                        </svg>
                    @endif
                </div>
            </div>

            {{-- MIDDLE ROW: Weather, Donut, Gauge --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- 1. WEATHER IMPACT (NEW TRACKER) --}}
                <div class="glass-card rounded-3xl p-6 flex flex-col justify-between">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-slate-900">Weather Impact</h3>
                        <p class="text-sm text-slate-500">Avg. rating by weather</p>
                    </div>

                    <div class="space-y-5">
                        @foreach(['sunny' => ['icon'=>'wb_sunny', 'color'=>'text-amber-500', 'bg'=>'bg-amber-500'], 
                                  'cloudy' => ['icon'=>'cloud', 'color'=>'text-slate-500', 'bg'=>'bg-slate-500'], 
                                  'rainy' => ['icon'=>'rainy', 'color'=>'text-blue-500', 'bg'=>'bg-blue-500']] as $type => $style)
                            @php $stat = $weatherData[$type]; @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined {{ $style['color'] }}">{{ $style['icon'] }}</span>
                                        <span class="text-sm font-semibold capitalize text-slate-700">{{ $type }}</span>
                                    </div>
                                    <span class="text-sm font-bold text-slate-900">{{ $stat['avg_rating'] }} <span class="text-slate-400 text-xs font-normal">/10</span></span>
                                </div>
                                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $style['bg'] }} opacity-80 rounded-full transition-all duration-1000" style="width: {{ $stat['avg_rating'] * 10 }}%"></div>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1 text-right">{{ $stat['count'] }} entries</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- 2. MOOD DISTRIBUTION --}}
                <div class="glass-card rounded-3xl p-6 flex flex-col items-center">
                    <div class="w-full mb-2">
                        <h3 class="text-lg font-bold text-slate-900">Mood Mix</h3>
                    </div>
                    @if($totalEntries > 0)
                        <div class="relative w-40 h-40 my-auto">
                            <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" fill="transparent" r="40" stroke="#F2F2F7" stroke-width="12"></circle>
                                @php $offset = 0; $circumference = 251.2; @endphp
                                @foreach($moodPercentages as $mood => $data)
                                    @if($data['count'] > 0)
                                        @php $dash = ($data['percent']/100)*$circumference; @endphp
                                        <circle cx="50" cy="50" fill="transparent" r="40" stroke="{{ $data['color'] }}" stroke-dasharray="{{ $dash }} {{ $circumference }}" stroke-dashoffset="-{{ $offset }}" stroke-width="12"></circle>
                                        @php $offset += $dash; @endphp
                                    @endif
                                @endforeach
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-xl font-bold">{{ $totalEntries }}</span>
                                <span class="text-[10px] uppercase text-slate-400">Logs</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap justify-center gap-2 mt-4">
                            @foreach($moodPercentages as $mood => $data)
                                @if($data['count'] > 0)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium bg-slate-100 px-2 py-1 rounded-full text-slate-600">
                                        <span class="w-2 h-2 rounded-full" style="background:{{ $data['color'] }}"></span> {{ ucfirst($mood) }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="flex-1 flex items-center justify-center text-slate-400 text-sm">No data yet</div>
                    @endif
                </div>

                {{-- 3. AVERAGE RATING --}}
                <div class="glass-card rounded-3xl p-6 flex flex-col items-center justify-between">
                    <div class="w-full mb-2">
                        <h3 class="text-lg font-bold text-slate-900">Wellbeing Score</h3>
                    </div>
                    <div class="relative w-full flex-1 flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-primary">{{ $avgRating }}</div>
                            <div class="flex items-center justify-center gap-1 mt-2 text-slate-400 text-xs font-medium uppercase tracking-widest">
                                Average / 10
                            </div>
                        </div>
                    </div>
                    <div class="w-full grid grid-cols-2 gap-3 mt-4">
                        <div class="text-center p-2 bg-slate-50 rounded-xl">
                            <span class="block text-xs text-slate-400 uppercase">High</span>
                            <span class="font-bold text-slate-700">{{ $maxRating }}</span>
                        </div>
                        <div class="text-center p-2 bg-slate-50 rounded-xl">
                            <span class="block text-xs text-slate-400 uppercase">Low</span>
                            <span class="font-bold text-slate-700">{{ $minRating }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BOTTOM ROW: MINDSET TRACKER (NEW) --}}
            <div class="glass-card rounded-3xl p-6 md:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-indigo-50 rounded-xl text-indigo-600">
                        <span class="material-symbols-outlined">psychology</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Mindset Tracker</h3>
                        <p class="text-sm text-slate-500">Recent Goals & Affirmations</p>
                    </div>
                </div>

                @if($recentFocus->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($recentFocus as $focus)
                            <div class="bg-white/50 rounded-2xl p-5 border border-slate-100 hover:shadow-md transition-shadow">
                                <div class="text-xs font-semibold text-slate-400 mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                                    {{ $focus->entry_date->format('M d, Y') }}
                                </div>
                                
                                @if($focus->goals)
                                    <div class="mb-4">
                                        <p class="text-xs font-bold text-indigo-500 uppercase tracking-wide mb-1">Goal</p>
                                        <p class="text-sm text-slate-700 line-clamp-2">"{{ $focus->goals }}"</p>
                                    </div>
                                @endif

                                @if($focus->affirmations)
                                    <div>
                                        <p class="text-xs font-bold text-sky-500 uppercase tracking-wide mb-1">Affirmation</p>
                                        <p class="text-sm text-slate-700 italic line-clamp-2">"{{ $focus->affirmations }}"</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 text-slate-400">
                        <p>No goals or affirmations tracked yet.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection