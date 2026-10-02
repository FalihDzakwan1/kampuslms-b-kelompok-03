<x-layout title="Edit Tugas - {{ $assignment->title }} | KampusLMS">
    <div class="page-container max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('dosen.assignments.show', $assignment) }}" class="text-blue-600 hover:underline text-sm font-medium">
                ← Kembali ke Detail Tugas
            </a>
        </div>

        <section class="course-card bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Edit Tugas</h1>
                <p class="text-gray-500 text-sm mt-1">Mata Kuliah: <strong class="text-gray-700">{{ $assignment->course->name }}</strong></p>
            </div>

            <form action="{{ route('dosen.assignments.update', $assignment) }}" method="POST" class="flex flex-col gap-5">
                @csrf
                @method('PUT')

                {{-- Judul Tugas --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Tugas</label>
                    <input type="text" name="title" value="{{ old('title', $assignment->title) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Instruksi Tugas --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Instruksi Pengerjaan</label>
                    <textarea name="instructions" rows="5" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">{{ old('instructions', $assignment->instructions) }}</textarea>
                    @error('instructions')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {{-- Batas Waktu --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Batas Waktu</label>
                        <input type="datetime-local" name="due_at" 
                            value="{{ old('due_at', $assignment->due_at ? \Carbon\Carbon::parse($assignment->due_at)->format('Y-m-d\TH:i') : '') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        @error('due_at')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nilai Maksimal --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nilai Maksimal</label>
                        <input type="number" name="max_score" value="{{ old('max_score', $assignment->max_score) }}" min="1" max="100"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        @error('max_score')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Status Publikasi</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                            <option value="published" {{ old('status', $assignment->status) === 'published' ? 'selected' : '' }}>Publikasi (Aktif)</option>
                            <option value="draft" {{ old('status', $assignment->status) === 'draft' ? 'selected' : '' }}>Draft (Disimpan Sementara)</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                    <a href="{{ route('dosen.assignments.show', $assignment) }}" class="px-5 py-2 rounded-xl bg-gray-100 text-gray-600 font-medium hover:bg-gray-200 transition">
                        Batal
                    </a>
                    <button type="submit" class="btn-primary">
                        Perbarui Tugas
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-layout>
