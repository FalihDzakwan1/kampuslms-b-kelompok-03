<x-layout>
    <x-slot:title>Daftar Mata Kuliah | KampusLMS</x-slot:title>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 font-sans">

        {{-- ========================================================= --}}
        {{-- 1. FLASH MESSAGE --}}
        {{-- ========================================================= --}}
        @if (session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm text-sm">
                <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </div>

                <div class="font-medium">
                    {{ session('success') }}
                </div>
            </div>
        @endif


        {{-- ========================================================= --}}
        {{-- 2. HERO BANNER --}}
        {{-- ========================================================= --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0a2540] via-[#0e3b68] to-[#0284c7] p-8 md:p-10 text-white shadow-xl">

            {{-- Glow --}}
            <div class="absolute -right-16 -top-24 w-96 h-96 rounded-full bg-cyan-400/20 blur-3xl pointer-events-none"></div>

            <div class="absolute right-1/3 -bottom-24 w-80 h-80 rounded-full bg-blue-500/20 blur-2xl pointer-events-none"></div>

            <div class="relative z-10 max-w-3xl space-y-3">


                {{-- Title --}}
                <h1 class="text-left text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight text-white">
                                    <span class="text-white">
                    Daftar Mata Kuliah
                </h1>

                {{-- Description --}}
                <p class="text-sm md:text-base text-slate-200/90 leading-relaxed">
                    Kelola dan lihat daftar mata kuliah yang tersedia
                    dalam sistem KampusLMS secara terpadu.
                </p>


                {{-- Quick Stats --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4">

                    {{-- Total Mata Kuliah --}}
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10">

                        <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-cyan-300 flex items-center justify-center text-lg">
                            📚
                        </div>

                        <div>
                            <p class="text-[10px] text-cyan-200 uppercase font-bold tracking-wider">
                                Mata Kuliah
                            </p>

                            <p class="text-base font-extrabold text-white">
                                {{ is_countable($courses) ? count($courses) : ($courses->total() ?? 0) }}
                                Kursus
                            </p>
                        </div>
                    </div>


                    {{-- Semester --}}
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10">

                        <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-cyan-300 flex items-center justify-center text-lg">
                            🎓
                        </div>

                        <div>
                            <p class="text-[10px] text-cyan-200 uppercase font-bold tracking-wider">
                                Semester
                            </p>

                            <p class="text-base font-extrabold text-white">
                                Ganjil
                            </p>
                        </div>
                    </div>


                    {{-- Beban Studi --}}
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10">

                        <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-cyan-300 flex items-center justify-center text-lg">
                            ⭐
                        </div>

                        <div>
                            <p class="text-[10px] text-cyan-200 uppercase font-bold tracking-wider">
                                Beban Studi
                            </p>

                            <p class="text-base font-extrabold text-white">
                                24 SKS Max
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        {{-- ========================================================= --}}
        {{-- 3. SEARCH & FILTER --}}
        {{-- ========================================================= --}}
        <form
            action="{{ route(auth()->user()->role . '.courses.index') }}"
            method="GET"
            class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4"
        >

            {{-- Search --}}
            <div class="relative w-full md:w-96">

                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    placeholder="Cari mata kuliah..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent placeholder-slate-400"
                >
            </div>


            {{-- Filter & Tambah --}}
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">

                {{-- Status --}}
                <div class="relative">

                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 appearance-none pr-8 cursor-pointer"
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

                    <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </span>
                </div>


                {{-- Tambah Mata Kuliah --}}
                @if(auth()->user()->role !== 'mahasiswa')
                    <a
                        href="{{ route(auth()->user()->role . '.courses.create') }}"
                        class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-xl shadow-sm transition inline-flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        {{-- 4. TABLE MATA KULIAH --}}
        {{-- ========================================================= --}}
        <section class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden p-6">

            {{-- Table Header --}}
            <div class="flex items-center justify-between pb-6 mb-2 border-b border-slate-100">

                <div class="flex items-center gap-2.5">

                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>

                    <div>
                        <h3 class="font-bold text-slate-800 text-base">
                            Daftar Mata Kuliah
                        </h3>

                        <p class="text-xs text-slate-400">
                            Daftar kurikulum akademik yang tersedia di sistem
                        </p>
                    </div>

                </div>


                <span class="px-3 py-1 rounded-full text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-100">
                    {{ is_countable($courses) ? count($courses) : ($courses->total() ?? 0) }}
                    Mata Kuliah
                </span>

            </div>


            {{-- Data Available --}}
            @if ($courses->count())

                <div class="overflow-x-auto">

                    <table class="w-full text-left border-collapse">

                        <thead>
                            <tr class="text-[11px] font-bold tracking-wider text-slate-400 uppercase border-b border-slate-100">

                                <th class="py-4 px-4">
                                    Kode
                                </th>

                                <th class="py-4 px-4">
                                    Mata Kuliah
                                </th>

                                <th class="py-4 px-4">
                                    SKS
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

                            @foreach ($courses as $course)

                                <tr class="hover:bg-slate-50/70 transition">

                                    {{-- Kode --}}
                                    <td class="py-5 px-4 font-mono font-bold text-slate-700">

                                        <span class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-mono font-bold">
                                            {{ $course->code }}
                                        </span>

                                    </td>


                                    {{-- Nama Mata Kuliah --}}
                                    <td class="py-5 px-4">

                                        <div class="font-bold text-slate-800 text-sm">

                                            <a
                                                href="{{ route(auth()->user()->role . '.courses.show', $course->id) }}"
                                                class="text-[#0a2540] hover:text-blue-600 transition"
                                            >
                                                {{ $course->name }}
                                            </a>

                                        </div>


                                        <div class="flex items-center gap-2 mt-1.5 text-xs">

                                            {{-- Materi --}}
                                            <a
                                                href="{{ route(auth()->user()->role . '.courses.materials.index', $course->id) }}"
                                                class="inline-flex items-center gap-1 text-slate-600 bg-slate-100 hover:bg-slate-200 px-2 py-0.5 rounded-md text-[11px] transition"
                                            >
                                                📂
                                                {{ $course->materials()->count() }}
                                                Materi
                                            </a>


                                            {{-- Tugas --}}
                                            <a
                                                href="{{ route(auth()->user()->role . '.courses.assignments.index', $course->id) }}"
                                                class="inline-flex items-center gap-1 text-blue-700 bg-blue-50 hover:bg-blue-100 px-2 py-0.5 rounded-md text-[11px] font-medium transition"
                                            >
                                                📝
                                                {{ $course->assignments()->count() }}
                                                Tugas
                                            </a>

                                        </div>
                                    </td>


                                    {{-- SKS --}}
                                    <td class="py-5 px-4">

                                        <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-800 text-xs font-bold">
                                            {{ $course->sks ?? $course->credits ?? 3 }}
                                            SKS
                                        </span>

                                    </td>


                                    {{-- Dosen --}}
                                    <td class="py-5 px-4">

                                        <div class="flex items-center gap-2.5">

                                            <div class="w-8 h-8 rounded-full bg-[#0a2540] text-white font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($course->lecturer->name ?? 'D', 0, 1)) }}
                                            </div>

                                            <div>

                                                <p class="font-semibold text-slate-800 text-xs">
                                                    {{ $course->lecturer->name ?? 'Dosen Pengampu' }}
                                                </p>

                                                <p class="text-[10px] text-slate-400">
                                                    Pengampu Kelas
                                                </p>

                                            </div>

                                        </div>
                                    </td>


                                    {{-- Status --}}
                                    <td class="py-5 px-4">

                                        @if (($course->status ?? 'active') === 'active')

                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">

                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>

                                                Aktif

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

                                            {{-- ADMIN --}}
                                            @if(auth()->user()->role === 'admin')

                                                <a
                                                    href="{{ route(auth()->user()->role . '.courses.edit', $course->id) }}"
                                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition"
                                                >
                                                    Edit
                                                </a>


                                                <form
                                                    action="{{ route(auth()->user()->role . '.courses.destroy', $course->id) }}"
                                                    method="POST"
                                                    class="inline"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold rounded-lg transition"
                                                        onclick="return confirm('Yakin ingin menghapus mata kuliah ini?')"
                                                    >
                                                        Hapus
                                                    </button>
                                                </form>


                                            {{-- DOSEN --}}
                                            @elseif(auth()->user()->role === 'dosen')

                                                <a
                                                    href="{{ route('dosen.courses.show', $course->id) }}"
                                                    class="inline-flex items-center gap-1 px-3.5 py-1.5 bg-[#0a2540] hover:bg-[#0e3b68] text-white text-xs font-medium rounded-xl shadow-sm transition"
                                                >
                                                    Buka Kelas

                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M14 5l7 7m0 0l-7 7m7-7H3"
                                                        />
                                                    </svg>
                                                </a>


                                                <a
                                                    href="{{ route(auth()->user()->role . '.courses.edit', $course->id) }}"
                                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition"
                                                >
                                                    Edit
                                                </a>


                                                <form
                                                    action="{{ route(auth()->user()->role . '.courses.destroy', $course->id) }}"
                                                    method="POST"
                                                    class="inline"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold rounded-lg transition"
                                                        onclick="return confirm('Yakin ingin menghapus mata kuliah ini?')"
                                                    >
                                                        Hapus
                                                    </button>
                                                </form>


                                            {{-- MAHASISWA --}}
                                            @elseif(auth()->user()->role === 'mahasiswa')

                                                @php
                                                    $isEnrolled = $course->students()
                                                        ->where('users.id', auth()->id())
                                                        ->exists();
                                                @endphp

                                                @if($isEnrolled)

                                                    <a
                                                        href="{{ route('mahasiswa.courses.show', $course->id) }}"
                                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#0a2540] hover:bg-[#0e3b68] text-white font-medium text-xs rounded-xl shadow-sm transition"
                                                    >
                                                        Buka Kelas

                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                                            class="inline-flex items-center gap-1 px-4 py-2 bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white font-semibold text-xs rounded-xl border border-emerald-200 transition"
                                                        >
                                                            + Gabung
                                                        </button>
                                                    </form>

                                                @endif

                                            @endif

                                        </div>
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>
                </div>


            {{-- EMPTY STATE --}}
            @else

                <div class="py-16 text-center">

                    <div class="w-16 h-16 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center text-3xl mx-auto mb-4">
                        📚
                    </div>

                    <h3 class="text-base font-bold text-slate-800">
                        Belum Ada Mata Kuliah
                    </h3>

                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Belum terdapat data mata kuliah yang tersedia
                        untuk kriteria pencarian atau semester ini.
                    </p>


                    @if(auth()->user()->role !== 'mahasiswa')

                        <div class="mt-6">

                            <a
                                href="{{ route(auth()->user()->role . '.courses.create') }}"
                                class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition shadow-sm"
                            >
                                + Tambah Mata Kuliah
                            </a>

                        </div>

                    @endif

                </div>

            @endif


            {{-- ========================================================= --}}
            {{-- 5. PAGINATION --}}
            {{-- ========================================================= --}}
            @if (
                is_object($courses) &&
                method_exists($courses, 'hasPages') &&
                $courses->hasPages()
            )
                <div class="pt-6 border-t border-slate-100 flex justify-center">
                    {{ $courses->links() }}
                </div>
            @endif

        </section>


        {{-- ========================================================= --}}
        {{-- 6. INFORMATION CARD --}}
        {{-- ========================================================= --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">

            <div class="flex items-center gap-3.5">

                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">

                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        Informasi Akademik & KRS
                    </h4>

                    <p class="text-xs text-slate-500">
                        Pastikan data mata kuliah yang dimasukkan sudah sesuai
                        dengan jadwal kurikulum resmi semester berjalan.
                    </p>
                </div>

            </div>


            <div class="px-4 py-2 rounded-xl bg-slate-50 border border-slate-200/80 text-xs font-bold text-slate-600 shrink-0">
                Semester Ganjil 2024/2025
            </div>

        </div>

    </div>
</x-layout>
