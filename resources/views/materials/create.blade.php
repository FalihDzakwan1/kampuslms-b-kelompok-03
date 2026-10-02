<x-layout title="Tambah Materi - {{ $course->name }} | KampusLMS">
    <div class="page-container max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('dosen.courses.materials.index', $course) }}" class="text-blue-600 hover:underline text-sm font-medium">
                ← Kembali ke Daftar Materi
            </a>
        </div>

        <section class="course-card bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Tambah Materi Kuliah</h1>
                <p class="text-gray-500 text-sm mt-1">Mata Kuliah: <strong class="text-gray-700">{{ $course->name }}</strong></p>
            </div>

            <form action="{{ route('dosen.courses.materials.store', $course) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-5">
                @csrf

                {{-- Judul Materi --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Materi</label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="Contoh: Modul 04 - Pengelolaan State & Validasi" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipe Materi --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tipe Materi</label>
                    <select name="type" id="material-type" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="file" {{ old('type', 'file') === 'file' ? 'selected' : '' }}>📄 Berkas Dokumen (PDF, PPT, DOCX, ZIP)</option>
                        <option value="link" {{ old('type') === 'link' ? 'selected' : '' }}>🔗 Tautan / URL Eksternal (Website, Video, Docs)</option>
                    </select>
                    @error('type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Upload Berkas --}}
                <div id="file-input-group">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Berkas Materi (Maks 10MB)</label>
                    <input type="file" name="file"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                    @error('file')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tautan URL --}}
                <div id="link-input-group">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tautan Eksternal (URL)</label>
                    <input type="url" name="external_url" value="{{ old('external_url') }}" placeholder="https://contoh.com/materi-kuliah"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('external_url')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi Materi --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi / Catatan Materi</label>
                    <textarea name="description" rows="4" placeholder="Penjelasan singkat mengenai materi ini..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tombol Aksi --}}
                <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                    <a href="{{ route('dosen.courses.materials.index', $course) }}" class="px-5 py-2 rounded-xl bg-gray-100 text-gray-600 font-medium hover:bg-gray-200 transition">
                        Batal
                    </a>
                    <button type="submit" class="btn-primary">
                        Simpan Materi
                    </button>
                </div>
            </form>
        </section>
    </div>

    <script>
        const typeSelect = document.getElementById('material-type');
        const fileGroup = document.getElementById('file-input-group');
        const linkGroup = document.getElementById('link-input-group');

        function toggleInputs() {
            if (typeSelect.value === 'file') {
                fileGroup.style.display = 'block';
                linkGroup.style.display = 'none';
            } else {
                fileGroup.style.display = 'none';
                linkGroup.style.display = 'block';
            }
        }

        typeSelect.addEventListener('change', toggleInputs);
        toggleInputs();
    </script>
</x-layout>
