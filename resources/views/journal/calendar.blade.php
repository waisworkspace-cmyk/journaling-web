@extends('layouts.app')

@section('title', 'Journal Calendar')

@section('content')
<style>
    /* Sembunyikan scrollbar tapi tetap bisa scroll */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    
    /* Animasi Modal */
    .modal-enter { opacity: 0; transform: scale(0.95); }
    .modal-enter-active { opacity: 1; transform: scale(1); transition: opacity 0.3s ease-out, transform 0.3s ease-out; }
    .modal-exit { opacity: 1; transform: scale(1); }
    .modal-exit-active { opacity: 0; transform: scale(0.95); transition: opacity 0.2s ease-in, transform 0.2s ease-in; }

    /* --- PERBAIKAN ANIMASI SLIDER GAMBAR --- */
    /* Keyframes untuk 2 gambar (geser 50%) */
    @keyframes slide-2 {
        0%, 45% { transform: translateX(0%); }
        50%, 95% { transform: translateX(-50%); } /* -50% karena ada 2 gambar (100/2) */
        100% { transform: translateX(0%); }
    }
    /* Keyframes untuk 3 gambar */
    @keyframes slide-3 {
        0%, 30% { transform: translateX(0%); }
        33%, 63% { transform: translateX(-33.33%); }
        66%, 96% { transform: translateX(-66.66%); }
        100% { transform: translateX(0%); }
    }
    /* Keyframes untuk 4 gambar */
    @keyframes slide-4 {
        0%, 22% { transform: translateX(0%); }
        25%, 47% { transform: translateX(-25%); }
        50%, 72% { transform: translateX(-50%); }
        75%, 97% { transform: translateX(-75%); }
        100% { transform: translateX(0%); }
    }

    /* Class Helper untuk Trigger Animasi */
    .animate-slide-2 { animation: slide-2 8s infinite cubic-bezier(0.4, 0, 0.2, 1); }
    .animate-slide-3 { animation: slide-3 12s infinite cubic-bezier(0.4, 0, 0.2, 1); }
    .animate-slide-4 { animation: slide-4 16s infinite cubic-bezier(0.4, 0, 0.2, 1); }
</style>

<div class="h-full flex flex-col relative z-0 bg-[#F8F9FA]">
    
    {{-- HEADER --}}
    <header class="h-16 flex items-center justify-between px-8 z-10 sticky top-0 bg-[#F8F9FA]/90 backdrop-blur-md border-b border-slate-200/60">
        <div class="flex items-center gap-6">
            <h2 class="text-2xl font-bold tracking-tight text-slate-800">{{ $currentMonth }} {{ $currentYear }}</h2>
            <div class="flex items-center gap-1 bg-white p-1 rounded-lg border border-slate-200 shadow-sm">
                <a href="{{ route('journal.index', ['month' => $prevDate->month, 'year' => $prevDate->year]) }}" class="p-1 hover:bg-slate-100 rounded-md text-slate-600 transition-colors"><span class="material-symbols-outlined">chevron_left</span></a>
                <a href="{{ route('journal.index', ['month' => $nextDate->month, 'year' => $nextDate->year]) }}" class="p-1 hover:bg-slate-100 rounded-md text-slate-600 transition-colors"><span class="material-symbols-outlined">chevron_right</span></a>
                <a href="{{ route('journal.index') }}" class="px-3 py-1 text-xs font-medium hover:bg-slate-100 rounded-md text-slate-600 transition-colors">Today</a>
            </div>
        </div>
    </header>

    {{-- CALENDAR GRID --}}
    <div class="flex-1 overflow-y-auto p-8 pt-2">
        {{-- Days Header --}}
        <div class="grid grid-cols-7 gap-4 mb-4 text-center">
            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $day }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 grid-rows-5 gap-4 min-h-[1100px]">
            {{-- Empty Slots --}}
            @for($i=0; $i < $startDayOfWeek; $i++)
                <div class="glass-card rounded-2xl p-3 opacity-30 bg-gray-50 border-transparent shadow-none"></div>
            @endfor

            {{-- Dates Loop --}}
            @for($day=1; $day <= $daysInMonth; $day++)
                @php 
                    $hasEntry = isset($entries[$day]);
                    $entry = $hasEntry ? $entries[$day] : null;
                    $isToday = ($day == now()->day && $monthInt == now()->month && $currentYear == now()->year);
                    
                    // Logic animasi foto (slide otomatis)
                    $photoCount = $hasEntry && $entry->photo_paths ? count($entry->photo_paths) : 0;
                    
                    // Tentukan class animasi berdasarkan jumlah foto
                    $slideAnimation = '';
                    if ($photoCount > 1) {
                        $slideAnimation = 'animate-slide-' . min($photoCount, 4); 
                    }
                    
                    // Hitung lebar container (misal 2 foto = 200%, 3 foto = 300%)
                    $widthClass = $photoCount > 0 ? 'width: ' . ($photoCount * 100) . '%' : '';
                @endphp

                {{-- 
                    INTERAKSI:
                    - Jika ada entry -> onclick="openPreview(...)"
                    - Jika kosong -> href="..." langsung ke Create
                --}}
                @if($hasEntry)
                    <div onclick="openPreview({{ $day }})" 
                @else
                    <a href="{{ route('journal.create', ['date' => \Carbon\Carbon::createFromDate($currentYear, $monthInt, $day)->format('Y-m-d')]) }}" 
                @endif
                   class="glass-card rounded-2xl p-3 flex flex-col relative group cursor-pointer transition-all duration-300 hover:scale-[1.02] hover:shadow-lg
                            {{ $isToday ? 'border-primary/50 bg-primary/5 shadow-md ring-1 ring-primary/20' : 'bg-white/60' }}
                            {{ $hasEntry ? 'border-slate-200' : '' }}">
                    
                    {{-- Header Tanggal & Mood --}}
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

                    {{-- Preview Foto (Slider) --}}
                    @if($hasEntry && $photoCount > 0)
                        <div class="mt-2 w-full h-40 rounded-lg overflow-hidden relative shadow-sm group-hover:shadow-md transition-all">
                             {{-- Container Animasi --}}
                             <div class="h-full flex {{ $slideAnimation }}" style="{{ $widthClass }}">
                                 @foreach($entry->photo_paths as $path)
                                     <div class="h-full w-full flex-shrink-0 bg-gray-100">
                                         <img src="{{ asset('storage/' . $path) }}" class="w-full h-full object-cover">
                                     </div>
                                 @endforeach
                             </div>
                             
                             {{-- Indikator Titik --}}
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
                    @elseif($isToday && !$hasEntry)
                        <div class="mt-auto"><span class="text-[10px] font-semibold text-primary uppercase tracking-wide">Today</span></div>
                    @elseif(!$hasEntry)
                         <div class="mt-auto opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="text-[10px] text-slate-400 font-medium">Create Entry</span>
                        </div>
                    @endif

                @if($hasEntry) </div> @else </a> @endif
            @endfor
        </div>
    </div>
</div>

{{-- 
    PREVIEW MODAL 
    Isinya disesuaikan dengan Create: Photos, Metrics (Mood, Weather, Rating), dan Text Sections.
--}}
<div id="previewModal" class="fixed inset-0 z-50 hidden transition-opacity duration-300" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div id="modalBackdrop" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" onclick="closePreview()"></div>
    
    {{-- Modal Panel --}}
    <div class="relative z-10 flex h-full items-center justify-center p-4 pointer-events-none">
        <div id="modalPanel" class="pointer-events-auto w-full max-w-3xl transform scale-95 opacity-0 rounded-3xl bg-white shadow-2xl transition-all duration-300 flex flex-col max-h-[90vh] overflow-hidden border border-slate-100">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0 bg-white sticky top-0 z-20">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Entry Preview</p>
                    <h3 id="previewDate" class="text-xl font-bold text-slate-800 mt-0.5">Date</h3>
                </div>
                <button type="button" onclick="closePreview()" class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-full hover:bg-slate-100">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            {{-- Modal Content (Scrollable) --}}
            <div class="p-6 overflow-y-auto space-y-8 bg-[#F8F9FA]">
                
                {{-- 1. PHOTOS (Moments) --}}
                <div id="previewPhotosSection" class="hidden">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Moments</h4>
                    <div id="previewPhotos" class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide snap-x">
                        </div>
                </div>

                {{-- 2. METRICS (Mood, Weather, Rating) --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 grid grid-cols-3 gap-6 text-center">
                    <div class="flex flex-col items-center gap-2 border-r border-slate-100">
                        <span class="text-xs font-bold text-slate-400 uppercase">Mood</span>
                        <div class="text-3xl" id="previewMood">😐</div>
                        <span id="previewMoodText" class="text-xs font-medium text-slate-600 capitalize">Neutral</span>
                    </div>
                    <div class="flex flex-col items-center gap-2 border-r border-slate-100">
                        <span class="text-xs font-bold text-slate-400 uppercase">Weather</span>
                        <div class="text-3xl"><span id="previewWeatherIcon" class="material-symbols-outlined text-3xl text-blue-400">cloud</span></div>
                        <span id="previewWeatherText" class="text-xs font-medium text-slate-600 capitalize">Cloudy</span>
                    </div>
                    <div class="flex flex-col items-center gap-2">
                        <span class="text-xs font-bold text-slate-400 uppercase">Rating</span>
                        <div class="text-3xl font-bold text-primary"><span id="previewRating">0</span><span class="text-lg text-slate-300 font-normal">/10</span></div>
                        <span class="text-xs font-medium text-slate-600">Daily Score</span>
                    </div>
                </div>

                {{-- 3. TEXT CONTENT --}}
                <div class="grid grid-cols-1 gap-6">
                    {{-- Positivity --}}
                    <div id="secPositive" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hidden">
                        <h4 class="flex items-center gap-2 text-sm font-bold text-slate-900 mb-3">
                            <span class="p-1 rounded-full bg-green-100 text-green-600"><span class="material-symbols-outlined text-sm">favorite</span></span>
                            Positivity
                        </h4>
                        <p id="txtPositive" class="text-slate-600 leading-relaxed text-[15px]"></p>
                    </div>

                    {{-- Improvement --}}
                    <div id="secNegative" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hidden">
                        <h4 class="flex items-center gap-2 text-sm font-bold text-slate-900 mb-3">
                            <span class="p-1 rounded-full bg-amber-100 text-amber-600"><span class="material-symbols-outlined text-sm">trending_up</span></span>
                            Improvement
                        </h4>
                        <p id="txtNegative" class="text-slate-600 leading-relaxed text-[15px]"></p>
                    </div>

                    {{-- Gratitude --}}
                    <div id="secGratitude" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hidden">
                        <h4 class="flex items-center gap-2 text-sm font-bold text-slate-900 mb-3">
                            <span class="p-1 rounded-full bg-rose-100 text-rose-600"><span class="material-symbols-outlined text-sm">volunteer_activism</span></span>
                            Gratitude
                        </h4>
                        <p id="txtGratitude" class="text-slate-600 leading-relaxed text-[15px]"></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Goals --}}
                        <div id="secGoals" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hidden">
                            <h4 class="flex items-center gap-2 text-sm font-bold text-slate-900 mb-3">
                                <span class="p-1 rounded-full bg-indigo-100 text-indigo-600"><span class="material-symbols-outlined text-sm">flag</span></span>
                                Goals
                            </h4>
                            <p id="txtGoals" class="text-slate-600 leading-relaxed text-[15px]"></p>
                        </div>
                        
                        {{-- Affirmations --}}
                        <div id="secAffirmations" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hidden">
                            <h4 class="flex items-center gap-2 text-sm font-bold text-slate-900 mb-3">
                                <span class="p-1 rounded-full bg-sky-100 text-sky-600"><span class="material-symbols-outlined text-sm">auto_awesome</span></span>
                                Affirmations
                            </h4>
                            <p id="txtAffirmations" class="text-slate-600 leading-relaxed text-[15px]"></p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="p-4 border-t border-slate-100 bg-white flex justify-end gap-3 shrink-0">
                <button onclick="closePreview()" class="px-6 py-2.5 text-sm font-semibold text-slate-500 hover:text-slate-700 transition-colors rounded-full hover:bg-slate-50">Close</button>
                <a id="btnEdit" href="#" class="px-8 py-2.5 bg-primary text-white text-sm font-bold rounded-full shadow-lg shadow-blue-500/20 hover:bg-blue-600 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">edit_note</span> Edit Entry
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Data Entry dari Server
    const entries = @json($entries);
    const currentYear = {{ $currentYear }};
    const currentMonth = {{ $monthInt }};

    const modal = document.getElementById('previewModal');
    const backdrop = document.getElementById('modalBackdrop');
    const panel = document.getElementById('modalPanel');

    function openPreview(day) {
        // Construct date YYYY-MM-DD
        const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const editUrl = `{{ route('journal.create') }}?date=${dateStr}`;
        const entry = entries[day];

        // Jika tidak ada entry (untuk keamanan), langsung redirect
        if (!entry) {
            window.location.href = editUrl;
            return;
        }

        // --- POPULATE MODAL ---
        
        // 1. Date Header
        const dateObj = new Date(dateStr);
        document.getElementById('previewDate').innerText = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        
        // 2. Edit Link
        document.getElementById('btnEdit').href = editUrl;

        // 3. Metrics
        // Mood
        const moods = {'happy': '🙂', 'sad': '😢', 'excited': '🤩', 'neutral': '😐', 'anxious': '😰'};
        document.getElementById('previewMood').innerText = moods[entry.mood] || '😐';
        document.getElementById('previewMoodText').innerText = entry.mood || 'Neutral';
        
        // Weather
        const weatherIcons = {'sunny': 'wb_sunny', 'cloudy': 'cloud', 'rainy': 'rainy'};
        const wVal = entry.weather || 'sunny';
        document.getElementById('previewWeatherIcon').innerText = weatherIcons[wVal] || 'wb_sunny';
        document.getElementById('previewWeatherText').innerText = wVal;
        
        // Rating
        document.getElementById('previewRating').innerText = entry.rating || 0;

        // 4. Photos
        const photoSection = document.getElementById('previewPhotosSection');
        const photoContainer = document.getElementById('previewPhotos');
        photoContainer.innerHTML = '';
        
        if (entry.photo_paths && entry.photo_paths.length > 0) {
            photoSection.classList.remove('hidden');
            entry.photo_paths.forEach(path => {
                const imgUrl = `/storage/${path}`;
                photoContainer.innerHTML += `
                    <div class="shrink-0 w-40 h-40 rounded-2xl overflow-hidden shadow-sm border border-slate-100">
                        <img src="${imgUrl}" class="w-full h-full object-cover">
                    </div>`;
            });
        } else {
            photoSection.classList.add('hidden');
        }

        // 5. Text Sections
        setText('secPositive', 'txtPositive', entry.positive_highlight);
        setText('secNegative', 'txtNegative', entry.negative_reflection);
        setText('secGratitude', 'txtGratitude', entry.gratitude);
        setText('secGoals', 'txtGoals', entry.goals);
        setText('secAffirmations', 'txtAffirmations', entry.affirmations);

        // --- SHOW MODAL ---
        modal.classList.remove('hidden');
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'scale-95');
            panel.classList.add('opacity-100', 'scale-100');
        }, 10);
    }

    function setText(sectionId, textId, content) {
        const section = document.getElementById(sectionId);
        const textElement = document.getElementById(textId);
        if (content && content.trim() !== "") {
            textElement.innerText = content;
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
        }
    }

    function closePreview() {
        backdrop.classList.add('opacity-0');
        panel.classList.remove('opacity-100', 'scale-100');
        panel.classList.add('opacity-0', 'scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePreview();
    });
</script>
@endsection