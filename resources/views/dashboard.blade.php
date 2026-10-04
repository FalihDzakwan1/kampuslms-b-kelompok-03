<x-layout title="KampusLMS - Portal Mahasiswa">

    <div class="min-h-screen bg-[#f7f9fb] text-slate-800">

        @auth

            @if(auth()->user()->role === 'mahasiswa')

                <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">

                    {{-- ========================================================= --}}
                    {{-- HERO --}}
                    {{-- ========================================================= --}}
                    <section class="relative w-full overflow-hidden rounded-3xl bg-gradient-to-r from-[#0a2540] via-[#0f3460] to-[#1e3a8a] shadow-2xl border border-blue-900/40">

                        {{-- GAMBAR GEDUNG --}}
                        <div
                            class="absolute inset-0 bg-cover bg-center opacity-30 mix-blend-overlay scale-105"
                            style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCUzdctJKq8ITACC1ZzkMsJ8VYNJEE8JXOgaPiK7z8ktVj-9XgcjV3nSyRgrqAfm1TZxGaGFTZJZuHDPU0Bwz8MXw0w7SzQIwMrHWH5gwd8vuikTmvCr-OkI2qqKsJaiXizSz4KKrdmK2C3ZVxS1WAvpNUXeBpR9c2NiBW7CmwQ3FQfIgODAFy50Bemivbxm1izY3UX4JCiOmo2lqr6n5iW__OmUzJCs42LTSEN3QgvvtuUYFAmXHdESVdSlvvqW0Iwufc')">
                        </div>

                        {{-- GLOW --}}
                        <div class="absolute -top-24 -left-20 w-96 h-96 rounded-full bg-cyan-500/25 blur-3xl pointer-events-none"></div>

                        <div class="absolute -bottom-24 right-10 w-96 h-96 rounded-full bg-blue-500/35 blur-3xl pointer-events-none"></div>

                        <div class="absolute top-1/2 left-1/3 -translate-y-1/2 w-80 h-80 rounded-full bg-teal-400/15 blur-3xl pointer-events-none"></div>

                        {{-- OVERLAY --}}
                        <div class="absolute inset-0 bg-gradient-to-r from-[#0a2540]/90 via-[#0f3460]/75 to-transparent"></div>

                        {{-- HERO CONTENT --}}
                        <div class="relative z-10 px-6 py-12 sm:px-10 sm:py-16 lg:px-14 lg:py-20 max-w-4xl flex flex-col gap-6 text-left">

                            <div class="flex flex-col gap-2 text-left">

                                <span class="text-sm sm:text-base font-semibold text-blue-200 tracking-wide">
                                    Selamat Datang Kembali,
                                    {{ auth()->user()->name ?? 'Mahasiswa' }}
                                </span>

                            
                                <h1 class="text-left text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight text-white">
                                    <span class="text-white">
                                        Untuk Sang Pencipta
                                    </span>

                                    <br>

                                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-300 via-cyan-300 to-blue-200">
                                        dan Bumi Etam
                                    </span>
                                </h1>



                            </div>

                            <p class="text-left text-base sm:text-lg text-blue-100/90 max-w-2xl leading-relaxed">
                                Akses cepat materi perkuliahan, jadwal praktikum interaktif,
                                tenggat tugas terintegrasi, dan pantau progres indeks prestasi
                                akademik Anda dalam satu portal terpadu.
                            </p>

                        </div>

                    </section>


                    {{-- ========================================================= --}}
                    {{-- BENTO CARDS --}}
                    {{-- ========================================================= --}}
                    <section class="relative z-20 -mt-10 sm:-mt-12 px-2 sm:px-4 grid grid-cols-1 md:grid-cols-3 gap-6">

                        {{-- CARD MATA KULIAH --}}
                        <div class="group relative flex flex-col justify-between p-6 sm:p-8 rounded-3xl bg-white border border-slate-200/80 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 text-center items-center">

                            <div class="flex flex-col items-center gap-4">

                                <div class="w-16 h-16 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm group-hover:scale-110 transition-transform duration-300">

                                    <svg class="w-9 h-9"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>

                                </div>

                                <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-100 text-xs font-semibold">
                                    Semester Aktif
                                </span>

                                <div class="flex flex-col gap-2 text-center">

                                    <h2 class="text-xl font-bold text-slate-900">
                                        Mata Kuliah Saya
                                    </h2>

                                    <p class="text-sm text-slate-500 max-w-xs leading-relaxed">
                                        Lihat mata kuliah yang diambil, materi belajar,
                                        dan silabus akademik semester ini.
                                    </p>

                                </div>

                            </div>

                            <div class="pt-6 w-full">

                                <a
                                    href="{{ route('mahasiswa.courses.index') }}"
                                    class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-md shadow-blue-600/30 transition-all"
                                >
                                    Buka Kelas Saya

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>

                                </a>

                            </div>

                        </div>


                        {{-- CARD TUGAS --}}
                        <div class="group relative flex flex-col justify-between p-6 sm:p-8 rounded-3xl bg-white border border-slate-200/80 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 text-center items-center">

                            <div class="flex flex-col items-center gap-4">

                                <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shadow-sm group-hover:scale-110 transition-transform duration-300">

                                    <svg class="w-9 h-9"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>

                                </div>

                                <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-100 text-xs font-semibold">
                                    Praktikum & Kuis
                                </span>

                                <div class="flex flex-col gap-2 text-center">

                                    <h2 class="text-xl font-bold text-slate-900">
                                        Tugas & Pengumpulan
                                    </h2>

                                    <p class="text-sm text-slate-500 max-w-xs leading-relaxed">
                                        Pantau status pengerjaan tugas, deadline praktikum,
                                        dan riwayat perolehan nilai.
                                    </p>

                                </div>

                            </div>

                            <div class="pt-6 w-full">

                                <a
                                    href="{{ route('mahasiswa.submissions.index') }}"
                                    class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-md shadow-blue-600/30 transition-all"
                                >
                                    Buka Tugas Saya

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>

                                </a>

                            </div>

                        </div>


                        {{-- CARD MATERI --}}
                        <div class="group relative flex flex-col justify-between p-6 sm:p-8 rounded-3xl bg-white border border-slate-200/80 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 text-center items-center">

                            <div class="flex flex-col items-center gap-4">

                                <div class="w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm group-hover:scale-110 transition-transform duration-300">

                                    <svg class="w-9 h-9"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1" />

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" />
                                    </svg>

                                </div>

                                <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs font-semibold">
                                    Repository Silabus
                                </span>

                                <div class="flex flex-col gap-2 text-center">

                                    <h2 class="text-xl font-bold text-slate-900">
                                        Materi Perkuliahan
                                    </h2>

                                    <p class="text-sm text-slate-500 max-w-xs leading-relaxed">
                                        Unduh modul perkuliahan, slide presentasi dosen,
                                        dan arsip referensi silabus.
                                    </p>

                                </div>

                            </div>

                            <div class="pt-6 w-full">

                                <a
                                    href="{{ route('mahasiswa.courses.index') }}"
                                    class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-sm transition-all"
                                >
                                    Akses Materi

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>

                                </a>

                            </div>

                        </div>

                    </section>


                    {{-- ========================================================= --}}
                    {{-- METRIC --}}
                    {{-- ========================================================= --}}
                    <section class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div class="flex items-center gap-4 p-5 rounded-3xl bg-white border border-slate-200/70 shadow-sm hover:shadow-md transition">

                            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center">

                                <svg class="w-6 h-6"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M22 10l-10-5L2 10l10 5 10-5z" />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M6 12.5V16c3.5 2 8.5 2 12 0v-3.5" />
                                </svg>

                            </div>

                            <div class="flex flex-col">

                                <div class="flex items-baseline gap-1.5">

                                    <span class="text-3xl font-extrabold text-slate-900">
                                        21
                                    </span>

                                    <span class="text-xs font-semibold text-slate-500">
                                        SKS Diambil
                                    </span>

                                </div>

                                <span class="text-xs text-slate-400">
                                    Semester Aktif
                                </span>

                            </div>

                        </div>


                        <div class="flex items-center gap-4 p-5 rounded-3xl bg-white border border-slate-200/70 shadow-sm hover:shadow-md transition">

                            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center">

                                <svg class="w-6 h-6"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M8 7V3m8 4V3m-9 8h10m-9 6h6m-9 4h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>

                            </div>

                            <div class="flex flex-col">

                                <div class="flex items-baseline gap-1.5">

                                    <span class="text-3xl font-extrabold text-rose-600">
                                        02
                                    </span>

                                    <span class="text-xs font-semibold text-slate-500">
                                        Tugas Aktif
                                    </span>

                                </div>

                                <span class="text-xs text-slate-400">
                                    Mendekati Tenggat Waktu
                                </span>

                            </div>

                        </div>

                    </section>


                    {{-- ========================================================= --}}
                    {{-- MATA KULIAH --}}
                    {{-- ========================================================= --}}
                    <section
                        id="matakuliah"
                        class="mt-12 flex flex-col gap-6 text-left"
                    >

                        {{-- HEADER RATA KIRI --}}
                        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 text-left">

                            <div class="flex flex-col gap-1 text-left">

                                <span class="text-xs font-bold uppercase tracking-wider text-blue-600 text-left">
                                    Database Perkuliahan Aktif
                                </span>

                                <h2 class="text-2xl font-bold text-slate-900 text-left">
                                    Mata Kuliah Semester Ini
                                </h2>

                            </div>

                            <div class="flex items-center gap-3">

                                {{-- SEARCH --}}
                                <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-slate-200 shadow-sm">

                                    <svg class="w-4 h-4 text-slate-400"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>

                                    <input
                                        type="text"
                                        placeholder="Cari mata kuliah..."
                                        class="bg-transparent border-0 p-0 text-slate-800 placeholder:text-slate-400 text-xs focus:ring-0 focus:outline-none w-36 sm:w-48"
                                    >

                                </div>

                                <a
                                    href="{{ route('mahasiswa.courses.index') }}"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white hover:bg-slate-50 border border-slate-200 shadow-sm text-slate-700 font-semibold text-xs transition"
                                >
                                    Lihat Semua →
                                </a>

                            </div>

                        </div>


                        {{-- COURSE CARDS --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                            @forelse($courses ?? [] as $course)

                                <div class="flex flex-col justify-between p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-lg transition-all duration-200 text-left">

                                    <div class="flex flex-col gap-4">

                                        <div class="flex items-center justify-between">

                                            <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-100 text-xs font-bold">
                                                {{ $course->code }}
                                            </span>

                                            <span class="text-xs font-semibold text-slate-500">
                                                {{ $course->sks }} SKS
                                            </span>

                                        </div>


                                        <div class="flex flex-col gap-1.5 text-left">

                                            <h3 class="text-lg font-bold text-slate-900 text-left">
                                                {{ $course->name }}
                                            </h3>

                                            <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed text-left">
                                                {{ $course->description ?? 'Deskripsi mata kuliah belum tersedia.' }}
                                            </p>

                                        </div>

                                    </div>


                                    <div class="pt-6 flex flex-col gap-3">

                                        <div class="flex items-center gap-2 text-slate-600 text-xs font-medium">

                                            <svg class="w-4 h-4 text-blue-600"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM5 21a7 7 0 0114 0" />
                                            </svg>

                                            <span class="truncate">
                                                {{ optional($course->lecturer)->name ?? 'Dosen Pengampu' }}
                                            </span>

                                        </div>


                                        <div class="flex items-center justify-between pt-3 bg-slate-50 px-4 py-2.5 rounded-2xl border border-slate-100">

                                            <div class="flex items-center gap-1.5">

                                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>

                                                <span class="text-xs font-semibold text-emerald-700">
                                                    Sedang Berlangsung
                                                </span>

                                            </div>

                                            <a
                                                href="{{ route('mahasiswa.courses.show', $course->id) }}"
                                                class="text-xs font-bold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1"
                                            >
                                                Masuk →

                                            </a>

                                        </div>

                                    </div>

                                </div>

                            @empty

                                {{-- CARD DEFAULT 1 --}}
                                <div class="flex flex-col justify-between p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-lg transition-all text-left">

                                    <div class="flex flex-col gap-4">

                                        <div class="flex items-center justify-between">

                                            <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-100 text-xs font-bold">
                                                IF2101
                                            </span>

                                            <span class="text-xs font-semibold text-slate-500">
                                                3 SKS
                                            </span>

                                        </div>

                                        <div>

                                            <h3 class="text-lg font-bold text-slate-900">
                                                Basis Data
                                            </h3>

                                            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                                                Model relasional data, normalisasi tabel,
                                                perancangan query SQL kompleks,
                                                dan manajemen basis data transaksi.
                                            </p>

                                        </div>

                                    </div>

                                    <div class="pt-6">

                                        <div class="flex items-center gap-2 text-slate-600 text-xs font-medium">
                                            Dosen Pengampu
                                        </div>

                                    </div>

                                </div>


                                {{-- CARD DEFAULT 2 --}}
                                <div class="flex flex-col justify-between p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-lg transition-all text-left">

                                    <div class="flex flex-col gap-4">

                                        <div class="flex items-center justify-between">

                                            <span class="px-3 py-1 rounded-full bg-cyan-50 text-cyan-800 border border-cyan-100 text-xs font-bold">
                                                IF2204
                                            </span>

                                            <span class="text-xs font-semibold text-slate-500">
                                                4 SKS
                                            </span>

                                        </div>

                                        <div>

                                            <h3 class="text-lg font-bold text-slate-900">
                                                Jaringan Komputer
                                            </h3>

                                            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                                                Arsitektur protokol TCP/IP, routing,
                                                subnetting VLSM, dan konfigurasi jaringan.
                                            </p>

                                        </div>

                                    </div>

                                    <div class="pt-6">

                                        <div class="flex items-center gap-2 text-slate-600 text-xs font-medium">
                                            Dosen Pengampu
                                        </div>

                                    </div>

                                </div>


                                {{-- CARD DEFAULT 3 --}}
                                <div class="flex flex-col justify-between p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-lg transition-all text-left">

                                    <div class="flex flex-col gap-4">

                                        <div class="flex items-center justify-between">

                                            <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 text-xs font-bold">
                                                IF2302
                                            </span>

                                            <span class="text-xs font-semibold text-slate-500">
                                                3 SKS
                                            </span>

                                        </div>

                                        <div>

                                            <h3 class="text-lg font-bold text-slate-900">
                                                Pemrograman Web
                                            </h3>

                                            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                                                Pengembangan aplikasi web modern,
                                                REST API, database, dan antarmuka responsif.
                                            </p>

                                        </div>

                                    </div>

                                    <div class="pt-6">

                                        <div class="flex items-center gap-2 text-slate-600 text-xs font-medium">
                                            Dosen Pengampu
                                        </div>

                                    </div>

                                </div>

                            @endforelse

                        </div>

                    </section>


                    {{-- ========================================================= --}}
                    {{-- DEADLINE --}}
                    {{-- ========================================================= --}}
                    <section
                        id="tugas"
                        class="mt-10 p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-blue-500/5 border border-amber-200/60 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm"
                    >

                        <div class="flex items-center gap-4">

                            <div class="w-14 h-14 rounded-2xl bg-amber-100 border border-amber-200 flex items-center justify-center text-amber-600 shrink-0">

                                <svg class="w-7 h-7"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="9"
                                        stroke-width="1.8" />

                                    <path
                                        stroke-linecap="round"
                                        stroke-width="1.8"
                                        d="M12 7v5l3 2" />
                                </svg>

                            </div>

                            <div class="flex flex-col text-left">

                                <span class="text-xs font-bold uppercase tracking-wider text-amber-700">
                                    Tenggat Waktu Terdekat
                                </span>

                                <h4 class="text-lg font-bold text-slate-900">
                                    Praktikum 04: Normalisasi Basis Data
                                </h4>

                                <p class="text-xs text-slate-600 leading-relaxed">
                                    Batas akhir unggah: Hari ini, pukul 23:59 WITA
                                    • Laboratorium Komputasi Dasar
                                </p>

                            </div>

                        </div>


                        <div class="flex items-center gap-3 shrink-0 w-full sm:w-auto">

                            <a
                                href="{{ route('mahasiswa.submissions.index') }}"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm transition-colors shadow-md shadow-blue-600/30"
                            >
                                Kumpulkan Tugas
                            </a>

                        </div>

                    </section>

                </main>

            @elseif(auth()->user()->role === 'dosen')

                {{-- =============================== --}}
                {{-- DASHBOARD DOSEN --}}
                {{-- =============================== --}}

                <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

                    <section class="rounded-3xl bg-gradient-to-r from-[#0a2540] to-[#0284c7] p-10 text-white shadow-xl">

                        <h1 class="text-3xl font-extrabold">
                            Portal Dosen
                        </h1>

                        <p class="mt-2 text-slate-200">
                            Kelola mata kuliah dan kegiatan pembelajaran Anda.
                        </p>

                    </section>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">

                        <a
                            href="{{ route('dosen.courses.index') }}"
                            class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-lg transition"
                        >

                            <div class="text-3xl mb-4">
                                📚
                            </div>

                            <h2 class="text-xl font-bold">
                                Mata Kuliah Diampu
                            </h2>

                            <p class="text-sm text-slate-500 mt-2">
                                Kelola mata kuliah dan aktivitas pembelajaran.
                            </p>

                        </a>


                        <a
                            href="{{ route('dosen.courses.create') }}"
                            class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-lg transition"
                        >

                            <div class="text-3xl mb-4">
                                ➕
                            </div>

                            <h2 class="text-xl font-bold">
                                Tambah Mata Kuliah
                            </h2>

                            <p class="text-sm text-slate-500 mt-2">
                                Tambahkan mata kuliah baru.
                            </p>

                        </a>

                    </div>

                </main>

            @elseif(auth()->user()->role === 'admin')

                {{-- =============================== --}}
                {{-- DASHBOARD ADMIN --}}
                {{-- =============================== --}}

                <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

                    <section class="rounded-3xl bg-gradient-to-r from-[#0a2540] to-[#0284c7] p-10 text-white shadow-xl">

                        <h1 class="text-3xl font-extrabold">
                            Portal Administrator
                        </h1>

                        <p class="mt-2 text-slate-200">
                            Kelola pengguna dan mata kuliah KampusLMS.
                        </p>

                    </section>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">

                        <a
                            href="{{ route('admin.users.index') }}"
                            class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-lg transition"
                        >

                            <div class="text-3xl mb-4">
                                👥
                            </div>

                            <h2 class="text-xl font-bold">
                                Manajemen Pengguna
                            </h2>

                            <p class="text-sm text-slate-500 mt-2">
                                Kelola pengguna KampusLMS.
                            </p>

                        </a>


                        <a
                            href="{{ route('admin.courses.index') }}"
                            class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-lg transition"
                        >

                            <div class="text-3xl mb-4">
                                📚
                            </div>

                            <h2 class="text-xl font-bold">
                                Semua Mata Kuliah
                            </h2>

                            <p class="text-sm text-slate-500 mt-2">
                                Kelola seluruh mata kuliah.
                            </p>

                        </a>

                    </div>

                </main>

            @endif

        @else

            {{-- =============================== --}}
            {{-- BELUM LOGIN --}}
            {{-- =============================== --}}

            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

                <section class="rounded-3xl bg-gradient-to-r from-[#0a2540] to-[#0284c7] p-12 text-white text-center shadow-xl">

                    <h1 class="text-4xl font-extrabold">
                        Selamat Datang di KampusLMS
                    </h1>

                    <p class="mt-4 text-slate-200">
                        Sistem Pembelajaran Daring Terpadu.
                    </p>

                    <a
                        href="{{ route('login') }}"
                        class="inline-flex mt-6 px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-full font-semibold"
                    >
                        Masuk ke Sistem
                    </a>

                </section>

            </main>

        @endauth

    </div>

</x-layout>
