@extends('layouts.app')

@section('title', 'Photo Gallery')

@section('content')
<style>
    /* Custom Scrollbar */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 3px; }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04);
    }
</style>

<div class="flex flex-col h-full w-full relative z-10 bg-[#F8F9FA]">

    {{-- Header --}}
    <header class="h-16 px-6 md:px-8 border-b border-slate-200/60 flex items-center justify-between bg-white/60 backdrop-blur-md shrink-0 sticky top-0 z-30">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Photo Gallery</h1>
        <div class="text-xs font-medium text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
            {{ $entriesWithPhotos->pluck('photo_paths')->flatten()->count() }} Photos Total
        </div>
    </header>

    {{-- Content --}}
    <div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
        
        @if($entriesWithPhotos->isEmpty())
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center py-32 text-center animate-fade-in">
                <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-4xl text-slate-300">photo_library</span>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">No photos yet</h3>
                <p class="text-slate-500 max-w-xs mx-auto mb-6">Your gallery is empty. Start adding photos to your journal entries!</p>
                <a href="{{ route('journal.create') }}" class="px-6 py-2 bg-primary text-white rounded-full font-semibold shadow-lg shadow-blue-500/20 hover:bg-blue-600 transition-colors">
                    Create Entry
                </a>
            </div>
        @else
            {{-- Group Entries by Month & Year --}}
            @php
                $groupedEntries = $entriesWithPhotos->groupBy(function($item) {
                    return $item->entry_date->format('F Y');
                });
            @endphp

            <div class="max-w-7xl mx-auto space-y-12 pb-12">
                @foreach($groupedEntries as $monthYear => $entries)
                    <div class="animate-fade-in">
                        {{-- Month Header --}}
                        <div class="flex items-center gap-4 mb-6 sticky top-0 z-10 py-2">
                            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $monthYear }}</h2>
                            <div class="h-px flex-1 bg-slate-200"></div>
                        </div>

                        {{-- Photos Grid --}}
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            @foreach($entries as $entry)
                                @if(is_array($entry->photo_paths))
                                    @foreach($entry->photo_paths as $index => $path)
                                        <div class="group relative aspect-square rounded-2xl overflow-hidden cursor-pointer bg-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ring-1 ring-black/5"
                                             onclick="openLightbox('{{ asset('storage/' . $path) }}', '{{ $entry->entry_date->format('l, F d, Y') }}', '{{ route('journal.create', ['date' => $entry->entry_date->format('Y-m-d')]) }}', '{{ $entry->mood }}')">
                                            
                                            <img src="{{ asset('storage/' . $path) }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" loading="lazy">
                                            
                                            {{-- Hover Overlay --}}
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4">
                                                <div class="translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                                    <p class="text-white text-xs font-bold mb-0.5">{{ $entry->entry_date->format('d M') }}</p>
                                                    <p class="text-white/80 text-[10px] line-clamp-1">
                                                        @if($entry->mood == 'happy') Feeling Happy
                                                        @elseif($entry->mood == 'sad') Feeling Sad
                                                        @elseif($entry->mood == 'excited') Feeling Excited
                                                        @else Feeling Neutral @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Lightbox Modal --}}
    <div id="lightbox" class="fixed inset-0 z-[60] bg-black/95 hidden backdrop-blur-xl transition-all duration-300 opacity-0" onclick="closeLightbox(event)">
        <button class="absolute top-6 right-6 text-white/70 hover:text-white transition-colors z-50 p-2">
            <span class="material-symbols-outlined text-3xl">close</span>
        </button>

        <div class="w-full h-full flex flex-col items-center justify-center p-4 relative">
            <img id="lightbox-img" src="" class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl scale-95 transition-transform duration-300">
            
            <div class="absolute bottom-8 left-0 right-0 flex justify-center pointer-events-none">
                <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-full px-6 py-3 flex items-center gap-6 pointer-events-auto shadow-2xl">
                    <div>
                        <p id="lightbox-date" class="text-white text-sm font-bold">Date</p>
                        <p id="lightbox-mood" class="text-white/60 text-xs uppercase tracking-wider">Mood</p>
                    </div>
                    <div class="w-px h-8 bg-white/20"></div>
                    <a id="lightbox-link" href="#" class="flex items-center gap-2 text-primary bg-white px-4 py-2 rounded-full text-sm font-bold hover:bg-blue-50 transition-colors">
                        <span>View Entry</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxDate = document.getElementById('lightbox-date');
    const lightboxLink = document.getElementById('lightbox-link');
    const lightboxMood = document.getElementById('lightbox-mood');

    function openLightbox(src, date, link, mood) {
        // Set Content
        lightboxImg.src = src;
        lightboxDate.innerText = date;
        lightboxLink.href = link;
        lightboxMood.innerText = mood ? mood.charAt(0).toUpperCase() + mood.slice(1) : 'Neutral';

        // Show
        lightbox.classList.remove('hidden');
        // Small delay to allow display:block to apply before opacity transition
        setTimeout(() => {
            lightbox.classList.remove('opacity-0');
            lightboxImg.classList.remove('scale-95');
            lightboxImg.classList.add('scale-100');
        }, 10);
    }

    function closeLightbox(event) {
        // Close if clicking outside the image (on the background or close button)
        if (event.target === lightbox || event.target.closest('button')) {
            lightbox.classList.add('opacity-0');
            lightboxImg.classList.remove('scale-100');
            lightboxImg.classList.add('scale-95');
            
            setTimeout(() => {
                lightbox.classList.add('hidden');
                lightboxImg.src = ''; // Clear src
            }, 300);
        }
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const closeBtn = lightbox.querySelector('button');
            closeLightbox({ target: closeBtn });
        }
    });
</script>
@endsection