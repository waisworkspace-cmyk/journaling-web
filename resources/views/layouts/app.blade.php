<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Sanctuary')</title>
    
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#007AFF",
                        "background-light": "#F2F2F7",
                        "surface-light": "#FFFFFF",
                        "mood-happy": "#34C759",
                        "mood-neutral": "#5AC8FA",
                        "mood-sad": "#5856D6",
                        "mood-anxious": "#FF9500",
                        "mood-calm": "#AF52DE"
                    },
                    fontFamily: {
                        "display": ["Inter", "-apple-system", "BlinkMacSystemFont", "sans-serif"]
                    }
                },
            },
        }
    </script>
    <style>
        /* Gabungan Style Scrollbar dan Material Icons */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, 0.15); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0, 0, 0, 0.25); }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .material-symbols-outlined.filled { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .glass-panel { background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(20px); border-right: 1px solid rgba(0, 0, 0, 0.05); }
        .glass-card { background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.6); }
        .ambient-bg {
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(19, 127, 236, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.05) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(19, 127, 236, 0.05) 0px, transparent 50%);
            background-attachment: fixed;
        }
        /* ... style yang sudah ada ... */

/* Animasi Slider Otomatis */
@keyframes slide-2 {
    0%, 45% { transform: translateX(0); }
    50%, 95% { transform: translateX(-100%); }
    100% { transform: translateX(0); }
}
@keyframes slide-3 {
    0%, 30% { transform: translateX(0); }
    33%, 63% { transform: translateX(-100%); }
    66%, 96% { transform: translateX(-200%); }
    100% { transform: translateX(0); }
}
@keyframes slide-4 {
    0%, 22% { transform: translateX(0); }
    25%, 47% { transform: translateX(-100%); }
    50%, 72% { transform: translateX(-200%); }
    75%, 97% { transform: translateX(-300%); }
    100% { transform: translateX(0); }
}

.animate-slide-2 { animation: slide-2 8s infinite cubic-bezier(0.4, 0, 0.2, 1); }
.animate-slide-3 { animation: slide-3 12s infinite cubic-bezier(0.4, 0, 0.2, 1); }
.animate-slide-4 { animation: slide-4 16s infinite cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="font-display text-slate-800 ambient-bg h-screen w-screen overflow-hidden flex selection:bg-primary selection:text-white">
    
    @include('components.sidebar')

    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        @yield('content')
    </main>

</body>
</html>