<aside class="glass-panel w-[280px] h-full flex flex-col flex-shrink-0 z-20 relative bg-white/60 backdrop-blur-xl border-r border-slate-200/60 hidden md:flex">
    <div class="p-6 h-16 flex items-center shrink-0 pb-2">
        <h1 class="text-xl font-semibold tracking-tight text-slate-800 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary filled">spa</span>
            Digital Sanctuary
        </h1>
    </div>
    
    <nav class="flex-1 px-4 py-6 flex flex-col gap-1 overflow-y-auto">
        <a class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('journal.index') ? 'bg-primary/10 text-primary' : 'text-slate-500 hover:bg-black/5 hover:text-slate-900' }}" 
           href="{{ route('journal.index') }}">
            <span class="material-symbols-outlined {{ request()->routeIs('journal.index') ? 'filled' : '' }}">book_2</span>
            <span class="text-sm font-medium">Journal</span>
        </a>

        <a class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('journal.gallery') ? 'bg-primary/10 text-primary' : 'text-slate-500 hover:bg-black/5 hover:text-slate-900' }}" 
           href="{{ route('journal.gallery') }}">
            <span class="material-symbols-outlined {{ request()->routeIs('journal.gallery') ? 'filled' : '' }}">photo_library</span>
            <span class="text-sm font-medium">Gallery</span>
        </a>

        <a class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('journal.mood') ? 'bg-primary/10 text-primary' : 'text-slate-500 hover:bg-black/5 hover:text-slate-900' }}" 
           href="{{ route('journal.mood') }}">
            <span class="material-symbols-outlined {{ request()->routeIs('journal.mood') ? 'filled' : '' }}">mood</span>
            <span class="text-sm font-medium">Mood Tracker</span>
        </a>

        <a class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('journal.search') ? 'bg-primary/10 text-primary' : 'text-slate-500 hover:bg-black/5 hover:text-slate-900' }}" 
           href="{{ route('journal.search') }}">
            <span class="material-symbols-outlined {{ request()->routeIs('journal.search') ? 'filled' : '' }}">search</span>
            <span class="text-sm font-medium">Search</span>
        </a>
    </nav>
</aside>