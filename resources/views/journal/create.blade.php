@extends('layouts.app')
@section('title', 'New Entry')
@section('content')

{{-- Container Utama: Full Page, Tengah, Rapi --}}
<div class="w-full min-h-full p-4 sm:p-8 overflow-y-auto">
    <form action="{{ route('journal.store') }}" method="POST" enctype="multipart/form-data" class="w-full max-w-3xl mx-auto">
        @csrf
        <input type="hidden" name="date" value="{{ $selectedDate->format('Y-m-d') }}">
        
        <div class="relative w-full flex flex-col bg-white border border-slate-200 shadow-xl rounded-2xl overflow-hidden ring-1 ring-black/5 animate-fade-in">
            
            {{-- Header Sticky --}}
            <header class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0 bg-white sticky top-0 z-30">
                <a href="{{ route('journal.index') }}" class="text-slate-500 hover:text-slate-700 text-[15px] font-medium transition-colors px-3 py-1.5 rounded-lg hover:bg-slate-50">Cancel</a>
                <div class="text-center"><h2 class="text-slate-900 text-[17px] font-semibold tracking-tight">
                    {{ isset($entryToEdit) ? 'Edit Entry' : 'New Entry' }}
                </h2></div>
                <button type="submit" class="bg-primary hover:bg-blue-600 text-white text-[15px] font-semibold px-5 py-1.5 rounded-full transition-colors shadow-lg shadow-blue-500/20">Save</button>
            </header>

            {{-- Content Area --}}
            <div class="p-0">
                <div class="flex flex-col w-full">
                    <div class="pt-8 pb-4 text-center">
                        <p class="text-slate-400 text-sm font-medium uppercase tracking-wide">Writing for</p>
                        <h1 class="text-2xl font-bold text-slate-800 mt-1">{{ $selectedDate->format('F d, Y') }}</h1>
                    </div>

                    <div class="px-6 sm:px-10 py-6">
                        <div class="mb-10">
                            <h3 class="text-slate-900 text-sm font-semibold mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg text-slate-400">image</span> Moments
                            </h3>
                            
                            {{-- Logic Foto Sederhana: Tampilkan yang sudah ada + Input Baru --}}
                            @php
                                $existingPhotos = isset($entryToEdit) && $entryToEdit->photo_paths ? $entryToEdit->photo_paths : [];
                            @endphp

                            <div class="flex gap-4 overflow-x-auto pb-4 scrollbar-hide snap-x">
                                {{-- Tampilkan Foto Lama --}}
                                @foreach($existingPhotos as $path)
                                    <div class="shrink-0 w-36 h-36 rounded-2xl border border-slate-200 bg-slate-50 relative overflow-hidden">
                                        <img src="{{ asset('storage/' . $path) }}" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <label class="cursor-pointer bg-red-500 text-white p-2 rounded-full hover:bg-red-600 shadow-sm">
                                                <input type="checkbox" name="remove_photos[]" value="{{ $path }}" class="hidden">
                                                <span class="material-symbols-outlined text-sm">delete</span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Tombol Tambah Foto Baru --}}
                                <div onclick="document.getElementById('photoInput').click()" class="shrink-0 w-36 h-36 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 flex flex-col items-center justify-center gap-2 cursor-pointer text-slate-400 hover:text-primary hover:border-primary hover:bg-blue-50 transition-all relative overflow-hidden group">
                                    <span class="material-symbols-outlined text-3xl group-hover:scale-110 transition-transform" id="photoIcon">add_a_photo</span>
                                    <span class="text-xs font-medium" id="photoText">Add Photo</span>
                                </div>
                                <input type="file" name="photos[]" id="photoInput" class="hidden" accept="image/*" multiple>
                            </div>
                            <p class="text-xs text-slate-400 mt-2">Check the delete icon on photos to remove them upon saving.</p>
                        </div>

                        <div class="h-px bg-slate-100 w-full mb-10"></div>

                        @php $currentMood = $entryToEdit->mood ?? 'neutral'; @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-10">
                            <div class="flex flex-col gap-4">
                                <label class="text-slate-900 text-sm font-semibold flex items-center gap-2">
                                    <span class="material-symbols-outlined text-lg text-slate-400">mood</span> How are you feeling?
                                </label>
                                <div class="flex gap-3 flex-wrap">
                                    <input type="hidden" name="mood" id="moodInput" value="{{ $currentMood }}">
                                    
                                    <button type="button" onclick="selectMood('sad', this)" class="mood-btn h-12 w-12 flex items-center justify-center rounded-xl {{ $currentMood == 'sad' ? 'bg-primary text-white ring-2 ring-blue-500/20 scale-105' : 'bg-slate-50 border border-slate-200' }} text-2xl transition-all">😢</button>
                                    <button type="button" onclick="selectMood('neutral', this)" class="mood-btn h-12 w-12 flex items-center justify-center rounded-xl {{ $currentMood == 'neutral' ? 'bg-primary text-white ring-2 ring-blue-500/20 scale-105' : 'bg-slate-50 border border-slate-200' }} text-2xl transition-all">😐</button>
                                    <button type="button" onclick="selectMood('happy', this)" class="mood-btn h-12 w-12 flex items-center justify-center rounded-xl {{ $currentMood == 'happy' ? 'bg-primary text-white ring-2 ring-blue-500/20 scale-105' : 'bg-slate-50 border border-slate-200' }} text-2xl transition-all">🙂</button>
                                    <button type="button" onclick="selectMood('excited', this)" class="mood-btn h-12 w-12 flex items-center justify-center rounded-xl {{ $currentMood == 'excited' ? 'bg-primary text-white ring-2 ring-blue-500/20 scale-105' : 'bg-slate-50 border border-slate-200' }} text-2xl transition-all">😀</button>
                                </div>
                            </div>

                            @php $currentRating = $entryToEdit->rating ?? 7; @endphp
                            <div class="flex flex-col gap-4">
                                <div class="flex justify-between items-end">
                                    <label class="text-slate-900 text-sm font-semibold flex items-center gap-2">
                                        <span class="material-symbols-outlined text-lg text-slate-400">star</span> Daily Rating
                                    </label>
                                    <span class="text-primary text-2xl font-bold font-display"><span id="ratingValue">{{ $currentRating }}</span><span class="text-slate-300 text-base font-medium ml-1">/ 10</span></span>
                                </div>
                                <div class="px-2">
                                    <input name="rating" type="range" min="1" max="10" value="{{ $currentRating }}" 
                                        class="w-full accent-primary h-2 bg-slate-100 rounded-lg appearance-none cursor-pointer hover:bg-slate-200 transition-colors"
                                        oninput="document.getElementById('ratingValue').innerText = this.value">
                                </div>
                            </div>
                        </div>

                        <div class="h-px bg-slate-100 w-full mb-10"></div>

                        <div class="space-y-8 pb-6">
                            <div class="group">
                                <label class="flex items-center gap-2 text-slate-900 text-sm font-semibold mb-3">
                                    <div class="p-1 rounded bg-green-100 text-green-600"><span class="material-symbols-outlined text-lg">thumb_up</span></div>
                                    Positive Highlights
                                </label>
                                <textarea name="positive" class="w-full bg-slate-50 hover:bg-white focus:bg-white text-slate-800 text-base rounded-xl border border-slate-200 focus:border-primary focus:ring-2 focus:ring-primary/20 p-4 resize-y min-h-[120px] shadow-sm transition-all" placeholder="What went well today? What are you grateful for?">{{ $entryToEdit->positive_highlight ?? '' }}</textarea>
                            </div>
                            <div class="group">
                                <label class="flex items-center gap-2 text-slate-900 text-sm font-semibold mb-3">
                                    <div class="p-1 rounded bg-red-100 text-red-500"><span class="material-symbols-outlined text-lg">thumb_down</span></div>
                                    Negative Reflections
                                </label>
                                <textarea name="negative" class="w-full bg-slate-50 hover:bg-white focus:bg-white text-slate-800 text-base rounded-xl border border-slate-200 focus:border-primary focus:ring-2 focus:ring-primary/20 p-4 resize-y min-h-[120px] shadow-sm transition-all" placeholder="What challenged you? What could go better?">{{ $entryToEdit->negative_reflection ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function selectMood(moodValue, btnElement) {
        document.getElementById('moodInput').value = moodValue;
        document.querySelectorAll('.mood-btn').forEach(btn => {
            btn.className = 'mood-btn h-12 w-12 flex items-center justify-center rounded-xl bg-slate-50 border border-slate-200 hover:border-slate-300 hover:bg-slate-100 text-2xl transition-all';
        });
        btnElement.className = 'mood-btn h-12 w-12 flex items-center justify-center rounded-xl bg-primary text-white shadow-lg shadow-blue-500/30 text-2xl scale-105 ring-2 ring-blue-500/20 transition-all';
    }
</script>
@endsection