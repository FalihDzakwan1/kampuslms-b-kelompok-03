<x-layout>
    <x-slot:title>Daftar Mata Kuliah | KampusLMS</x-slot:title>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        @php
            $courseTotal = method_exists($courses, 'total')
                ? $courses->total()
                : $courses->count();
        @endphp


        {{-- ========================================================= --}}
        {{-- 1. HERO BANNER --}}
        {{-- ========================================================= --}}
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0a2540] via-[#0e3b68] to-[#0284c7] p-8 md:p-10 text-white shadow-xl">

            {{-- Glow --}}
            <div class="absolute -right-16 -top-24 w-96 h-96 rounded-full bg-cyan-400/20 blur-3xl pointer-events-none"></div>

            <div class="absolute -bottom-24 right-1/3 w-80 h-80 rounded-full bg-blue-500/20 blur-3xl pointer-events-none"></div>


            <div class="relative z-10 max-w-4xl">

                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-xs font-semibold text-cyan-200">

                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>

                    SISTEM INFORMASI AKADEMIK • 2024/2025

                </div>


                {{-- Title --}}
                <h1 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight">
                    Daftar Mata Kuliah
                </h1>


                {{-- Description --}}
                <p class="mt-3 text-sm md:text-base text-slate-200/90 leading-relaxed max-w-3xl">
                    Kelola, jelajahi, dan ikuti mata kuliah aktif
                    di lingkungan Institut Teknologi Kalimantan
                    semester ini secara terpadu.
                </p>


                {{-- ===================================================== --}}
                {{-- 3 METRIC --}}
                {{-- ===================================================== --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6">

                    {{-- Total Mata Kuliah --}}
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10">

                        <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-cyan-300 flex items-center justify-center shrink-0">

                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                                />
                            </svg>

                        </div>

                        <div>

                            <p class="text-[10px] text-cyan-200 uppercase font-bold tracking-wider">
                                Total Mata Kuliah
                            </p>

                            <p class="text-sm font-extrabold text-white">
                                {{ $courseTotal }} Mata Kuliah
                            </p>

                        </div>

                    </div>


                    {{-- Periode --}}
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10">

                        <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-cyan-300 flex items-center justify-center shrink-0">

                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 14l9-5-9-5-9 5 9 5z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"
                                />
                            </svg>

                        </div>

                        <div>

                            <p class="text-[10px] text-cyan-200 uppercase font-bold tracking-wider">
                                Periode
                            </p>

                            <p class="text-sm font-extrabold text-white">
                                Semester Ganjil
                            </p>

                        </div>

                    </div>


                    {{-- Beban Maksimal --}}
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10">

                        <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-cyan-300 flex items-center justify-center shrink-0">

                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                                />
                            </svg>

                        </div>

                        <div>

                            <p class="text-[10px] text-cyan-200 uppercase font-bold tracking-wider">
                                Beban Maksimal
                            </p>

                            <p class="text-sm font-extrabold text-white">
                                24 SKS
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        {{-- ========================================================= --}}
        {{-- 2. FLASH MESSAGE --}}
        {{-- ========================================================= --}}
        @if(session('success'))

            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm">

                <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">

                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>

                </div>

                <p class="text-sm font-medium">
                    {{ session('success') }}
                </p>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- 3. SEARCH & FILTER --}}
        {{-- ========================================================= --}}
        <form
            action="{{ route(auth()->user()->role . '.courses.index') }}"
            method="GET"
            class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4"
        >

            {{-- Search --}}
            <div class="relative w-full md:w-96">

                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">

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
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>

                </span>


                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari kode mata kuliah, nama kelas, atau dosen..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-slate-400"
                >

            </div>


            {{-- Filter --}}
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto md:justify-end">

                <select
                    name="status"
                    onchange="this.form.submit()"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">
                        Semua Status
                    </option>

                    <option
                        value="active"
                        {{ request('status') === 'active' ? 'selected' : '' }}
                    >
                        Aktif
                    </option>

                    <option
                        value="draft"
                        {{ request('status') === 'draft' ? 'selected' : '' }}
                    >
                        Draft
                    </option>

                    <option
                        value="archived"
                        {{ request('status') === 'archived' ? 'selected' : '' }}
                    >
                        Arsip
                    </option>
                </select>


                {{-- Tombol tambah hanya admin/dosen --}}
                @if(auth()->user()->role !== 'mahasiswa')

                    <a
                        href="{{ route(auth()->user()->role . '.courses.create') }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-sm transition"
                    >

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
                                d="M12 4v16m8-8H4"
                            />
                        </svg>

                        Tambah Mata Kuliah

                    </a>

                @endif

            </div>

        </form>


        {{-- ========================================================= --}}
        {{-- 4. TABLE --}}
        {{-- ========================================================= --}}
        <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden p-6">

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-6 mb-2 border-b border-slate-100">

                <div class="flex items-center gap-2.5">

                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600 shrink-0"></span>

                    <div>

                        <h3 class="font-bold text-slate-800 text-base">
                            Mata Kuliah Ditawarkan
                        </h3>

                        <p class="text-xs text-slate-400">
                            Daftar mata kuliah yang tersedia di sistem KampusLMS
                        </p>

                    </div>

                </div>


                <span class="px-3 py-1 rounded-full text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-100 self-start sm:self-auto">
                    {{ $courseTotal }} Mata Kuliah
                </span>

            </div>


            {{-- Data --}}
            @if($courses->count())

                <div class="overflow-x-auto">

                    <table class="w-full text-left border-collapse min-w-[900px]">

                        <thead>

                            <tr class="text-[11px] font-bold tracking-wider text-slate-400 uppercase border-b border-slate-100">

                                <th class="py-4 px-4">
                                    Kode
                                </th>

                                <th class="py-4 px-4">
                                    Mata Kuliah
                                </th>

                                <th class="py-4 px-4">
                                    Bobot
                                </th>

                                <th class="py-4 px-4">
                                    Dosen Pengampu
                                </th>

                                <th class="py-4 px-4">
                                    Status
                                </th>

                                <th class="py-4 px-4 text-right">
                                    Aksi
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-slate-100 text-sm">

                            @foreach($courses as $course)

                                @php
                                    $materialsCount = $course->materials ? $course->materials->count() : 0;
                                    $assignmentsCount = $course->assignments ? $course->assignments->count() : 0;

                                    $isEnrolled = false;

                                    if (auth()->user()->role === 'mahasiswa') {
                                        $isEnrolled = $course->students()
                                            ->where('users.id', auth()->id())
                                            ->exists();
                                    }
                                @endphp


                                <tr class="hover:bg-slate-50/70 transition">

                                    {{-- Kode --}}
                                    <td class="py-5 px-4">

                                        <span class="inline-flex px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-mono font-bold">
                                            {{ $course->code }}
                                        </span>

                                    </td>


                                    {{-- Mata Kuliah --}}
                                    <td class="py-5 px-4">

                                        <div class="font-bold text-slate-800 text-sm">

                                            @if(auth()->user()->role === 'mahasiswa')

                                                <a
                                                    href="{{ route('mahasiswa.courses.show', $course->id) }}"
                                                    class="hover:text-blue-600 transition"
                                                >
                                                    {{ $course->name }}
                                                </a>

                                            @elseif(auth()->user()->role === 'dosen')

                                                <a
                                                    href="{{ route('dosen.courses.show', $course->id) }}"
                                                    class="hover:text-blue-600 transition"
                                                >
                                                    {{ $course->name }}
                                                </a>

                                            @else

                                                {{ $course->name }}

                                            @endif

                                        </div>


                                        {{-- Materi & Tugas --}}
                                        <div class="flex items-center gap-2 mt-1.5 text-xs">

                                            @if(auth()->user()->role !== 'admin')

                                                <a
                                                    href="{{ route(auth()->user()->role . '.courses.materials.index', $course->id) }}"
                                                    class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-600 px-2 py-0.5 rounded-md text-[11px] transition"
                                                >
                                                    📄
                                                    {{ $materialsCount }}
                                                    Materi
                                                </a>


                                                <a
                                                    href="{{ route(auth()->user()->role . '.courses.assignments.index', $course->id) }}"
                                                    class="inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 text-blue-700 px-2 py-0.5 rounded-md text-[11px] transition"
                                                >
                                                    ✏️
                                                    {{ $assignmentsCount }}
                                                    Tugas
                                                </a>

                                            @else

                                                <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md text-[11px]">
                                                    📄
                                                    {{ $materialsCount }}
                                                    Materi
                                                </span>

                                                <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-0.5 rounded-md text-[11px]">
                                                    ✏️
                                                    {{ $assignmentsCount }}
                                                    Tugas
                                                </span>

                                            @endif

                                        </div>

                                    </td>


                                    {{-- SKS --}}
                                    <td class="py-5 px-4">

                                        <span class="inline-flex px-3 py-1 rounded-lg bg-slate-100 text-slate-800 text-xs font-bold">
                                            {{ $course->sks ?? 0 }}
                                            SKS
                                        </span>

                                    </td>


                                    {{-- Dosen --}}
                                    <td class="py-5 px-4">

                                        <div class="flex items-center gap-3">

                                            <div class="w-9 h-9 rounded-full bg-[#0a2540] text-white font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr(optional($course->lecturer)->name ?? 'D', 0, 2)) }}
                                            </div>

                                            <div class="min-w-0">

                                                <p class="font-semibold text-slate-800 text-xs truncate max-w-[180px]">
                                                    {{ optional($course->lecturer)->name ?? 'Dosen Pengampu' }}
                                                </p>

                                                <p class="text-[10px] text-slate-400">
                                                    Dosen Pengampu
                                                </p>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- Status --}}
                                    <td class="py-5 px-4">

                                        @if(($course->status ?? 'active') === 'active')

                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">

                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>

                                                Aktif

                                            </span>

                                        @elseif(($course->status ?? '') === 'archived')

                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-100">

                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>

                                                Arsip

                                            </span>

                                        @else

                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">

                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>

                                                Draft

                                            </span>

                                        @endif

                                    </td>


                                    {{-- Aksi --}}
                                    <td class="py-5 px-4 text-right">

                                        <div class="flex items-center justify-end gap-2">

                                            {{-- ======================= --}}
                                            {{-- MAHASISWA --}}
                                            {{-- ======================= --}}
                                            @if(auth()->user()->role === 'mahasiswa')

                                                @if($isEnrolled)

                                                    <a
                                                        href="{{ route('mahasiswa.courses.show', $course->id) }}"
                                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#0a2540] hover:bg-[#0e3b68] text-white font-medium text-xs rounded-xl shadow-sm transition"
                                                    >
                                                        Buka Kelas

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

                                                @else

                                                    <form
                                                        action="{{ route('mahasiswa.courses.enroll', $course->id) }}"
                                                        method="POST"
                                                        class="inline"
                                                    >
                                                        @csrf

                                                        <button
                                                            type="submit"
                                                            class="inline-flex items-center gap-1 px-4 py-2 bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 font-semibold text-xs rounded-xl border border-slate-200 transition"
                                                        >
                                                            + Gabung Kelas
                                                        </button>

                                                    </form>

                                                @endif


                                            {{-- ======================= --}}
                                            {{-- DOSEN --}}
                                            {{-- ======================= --}}
                                            @elseif(auth()->user()->role === 'dosen')

                                                <a
                                                    href="{{ route('dosen.courses.show', $course->id) }}"
                                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#0a2540] hover:bg-[#0e3b68] text-white font-medium text-xs rounded-xl shadow-sm transition"
                                                >
                                                    Buka Kelas

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


                                            {{-- ======================= --}}
                                            {{-- ADMIN --}}
                                            {{-- ======================= --}}
                                            @elseif(auth()->user()->role === 'admin')

                                                <a
                                                    href="{{ route('admin.courses.index') }}"
                                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#0a2540] hover:bg-[#0e3b68] text-white font-medium text-xs rounded-xl shadow-sm transition"
                                                >
                                                    Kelola

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

                                            @endif

                                        </div>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


            {{-- ========================================================= --}}
            {{-- EMPTY STATE --}}
            {{-- ========================================================= --}}
            @else

                <div class="py-16 text-center">

                    <div class="w-16 h-16 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center text-3xl mx-auto mb-4">
                        📚
                    </div>

                    <h3 class="text-base font-bold text-slate-800">
                        Belum Ada Mata Kuliah
                    </h3>

                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Belum terdapat data mata kuliah
                        untuk kriteria yang dipilih.
                    </p>


                    @if(auth()->user()->role !== 'mahasiswa')

                        <a
                            href="{{ route(auth()->user()->role . '.courses.create') }}"
                            class="inline-flex items-center gap-1.5 mt-6 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-sm transition"
                        >
                            + Tambah Mata Kuliah
                        </a>

                    @endif

                </div>

            @endif


            {{-- Pagination --}}
            @if(
                is_object($courses) &&
                method_exists($courses, 'hasPages') &&
                $courses->hasPages()
            )

                <div class="pt-6 border-t border-slate-100 flex justify-center">

                    {{ $courses->withQueryString()->links() }}

                </div>

            @endif

        </section>


        {{-- ========================================================= --}}
        {{-- 5. BOTTOM INFORMATION --}}
        {{-- ========================================================= --}}
        <section class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">

            <div class="flex items-center gap-3.5">

                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">

                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>

                </div>

                <div>

                    <h4 class="text-sm font-bold text-slate-800">
                        Konsultasi Rencana Studi (KRS)
                    </h4>

                    <p class="text-xs text-slate-500">
                        Pastikan mata kuliah yang dipilih sesuai dengan
                        rencana studi dan kurikulum semester berjalan.
                    </p>

                </div>

            </div>


            <a
                href="{{ route('tentang') }}"
                class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shrink-0 inline-flex items-center gap-2"
            >

                <svg
                    class="w-4 h-4 text-slate-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"
                    />
                </svg>

                Hubungi Dosen Wali

            </a>

        </section>

    </div>
</x-layout>
