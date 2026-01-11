@extends('layouts.app')

@section('title', 'Journal Calendar')

@section('content')
<div class="h-full flex flex-col relative z-0 transition-all duration-300 {{ isset($showCreateModal) ? 'blur-sm scale-[0.99]' : '' }}">
    
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

    <div class="flex-1 overflow-y-auto p-8 pt-2">
        <div class="grid grid-cols-7 gap-4 mb-4 text-center">
            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $day }}</div>
            @endforeach
        </div>

        {{-- UPDATE: Tinggi minimum dinaikkan lagi ke 1100px agar muat untuk foto besar & teks lebih besar --}}
        <div class="grid grid-cols-7 grid-rows-5 gap-4 min-h-[1100px]">
            @for($i=0; $i < $startDayOfWeek; $i++)
                <div class="glass-card rounded-2xl p-3 opacity-30 bg-gray-50 border-transparent shadow-none"></div>
            @endfor

            @for($day=1; $day <= $daysInMonth; $day++)
                @php 
                    $hasEntry = isset($entries[$day]);
                    $entry = $hasEntry ? $entries[$day] : null;
                    $isToday = ($day == now()->day && $monthInt == now()->month && $currentYear == now()->year);
                    $dateString = \Carbon\Carbon::createFromDate($currentYear, $monthInt, $day)->format('Y-m-d');
                    
                    // Hitung jumlah foto untuk slider
                    $photoCount = $hasEntry && $entry->photo_paths ? count($entry->photo_paths) : 0;
                    $slideAnimation = $photoCount > 1 ? 'animate-slide-' . $photoCount : '';
                    $widthClass = $photoCount > 0 ? 'width: ' . ($photoCount * 100) . '%' : '';
                @endphp

                <a href="{{ route('journal.create', ['date' => $dateString]) }}" 
                   class="glass-card rounded-2xl p-3 flex flex-col relative group cursor-pointer transition-all duration-300
                            {{ $isToday ? 'border-primary/50 bg-primary/5 shadow-md ring-1 ring-primary/20' : '' }}
                            {{ $hasEntry ? 'border-green-400/30 bg-white/80' : 'hover:bg-white/60' }}">
                    
                    <div class="flex justify-between items-start z-10 relative">
                        <span class="text-lg font-medium {{ $isToday ? 'text-primary font-bold' : 'text-slate-700' }}">{{ $day }}</span>
                        @if($hasEntry)
                            <span class="text-xl transform group-hover:scale-110 transition-transform">
                                @if($entry->mood == 'happy') 🙂 @elseif($entry->mood == 'sad') 😢 @elseif($entry->mood == 'excited') 🤩 @else 😐 @endif
                            </span>
                        @endif
                    </div>

                    @if($hasEntry && $photoCount > 0)
                        {{-- Foto tetap besar (h-40) sesuai permintaan sebelumnya --}}
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
                            {{-- UPDATE: Font highlight diperbesar dari text-[10px] ke text-xs --}}
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

{{-- Bagian Modal (Create/Edit) tidak berubah --}}
@if(isset($showCreateModal) && $showCreateModal)
@php
    $existingPhotos = isset($entryToEdit) && $entryToEdit->photo_paths ? $entryToEdit->photo_paths : [];
    $countExisting = count($existingPhotos);
    $slotsLeft = 4 - $countExisting;
@endphp

<div class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-slate-900/20 backdrop-blur-sm animate-fade-in">
    <a href="{{ route('journal.index', ['month' => $monthInt, 'year' => $currentYear]) }}" class="absolute inset-0 z-0 cursor-default"></a>

    <form action="{{ route('journal.store') }}" method="POST" enctype="multipart/form-data" class="relative w-full max-w-[640px] max-h-[90vh] flex flex-col bg-white/90 backdrop-blur-2xl border border-white/60 shadow-2xl rounded-2xl overflow-hidden ring-1 ring-black/5 z-10 transform transition-all scale-100">
        @csrf
        <input type="hidden" name="date" value="{{ $selectedDate->format('Y-m-d') }}">

        <header class="flex items-center justify-between px-6 py-4 border-b border-slate-200/60 shrink-0 bg-white/60 sticky top-0 z-10 backdrop-blur-md">
            <a href="{{ route('journal.index', ['month' => $monthInt, 'year' => $currentYear]) }}" class="text-primary hover:text-blue-600 text-[17px] font-normal px-2 py-1 rounded hover:bg-black/5">Cancel</a>
            <h2 class="text-slate-900 text-[17px] font-semibold tracking-tight">{{ isset($entryToEdit) ? 'Edit Entry' : 'New Entry' }}</h2>
            <button type="submit" class="bg-primary hover:bg-blue-600 text-white text-[15px] font-semibold px-5 py-1.5 rounded-full shadow-lg shadow-blue-500/20">Save</button>
        </header>

        <div class="flex-1 overflow-y-auto p-0 scroll-smooth">
            <div class="pt-6 pb-2 text-center">
                <p class="text-slate-400 text-sm font-medium">{{ $selectedDate->format('F d, Y') }}</p>
            </div>

            <div class="px-6 py-4">
                <h3 class="text-slate-900 text-sm font-semibold mb-3 px-1 flex justify-between">
                    Moments 
                    <span class="text-xs text-slate-400 font-normal">{{ $countExisting }}/4 Used</span>
                </h3>
                
                <div class="grid grid-cols-4 gap-3 mb-6">
                    @foreach($existingPhotos as $path)
                        <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-200 group bg-slate-50" id="existing-{{ $loop->index }}">
                            <img src="{{ asset('storage/' . $path) }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <label class="cursor-pointer bg-red-500 text-white p-1.5 rounded-full hover:bg-red-600 transition-colors shadow-sm">
                                    <input type="checkbox" name="remove_photos[]" value="{{ $path }}" class="hidden" onchange="markForDeletion(this, 'existing-{{ $loop->index }}')">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </label>
                            </div>
                        </div>
                    @endforeach

                    @if($slotsLeft > 0)
                        <div class="aspect-square rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 hover:bg-slate-100 transition-colors cursor-pointer flex flex-col items-center justify-center text-slate-400 hover:text-primary relative group overflow-hidden" onclick="document.getElementById('newPhotosInput').click()">
                            <span class="material-symbols-outlined">add_a_photo</span>
                            <span class="text-[10px] font-medium mt-1">Add</span>
                            <div id="newPhotoPreviewContainer" class="absolute inset-0 bg-white hidden flex-col items-center justify-center">
                                <span class="text-xs text-slate-500 font-medium" id="newPhotoCount">0</span>
                                <span class="text-[10px] text-slate-400">Selected</span>
                            </div>
                        </div>
                        <input type="file" name="photos[]" id="newPhotosInput" class="hidden" accept="image/*" multiple onchange="handleFileSelect(event, {{ $slotsLeft }})">
                    @endif

                    @for($i = 0; $i < (4 - $countExisting - ($slotsLeft > 0 ? 1 : 0)); $i++)
                        <div class="aspect-square rounded-xl border border-slate-100 bg-slate-50/50"></div>
                    @endfor
                </div>

                <div class="h-px bg-slate-200 mx-1 mb-6"></div>

                @php $currentMood = $entryToEdit->mood ?? 'neutral'; @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div class="flex flex-col gap-3">
                        <label class="text-slate-900 text-sm font-semibold">Mood</label>
                        <div class="flex gap-2 flex-wrap">
                            <input type="hidden" name="mood" id="moodInput" value="{{ $currentMood }}">
                            <button type="button" onclick="selectMood('sad', this)" class="mood-btn h-10 w-10 flex items-center justify-center rounded-lg {{ $currentMood == 'sad' ? 'bg-primary text-white ring-2 ring-blue-500/20 scale-105' : 'bg-slate-100 text-slate-500' }} text-xl transition-all">😢</button>
                            <button type="button" onclick="selectMood('neutral', this)" class="mood-btn h-10 w-10 flex items-center justify-center rounded-lg {{ $currentMood == 'neutral' ? 'bg-primary text-white ring-2 ring-blue-500/20 scale-105' : 'bg-slate-100 text-slate-500' }} text-xl transition-all">😐</button>
                            <button type="button" onclick="selectMood('happy', this)" class="mood-btn h-10 w-10 flex items-center justify-center rounded-lg {{ $currentMood == 'happy' ? 'bg-primary text-white ring-2 ring-blue-500/20 scale-105' : 'bg-slate-100 text-slate-500' }} text-xl transition-all">🙂</button>
                            <button type="button" onclick="selectMood('excited', this)" class="mood-btn h-10 w-10 flex items-center justify-center rounded-lg {{ $currentMood == 'excited' ? 'bg-primary text-white ring-2 ring-blue-500/20 scale-105' : 'bg-slate-100 text-slate-500' }} text-xl transition-all">🤩</button>
                        </div>
                    </div>
                </div>

                @php $currentRating = $entryToEdit->rating ?? 7; @endphp
                <div class="flex flex-col gap-4 mb-8">
                    <div class="flex justify-between items-end">
                        <label class="text-slate-900 text-sm font-semibold">Daily Rating</label>
                        <span class="text-primary text-xl font-bold font-display"><span id="ratingValue">{{ $currentRating }}</span><span class="text-slate-400 text-sm font-medium ml-1">/ 10</span></span>
                    </div>
                    <input name="rating" type="range" min="1" max="10" value="{{ $currentRating }}" class="w-full accent-primary h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer" oninput="document.getElementById('ratingValue').innerText = this.value">
                </div>

                <div class="h-px bg-slate-200 mx-1 mb-6"></div>

                <div class="space-y-6 pb-12">
                    <div class="group">
                        <label class="flex items-center gap-2 text-slate-900 text-sm font-semibold mb-2">
                            <span class="material-symbols-outlined text-green-500 text-sm filled">thumb_up</span> Positive Highlights
                        </label>
                        <textarea name="positive" class="w-full bg-slate-50 text-slate-800 text-sm rounded-xl border border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary p-3 resize-none shadow-sm transition-all" rows="3">{{ $entryToEdit->positive_highlight ?? '' }}</textarea>
                    </div>
                    <div class="group">
                        <label class="flex items-center gap-2 text-slate-900 text-sm font-semibold mb-2">
                            <span class="material-symbols-outlined text-red-500 text-sm filled">thumb_down</span> Negative Reflections
                        </label>
                        <textarea name="negative" class="w-full bg-slate-50 text-slate-800 text-sm rounded-xl border border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary p-3 resize-none shadow-sm transition-all" rows="3">{{ $entryToEdit->negative_reflection ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function selectMood(mood, btn) {
        document.getElementById('moodInput').value = mood;
        document.querySelectorAll('.mood-btn').forEach(b => {
            b.className = 'mood-btn h-10 w-10 flex items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 text-xl transition-all';
        });
        btn.className = 'mood-btn h-10 w-10 flex items-center justify-center rounded-lg bg-primary text-white shadow-lg text-xl scale-105 ring-2 ring-blue-500/20 transition-all';
    }

    // Fungsi visual saat foto lama dihapus
    function markForDeletion(checkbox, divId) {
        const div = document.getElementById(divId);
        if (checkbox.checked) {
            div.classList.add('opacity-50', 'grayscale', 'scale-95'); // Efek visual dihapus
            div.classList.remove('border-slate-200');
            div.classList.add('border-red-500', 'ring-2', 'ring-red-500/20');
        } else {
            div.classList.remove('opacity-50', 'grayscale', 'scale-95', 'border-red-500', 'ring-2', 'ring-red-500/20');
            div.classList.add('border-slate-200');
        }
    }

    // Handle upload foto baru (Multiple)
    function handleFileSelect(event, maxAllowed) {
        const files = event.target.files;
        const container = document.getElementById('newPhotoPreviewContainer');
        const countSpan = document.getElementById('newPhotoCount');
        
        if (files.length > 0) {
            if (files.length > maxAllowed) {
                alert('You can only add ' + maxAllowed + ' more photo(s).');
                event.target.value = ""; // Reset
                container.classList.add('hidden');
                return;
            }
            container.classList.remove('hidden');
            container.classList.add('flex');
            countSpan.innerText = '+' + files.length;
        } else {
            container.classList.add('hidden');
        }
    }
</script>
@endif
@endsection