<x-layout title="Edit Materi - {{ $material->title }} | KampusLMS">
    <div class="page-container max-w-3xl">
        <div class="mb-6">
            <a href="{{ route(auth()->user()->role . '.materials.show', $material) }}" class="text-blue-600 hover:underline text-sm font-medium">
                ← Kembali ke Detail Materi
            </a>
        </div>

        <section class="course-card bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Edit Materi Kuliah</h1>
                <p class="text-gray-500 text-sm mt-1">Mata Kuliah: <strong class="text-gray-700">{{ $material->course->name }}</strong></p>
            </div>

            <form action="{{ route(auth()->user()->role . '.materials.update', $material) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-5">
                @csrf
                @method('PUT')

                {{-- Judul Materi --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Materi</label>
                    <input type="text" name="title" value="{{ old('title', $material->title) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipe Materi --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tipe Materi Perkuliahan</label>
                    <select name="type" id="material-type" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="file" {{ old('type', $material->type) === 'file' ? 'selected' : '' }}>📄 Berkas Dokumen (PDF, PPTX, DOCX — Maks 20 MB)</option>
                        <option value="link" {{ old('type', $material->type) === 'link' ? 'selected' : '' }}>🔗 Alternatif Tautan Eksternal (Google Drive, YouTube, Docs, dll.)</option>
                    </select>
                    @error('type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Upload Berkas Baru --}}
                <div id="file-input-group" class="p-4 bg-purple-50/50 rounded-xl border border-purple-100">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Ganti Berkas Materi
                        <span class="text-xs text-purple-700 font-medium ml-1">(Opsional, PDF, PPTX, DOCX — Maks. 20 MB)</span>
                    </label>
                    @if($material->type === 'file' && $material->file_path)
                        <div class="flex items-center gap-2 p-2.5 bg-white rounded-lg border border-purple-200 mb-3 text-xs text-gray-600">
                            <span class="text-base">📄</span>
                            <span>Berkas saat ini di disk privat: <strong class="text-gray-900 font-semibold">{{ $material->original_name ?? 'Berkas' }}</strong></span>
                            @if($material->file_size)
                                <span class="text-gray-400">({{ number_format($material->file_size / (1024 * 1024), 2) }} MB)</span>
                            @endif
                        </div>
                    @endif
                    <input type="file" name="file" id="material-file-input"
                        accept=".pdf,.pptx,.docx,application/pdf,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-purple-600 file:text-white hover:file:bg-purple-700 transition">
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-gray-500">
                        <svg class="w-4 h-4 text-emerald-600 inline shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span>Berkas baru akan disimpan di <strong>disk privat server</strong> menggantikan berkas lama.</span>
                    </div>
                    @error('file')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tautan URL --}}
                <div id="link-input-group" class="p-4 bg-cyan-50/50 rounded-xl border border-cyan-100">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Alternatif Tautan Eksternal (URL)
                    </label>
                    <input type="url" name="external_url" value="{{ old('external_url', $material->external_url) }}" placeholder="https://drive.google.com/... atau https://youtu.be/..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 outline-none bg-white">
                    <p class="mt-1 text-xs text-gray-500">Gunakan tautan eksternal sebagai alternatif jika materi disimpan di cloud storage atau platform eksternal.</p>
                    @error('external_url')
                        <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi Materi --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi / Catatan Materi</label>
                    <textarea name="description" rows="4" placeholder="Penjelasan singkat mengenai materi ini..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">{{ old('description', $material->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tombol Aksi --}}
                <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                    <a href="{{ route(auth()->user()->role . '.materials.show', $material) }}" class="px-5 py-2 rounded-xl bg-gray-100 text-gray-600 font-medium hover:bg-gray-200 transition">
                        Batal
                    </a>
                    <button type="submit" class="btn-primary">
                        Perbarui Materi
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
