<x-layout title="Detail Pengumpulan Tugas | KampusLMS">
    <div class="page-container max-w-4xl">
        {{-- Navigasi Balik --}}
        <div class="mb-6 flex justify-between items-center">
            @if(auth()->user()->role === 'mahasiswa')
                <a href="{{ route('mahasiswa.submissions.index') }}" class="text-blue-600 hover:underline text-sm font-medium">
                    ← Kembali ke Riwayat Tugas
                </a>
            @else
                <a href="{{ route(auth()->user()->role . '.assignments.show', $submission->assignment) }}" class="text-blue-600 hover:underline text-sm font-medium">
                    ← Kembali ke Tugas {{ $submission->assignment->title ?? '' }}
                </a>
            @endif
        </div>

        {{-- Kartu Info Tugas & Pengumpulan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-6">
            <div class="flex items-center gap-2 mb-3">
                <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-full">
                    {{ $submission->assignment->course->code ?? '' }} — {{ $submission->assignment->course->name ?? '' }}
                </span>
                @if($submission->is_late)
                    <span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                        Terlambat
                    </span>
                @else
                    <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                        Tepat Waktu
                    </span>
                @endif
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $submission->assignment->title ?? 'Tugas' }}</h1>
            <p class="text-gray-500 text-sm mb-6">Batas Waktu: <strong>{{ $submission->assignment->due_at ? \Carbon\Carbon::parse($submission->assignment->due_at)->translatedFormat('l, d F Y - H:i') : 'Tidak ditentukan' }}</strong></p>

            {{-- Detail Pengumpul --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-xl text-sm mb-6">
                <div>
                    <span class="text-gray-500 block text-xs">Mahasiswa Pengumpul:</span>
                    <strong class="text-gray-800">{{ $submission->student->name ?? 'Mahasiswa' }}</strong>
                    <span class="text-xs text-gray-500 block mt-0.5">NIM/NIP: {{ $submission->student->nim_nip ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-xs">Waktu Dikirim:</span>
                    <strong class="text-gray-800">{{ $submission->submitted_at ? \Carbon\Carbon::parse($submission->submitted_at)->translatedFormat('d M Y, H:i') : '-' }}</strong>
                </div>
            </div>

            @php
                $ext = strtoupper(pathinfo($submission->original_name ?? '', PATHINFO_EXTENSION));
                $isPdf = $ext === 'PDF';
            @endphp

            {{-- Berkas yang Dikumpulkan --}}
            <div class="p-6 bg-blue-50/50 rounded-2xl border border-blue-100 mb-6 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('submissions.preview', $submission) }}" target="_blank"
                        title="Klik untuk pratinjau berkas di tab baru"
                        class="text-3xl hover:scale-110 transition-transform duration-150 inline-block">
                        @if($isPdf) 📕 @else 📁 @endif
                    </a>
                    <div>
                        <a href="{{ route('submissions.preview', $submission) }}" target="_blank"
                            class="text-sm font-bold text-gray-800 hover:text-blue-600 hover:underline transition inline-flex items-center gap-1.5"
                            title="Klik untuk pratinjau berkas di tab baru">
                            <span>{{ $submission->original_name ?? 'Berkas Tugas' }}</span>
                            <span class="text-xs text-blue-500 font-normal">⛶</span>
                        </a>
                        <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-2">
                            <span>{{ $submission->formatted_file_size ?? 'Dokumen' }}</span>
                            <span>•</span>
                            <span class="text-blue-700 font-medium">Tersimpan di Disk Privat KampusLMS</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('submissions.preview', $submission) }}" target="_blank"
                        class="px-4 py-2.5 rounded-xl bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 font-semibold text-xs transition flex items-center gap-1.5 shadow-sm">
                        <span>👁 Pratinjau Berkas</span>
                    </a>
                    <a href="{{ route('submissions.download', $submission) }}" target="_blank" download
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition flex items-center gap-2 shadow-sm">
                        <span>⬇ Unduh Berkas Tugas</span>
                    </a>
                </div>
            </div>

            {{-- Viewer PDF Interaktif Jika Berkas Berformat PDF --}}
            @if($isPdf && $submission->file_path)
                <div class="mb-6 rounded-2xl overflow-hidden border border-gray-200 bg-gray-900 shadow-sm">
                    <div class="p-3 bg-gray-800 text-gray-200 text-xs flex justify-between items-center px-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span class="font-medium text-gray-300">Pratinjau Jawaban Mahasiswa: {{ $submission->original_name }}</span>
                            <span class="text-gray-400">({{ $submission->formatted_file_size ?? 'Dokumen PDF' }})</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-[11px] text-gray-400 hidden sm:inline">Disk Privat Terproteksi</span>
                            <a href="{{ route('submissions.preview', $submission) }}" target="_blank"
                                class="text-xs text-blue-300 hover:text-blue-100 font-medium underline flex items-center gap-1">
                                <span>⛶ Buka Tab Penuh</span>
                            </a>
                        </div>
                    </div>
                    <iframe src="{{ route('submissions.preview', $submission) }}#toolbar=1"
                        class="w-full h-[650px] border-0 bg-white"
                        title="Pratinjau PDF {{ $submission->original_name }}">
                    </iframe>
                </div>
            @endif

            {{-- Catatan Mahasiswa --}}
            @if($submission->note)
                <div class="mb-6">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Catatan Mahasiswa:</h3>
                    <div class="p-4 bg-gray-50 rounded-xl text-gray-700 text-sm leading-relaxed border border-gray-100">
                        {{ $submission->note }}
                    </div>
                </div>
            @endif

            {{-- Hasil Penilaian --}}
            <div class="p-6 rounded-2xl border {{ $submission->grade ? 'bg-green-50/50 border-green-200' : 'bg-yellow-50/50 border-yellow-200' }}">
                <h3 class="text-sm font-bold {{ $submission->grade ? 'text-green-800' : 'text-yellow-800' }} mb-2">
                    Hasil Penilaian Dosen
                </h3>

                @if($submission->grade)
                    <div class="flex items-baseline gap-2 mb-3">
                        <span class="text-4xl font-extrabold text-green-700">{{ $submission->grade->score }}</span>
                        <span class="text-sm text-gray-500 font-medium">/ {{ $submission->assignment->max_score ?? 100 }} Poin</span>
                    </div>

                    @if($submission->grade->feedback)
                        <div class="p-4 bg-white rounded-xl border border-green-100 text-sm text-gray-700">
                            <span class="font-semibold block text-xs text-gray-500 mb-1">Catatan / Umpan Balik:</span>
                            {{ $submission->grade->feedback }}
                        </div>
                    @endif

                    <p class="text-xs text-gray-400 mt-3">
                        Dinilai pada: {{ \Carbon\Carbon::parse($submission->grade->graded_at)->translatedFormat('d M Y, H:i') }}
                        @if($submission->grade->grader)
                            • Oleh: <span class="text-gray-600 font-semibold">{{ $submission->grade->grader->name }}</span>
                        @endif
                    </p>
                @else
                    <p class="text-sm text-yellow-700">Tugas ini belum dinilai oleh dosen pengampu.</p>
                @endif
            </div>

            {{-- Form Penilaian Dosen --}}
            @if(auth()->user()->role === 'dosen' && $submission->assignment->course->lecturer_id === auth()->id())
                <div class="mt-6 p-6 rounded-2xl border border-blue-100 bg-blue-50/40">
                    <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span>✏️</span> {{ $submission->grade ? 'Perbarui Nilai & Umpan Balik' : 'Beri Nilai untuk Mahasiswa' }}
                    </h3>

                    <form action="{{ route(auth()->user()->role . '.submissions.grade', $submission) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Nilai (Skor 0 - {{ $submission->assignment->max_score ?? 100 }}) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="score" min="0" max="{{ $submission->assignment->max_score ?? 100 }}" step="0.5" 
                                value="{{ old('score', $submission->grade->score ?? '') }}" required
                                class="w-full max-w-xs px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                placeholder="Contoh: 85">
                            @error('score')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Umpan Balik / Catatan untuk Mahasiswa (Opsional)
                            </label>
                            <textarea name="feedback" rows="3" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                                placeholder="Berikan saran, masukan perbaikan, atau apresiasi...">{{ old('feedback', $submission->grade->feedback ?? '') }}</textarea>
                            @error('feedback')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit" class="btn-primary text-xs px-6 py-2.5">
                                💾 Simpan Nilai & Umpan Balik
                            </button>
                        </div>
                    </form>
                </div>
            @endcan

            {{-- Batalkan Pengumpulan (Mahasiswa) --}}
            @can('delete', $submission)
                <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                    <form action="{{ route('mahasiswa.submissions.destroy', $submission) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-50 text-red-700 hover:bg-red-100 font-semibold text-xs transition"
                            onclick="return confirm('Apakah Anda yakin ingin membatalkan pengumpulan tugas ini untuk mengunggah ulang?')">
                            🗑 Batalkan Pengumpulan Tugas
                        </button>
                    </form>
                </div>
            @endcan
        </div>
    </div>
</x-layout>
