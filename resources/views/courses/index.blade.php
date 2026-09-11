<x-layout>
    <x-slot:title>Daftar Mata Kuliah</x-slot:title>

    <div class="page-container py-8">

        <!-- Notifikasi Flash Message -->
        @if (session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"/>
                    </svg>

                    <span>{{ session('success') }}</span>
                </div>

                <button type="button"
                    onclick="this.parentElement.remove()"
                    class="text-emerald-500 hover:text-emerald-700 text-base font-bold leading-none">
                    &times;
                </button>
            </div>
        @endif


        <!-- Header: Judul & Tombol Tambah Mata Kuliah -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">

            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mb-1">
                    Daftar Mata Kuliah
                </h1>

                <p class="text-sm text-slate-500">
                    Kelola data mata kuliah dan pengampu akademik semester ini.
                </p>
            </div>

            <a href="{{ route('courses.create') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl bg-slate-900 text-white hover:bg-slate-800 shadow-sm transition-all duration-150">

                <svg class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v16m8-8H4"/>

                </svg>

                Tambah Mata Kuliah
            </a>
        </div>


        <!-- Toolbar: Counter Jumlah MK & Input Pencarian -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">

            <div class="text-sm font-medium text-slate-600 flex items-center">

                <span>Jumlah Mata Kuliah:</span>

                <span class="ml-2 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-bold text-xs">
                    {{ is_countable($courses) ? count($courses) : ($courses->total() ?? 0) }}
                </span>

            </div>


            <form action="{{ route('courses.index') }}"
                method="GET"
                class="w-full sm:w-80 relative">

                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">

                    <svg class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>

                    </svg>

                </span>

                <input type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari mata kuliah..."
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-600/10 transition duration-150">

            </form>

        </div>


        <!-- Tabel Modern -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">

            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm text-slate-600 border-collapse">

                    <thead class="bg-slate-50/80 text-xs font-semibold text-slate-500 border-b border-slate-200">

                        <tr>
                            <th class="px-6 py-4">Kode MK</th>
                            <th class="px-6 py-4">Nama Mata Kuliah</th>
                            <th class="px-6 py-4">SKS</th>
                            <th class="px-6 py-4">Dosen Pengampu</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @forelse($courses as $course)

                            <tr class="hover:bg-blue-50/20 transition duration-150">

                                <!-- Kode MK -->
                                <td class="px-6 py-4 font-semibold text-slate-900 font-mono text-xs sm:text-sm">
                                    {{ $course->code }}
                                </td>


                                <!-- Nama MK -->
                                <td class="px-6 py-4 font-medium text-slate-800">
                                    {{ $course->name }}
                                </td>


                                <!-- SKS -->
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $course->sks }} SKS
                                </td>


                                <!-- Dosen Pengampu -->
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $course->lecturer_id }}
                                </td>


                                <!-- Status -->
                                <td class="px-6 py-4">

                                    @php
                                        $status = strtolower($course->status ?? 'aktif');
                                    @endphp

                                    @if($status === 'aktif')

                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                            Aktif
                                        </span>

                                    @else

                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 border border-amber-200/80">
                                            Draft
                                        </span>

                                    @endif

                                </td>


                                <!-- Tombol Aksi -->
                                <td class="px-6 py-4 text-right">

                                    <div class="inline-flex items-center gap-2">

                                        <!-- Tombol Edit -->
                                        <a href="{{ route('courses.edit', $course->id) }}"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition"
                                            title="Edit">

                                            <svg class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24">

                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>

                                            </svg>

                                        </a>


                                        <!-- Tombol Hapus -->
                                        <form action="{{ route('courses.destroy', $course->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?')"
                                            class="inline">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                                title="Hapus">

                                                <svg class="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24">

                                                    <path stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>

                                                </svg>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td colspan="6"
                                    class="px-6 py-12 text-center text-slate-400 text-sm">

                                    <div class="flex flex-col items-center justify-center gap-2">

                                        <svg class="w-8 h-8 text-slate-300"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18 18.247 18 16.5 18c-1.746 0-3.332 0-4.5 1.253"/>

                                        </svg>

                                        <span>
                                            Belum ada data mata kuliah.
                                        </span>

                                        <a href="{{ route('courses.create') }}"
                                            class="text-blue-600 hover:underline text-xs font-semibold mt-1">
                                            + Tambah mata kuliah pertama
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            <!-- Pagination -->
            @if(is_object($courses) && method_exists($courses, 'hasPages') && $courses->hasPages())

                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $courses->links() }}
                </div>

            @endif

        </div>

    </div>

</x-layout>