@extends('layouts.app')

@section('title', 'Gallery')

@section('content')
{{-- Background Gradient --}}
<div class="absolute inset-0 z-0 pointer-events-none opacity-60" style="background-image: radial-gradient(circle at 15% 50%, rgba(0, 122, 255, 0.08), transparent 25%), radial-gradient(circle at 85% 30%, rgba(52, 199, 89, 0.08), transparent 25%);">
</div>

<div class="flex flex-col h-full w-full relative z-10 bg-slate-50/30">

    {{-- Header --}}
    <header class="h-16 px-8 border-b border-slate-200/60 flex items-center justify-between bg-white/40 backdrop-blur-md shrink-0 sticky top-0 z-20">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Gallery</h1>
        
    </header>

    {{-- Scrollable Content --}}
    <div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth" id="gallery-container">
        
        @if($entriesWithPhotos->isEmpty())
            {{-- Empty State --}}
            <div class="h-full flex flex-col items-center justify-center text-slate-400">
                <span class="material-symbols-outlined text-6xl mb-4 opacity-30">photo_library</span>
                <p class="text-lg font-medium text-slate-500">No photos yet</p>
                <p class="text-sm mb-6">Your journal memories will appear here.</p>
                <a href="{{ route('journal.index') }}" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                    Go to Journal
                </a>
            </div>
        @else
            <div class="max-w-7xl mx-auto pb-10 space-y-10">
                @foreach($entriesWithPhotos as $entry)
                    @if(is_array($entry->photo_paths) && count($entry->photo_paths) > 0)
                        
                        {{-- GROUP PER HARI --}}
                        <section>
                            {{-- Header Tanggal --}}
                            <div class="flex items-center gap-3 mb-4 sticky top-0 z-10 py-2 bg-slate-50/90 backdrop-blur-sm w-fit pr-4 rounded-r-lg">
                                <h2 class="text-lg font-bold text-slate-800">
                                    {{ $entry->entry_date->format('F d, Y') }}
                                </h2>
                                <span class="text-xs font-medium text-slate-500 px-2 py-0.5 bg-slate-200/60 rounded-full">
                                    {{ $entry->entry_date->format('l') }}
                                </span>
                            </div>

                            {{-- Grid Foto --}}
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                @foreach($entry->photo_paths as $path)
                                    <div class="relative group cursor-zoom-in rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 aspect-square bg-slate-200"
                                         onclick="openLightbox('{{ asset('storage/' . $path) }}', '{{ $entry->entry_date->format('F d, Y') }}')">
                                        
                                        <img src="{{ asset('storage/' . $path) }}" 
                                             alt="Memory" 
                                             class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500"
                                             loading="lazy">
                                        
                                        {{-- Overlay Hover --}}
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300"></div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Lightbox / Modal (Hidden by default) --}}
<div id="lightbox-modal" 
     class="fixed inset-0 z-50 bg-black/90 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4"
     onclick="closeLightbox()">
    
    {{-- Close Button --}}
    <button class="absolute top-4 right-4 text-white/70 hover:text-white p-2 transition-colors z-50">
        <span class="material-symbols-outlined text-3xl">close</span>
    </button>

    {{-- Content --}}
    <div class="relative max-w-5xl max-h-screen w-full flex flex-col items-center" onclick="event.stopPropagation()">
        <img id="lightbox-image" src="" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain">
        <p id="lightbox-caption" class="text-white/80 mt-4 text-sm font-medium bg-black/40 px-3 py-1 rounded-full backdrop-blur-md"></p>
    </div>
</div>

{{-- Script Sederhana untuk Modal --}}
<script>
    function openLightbox(imageUrl, dateCaption) {
        const modal = document.getElementById('lightbox-modal');
        const img = document.getElementById('lightbox-image');
        const cap = document.getElementById('lightbox-caption');

        img.src = imageUrl;
        cap.innerText = dateCaption;

        modal.classList.remove('hidden');
        // Sedikit delay agar transisi opacity berjalan mulus
        setTimeout(() => {
            modal.classList.remove('opacity-0');
        }, 10);
    }

    function closeLightbox() {
        const modal = document.getElementById('lightbox-modal');
        modal.classList.add('opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.getElementById('lightbox-image').src = '';
        }, 300); // Sesuaikan dengan duration-300 di class CSS
    }

    // Close on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            closeLightbox();
        }
    });
</script>
@endsection