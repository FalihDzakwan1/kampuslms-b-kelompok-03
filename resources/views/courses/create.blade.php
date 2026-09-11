<x-layout>
    <x-slot:title>Tambah Mata Kuliah</x-slot:title>

    <div class="page-container max-w-4xl mx-auto py-8">

        <!-- Top Navigation -->
        <div class="flex items-center justify-between mb-8">

            <a href="{{ route('courses.index') }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
                <svg class="w-4 h-4"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>

                Kembali ke Daftar Mata Kuliah
            </a>

            <div class="text-xs text-slate-400 font-medium">
                Mata Kuliah
                <span class="mx-1">&rsaquo;</span>
                <span class="text-slate-700 font-semibold">
                    Tambah Baru
                </span>
            </div>

        </div>

        <!-- Page Header -->
        <div class="mb-8">

            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mb-2">
                Tambah Mata Kuliah
            </h1>

            <p class="text-sm text-slate-500">
                Masukkan detail informasi mata kuliah baru untuk semester ini.
            </p>

        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-10">

            <form action="{{ route('courses.store') }}"
                  method="POST"
                  class="space-y-6">

                @csrf

                <!-- Kode Mata Kuliah & SKS -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Kode -->
                    <div class="md:col-span-2">

                        <div class="flex items-center justify-between mb-1.5">
                            <label for="code"
                                   class="text-sm font-semibold text-slate-700">
                                Kode Mata Kuliah
                                <span class="text-rose-500">*</span>
                            </label>

                            <span class="text-xs text-slate-400 font-mono">
                                Format: AB-123
                            </span>
                        </div>

                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold">
                                #
                            </span>

                            <input type="text"
                                   id="code"
                                   name="code"
                                   value="{{ old('code') }}"
                                   placeholder="Contoh: SI-301"
                                   required
                                   class="w-full pl-9 pr-3.5 py-2.5 bg-white border
                                   @error('code')
                                       border-rose-400
                                   @else
                                       border-slate-300
                                   @enderror
                                   rounded-xl text-sm focus:border-blue-600
                                   focus:outline-none focus:ring-2
                                   focus:ring-blue-600/20">
                        </div>

                        @error('code')
                            <p class="text-xs text-rose-600 mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <!-- SKS -->
                    <div>

                        <label for="credits"
                               class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Jumlah SKS
                            <span class="text-rose-500">*</span>
                        </label>

                        <select id="credits"
                                name="credits"
                                required
                                class="w-full px-3.5 py-2.5 bg-white border
                                border-slate-300 rounded-xl text-sm
                                focus:border-blue-600 focus:outline-none
                                focus:ring-2 focus:ring-blue-600/20">

                            <option value="1"
                                {{ old('credits') == '1' ? 'selected' : '' }}>
                                1 SKS
                            </option>

                            <option value="2"
                                {{ old('credits') == '2' ? 'selected' : '' }}>
                                2 SKS
                            </option>

                            <option value="3"
                                {{ old('credits', '3') == '3' ? 'selected' : '' }}>
                                3 SKS
                            </option>

                            <option value="4"
                                {{ old('credits') == '4' ? 'selected' : '' }}>
                                4 SKS
                            </option>

                        </select>

                    </div>

                </div>

                <!-- Nama Mata Kuliah -->
                <div>

                    <label for="name"
                           class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Nama Mata Kuliah
                        <span class="text-rose-500">*</span>
                    </label>

                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name') }}"
                           placeholder="Contoh: Pemrograman Web Lanjut"
                           required
                           class="w-full px-3.5 py-2.5 bg-white border
                           border-slate-300 rounded-xl text-sm
                           focus:border-blue-600 focus:outline-none
                           focus:ring-2 focus:ring-blue-600/20">

                    @error('name')
                        <p class="text-xs text-rose-600 mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <!-- Dosen Pengampu -->
                <div>

                    <label for="lecturer"
                           class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Dosen Pengampu
                        <span class="text-rose-500">*</span>
                    </label>

                    <input type="text"
                           id="lecturer"
                           name="lecturer"
                           value="{{ old('lecturer') }}"
                           placeholder="Pilih atau ketik Dosen Pengampu"
                           required
                           class="w-full px-3.5 py-2.5 bg-white border
                           border-slate-300 rounded-xl text-sm
                           focus:border-blue-600 focus:outline-none
                           focus:ring-2 focus:ring-blue-600/20">

                    @error('lecturer')
                        <p class="text-xs text-rose-600 mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <!-- Deskripsi -->
                <div>

                    <div class="flex items-center justify-between mb-1.5">

                        <label for="description"
                               class="text-sm font-semibold text-slate-700">
                            Deskripsi Mata Kuliah
                        </label>

                        <span class="text-xs text-slate-400">
                            Opsional
                        </span>

                    </div>

                    <textarea id="description"
                              name="description"
                              rows="4"
                              placeholder="Tuliskan deskripsi singkat mengenai silabus atau capaian mata kuliah..."
                              class="w-full px-3.5 py-2.5 bg-white border
                              border-slate-300 rounded-xl text-sm
                              focus:border-blue-600 focus:outline-none
                              focus:ring-2 focus:ring-blue-600/20">{{ old('description') }}</textarea>

                </div>

                <!-- Status -->
                <div>

                    <label for="status"
                           class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Status Publikasi
                        <span class="text-rose-500">*</span>
                    </label>

                    <select id="status"
                            name="status"
                            class="w-full px-3.5 py-2.5 bg-white border
                            border-slate-300 rounded-xl text-sm
                            focus:border-blue-600 focus:outline-none
                            focus:ring-2 focus:ring-blue-600/20">

                        <option value="aktif"
                            {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>
                            Aktif (Dapat diakses oleh mahasiswa)
                        </option>

                        <option value="draft"
                            {{ old('status') == 'draft' ? 'selected' : '' }}>
                            Draft (Hanya pengampu/admin)
                        </option>

                    </select>

                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">

                    <a href="{{ route('courses.index') }}"
                       class="px-5 py-2.5 text-sm font-medium rounded-xl
                       text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </a>

                    <button type="submit"
                            class="px-6 py-2.5 text-sm font-semibold rounded-xl
                            bg-blue-600 text-white hover:bg-blue-700
                            shadow-sm transition">
                        Simpan Mata Kuliah
                    </button>

                </div>

            </form>

        </div>

    </div>
</x-layout>