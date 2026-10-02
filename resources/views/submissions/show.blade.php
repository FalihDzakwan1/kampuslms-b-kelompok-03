<x-layout title="Detail Pengumpulan Tugas | KampusLMS">
    <div class="page-container max-w-4xl">
        {{-- Navigasi Balik --}}
        <div class="mb-6 flex justify-between items-center">
            @if(auth()->user()->role === 'mahasiswa')
                <a href="{{ route('mahasiswa.submissions.index') }}" class="text-blue-600 hover:underline text-sm font-medium">
                    ← Kembali ke Riwayat Tugas
                </a>
            @else
                <a href="{{ route('dosen.assignments.show', $submission->assignment) }}" class="text-blue-600 hover:underline text-sm font-medium">
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

            {{-- Berkas yang Dikumpulkan --}}
            <div class="p-6 bg-blue-50/50 rounded-2xl border border-blue-100 mb-6 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📁</span>
                    <div>
                        <h4 class="text-sm font-bold text-gray-800">{{ $submission->original_name ?? 'Berkas Tugas' }}</h4>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $submission->file_size ? number_format($submission->file_size / 1024, 1) . ' KB' : 'Dokumen' }}
                        </p>
                    </div>
                </div>

                <a href="{{ asset('storage/' . $submission->file_path) }}" target="_blank" download
                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition flex items-center gap-2 shadow-sm">
                    ⬇ Unduh Berkas Tugas
                </a>
            </div>

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
                    </p>
                @else
                    <p class="text-sm text-yellow-700">Tugas ini belum dinilai oleh dosen pengampu.</p>
                @endif
            </div>

            {{-- Batalkan Pengumpulan (Mahasiswa) --}}
            @if(auth()->user()->role === 'mahasiswa' && $submission->user_id === auth()->id() && !$submission->grade)
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
            @endif
        </div>
    </div>
</x-layout>
