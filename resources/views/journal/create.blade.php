@extends('layouts.app')
@section('title', 'New Entry')
@section('content')

<form action="{{ route('journal.store') }}" method="POST" class="h-full flex flex-col items-center justify-center p-4 sm:p-6 bg-black/10 backdrop-blur-[2px]">
    @csrf <div class="relative w-full max-w-[640px] max-h-[90vh] flex flex-col bg-white/85 backdrop-blur-2xl border border-white/60 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.15)] rounded-2xl overflow-hidden ring-1 ring-black/5 animate-fade-in">
        
        <header class="flex items-center justify-between px-6 py-4 border-b border-slate-200/60 shrink-0 bg-white/60 sticky top-0 z-10 backdrop-blur-md">
            <a href="{{ route('journal.index') }}" class="text-primary hover:text-blue-600 text-[17px] font-normal transition-colors px-2 py-1 rounded hover:bg-black/5">Cancel</a>
            <div class="text-center"><h2 class="text-slate-900 text-[17px] font-semibold tracking-tight">New Entry</h2></div>
            <button type="submit" class="bg-primary hover:bg-blue-600 text-white text-[15px] font-semibold px-5 py-1.5 rounded-full transition-colors shadow-lg shadow-blue-500/20">Save</button>
        </header>

        <div class="flex-1 overflow-y-auto p-0 scroll-smooth">
            <div class="flex flex-col max-w-[640px] mx-auto">
                <div class="pt-6 pb-2 text-center">
                    <p class="text-slate-400 text-sm font-medium">{{ now()->format('F d, Y') }}</p>
                </div>

                <div class="px-6 py-4">
                    <h3 class="text-slate-900 text-sm font-semibold mb-3 px-1">Moments</h3>
                    <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide snap-x">
                        <div class="shrink-0 w-32 h-32 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 flex flex-col items-center justify-center gap-2 cursor-pointer text-slate-400 hover:text-primary hover:bg-slate-100 transition-all">
                            <span class="material-symbols-outlined">add_a_photo</span>
                            <span class="text-xs font-medium">Add Photo</span>
                        </div>
                    </div>

                    <div class="h-px bg-slate-200 mx-6 my-6"></div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div class="flex flex-col gap-3">
                            <label class="text-slate-900 text-sm font-semibold">Mood</label>
                            <div class="flex gap-2 flex-wrap">
                                <input type="hidden" name="mood" id="moodInput" value="happy">
                                <button type="button" class="h-10 w-10 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 text-xl">😢</button>
                                <button type="button" class="h-10 w-10 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 text-xl">😐</button>
                                <button type="button" class="h-10 w-10 flex items-center justify-center rounded-lg bg-primary text-white shadow-lg text-xl scale-105">🙂</button>
                                <button type="button" class="h-10 w-10 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 text-xl">😀</button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 mb-8">
                        <div class="flex justify-between items-end">
                            <label class="text-slate-900 text-sm font-semibold">Daily Rating</label>
                            <span class="text-primary text-xl font-bold font-display">7<span class="text-slate-400 text-sm font-medium ml-1">/ 10</span></span>
                        </div>
                        <input name="rating" type="range" min="1" max="10" value="7" class="w-full accent-primary h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer">
                    </div>

                    <div class="h-px bg-slate-200 mx-6 my-6"></div>

                    <div class="space-y-6 pb-12">
                        <div class="group">
                            <label class="flex items-center gap-2 text-slate-900 text-sm font-semibold mb-2">
                                <span class="material-symbols-outlined text-green-500 text-sm">thumb_up</span> Positive Highlights
                            </label>
                            <textarea name="positive" class="w-full bg-slate-50 text-slate-800 text-sm rounded-xl border border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary p-3 resize-none shadow-sm" rows="3" placeholder="What went well today?"></textarea>
                        </div>
                        <div class="group">
                            <label class="flex items-center gap-2 text-slate-900 text-sm font-semibold mb-2">
                                <span class="material-symbols-outlined text-red-500 text-sm">thumb_down</span> Negative Reflections
                            </label>
                            <textarea name="negative" class="w-full bg-slate-50 text-slate-800 text-sm rounded-xl border border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary p-3 resize-none shadow-sm" rows="3" placeholder="What challenged you?"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection