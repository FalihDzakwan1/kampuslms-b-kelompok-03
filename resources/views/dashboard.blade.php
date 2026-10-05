<x-layout title="KampusLMS">

    <div class="min-h-screen bg-[#f7f9fb] text-slate-800">

        @auth

            {{-- ========================================================= --}}
            {{-- ========================================================= --}}
            {{-- MAHASISWA --}}
            {{-- ========================================================= --}}
            {{-- ========================================================= --}}
            @if(auth()->user()->role === 'mahasiswa')

                <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">

                    {{-- ===================================================== --}}
                    {{-- HERO MAHASISWA --}}
                    {{-- ===================================================== --}}
                    <section class="relative w-full overflow-hidden rounded-3xl bg-gradient-to-r from-[#0a2540] via-[#0f3460] to-[#1e3a8a] shadow-2xl border border-blue-900/40">

                        {{-- Gambar Gedung --}}
                        <div
                            class="absolute inset-0 bg-cover bg-center opacity-30 mix-blend-overlay scale-105"
                            style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCUzdctJKq8ITACC1ZzkMsJ8VYNJEE8JXOgaPiK7z8ktVj-9XgcjV3nSyRgrqAfm1TZxGaGFTZJZuHDPU0Bwz8MXw0w7SzQIwMrHWH5gwd8vuikTmvCr-OkI2qqKsJaiXizSz4KKrdmK2C3ZVxS1WAvpNUXeBpR9c2NiBW7CmwQ3FQfIgODAFy50Bemivbxm1izY3UX4JCiOmo2lqr6n5iW__OmUzJCs42LTSEN3QgvvtuUYFAmXHdESVdSlvvqW0Iwufc')"
                        ></div>

                        {{-- Glow --}}
                        <div class="absolute -top-24 -left-20 w-96 h-96 rounded-full bg-cyan-500/25 blur-3xl pointer-events-none"></div>

                        <div class="absolute -bottom-24 right-10 w-96 h-96 rounded-full bg-blue-500/35 blur-3xl pointer-events-none"></div>

                        <div class="absolute top-1/2 left-1/3 -translate-y-1/2 w-80 h-80 rounded-full bg-teal-400/15 blur-3xl pointer-events-none"></div>

                        {{-- Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-r from-[#0a2540]/90 via-[#0f3460]/75 to-transparent"></div>

                        {{-- Hero Content --}}
                        <div class="relative z-10 px-6 py-12 sm:px-10 sm:py-16 lg:px-14 lg:py-20 max-w-4xl flex flex-col gap-6 text-left">

                            <div class="flex flex-col gap-2 text-left">

                                <span class="text-sm sm:text-base font-semibold text-blue-200 tracking-wide">
                                    Selamat Datang Kembali,
                                    {{ auth()->user()->name ?? 'Mahasiswa' }}
                                </span>

                                <h1 class="text-left text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">

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


                    {{-- ===================================================== --}}
                    {{-- BENTO CARDS --}}
                    {{-- ===================================================== --}}
                    <section class="relative z-20 -mt-10 sm:-mt-12 px-2 sm:px-4 grid grid-cols-1 md:grid-cols-3 gap-6">

                        {{-- Mata Kuliah --}}
                        <div class="group flex flex-col justify-between p-6 sm:p-8 rounded-3xl bg-white border border-slate-200/80 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 text-center items-center">

                            <div class="flex flex-col items-center gap-4">

                                <div class="w-16 h-16 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm group-hover:scale-110 transition-transform duration-300">

                                    <svg
                                        class="w-9 h-9"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                                        />
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

                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3"
                                        />
                                    </svg>

                                </a>

                            </div>

                        </div>


                        {{-- Tugas --}}
                        <div class="group flex flex-col justify-between p-6 sm:p-8 rounded-3xl bg-white border border-slate-200/80 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 text-center items-center">

                            <div class="flex flex-col items-center gap-4">

                                <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shadow-sm group-hover:scale-110 transition-transform duration-300">

                                    <svg
                                        class="w-9 h-9"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                        />
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

                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3"
                                        />
                                    </svg>

                                </a>

                            </div>

                        </div>


                        {{-- Materi --}}
                        <div class="group flex flex-col justify-between p-6 sm:p-8 rounded-3xl bg-white border border-slate-200/80 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 text-center items-center">

                            <div class="flex flex-col items-center gap-4">

                                <div class="w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm group-hover:scale-110 transition-transform duration-300">

                                    <svg
                                        class="w-9 h-9"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"
                                        />
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

                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                        />
                                    </svg>

                                </a>

                            </div>

                        </div>

                    </section>


                    {{-- ===================================================== --}}
                    {{-- METRIC MAHASISWA --}}
                    {{-- ===================================================== --}}
                    <section class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div class="flex items-center gap-4 p-5 rounded-3xl bg-white border border-slate-200/70 shadow-sm hover:shadow-md transition">

                            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center">

                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M22 10l-10-5L2 10l10 5 10-5z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M6 12.5V16c3.5 2 8.5 2 12 0v-3.5"
                                    />
                                </svg>

                            </div>

                            <div>

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

                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M8 7V3m8 4V3m-9 8h10m-9 6h6m-9 4h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>

                            </div>

                            <div>

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


                    {{-- ===================================================== --}}
                    {{-- MATA KULIAH --}}
                    {{-- ===================================================== --}}
                    <section
                        id="matakuliah"
                        class="mt-12 flex flex-col gap-6 text-left"
                    >

                        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 text-left">

                            <div class="flex flex-col gap-1 text-left">

                                <span class="text-xs font-bold uppercase tracking-wider text-blue-600">
                                    Database Perkuliahan Aktif
                                </span>

                                <h2 class="text-2xl font-bold text-slate-900">
                                    Mata Kuliah Semester Ini
                                </h2>

                            </div>


                            <a
                                href="{{ route('mahasiswa.courses.index') }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white hover:bg-slate-50 border border-slate-200 shadow-sm text-slate-700 font-semibold text-xs transition self-start sm:self-auto"
                            >
                                Lihat Semua →
                            </a>

                        </div>


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

                                        <div>

                                            <h3 class="text-lg font-bold text-slate-900">
                                                {{ $course->name }}
                                            </h3>

                                            <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed">
                                                {{ $course->description ?? 'Deskripsi mata kuliah belum tersedia.' }}
                                            </p>

                                        </div>

                                    </div>


                                    <div class="pt-6 flex flex-col gap-3">

                                        <div class="flex items-center gap-2 text-slate-600 text-xs font-medium">

                                            <span class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                                                👤
                                            </span>

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
                                                class="text-xs font-bold text-blue-600 hover:text-blue-700"
                                            >
                                                Masuk →
                                            </a>

                                        </div>

                                    </div>

                                </div>

                            @empty

                                {{-- Default Course 1 --}}
                                <div class="flex flex-col justify-between p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm text-left">

                                    <div>

                                        <div class="flex items-center justify-between">

                                            <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-100 text-xs font-bold">
                                                IF2101
                                            </span>

                                            <span class="text-xs font-semibold text-slate-500">
                                                3 SKS
                                            </span>

                                        </div>

                                        <h3 class="text-lg font-bold text-slate-900 mt-4">
                                            Basis Data
                                        </h3>

                                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                                            Model relasional data, normalisasi tabel,
                                            perancangan query SQL, dan manajemen basis data.
                                        </p>

                                    </div>

                                </div>


                                {{-- Default Course 2 --}}
                                <div class="flex flex-col justify-between p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm text-left">

                                    <div>

                                        <div class="flex items-center justify-between">

                                            <span class="px-3 py-1 rounded-full bg-cyan-50 text-cyan-800 border border-cyan-100 text-xs font-bold">
                                                IF2204
                                            </span>

                                            <span class="text-xs font-semibold text-slate-500">
                                                4 SKS
                                            </span>

                                        </div>

                                        <h3 class="text-lg font-bold text-slate-900 mt-4">
                                            Jaringan Komputer
                                        </h3>

                                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                                            Arsitektur TCP/IP, routing, subnetting VLSM,
                                            dan konfigurasi jaringan.
                                        </p>

                                    </div>

                                </div>


                                {{-- Default Course 3 --}}
                                <div class="flex flex-col justify-between p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm text-left">

                                    <div>

                                        <div class="flex items-center justify-between">

                                            <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 text-xs font-bold">
                                                IF2302
                                            </span>

                                            <span class="text-xs font-semibold text-slate-500">
                                                3 SKS
                                            </span>

                                        </div>

                                        <h3 class="text-lg font-bold text-slate-900 mt-4">
                                            Pemrograman Web
                                        </h3>

                                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                                            Pengembangan aplikasi web modern,
                                            REST API, database, dan antarmuka responsif.
                                        </p>

                                    </div>

                                </div>

                            @endforelse

                        </div>

                    </section>


                    {{-- ===================================================== --}}
                    {{-- DEADLINE --}}
                    {{-- ===================================================== --}}
                    <section
                        id="tugas"
                        class="mt-10 p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-blue-500/5 border border-amber-200/60 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm"
                    >

                        <div class="flex items-center gap-4">

                            <div class="w-14 h-14 rounded-2xl bg-amber-100 border border-amber-200 flex items-center justify-center text-amber-600 shrink-0">
                                ⏱
                            </div>

                            <div class="text-left">

                                <span class="text-xs font-bold uppercase tracking-wider text-amber-700">
                                    Tenggat Waktu Terdekat
                                </span>

                                <h4 class="text-lg font-bold text-slate-900">
                                    Praktikum 04: Normalisasi Basis Data
                                </h4>

                                <p class="text-xs text-slate-600 leading-relaxed">
                                    Batas akhir unggah: Hari ini, pukul 23:59 WITA
                                </p>

                            </div>

                        </div>


                        <a
                            href="{{ route('mahasiswa.submissions.index') }}"
                            class="w-full md:w-auto inline-flex items-center justify-center px-6 py-3 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm transition"
                        >
                            Kumpulkan Tugas
                        </a>

                    </section>

                </main>


            {{-- ========================================================= --}}
            {{-- ========================================================= --}}
            {{-- DOSEN --}}
            {{-- ========================================================= --}}
            {{-- ========================================================= --}}
            @elseif(auth()->user()->role === 'dosen')

                <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 font-sans">

                    {{-- ===================================================== --}}
                    {{-- HERO DOSEN --}}
                    {{-- ===================================================== --}}
                    <section class="relative overflow-hidden rounded-3xl min-h-[340px] flex items-end p-8 sm:p-12 text-white shadow-xl bg-slate-900">

                        {{-- Foto Kampus --}}
                        <img
                            src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=1920&q=80"
                            alt="Gedung Kampus ITK"
                            class="absolute inset-0 w-full h-full object-cover object-center brightness-[0.38] scale-105"
                        />

                        {{-- Gradient Bawah --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0a2540] via-[#0a2540]/80 to-transparent"></div>

                        {{-- Gradient Samping --}}
                        <div class="absolute inset-0 bg-gradient-to-r from-[#0a2540]/90 via-transparent to-transparent"></div>

                        {{-- Glow --}}
                        <div class="absolute -top-24 -right-20 w-96 h-96 rounded-full bg-cyan-400/10 blur-3xl"></div>

                        <div class="relative z-10 max-w-3xl space-y-4">

                            {{-- Badge --}}
                            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-[11px] font-bold tracking-wider uppercase bg-white/10 backdrop-blur-md text-emerald-300 border border-white/20">

                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>

                                Institut Teknologi Kalimantan (ITK)
                                • Portal Dosen

                            </div>


                            {{-- Heading --}}
                            <div class="space-y-1">

                                <p class="text-xs sm:text-sm text-slate-300 font-medium">
                                    Selamat Datang di KampusLMS,
                                    {{ auth()->user()->name ?? 'Dosen' }}
                                </p>

                                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">

                                    <span class="text-white">
                                        Untuk Sang Pencipta
                                    </span>

                                    <br>

                                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-200 via-cyan-300 to-blue-200">
                                        dan Bumi Etam
                                    </span>

                                </h1>

                            </div>


                            {{-- Deskripsi --}}
                            <p class="text-xs sm:text-sm text-slate-200 leading-relaxed max-w-xl pt-1">
                                Sistem Informasi Akademik dan Pembelajaran terpadu
                                untuk kemudahan akses materi perkuliahan, tugas,
                                dan manajemen nilai Anda.
                            </p>

                        </div>

                    </section>


                    {{-- ===================================================== --}}
                    {{-- 2 MENU UTAMA DOSEN --}}
                    {{-- ===================================================== --}}
                    <section class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Mata Kuliah --}}
                        <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col justify-between">

                            <div class="space-y-6">

                                <div class="flex items-center justify-between">

                                    <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-3xl shadow-inner">
                                        📘
                                    </div>

                                    <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">
                                        Kurikulum
                                    </span>

                                </div>


                                <div class="text-center pt-2">

                                    <h2 class="text-2xl font-extrabold text-[#0a2540]">
                                        Mata Kuliah
                                    </h2>

                                    <p class="text-xs sm:text-sm text-slate-500 mt-2 max-w-sm mx-auto leading-relaxed">
                                        Daftar mata kuliah, enrollment mahasiswa,
                                        silabus, dan materi kuliah aktif.
                                    </p>

                                </div>

                            </div>


                            <div class="pt-6 flex justify-center">

                                <a
                                    href="{{ route('dosen.courses.index') }}"
                                    class="w-full sm:w-3/4 inline-flex items-center justify-center gap-2 py-3 px-6 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold transition shadow-sm"
                                >
                                    Lihat Mata Kuliah

                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3"
                                        />
                                    </svg>

                                </a>

                            </div>

                        </div>


                        {{-- Transkrip --}}
                        <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col justify-between">

                            <div class="space-y-6">

                                <div class="flex items-center justify-between">

                                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl shadow-inner">
                                        📑
                                    </div>

                                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                                        Laporan
                                    </span>

                                </div>


                                <div class="text-center pt-2">

                                    <h2 class="text-2xl font-extrabold text-[#0a2540]">
                                        Transkrip Nilai
                                    </h2>

                                    <p class="text-xs sm:text-sm text-slate-500 mt-2 max-w-sm mx-auto leading-relaxed">
                                        Cetak laporan dan ekspor transkrip akademik
                                        mahasiswa per semester.
                                    </p>

                                </div>

                            </div>


                            <div class="pt-6 flex justify-center">

                                <button
                                    type="button"
                                    onclick="alert('Fitur transkrip nilai mahasiswa belum memiliki route khusus.')"
                                    class="w-full sm:w-3/4 inline-flex items-center justify-center gap-2 py-3 px-6 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold transition"
                                >
                                    Buka Transkrip

                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>

                                </button>

                            </div>

                        </div>

                    </section>


                    {{-- ===================================================== --}}
                    {{-- 3 STATISTIK DOSEN --}}
                    {{-- ===================================================== --}}
                    <section class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm">

                        {{-- Mata Kuliah --}}
                        <div class="flex items-center gap-4 p-3 sm:border-r border-slate-100">

                            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                                📖
                            </div>

                            <div>

                                <p class="text-2xl font-extrabold text-[#0a2540] leading-none">
                                    {{ \App\Models\Course::count() }}
                                </p>

                                <p class="text-xs text-slate-400 font-semibold mt-1">
                                    Mata Kuliah Aktif
                                </p>

                            </div>

                        </div>


                        {{-- Mahasiswa --}}
                        <div class="flex items-center gap-4 p-3 sm:border-r border-slate-100">

                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                                🎓
                            </div>

                            <div>

                                <p class="text-2xl font-extrabold text-[#0a2540] leading-none">
                                    {{ \App\Models\User::where('role', 'mahasiswa')->count() }}
                                </p>

                                <p class="text-xs text-slate-400 font-semibold mt-1">
                                    Total Mahasiswa
                                </p>

                            </div>

                        </div>


                        {{-- Tugas --}}
                        <div class="flex items-center gap-4 p-3">

                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                                📋
                            </div>

                            <div>

                                <p class="text-2xl font-extrabold text-[#0a2540] leading-none">
                                    24
                                </p>

                                <p class="text-xs text-slate-400 font-semibold mt-1">
                                    Tugas Perlu Dinilai
                                </p>

                            </div>

                        </div>

                    </section>

                </main>


            {{-- ========================================================= --}}
            {{-- ========================================================= --}}
            {{-- ADMIN --}}
            {{-- ========================================================= --}}
            {{-- ========================================================= --}}
            @elseif(auth()->user()->role === 'admin')

                <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-8">

                    {{-- ===================================================== --}}
                    {{-- HERO ADMIN --}}
                    {{-- ===================================================== --}}
                    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0a2540] via-[#0f3460] to-[#1a365d] text-white p-7 sm:p-9 shadow-lg border border-slate-700/30">

                        <div class="absolute -right-24 -top-24 w-72 h-72 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>

                        <div class="absolute -left-20 -bottom-28 w-80 h-80 rounded-full bg-cyan-400/10 blur-3xl pointer-events-none"></div>

                        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">

                            <div class="space-y-2">

                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-bold tracking-wider uppercase bg-white/10 text-blue-200 border border-white/15">

                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>

                                    Portal Administrator • KampusLMS ITK

                                </div>


                                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                                    Selamat Datang, Administrator
                                </h1>


                                <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                                    Kelola data pengguna, hak akses akademik,
                                    dan seluruh mata kuliah KampusLMS secara
                                    terpadu melalui kontrol terpusat.
                                </p>

                            </div>


                            <div class="shrink-0">

                                <div class="px-4 py-2.5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 text-xs font-semibold text-slate-200">
                                    Semester Ganjil 2024/2025
                                </div>

                            </div>

                        </div>

                    </section>


                    {{-- ===================================================== --}}
                    {{-- QUICK STATS ADMIN --}}
                    {{-- ===================================================== --}}
                    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                        {{-- Total Pengguna --}}
                        <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm flex items-center gap-4">

                            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                                👥
                            </div>

                            <div>

                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                    Total Pengguna
                                </p>

                                <p class="text-xl font-extrabold text-[#0a2540] mt-0.5">
                                    {{ \App\Models\User::count() }} User
                                </p>

                            </div>

                        </div>


                        {{-- Total Mata Kuliah --}}
                        <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm flex items-center gap-4">

                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                                📚
                            </div>

                            <div>

                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                    Total Mata Kuliah
                                </p>

                                <p class="text-xl font-extrabold text-[#0a2540] mt-0.5">
                                    {{ \App\Models\Course::count() }} Matkul
                                </p>

                            </div>

                        </div>


                        {{-- Dosen --}}
                        <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm flex items-center gap-4">

                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                                👨‍🏫
                            </div>

                            <div>

                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                    Dosen Pengampu
                                </p>

                                <p class="text-xl font-extrabold text-[#0a2540] mt-0.5">
                                    {{ \App\Models\User::where('role', 'dosen')->count() }} Dosen
                                </p>

                            </div>

                        </div>


                        {{-- Mahasiswa --}}
                        <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm flex items-center gap-4">

                            <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl shrink-0">
                                🎓
                            </div>

                            <div>

                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                    Mahasiswa Aktif
                                </p>

                                <p class="text-xl font-extrabold text-[#0a2540] mt-0.5">
                                    {{ \App\Models\User::where('role', 'mahasiswa')->count() }} Mhs
                                </p>

                            </div>

                        </div>

                    </section>


                    {{-- ===================================================== --}}
                    {{-- MODUL UTAMA ADMIN --}}
                    {{-- ===================================================== --}}
                    <section class="space-y-4">

                        <div>

                            <h2 class="text-lg font-bold text-[#0a2540]">
                                Modul Utama Administrator
                            </h2>

                            <p class="text-xs text-slate-500">
                                Pilih menu di bawah ini untuk memulai
                                pengelolaan entitas akademik.
                            </p>

                        </div>


                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Manajemen Pengguna --}}
                            <div class="p-7 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between">

                                <div class="space-y-5">

                                    <div class="flex items-center justify-between gap-4">

                                        <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl">
                                            👥
                                        </div>

                                        <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">
                                            Modul Akses & Akun
                                        </span>

                                    </div>


                                    <div>

                                        <h3 class="text-xl font-extrabold text-[#0a2540]">
                                            Manajemen Pengguna
                                        </h3>

                                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                                            Kelola data dosen, mahasiswa, dan akun
                                            administrator. Atur hak akses, kredensial,
                                            dan status pengguna dalam satu sistem
                                            terintegrasi.
                                        </p>

                                    </div>


                                    <div class="grid grid-cols-3 gap-2 pt-2 text-center">

                                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">

                                            <p class="text-xs font-bold text-slate-800">
                                                {{ \App\Models\User::where('role', 'dosen')->count() }}
                                            </p>

                                            <p class="text-[10px] text-slate-400">
                                                Dosen
                                            </p>

                                        </div>


                                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">

                                            <p class="text-xs font-bold text-slate-800">
                                                {{ \App\Models\User::where('role', 'mahasiswa')->count() }}
                                            </p>

                                            <p class="text-[10px] text-slate-400">
                                                Mahasiswa
                                            </p>

                                        </div>


                                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">

                                            <p class="text-xs font-bold text-slate-800">
                                                {{ \App\Models\User::where('role', 'admin')->count() }}
                                            </p>

                                            <p class="text-[10px] text-slate-400">
                                                Admin
                                            </p>

                                        </div>

                                    </div>

                                </div>


                                <div class="pt-5 mt-6 border-t border-slate-100 flex items-center justify-between gap-4">

                                    <span class="text-xs font-medium text-emerald-600 flex items-center gap-1.5">

                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>

                                        Sistem Aktif

                                    </span>


                                    <a
                                        href="{{ route('admin.users.index') }}"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-[#0a2540] hover:bg-blue-900 text-white text-xs font-bold transition shadow-sm"
                                    >
                                        Kelola Pengguna

                                        <svg
                                            class="w-3.5 h-3.5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M14 5l7 7m0 0l-7 7m7-7H3"
                                            />
                                        </svg>

                                    </a>

                                </div>

                            </div>


                            {{-- Semua Mata Kuliah --}}
                            <div class="p-7 rounded-3xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between">

                                <div class="space-y-5">

                                    <div class="flex items-center justify-between gap-4">

                                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl">
                                            📚
                                        </div>

                                        <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold">
                                            Kurikulum & Silabus
                                        </span>

                                    </div>


                                    <div>

                                        <h3 class="text-xl font-extrabold text-[#0a2540]">
                                            Semua Mata Kuliah
                                        </h3>

                                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                                            Kelola seluruh data kurikulum mata kuliah,
                                            penetapan dosen pengampu, distribusi silabus,
                                            dan status kelas daring.
                                        </p>

                                    </div>


                                    <div class="grid grid-cols-3 gap-2 pt-2 text-center">

                                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">

                                            <p class="text-xs font-bold text-slate-800">
                                                Informatika
                                            </p>

                                            <p class="text-[10px] text-slate-400">
                                                Prodi
                                            </p>

                                        </div>


                                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">

                                            <p class="text-xs font-bold text-slate-800">
                                                Sistem Info
                                            </p>

                                            <p class="text-[10px] text-slate-400">
                                                Prodi
                                            </p>

                                        </div>


                                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">

                                            <p class="text-xs font-bold text-slate-800">
                                                Prodi Lain
                                            </p>

                                            <p class="text-[10px] text-slate-400">
                                                Lainnya
                                            </p>

                                        </div>

                                    </div>

                                </div>


                                <div class="pt-5 mt-6 border-t border-slate-100 flex items-center justify-between gap-4">

                                    <span class="text-xs font-semibold text-slate-400">
                                        Total: {{ \App\Models\Course::count() }} Matkul
                                    </span>


                                    <a
                                        href="{{ route('admin.courses.index') }}"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-sm"
                                    >
                                        Kelola Mata Kuliah

                                        <svg
                                            class="w-3.5 h-3.5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M14 5l7 7m0 0l-7 7m7-7H3"
                                            />
                                        </svg>

                                    </a>

                                </div>

                            </div>

                        </div>

                    </section>

                </main>

            @endif

        @else

            {{-- ========================================================= --}}
            {{-- BELUM LOGIN --}}
            {{-- ========================================================= --}}
            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

                <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0a2540] to-[#0284c7] p-12 text-white text-center shadow-xl">

                    <h1 class="text-4xl font-extrabold">
                        Selamat Datang di KampusLMS
                    </h1>

                    <p class="mt-4 text-slate-200">
                        Sistem Pembelajaran Daring Terpadu.
                    </p>

                    <a
                        href="{{ route('login') }}"
                        class="inline-flex mt-6 px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-full font-semibold transition"
                    >
                        Masuk ke Sistem
                    </a>

                </section>

            </main>

        @endauth

    </div>

</x-layout>
