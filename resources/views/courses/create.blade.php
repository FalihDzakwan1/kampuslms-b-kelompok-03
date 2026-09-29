<x-layout title="Tambah Mata Kuliah | KampusLMS">
    <div class="page-container max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('courses.index') }}" class="text-blue-600 hover:underline text-sm">← Kembali ke Daftar Mata Kuliah</a>
        </div>

        <section class="course-card">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-800 m-0">Tambah Mata Kuliah Baru</h1>
                <p class="text-gray-500 text-sm mt-1">Masukkan data mata kuliah baru ke dalam sistem</p>
            </div>

            <form action="{{ route('courses.store') }}" method="POST" class="flex flex-col gap-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kode Mata Kuliah</label>
                        <input type="text" name="code" value="{{ old('code') }}" placeholder="Contoh: SI2514024"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        @error('code')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">SKS</label>
                        <input type="number" name="sks" value="{{ old('sks') }}" min="1" max="6" placeholder="Contoh: 3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        @error('sks')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Mata Kuliah</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Pemrograman Web"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Dosen Pengampu</label>
                    <select name="lecturer_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                        <option value="">Pilih Dosen</option>
                        @foreach($lecturers as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('lecturer_id') == $dosen->id ? 'selected' : '' }}>
                                {{ $dosen->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('lecturer_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                        <option value="draft"    {{ old('status') == 'draft'    ? 'selected' : '' }}>Draft</option>
                        <option value="active"   {{ old('status') == 'active'   ? 'selected' : '' }}>Aktif</option>
                        <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Arsip</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Singkat</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                    <a href="{{ route('courses.index') }}" class="px-5 py-2 rounded-xl bg-gray-100 text-gray-600 font-medium hover:bg-gray-200 transition">Batal</a>
                    <button type="submit" class="btn-primary border-none cursor-pointer">Simpan Mata Kuliah</button>
                </div>
            </form>
        </section>
    </div>
</x-layout>