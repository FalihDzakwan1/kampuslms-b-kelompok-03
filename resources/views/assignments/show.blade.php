<x-layout title="{{ $assignment->title }} | KampusLMS">
    <div class="page-container max-w-4xl">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route(auth()->user()->role . '.courses.assignments.index', $assignment->course) }}" class="text-blue-600 hover:underline text-sm font-medium">
                ← Kembali ke Daftar Tugas
            </a>

            @can('update', $assignment)
                <div class="space-x-2">
                    <a href="{{ route(auth()->user()->role . '.assignments.edit', $assignment) }}" class="btn-secondary text-xs px-4 py-2">
                        ✎ Edit Tugas
                    </a>
                </div>
            @endcan
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-xl text-sm mb-6">
                <div>
                    <span class="text-gray-500 block">Batas Waktu:</span>
                    <strong class="text-gray-800">{{ $assignment->due_at ? \Carbon\Carbon::parse($assignment->due_at)->translatedFormat('l, d F Y - H:i') : 'Tidak ditentukan' }}</strong>
                </div>
                <div>
                    <span class="text-gray-500 block">Nilai Maksimal:</span>
                    <strong class="text-gray-800">{{ $assignment->max_score ?? 100 }} Poin</strong>
                </div>
                <div>
                    <span class="text-gray-500 block">Pengumpulan Terlambat:</span>
                    @if($assignment->allow_late)
                        <strong class="text-amber-700">Diizinkan (Ditandai Terlambat)</strong>
                    @else
                        <strong class="text-red-600">Ditolak Setelah Deadline</strong>
                    @endif
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
                $mySubmission = $assignment->submissions->firstWhere('user_id', auth()->id());
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Pengumpulan Tugas Anda</h2>

                @if($mySubmission)
                    <div class="p-5 bg-green-50 border border-green-200 rounded-xl mb-6">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                                <span class="text-green-800 font-bold text-sm">Tugas Sudah Dikumpulkan</span>
                                @if($mySubmission->is_late)
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Terlambat</span>
                                @else
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Tepat Waktu</span>
                                @endif
                            </div>
                            <a href="{{ route('mahasiswa.submissions.show', $mySubmission) }}" class="text-xs font-semibold text-blue-600 hover:underline">
                                Lihat Detail & Nilai →
                            </a>
                        </div>
                        <p class="text-sm text-gray-700">Waktu submit: <strong>{{ \Carbon\Carbon::parse($mySubmission->submitted_at)->translatedFormat('d M Y, H:i') }}</strong></p>
                        <p class="text-sm text-gray-700 mt-1">
                            Berkas:
                            <a href="{{ route('submissions.download', $mySubmission) }}" target="_blank" download class="font-bold text-blue-600 hover:underline">
                                {{ $mySubmission->original_name ?? 'Berkas Tugas' }}
                            </a>
                            @if($mySubmission->formatted_file_size)
                                <span class="text-xs text-gray-500">({{ $mySubmission->formatted_file_size }})</span>
                            @endif
                        </p>
                        @if($mySubmission->note)
                            <p class="text-sm text-gray-600 mt-2 italic">"{{ $mySubmission->note }}"</p>
                        @endif

                        @if($mySubmission->grade)
                            <div class="mt-4 p-4 bg-white border border-green-200 rounded-xl shadow-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-green-700">Hasil Penilaian Dosen</span>
                                    <span class="text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($mySubmission->grade->graded_at)->translatedFormat('d M Y, H:i') }}
                                    </span>
                                </div>
                                <div class="flex items-baseline gap-2 mt-2">
                                    <span class="text-3xl font-extrabold text-green-700">{{ $mySubmission->grade->score }}</span>
                                    <span class="text-xs text-gray-500 font-semibold">/ {{ $assignment->max_score ?? 100 }} Poin</span>
                                </div>
                                @if($mySubmission->grade->feedback)
                                    <div class="mt-2 text-xs text-gray-700 bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                        <strong class="text-gray-900 block text-[11px] mb-0.5">Umpan Balik Dosen:</strong>
                                        {{ $mySubmission->grade->feedback }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="mt-3 text-xs text-amber-700 font-medium flex items-center gap-1.5">
                                <span>⏳</span> Menunggu penilaian dari dosen pengampu.
                            </div>
                        @endif
                    </div>
                @endif

                @can('create', [App\Models\Submission::class, $assignment])
                    <form action="{{ route('mahasiswa.assignments.submissions.store', $assignment) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4 {{ $mySubmission ? 'pt-4 border-t border-gray-100' : '' }}">
                        @csrf
                        @if($mySubmission)
                            <h3 class="text-sm font-bold text-gray-700">🔄 Perbarui / Kumpulkan Ulang Berkas Tugas</h3>
                            <p class="text-xs text-gray-500 -mt-2">Mengunggah berkas baru akan menggantikan berkas pengumpulan Anda sebelumnya.</p>
                        @endif
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Unggah Berkas Tugas (PDF/DOC/DOCX, Maks 2MB)</label>
                            <input type="file" name="file" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @error('file')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                            <input type="text" name="note" value="{{ old('note', $mySubmission->note ?? '') }}" placeholder="Contoh: Tugas sudah disesuaikan dengan instruksi pertemuan 5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="btn-primary">
                                📤 {{ $mySubmission ? 'Kumpulkan Ulang Tugas' : 'Kumpulkan Tugas' }}
                            </button>
                        </div>
                    </form>
                @else
                    @if($mySubmission && $mySubmission->grade)
                        <p class="text-xs text-gray-500 italic">Tugas ini sudah dinilai oleh dosen sehingga berkas pengumpulan tidak dapat diperbarui lagi.</p>
                    @endif
                @endcan
            </div>
        @endif

        {{-- Area Dosen: Tabel Pengumpulan Tugas Mahasiswa --}}
        @can('viewAny', [App\Models\Submission::class, $assignment])
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="flex flex-wrap justify-between items-center gap-4 mb-6 pb-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Daftar Pengumpulan Mahasiswa</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Periksa berkas jawaban tugas mahasiswa dan berikan nilai.</p>
                    </div>
                    <span class="px-3.5 py-1.5 bg-blue-50 text-blue-700 rounded-full text-xs font-bold border border-blue-100">
                        📥 {{ $assignment->submissions->count() }} dari {{ $assignment->course->students->count() }} Mahasiswa Mengumpulkan
                    </span>
                </div>

                @if($assignment->course->students->count())
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
                                @php
                                    $submissionsMap = $assignment->submissions->keyBy('user_id');
                                @endphp

                                @foreach($assignment->course->students as $student)
                                    @php
                                        $sub = $submissionsMap->get($student->id);
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 transition">
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-gray-900">{{ $student->name }}</div>
                                            <div class="text-[11px] text-gray-400">NIM: {{ $student->nim_nip ?? '-' }}</div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($sub)
                                                <div>{{ \Carbon\Carbon::parse($sub->submitted_at)->translatedFormat('d M Y, H:i') }}</div>
                                                @if($sub->is_late)
                                                    <span class="inline-block mt-0.5 px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-bold">Terlambat</span>
                                                @else
                                                    <span class="inline-block mt-0.5 px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-bold">Tepat Waktu</span>
                                                @endif
                                            @else
                                                <span class="inline-block px-2.5 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[10px] font-semibold">
                                                    Belum Mengumpulkan
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($sub)
                                                <a href="{{ route('submissions.download', $sub) }}" target="_blank" download class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-semibold bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-md transition">
                                                    ⬇ {{ $sub->original_name ?? 'Berkas' }}
                                                </a>
                                                @if($sub->formatted_file_size)
                                                    <span class="text-[10px] text-gray-400 block mt-0.5">{{ $sub->formatted_file_size }}</span>
                                                @endif
                                                @if($sub->note)
                                                    <p class="text-[11px] text-gray-400 mt-1 italic line-clamp-1" title="{{ $sub->note }}">"{{ $sub->note }}"</p>
                                                @endif
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($sub && $sub->grade)
                                                <span class="px-2.5 py-1 bg-green-100 text-green-800 rounded-full font-bold">
                                                    ⭐ {{ $sub->grade->score }} / {{ $assignment->max_score ?? 100 }}
                                                </span>
                                            @elseif($sub)
                                                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 rounded-full font-semibold">
                                                    ⏳ Belum Dinilai
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            @if($sub)
                                                <a href="{{ route(auth()->user()->role . '.submissions.show', $sub) }}" class="btn-primary text-xs px-3 py-1.5 inline-flex items-center gap-1">
                                                    <span>{{ $sub->grade ? 'Ubah Nilai' : 'Beri Nilai' }}</span> →
                                                </a>
                                            @else
                                                <span class="text-gray-400 text-xs italic">Menunggu pengumpulan</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center text-gray-400 bg-gray-50 rounded-xl">
                        <p class="text-3xl mb-1">👥</p>
                        <p class="text-sm font-medium text-gray-600">Belum ada mahasiswa yang terdaftar di mata kuliah ini.</p>
                    </div>
                @endif
            </div>
        @endcan
    </div>
</x-layout>
