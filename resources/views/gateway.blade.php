<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>App Entry Gateway</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "ios-blue": "#007AFF",
                        "ios-gray": "#F2F2F7",
                        "ios-text-primary": "#000000",
                        "ios-text-secondary": "#8E8E93",
                        "card-bg": "rgba(255, 255, 255, 0.7)",
                        "pastel-blue": "#E0F2FE",
                        "pastel-pink": "#FAE8FF",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "xl": "1rem",
                        "2xl": "1.5rem",
                        "3xl": "2rem",
                    },
                    boxShadow: {
                        "soft": "0 8px 30px rgba(0, 0, 0, 0.04)",
                        "hover": "0 20px 40px rgba(0, 0, 0, 0.08)",
                    }
                },
            },
        }
    </script>

    <style>
        .ambient-bg {
            background-color: #ffffff;
            background-image: 
                radial-gradient(at 0% 0%, #E0F2FE 0px, transparent 50%),
                radial-gradient(at 100% 0%, #FAE8FF 0px, transparent 50%),
                radial-gradient(at 100% 100%, #E0F2FE 0px, transparent 50%),
                radial-gradient(at 0% 100%, #F3E8FF 0px, transparent 50%);
            background-attachment: fixed;
            background-size: cover;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .glass-card:hover {
            background: rgba(255, 255, 255, 0.85);
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06);
            border-color: rgba(255, 255, 255, 0.8);
        }
        .icon-container {
            background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="font-display text-ios-text-primary ambient-bg h-screen w-screen overflow-hidden flex flex-col items-center justify-center selection:bg-ios-blue selection:text-white">
    
    <div class="w-full max-w-7xl px-8 flex flex-col items-center justify-center h-full z-10 space-y-12">
        
        <div class="text-center space-y-4 animate-fade-in-up">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/50 backdrop-blur-md mb-4 shadow-sm border border-white/60">
                <span class="material-symbols-outlined text-ios-blue text-3xl" style="font-variation-settings: 'FILL' 1, 'wght' 600;">spa</span>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold tracking-tight text-gray-900 drop-shadow-sm">
                Good Morning, {{ $name ?? 'Guest' }}
            </h1>
            <p class="text-xl text-gray-500 font-medium">
                Where would you like to begin today?
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 w-full">
            
            <a class="glass-card rounded-3xl p-8 flex flex-col items-center text-center group h-80 justify-between relative overflow-hidden" href="{{ route('journal.index') }}">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <div class="icon-container w-20 h-20 rounded-2xl flex items-center justify-center mb-6 z-10 group-hover:scale-110 transition-transform duration-300">
                    <span class="material-symbols-outlined text-ios-blue text-4xl" style="font-variation-settings: 'FILL' 1;">book_2</span>
                </div>
                <div class="z-10 flex-1 flex flex-col justify-center">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Journal</h3>
                    <p class="text-sm text-gray-500 leading-relaxed px-2">Reflect on your day, capture memories, and express gratitude.</p>
                </div>
                <div class="mt-4 z-10 opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                    <span class="text-xs font-semibold text-ios-blue bg-blue-50 px-3 py-1 rounded-full">Open Journal</span>
                </div>
            </a>

            <a class="glass-card rounded-3xl p-8 flex flex-col items-center text-center group h-80 justify-between relative overflow-hidden" href="#">
                <div class="absolute inset-0 bg-gradient-to-br from-green-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <div class="icon-container w-20 h-20 rounded-2xl flex items-center justify-center mb-6 z-10 group-hover:scale-110 transition-transform duration-300">
                    <span class="material-symbols-outlined text-green-500 text-4xl" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                </div>
                <div class="z-10 flex-1 flex flex-col justify-center">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Habits</h3>
                    <p class="text-sm text-gray-500 leading-relaxed px-2">Build consistency and track your daily progress towards goals.</p>
                </div>
                <div class="mt-4 z-10 opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                    <span class="text-xs font-semibold text-green-600 bg-green-50 px-3 py-1 rounded-full">View Progress</span>
                </div>
            </a>

            <a class="glass-card rounded-3xl p-8 flex flex-col items-center text-center group h-80 justify-between relative overflow-hidden" href="#">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <div class="icon-container w-20 h-20 rounded-2xl flex items-center justify-center mb-6 z-10 group-hover:scale-110 transition-transform duration-300">
                    <span class="material-symbols-outlined text-purple-500 text-4xl" style="font-variation-settings: 'FILL' 1;">cloud</span>
                </div>
                <div class="z-10 flex-1 flex flex-col justify-center">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Thoughts</h3>
                    <p class="text-sm text-gray-500 leading-relaxed px-2">Capture fleeting ideas, inspirations, and brain dumps quickly.</p>
                </div>
                <div class="mt-4 z-10 opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                    <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-3 py-1 rounded-full">New Thought</span>
                </div>
            </a>

            <a class="glass-card rounded-3xl p-8 flex flex-col items-center text-center group h-80 justify-between relative overflow-hidden" href="#">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <div class="icon-container w-20 h-20 rounded-2xl flex items-center justify-center mb-6 z-10 group-hover:scale-110 transition-transform duration-300">
                    <span class="material-symbols-outlined text-orange-500 text-4xl" style="font-variation-settings: 'FILL' 1;">format_list_bulleted</span>
                </div>
                <div class="z-10 flex-1 flex flex-col justify-center">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Lists</h3>
                    <p class="text-sm text-gray-500 leading-relaxed px-2">Organize tasks, shopping, books to read, and everything else.</p>
                </div>
                <div class="mt-4 z-10 opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                    <span class="text-xs font-semibold text-orange-600 bg-orange-50 px-3 py-1 rounded-full">Manage Lists</span>
                </div>
            </a>

        </div>
    </div>

    <div class="fixed top-0 left-0 w-full h-full pointer-events-none z-0 overflow-hidden">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-blue-200/20 blur-[100px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-purple-200/20 blur-[100px]"></div>
    </div>

</body>
</html>