<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'KampusLMS' }}</title>

    {{-- Google Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>
 
<body class="antialiased">

    {{-- ========================================================= --}}
    {{-- NAVBAR --}}
    {{-- ========================================================= --}}
    <header class="sticky top-0 z-50 bg-[#0a2540]/95 backdrop-blur-md border-b border-slate-700/60 text-white">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">

            {{-- Logo + Navigation --}}
            <div class="flex items-center gap-8">

                {{-- Logo --}}
                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center gap-3"
                >
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center shadow-md shadow-blue-500/25">

                        <svg
                            class="w-6 h-6 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M12 14l9-5-9-5-9 5 9 5z"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                            />

                            <path
                                d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                            />
                        </svg>

                    </div>

                    <span class="text-2xl font-bold tracking-tight text-white">
                        Kampus<span class="text-sky-400 font-extrabold">LMS</span>
                    </span>
                </a>


                {{-- Navigation --}}
                @auth
                    <nav class="hidden md:flex items-center gap-1">

                        <a
                            href="{{ route('dashboard') }}"
                            class="px-4 py-2 text-sm font-semibold {{ request()->routeIs('dashboard') ? 'text-white bg-white/10' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-lg transition"
                        >
                            Dashboard
                        </a>

                        <a
                            href="{{ route(auth()->user()->role . '.courses.index') }}"
                            class="px-4 py-2 text-sm font-semibold {{ request()->routeIs('*courses*') ? 'text-white bg-white/10' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-lg transition"
                        >
                            Mata Kuliah
                        </a>

                        <a
                            href="{{ route('tentang') }}"
                            class="px-4 py-2 text-sm font-medium {{ request()->routeIs('tentang') ? 'text-white bg-white/10' : 'text-slate-300 hover:text-white hover:bg-white/10' }} rounded-lg transition"
                        >
                            Tentang
                        </a>

                    </nav>
                @endauth

            </div>


            {{-- User Area --}}
            @auth
                <div class="flex items-center gap-3 sm:gap-4">

                    {{-- Role --}}
                    <span class="px-3.5 py-1 text-xs font-bold uppercase tracking-wider bg-blue-500/20 text-cyan-300 border border-blue-400/40 rounded-full">
                        {{ auth()->user()->role }}
                    </span>


                    {{-- Nama --}}
                    <span class="hidden lg:inline text-xs text-slate-300 font-medium">
                        {{ auth()->user()->name }}
                    </span>


                    {{-- Profile Icon --}}
                    <div class="hidden sm:flex items-center">

                        <div class="w-9 h-9 rounded-full bg-slate-800/80 border border-slate-700 flex items-center justify-center text-slate-300 shadow-inner">

                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                />
                            </svg>

                        </div>

                    </div>


                    {{-- Logout --}}
                    <form
                        action="{{ route('logout') }}"
                        method="POST"
                        class="inline"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs sm:text-sm font-medium text-rose-300 hover:text-white bg-rose-950/40 hover:bg-rose-900/60 border border-rose-800/60 hover:border-rose-600 rounded-lg transition-all"
                        >
                            Keluar

                            <svg
                                class="w-3.5 h-3.5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                />
                            </svg>
                        </button>
                    </form>

                </div>
            @endauth

        </div>

    </header>


    {{-- ========================================================= --}}
    {{-- CONTENT --}}
    {{-- ========================================================= --}}
    <main class="flex-1">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                <div class="bg-emerald-50 text-emerald-700 p-4 rounded-lg border border-emerald-200 flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                <div class="bg-rose-50 text-rose-700 p-4 rounded-lg border border-rose-200 flex items-center gap-3">
                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        {{ $slot }}

    </main>


    {{-- ========================================================= --}}
    {{-- FOOTER --}}
    {{-- ========================================================= --}}
    <footer class="border-t border-slate-200 bg-white py-6 mt-10">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">

                <div>
                    <span class="font-semibold text-slate-700">
                        KampusLMS
                    </span>

                    <span class="mx-1">
                        •
                    </span>

                    Sistem Pembelajaran Daring Terpadu
                </div>

                <div>
                    © 2025 KampusLMS
                </div>

            </div>

    </footer>

    {{-- Script Navigasi Tombol Kembali --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Relokasi tombol kembali agar sejajar dengan Edit Mata Kuliah dan Hapus di halaman detail
            const detailActions = document.querySelector('.course-detail__actions');
            const topBack = document.querySelector('.page-container > .mb-6 > a, .page-container .mb-6 a');

            if (detailActions && topBack && topBack.textContent.includes('Kembali')) {
                const parentMb = topBack.closest('.mb-6');
                detailActions.appendChild(topBack);
                if (parentMb && parentMb.querySelectorAll('a, button').length === 0) {
                    parentMb.style.display = 'none';
                }
            }

            const backLinks = document.querySelectorAll('.page-container .mb-6 a, a.text-blue-600, .course-detail__actions a');
            backLinks.forEach(link => {
                const text = link.textContent.trim();
                if (text.includes('Kembali') && !link.classList.contains('back-nav-btn')) {
                    link.classList.add('back-nav-btn');
                    const labelText = text.replace(/^[←\s]+/, '').trim();
                    link.innerHTML = `
                        <svg class="back-nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span>${labelText}</span>
                    `;
                }
            });
        });
    </script>

</body>

</html>