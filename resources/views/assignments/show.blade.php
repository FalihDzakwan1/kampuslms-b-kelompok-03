<x-layout title="{{ $assignment->title }} | KampusLMS">
    <div class="page-container max-w-4xl">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route(auth()->user()->role . '.courses.assignments.index', $assignment->course) }}" class="text-blue-600 hover:underline text-sm font-medium">
                ← Kembali ke Daftar Tugas
            </a>

            @if(auth()->user()->role === 'dosen' && $assignment->course->lecturer_id === auth()->id())
                <div class="space-x-2">
                    <a href="{{ route('dosen.assignments.edit', $assignment) }}" class="btn-secondary text-xs px-4 py-2">
                        ✎ Edit Tugas
                    </a>
                </div>
            @endif
        </div>

        {{-- Kartu Detail Tugas --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-6">
            <div class="flex items-center gap-2 mb-3">
                <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-full">
                    {{ $assignment->course->code }} — {{ $assignment->course->name }}
                </span>
                @if($assignment->status === 'published')
                    <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Tersedia</span>
                @else
                    <span class="px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">Draft</span>
                @endif
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $assignment->title }}</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-xl text-sm mb-6">
                <div>
                    <span class="text-gray-500 block">Batas Waktu:</span>
                    <strong class="text-gray-800">{{ $assignment->due_at ? \Carbon\Carbon::parse($assignment->due_at)->translatedFormat('l, d F Y - H:i') : 'Tidak ditentukan' }}</strong>
                </div>
                <div>
                    <span class="text-gray-500 block">Nilai Maksimal:</span>
                    <strong class="text-gray-800">{{ $assignment->max_score ?? 100 }} Poin</strong>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Instruksi Tugas:</h3>
                <div class="p-6 bg-white border border-gray-200 rounded-xl text-gray-700 leading-relaxed whitespace-pre-line">
                    {{ $assignment->instructions ?? 'Tidak ada instruksi khusus.' }}
                </div>
            </div>
        </div>

        {{-- Area Mahasiswa: Pengumpulan Tugas --}}
        @if(auth()->user()->role === 'mahasiswa')
            @php
                $mySubmission = $assignment->submissions()->where('user_id', auth()->id())->first();
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Pengumpulan Tugas Anda</h2>

                @if($mySubmission)
                    <div class="p-5 bg-green-50 border border-green-200 rounded-xl">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-3 h-3 rounded-full bg-green-500"></span>
                            <span class="text-green-800 font-bold text-sm">Tugas Sudah Dikumpulkan</span>
                        </div>
                        <p class="text-sm text-gray-700">Waktu submit: <strong>{{ \Carbon\Carbon::parse($mySubmission->submitted_at)->translatedFormat('d M Y, H:i') }}</strong></p>
                        <p class="text-sm text-gray-700 mt-1">Berkas: <strong class="text-blue-600">{{ $mySubmission->original_name ?? 'Berkas Tugas' }}</strong></p>
                        @if($mySubmission->note)
                            <p class="text-sm text-gray-600 mt-2 italic">"{{ $mySubmission->note }}"</p>
                        @endif
                    </div>
                @else
                    <form action="{{ route('mahasiswa.assignments.submissions.store', $assignment) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Unggah Berkas Tugas (Maks 2MB)</label>
                            <input type="file" name="file" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @error('file')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                            <input type="text" name="note" value="{{ old('note') }}" placeholder="Contoh: Tugas sudah disesuaikan dengan instruksi pertemuan 5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="btn-primary">
                                📤 Kumpulkan Tugas
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        @endif

        {{-- Area Dosen: Ringkasan Pengumpulan Mahasiswa --}}
        @if(auth()->user()->role === 'dosen' && $assignment->course->lecturer_id === auth()->id())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Status Pengumpulan Mahasiswa</h2>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">
                        {{ $assignment->submissions()->count() }} / {{ $assignment->course->students()->count() }} Mahasiswa
                    </span>
                </div>
                <p class="text-sm text-gray-500">Mahasiswa yang telah mengumpulkan tugas dapat dinilai dan dipantau di sini.</p>
            </div>
        @endif
    </div>
</x-layout>
