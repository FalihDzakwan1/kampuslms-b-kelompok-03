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

        {{-- Area Dosen: Tabel Pengumpulan Tugas Mahasiswa --}}
        @if(auth()->user()->role === 'dosen' && $assignment->course->lecturer_id === auth()->id())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="flex flex-wrap justify-between items-center gap-4 mb-6 pb-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Daftar Pengumpulan Mahasiswa</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Periksa berkas jawaban tugas mahasiswa dan berikan nilai.</p>
                    </div>
                    <span class="px-3.5 py-1.5 bg-blue-50 text-blue-700 rounded-full text-xs font-bold border border-blue-100">
                        📥 {{ $assignment->submissions->count() }} dari {{ $assignment->course->students()->count() }} Mahasiswa Mengumpulkan
                    </span>
                </div>

                @if($assignment->submissions->count())
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="py-3.5 px-4">Mahasiswa</th>
                                    <th class="py-3.5 px-4">Waktu Submit</th>
                                    <th class="py-3.5 px-4">Berkas Jawaban</th>
                                    <th class="py-3.5 px-4">Status Nilai</th>
                                    <th class="py-3.5 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs text-gray-700">
                                @foreach($assignment->submissions as $sub)
                                    <tr class="hover:bg-gray-50/70 transition">
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-gray-900">{{ $sub->student->name ?? 'Mahasiswa' }}</div>
                                            <div class="text-[11px] text-gray-400">NIM: {{ $sub->student->nim_nip ?? '-' }}</div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div>{{ $sub->submitted_at ? \Carbon\Carbon::parse($sub->submitted_at)->translatedFormat('d M Y, H:i') : '-' }}</div>
                                            @if($sub->is_late)
                                                <span class="inline-block mt-0.5 px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-bold">Terlambat</span>
                                            @else
                                                <span class="inline-block mt-0.5 px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-bold">Tepat Waktu</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <a href="{{ asset('storage/' . $sub->file_path) }}" target="_blank" download class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-semibold bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-md transition">
                                                ⬇ {{ $sub->original_name ?? 'Berkas' }}
                                            </a>
                                            @if($sub->note)
                                                <p class="text-[11px] text-gray-400 mt-1 italic line-clamp-1" title="{{ $sub->note }}">"{{ $sub->note }}"</p>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($sub->grade)
                                                <span class="px-2.5 py-1 bg-green-100 text-green-800 rounded-full font-bold">
                                                    ⭐ {{ $sub->grade->score }} / {{ $assignment->max_score ?? 100 }}
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 rounded-full font-semibold">
                                                    ⏳ Belum Dinilai
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <a href="{{ route('dosen.submissions.show', $sub) }}" class="btn-primary text-xs px-3 py-1.5 inline-flex items-center gap-1">
                                                <span>{{ $sub->grade ? 'Ubah Nilai' : 'Beri Nilai' }}</span> →
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center text-gray-400 bg-gray-50 rounded-xl">
                        <p class="text-3xl mb-1">📥</p>
                        <p class="text-sm font-medium text-gray-600">Belum ada mahasiswa yang mengumpulkan tugas ini.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-layout>
