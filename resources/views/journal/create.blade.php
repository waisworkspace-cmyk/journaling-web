@extends('layouts.app')

@section('title', 'Detailed Journal Entry')

@section('content')
{{-- Style khusus untuk halaman ini sesuai request --}}
<style>
    /* Custom Scrollbar */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, 0.1); border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(0, 0, 0, 0.2); }
    
    /* Range Slider Styling */
    input[type=range] { -webkit-appearance: none; background: transparent; }
    input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; }
    
    /* Glass Card Effect */
    .glass-card {
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        backdrop-filter: blur(10px);
    }
    
    /* Hide scrollbar for gallery */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<div class="flex-1 h-full overflow-y-auto bg-[#F8F9FA]">
    <form action="{{ route('journal.store') }}" method="POST" enctype="multipart/form-data" class="max-w-5xl mx-auto p-6 lg:p-12">
        @csrf
        <input type="hidden" name="date" value="{{ $selectedDate->format('Y-m-d') }}">

        {{-- HEADER --}}
        <header class="flex items-center justify-between mb-10 sticky top-0 bg-[#F8F9FA]/90 backdrop-blur-md z-30 py-4 -mx-4 px-4 lg:-mx-12 lg:px-12 border-b border-slate-200/50 lg:border-none">
            <div>
                <p class="text-slate-400 font-medium mb-1">{{ $selectedDate->format('l, F d, Y') }}</p>
                <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Daily Reflection</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('journal.index') }}" class="px-6 py-2.5 text-[15px] font-semibold text-slate-500 hover:text-slate-700 transition-colors">Discard</a>
                <button type="submit" class="px-8 py-2.5 bg-primary hover:bg-blue-600 text-white rounded-full font-semibold shadow-lg shadow-blue-500/20 transition-all transform active:scale-95">Save Entry</button>
            </div>
        </header>

        <div class="grid grid-cols-1 gap-8 animate-fade-in">
            
            {{-- TOP ROW: PHOTOS & METRICS --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                
                {{-- 1. MOMENTS (PHOTOS) --}}
                <div class="glass-card rounded-3xl p-6 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Moments</h3>
                        <label for="photoInput" class="text-primary text-sm font-medium cursor-pointer hover:underline">Add photos</label>
                        <input type="file" name="photos[]" id="photoInput" class="hidden" accept="image/*" multiple onchange="previewImages(event)">
                    </div>
                    
                    <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide snap-x" id="photoContainer">
                        {{-- Tombol Add (Visual) --}}
                        <div onclick="document.getElementById('photoInput').click()" class="shrink-0 w-28 h-28 rounded-2xl border-2 border-dashed border-slate-200 bg-white/50 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-primary/40 hover:bg-blue-50 transition-all group">
                            <span class="material-symbols-outlined text-slate-400 group-hover:text-primary transition-colors">add_a_photo</span>
                        </div>

                        {{-- Existing Photos --}}
                        @if(isset($entryToEdit) && $entryToEdit->photo_paths)
                            @foreach($entryToEdit->photo_paths as $path)
                                <div class="shrink-0 w-28 h-28 rounded-2xl overflow-hidden relative border border-white shadow-sm group">
                                    <img src="{{ asset('storage/' . $path) }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <label class="cursor-pointer bg-red-500 text-white p-1.5 rounded-full hover:bg-red-600 transition-colors shadow-sm">
                                            <input type="checkbox" name="remove_photos[]" value="{{ $path }}" class="hidden">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        
                        {{-- Preview New Photos will appear here --}}
                        <div id="newPhotoPreviews" class="flex gap-4"></div>
                    </div>
                </div>

                {{-- 2. METRICS (MOOD, WEATHER, RATING) --}}
                <div class="glass-card rounded-3xl p-6 space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        
                        {{-- Mood Selector --}}
                        <div class="space-y-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Mood</h3>
                            @php $currentMood = $entryToEdit->mood ?? 'neutral'; @endphp
                            <input type="hidden" name="mood" id="moodInput" value="{{ $currentMood }}">
                            <div class="flex gap-2">
                                @foreach(['sad' => '😢', 'neutral' => '😐', 'happy' => '🙂', 'excited' => '😀'] as $val => $emoji)
                                    <button type="button" onclick="selectOption('mood', '{{ $val }}', this)" 
                                        class="h-10 w-10 flex items-center justify-center rounded-xl border transition-all text-xl
                                        {{ $currentMood == $val ? 'bg-primary text-white shadow-lg shadow-blue-500/20 border-transparent' : 'bg-white border-slate-100 text-slate-400 grayscale hover:grayscale-0' }}">
                                        {{ $emoji }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Weather Selector (New Feature) --}}
                        <div class="space-y-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Weather</h3>
                            @php $currentWeather = $entryToEdit->weather ?? 'sunny'; @endphp
                            <input type="hidden" name="weather" id="weatherInput" value="{{ $currentWeather }}">
                            <div class="flex gap-2">
                                <button type="button" onclick="selectOption('weather', 'sunny', this)" class="weather-btn h-10 w-10 flex items-center justify-center rounded-xl border transition-all {{ $currentWeather == 'sunny' ? 'bg-primary text-white shadow-lg shadow-blue-500/20 border-transparent' : 'bg-white border-slate-100 text-slate-400' }}">
                                    <span class="material-symbols-outlined text-[20px]">wb_sunny</span>
                                </button>
                                <button type="button" onclick="selectOption('weather', 'cloudy', this)" class="weather-btn h-10 w-10 flex items-center justify-center rounded-xl border transition-all {{ $currentWeather == 'cloudy' ? 'bg-primary text-white shadow-lg shadow-blue-500/20 border-transparent' : 'bg-white border-slate-100 text-slate-400' }}">
                                    <span class="material-symbols-outlined text-[20px]">cloud</span>
                                </button>
                                <button type="button" onclick="selectOption('weather', 'rainy', this)" class="weather-btn h-10 w-10 flex items-center justify-center rounded-xl border transition-all {{ $currentWeather == 'rainy' ? 'bg-primary text-white shadow-lg shadow-blue-500/20 border-transparent' : 'bg-white border-slate-100 text-slate-400' }}">
                                    <span class="material-symbols-outlined text-[20px]">rainy</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Rating Slider --}}
                    @php $currentRating = $entryToEdit->rating ?? 8; @endphp
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Daily Rating</h3>
                            <span class="text-primary font-bold"><span id="ratingDisplay">{{ $currentRating }}</span> / 10</span>
                        </div>
                        <div class="relative h-6 flex items-center group">
                            <input name="rating" class="w-full absolute z-20 opacity-0 cursor-pointer h-full" max="10" min="1" type="range" value="{{ $currentRating }}" oninput="updateRating(this.value)"/>
                            
                            {{-- Custom Track --}}
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden relative z-10 border border-slate-200/50">
                                <div id="ratingFill" class="h-full bg-primary rounded-full transition-all duration-150 ease-out" style="width: {{ $currentRating * 10 }}%;"></div>
                            </div>
                            
                            {{-- Custom Thumb Handle --}}
                            <div id="ratingHandle" class="h-6 w-6 bg-white rounded-full shadow-lg absolute z-10 pointer-events-none transform -translate-x-1/2 border border-slate-200 transition-all duration-150 ease-out flex items-center justify-center" style="left: {{ $currentRating * 10 }}%;">
                                <div class="w-2 h-2 bg-primary rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MAIN TEXT AREAS --}}
            <div class="space-y-6">
                
                {{-- 1. Positivity --}}
                <section class="glass-card rounded-3xl p-8 hover:shadow-md transition-shadow duration-300">
                    <header class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 shadow-sm">
                            <span class="material-symbols-outlined font-bold">favorite</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Positivity</h3>
                            <p class="text-slate-400 text-sm">What went well today?</p>
                        </div>
                    </header>
                    <textarea name="positive" class="w-full bg-transparent border-none focus:ring-0 p-0 text-slate-700 text-lg placeholder:text-slate-300 min-h-[120px] resize-none leading-relaxed" placeholder="Share your wins and happy moments...">{{ $entryToEdit->positive_highlight ?? '' }}</textarea>
                </section>

                {{-- 2. Improvement (Negative) --}}
                <section class="glass-card rounded-3xl p-8 hover:shadow-md transition-shadow duration-300">
                    <header class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 shadow-sm">
                            <span class="material-symbols-outlined font-bold">trending_up</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Improvement</h3>
                            <p class="text-slate-400 text-sm">What could have gone better?</p>
                        </div>
                    </header>
                    <textarea name="negative" class="w-full bg-transparent border-none focus:ring-0 p-0 text-slate-700 text-lg placeholder:text-slate-300 min-h-[120px] resize-none leading-relaxed" placeholder="Reflect on areas for growth...">{{ $entryToEdit->negative_reflection ?? '' }}</textarea>
                </section>

                {{-- 3. Gratitude (New) --}}
                <section class="glass-card rounded-3xl p-8 hover:shadow-md transition-shadow duration-300">
                    <header class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 shadow-sm">
                            <span class="material-symbols-outlined font-bold">volunteer_activism</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Gratitude</h3>
                            <p class="text-slate-400 text-sm">What am I really grateful for?</p>
                        </div>
                    </header>
                    <textarea name="gratitude" class="w-full bg-transparent border-none focus:ring-0 p-0 text-slate-700 text-lg placeholder:text-slate-300 min-h-[120px] resize-none leading-relaxed" placeholder="I'm thankful for...">{{ $entryToEdit->gratitude ?? '' }}</textarea>
                </section>

                {{-- 4. Goals & Affirmations (Split) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <section class="glass-card rounded-3xl p-8 hover:shadow-md transition-shadow duration-300">
                        <header class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                                <span class="material-symbols-outlined font-bold">flag</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Goals</h3>
                                <p class="text-slate-400 text-sm">I will make tomorrow great by...</p>
                            </div>
                        </header>
                        <textarea name="goals" class="w-full bg-transparent border-none focus:ring-0 p-0 text-slate-700 text-lg placeholder:text-slate-300 min-h-[120px] resize-none leading-relaxed" placeholder="Define your focus...">{{ $entryToEdit->goals ?? '' }}</textarea>
                    </section>

                    <section class="glass-card rounded-3xl p-8 hover:shadow-md transition-shadow duration-300">
                        <header class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-sky-100 flex items-center justify-center text-sky-600 shadow-sm">
                                <span class="material-symbols-outlined font-bold">colors_spark</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Affirmations</h3>
                                <p class="text-slate-400 text-sm">Today I always am...</p>
                            </div>
                        </header>
                        <textarea name="affirmations" class="w-full bg-transparent border-none focus:ring-0 p-0 text-slate-700 text-lg placeholder:text-slate-300 min-h-[120px] resize-none leading-relaxed" placeholder="Speak kindness to yourself...">{{ $entryToEdit->affirmations ?? '' }}</textarea>
                    </section>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="py-12 flex justify-center">
                <div class="flex items-center gap-2 text-slate-400 hover:text-slate-600 transition-colors cursor-help" title="Your data is safe">
                    <span class="material-symbols-outlined text-[18px]">lock</span>
                    <span class="text-sm font-medium">End-to-end encrypted entry</span>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Logic untuk Slider Rating Visual
    function updateRating(val) {
        document.getElementById('ratingDisplay').innerText = val;
        // Update lebar fill bar
        document.getElementById('ratingFill').style.width = (val * 10) + '%';
        // Update posisi handle
        document.getElementById('ratingHandle').style.left = (val * 10) + '%';
    }

    // Logic untuk Mood & Weather Selector
    function selectOption(type, value, btn) {
        document.getElementById(type + 'Input').value = value;
        
        // Reset tombol dalam group yang sama
        const buttons = btn.parentElement.querySelectorAll('button');
        buttons.forEach(b => {
            b.className = 'h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-slate-100 text-slate-400 shadow-sm transition-all ' + (type === 'mood' ? 'grayscale hover:grayscale-0' : '');
        });

        // Highlight tombol aktif
        btn.className = 'h-10 w-10 flex items-center justify-center rounded-xl bg-primary text-white shadow-lg shadow-blue-500/20 border-transparent transition-all transform scale-105';
    }

    // Logic Preview Gambar
    function previewImages(event) {
        const container = document.getElementById('newPhotoPreviews');
        container.innerHTML = '';
        const files = event.target.files;

        if (files) {
            for(let i=0; i<files.length; i++) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'shrink-0 w-28 h-28 rounded-2xl overflow-hidden relative border border-white shadow-sm';
                    div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                    container.appendChild(div);
                }
                reader.readAsDataURL(files[i]);
            }
        }
    }
</script>
@endsection