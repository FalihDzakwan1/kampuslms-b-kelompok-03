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

                    {{-- [ACCPT-NAVBAR-BELL] Lonceng Notifikasi & Dropdown --}}
                    <div class="relative" id="notif-wrapper">
                        <button
                            type="button"
                            id="notif-bell-btn"
                            class="relative p-2 text-slate-300 hover:text-white hover:bg-white/10 rounded-xl transition focus:outline-none"
                            title="Notifikasi"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>

                            {{-- Badge Counter Unread --}}
                            <span
                                id="notif-badge"
                                class="hidden absolute top-1 right-1 items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-rose-500 rounded-full min-w-4.5 h-4.5 shadow-sm ring-2 ring-[#0a2540]"
                            >
                                0
                            </span>
                        </button>

                        {{-- Dropdown Notifikasi --}}
                        <div
                            id="notif-dropdown"
                            class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-[#0a2540] border border-slate-700 rounded-2xl shadow-2xl py-2 z-50 text-slate-200"
                        >
                            <div class="px-4 py-2.5 border-b border-slate-700/60 flex items-center justify-between">
                                <span class="font-bold text-sm text-white flex items-center gap-2">
                                    Notifikasi
                                    <span id="notif-count-tag" class="text-xs bg-sky-500/20 text-sky-300 px-2 py-0.5 rounded-full font-semibold">0</span>
                                </span>
                                <button
                                    type="button"
                                    id="notif-mark-all-btn"
                                    class="text-xs text-sky-400 hover:text-sky-300 font-medium transition cursor-pointer"
                                >
                                    Tandai dibaca
                                </button>
                            </div>

                            <div id="notif-list" class="max-h-72 overflow-y-auto divide-y divide-slate-800/80">
                                <div class="p-4 text-center text-xs text-slate-400">Tidak ada notifikasi baru.</div>
                            </div>

                            <div class="px-4 py-2 border-t border-slate-700/60 text-center bg-slate-900/40 rounded-b-2xl">
                                <a href="{{ route('notifications.index') }}" class="text-xs text-sky-400 hover:text-sky-300 font-semibold block py-1 transition">
                                    Lihat Semua Notifikasi →
                                </a>
                            </div>
                        </div>
                    </div>

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

    {{-- [ACCPT-NOTIF-POLLING] Script Polling Notifikasi Navbar --}}
    @auth
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const bellBtn = document.getElementById('notif-bell-btn');
            const badge = document.getElementById('notif-badge');
            const dropdown = document.getElementById('notif-dropdown');
            const notifList = document.getElementById('notif-list');
            const countTag = document.getElementById('notif-count-tag');
            const markAllBtn = document.getElementById('notif-mark-all-btn');

            if (!bellBtn) return;

            // Toggle dropdown buka/tutup
            bellBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', (e) => {
                if (!dropdown.contains(e.target) && !bellBtn.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });

            // Render list notifikasi
            function renderNotifs(items) {
                if (!items || items.length === 0) {
                    notifList.innerHTML = '<div class="p-4 text-center text-xs text-slate-400">Tidak ada notifikasi baru.</div>';
                    return;
                }

                notifList.innerHTML = items.map(item => {
                    const isUnread = !item.read_at;
                    const bg = isUnread ? 'bg-white/5 border-l-2 border-sky-400' : 'bg-transparent';
                    const isGrade = item.data.type === 'grade_given';
                    const isUpdate = item.data.is_update;
                    
                    let badgeLabel = 'PEMBERITAHUAN';
                    if (isGrade) {
                        badgeLabel = isUpdate ? 'NILAI DIPERBARUI' : 'NILAI KELUAR';
                    } else if (item.data.type === 'assignment_published') {
                        badgeLabel = 'TUGAS BARU';
                    }

                    const badgeHtml = `<span style="font-size: 11px; font-weight: 800; letter-spacing: 0.05em;" class="uppercase text-white">${badgeLabel}</span>`;
                    const courseHtml = item.data.course_name ? `<span style="font-size: 11px;" class="text-slate-300">${item.data.course_name}</span>` : '';
                    const title = item.data.title || 'Pemberitahuan';
                    const url = '/notifications/' + item.id + '/open';

                    return `
                        <a href="${url}" class="block px-4 py-3 hover:bg-white/10 transition ${bg}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        ${badgeHtml}
                                        ${courseHtml}
                                    </div>
                                    <p style="font-size: 12px; font-weight: 600;" class="text-white mt-0.5 leading-snug">${title}</p>
                                    <span style="font-size: 10px;" class="text-slate-400 mt-1 block">${item.created_at}</span>
                                </div>
                                ${isUnread ? '<span style="width: 7px; height: 7px;" class="rounded-full bg-sky-400 mt-1.5 flex-shrink-0"></span>' : ''}
                            </div>
                        </a>
                    `;
                }).join('');
            }

            // Polling notifikasi setiap 8 detik
            async function fetchNotifs() {
                try {
                    const res = await fetch('{{ route("notifications.poll") }}', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) return;
                    const data = await res.json();

                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                        badge.classList.remove('hidden');
                        badge.classList.add('inline-flex');
                    } else {
                        badge.classList.add('hidden');
                        badge.classList.remove('inline-flex');
                    }

                    if (countTag) countTag.textContent = data.unread_count;
                    renderNotifs(data.notifications);
                } catch (e) {
                    console.error('Gagal polling notifikasi:', e);
                }
            }

            // Tandai semua dibaca
            markAllBtn?.addEventListener('click', async () => {
                try {
                    const res = await fetch('{{ route("notifications.mark-as-read", "all") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    if (res.ok) fetchNotifs();
                } catch (e) {
                    console.error('Error:', e);
                }
            });

            // Panggil saat halaman dibuka
            fetchNotifs();

            // Polling interval 8 detik
            setInterval(fetchNotifs, 8000);
        });
    </script>
    @endauth

</body>

</html>