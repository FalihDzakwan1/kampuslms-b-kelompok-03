<x-layout title="Detail Mata Kuliah | KampusLMS">
    <div class="page-container">
        <div class="mb-6">
            <a href="{{ route(auth()->user()->role . '.courses.index') }}" class="text-blue-600 hover:underline text-sm">← Kembali ke Daftar Mata Kuliah</a>
        </div>

        <div class="course-detail bg-white rounded-2xl shadow-lg border border-gray-100 mx-auto">
            <div class="course-detail__header flex justify-between items-start">
                <div>
                    <p class="course-detail__eyebrow">Detail Mata Kuliah</p>
                    <h1 class="text-3xl font-bold text-gray-800">{{ $course->name }}</h1>
                </div>

                @if ($course->status === 'active')
                    <span class="flex items-center gap-2 px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold uppercase">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span> Aktif
                    </span>
                @elseif ($course->status === 'archived')
                    <span class="flex items-center gap-2 px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold uppercase">
                        <span class="w-2 h-2 rounded-full bg-yellow-500"></span> Arsip
                    </span>
                @else
                    <span class="flex items-center gap-2 px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold uppercase">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span> Draft
                    </span>
                @endif
            </div>

            <div class="course-detail__info">
                <div>
                    <span>Kode Mata Kuliah</span>
                    <strong>{{ $course->code }}</strong>
                </div>
                <div>
                    <span>SKS</span>
                    <strong>{{ $course->sks }} SKS</strong>
                </div>
                <div>
                    <span>Dosen Pengampu</span>
                    <strong>{{ $course->lecturer->name ?? '-' }}</strong>
                </div>
            </div>

            <div class="mb-8 p-6 bg-gray-50 rounded-xl border border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Deskripsi</h3>
                <p class="text-gray-600 leading-relaxed">{{ $course->description ?? 'Belum ada deskripsi untuk mata kuliah ini.' }}</p>
            </div>

            {{-- Section 1: Materi Pembelajaran --}}
            <div class="mb-10 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4 pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">📂</span>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">Materi Perkuliahan</h2>
                            <p class="text-xs text-gray-500">Bahan ajar, modul slide, dokumen referensi, dan tautan perkuliahan.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @can('create', [App\Models\Material::class, $course])
                            <a href="{{ route(auth()->user()->role . '.courses.materials.create', $course) }}" class="btn-primary text-xs px-4 py-2">
                                + Tambah Materi
                            </a>
                        @endcan
                        <a href="{{ route(auth()->user()->role . '.courses.materials.index', $course) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-2 rounded-xl transition">
                            Lihat Semua ({{ $course->materials->count() }}) →
                        </a>
                    </div>
                </div>

                @if($course->materials->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($course->materials->take(6) as $material)
                            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/60 hover:bg-gray-50 hover:border-blue-200 transition flex items-start justify-between gap-3">
                                @php
                                    $ext = strtoupper(pathinfo($material->original_name ?? '', PATHINFO_EXTENSION));
                                @endphp
                                <div class="flex items-start gap-3">
                                    <span class="text-2xl mt-0.5">
                                        @if($material->type === 'file')
                                            @if($ext === 'PDF') 📕 @elseif(in_array($ext, ['PPTX', 'PPT'])) 📊 @elseif(in_array($ext, ['DOCX', 'DOC'])) 📝 @else 📄 @endif
                                        @else
                                            🔗
                                        @endif
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <h4 class="font-bold text-sm text-gray-900 line-clamp-1">{{ $material->title }}</h4>
                                            @if($material->type === 'file' && $ext)
                                                <span class="px-1.5 py-0.5 bg-purple-100 text-purple-700 text-[10px] font-bold rounded">
                                                    {{ $ext }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $material->description ?? 'Tidak ada deskripsi.' }}</p>
                                        @if($material->type === 'file' && $material->original_name)
                                            <span class="text-[11px] text-gray-500 mt-1 block line-clamp-1" title="{{ $material->original_name }}">
                                                {{ $material->original_name }}@if($material->formatted_file_size) · {{ $material->formatted_file_size }}@endif
                                            </span>
                                        @endif
                                        <span class="text-[11px] text-gray-400 mt-1 block">Oleh: {{ $material->uploader->name ?? 'Dosen' }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    @if($material->type === 'file')
                                        @if($ext === 'PDF')
                                            <a href="{{ route('materials.preview', $material) }}" target="_blank" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2.5 py-1.5 rounded-lg transition flex items-center gap-1">
                                                👁 Pratinjau
                                            </a>
                                        @endif
                                        <a href="{{ route('materials.download', $material) }}" class="text-xs font-semibold text-purple-600 hover:text-purple-800 bg-purple-50 hover:bg-purple-100 border border-purple-200 px-2.5 py-1.5 rounded-lg transition">
                                            ⬇ Unduh
                                        </a>
                                    @endif
                                    <a href="{{ route(auth()->user()->role . '.materials.show', $material) }}" class="text-xs font-semibold text-blue-600 hover:underline bg-white border border-gray-200 px-3 py-1.5 rounded-lg shadow-2xs">
                                        Buka
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-gray-400 bg-gray-50 rounded-xl">
                        <p class="text-3xl mb-1">📂</p>
                        <p class="text-sm font-medium text-gray-600">Belum ada materi pembelajaran yang diunggah.</p>
                        @can('create', [App\Models\Material::class, $course])
                            <a href="{{ route(auth()->user()->role . '.courses.materials.create', $course) }}" class="mt-2 inline-block text-xs text-blue-600 font-semibold hover:underline">
                                + Unggah materi pertama sekarang
                            </a>
                        @endcan
                    </div>
                @endif
            </div>

            {{-- Section 2: Tugas & Aktivitas Perkuliahan --}}
            <div class="mb-10 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4 pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">📝</span>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">Tugas & Aktivitas</h2>
                            <p class="text-xs text-gray-500">Tugas terstruktur, batas waktu pengumpulan, dan status penilaian.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @can('create', [App\Models\Assignment::class, $course])
                            <a href="{{ route(auth()->user()->role . '.courses.assignments.create', $course) }}" class="btn-primary text-xs px-4 py-2">
                                + Buat Tugas Baru
                            </a>
                        @endcan
                        <a href="{{ route(auth()->user()->role . '.courses.assignments.index', $course) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-2 rounded-xl transition">
                            Lihat Semua ({{ $course->assignments->count() }}) →
                        </a>
                    </div>
                </div>

                @if($course->assignments->count())
                    <div class="space-y-3">
                        @foreach($course->assignments as $assignment)
                            @php
                                $mySubmission = auth()->user()->role === 'mahasiswa' 
                                    ? $assignment->submissions->where('user_id', auth()->id())->first() 
                                    : null;
                            @endphp
                            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/60 hover:bg-gray-50 hover:border-blue-200 transition flex flex-wrap items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">📋</span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold text-sm text-gray-900">{{ $assignment->title }}</h4>
                                            @if($assignment->status === 'published')
                                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-bold rounded-full">Aktif</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-gray-200 text-gray-700 text-[10px] font-bold rounded-full">Draft</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">
                                            ⏰ Batas Waktu: <strong>{{ $assignment->due_at ? \Carbon\Carbon::parse($assignment->due_at)->translatedFormat('d M Y, H:i') : 'Tidak ditentukan' }}</strong>
                                            <span class="mx-2">•</span>
                                            Maks. Skor: {{ $assignment->max_score ?? 100 }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    @if(auth()->user()->role === 'mahasiswa')
                                        @if($mySubmission)
                                            <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full flex items-center gap-1">
                                                ✓ Sudah Dikumpulkan
                                            </span>
                                        @else
                                            <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded-full flex items-center gap-1">
                                                ⏱ Belum Mengumpulkan
                                            </span>
                                        @endif
                                    @elseif(auth()->user()->role === 'dosen')
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                            📥 {{ $assignment->submissions->count() }} Pengumpulan
                                        </span>
                                    @endif

                                    <a href="{{ route(auth()->user()->role . '.assignments.show', $assignment) }}" class="btn-primary text-xs px-4 py-2">
                                        {{ auth()->user()->role === 'mahasiswa' ? ($mySubmission ? 'Lihat Jawaban' : 'Kerjakan / Kumpul') : 'Periksa Tugas' }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-gray-400 bg-gray-50 rounded-xl">
                        <p class="text-3xl mb-1">📝</p>
                        <p class="text-sm font-medium text-gray-600">Belum ada tugas yang dibuat pada mata kuliah ini.</p>
                        @can('create', [App\Models\Assignment::class, $course])
                            <a href="{{ route(auth()->user()->role . '.courses.assignments.create', $course) }}" class="mt-2 inline-block text-xs text-blue-600 font-semibold hover:underline">
                                + Buat tugas pertama sekarang
                            </a>
                        @endcan
                    </div>
                @endif
            </div>

            {{-- Section 3: Rekap Nilai (No. 7) — Dosen & Mahasiswa --}}
            @if(isset($rekap) && $rekap !== null)
                <div class="mb-10 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <span class="text-xl">📊</span>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">Rekap Nilai</h2>
                            <p class="text-xs text-gray-500">
                                @if(auth()->user()->role === 'dosen')
                                    Ringkasan pengumpulan dan rata-rata nilai tiap tugas pada mata kuliah ini.
                                @else
                                    Ringkasan nilai Anda pada mata kuliah ini.
                                @endif
                            </p>
                        </div>
                    </div>

                    @if(auth()->user()->role === 'dosen')
                        {{-- Rekap Dosen: tabel per tugas --}}
                        @if($rekap->isNotEmpty())
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                            <th class="py-3 px-4">Tugas</th>
                                            <th class="py-3 px-4 text-center">Status</th>
                                            <th class="py-3 px-4 text-center">Terkumpul</th>
                                            <th class="py-3 px-4 text-center">Sudah Dinilai</th>
                                            <th class="py-3 px-4 text-center">Rata-rata Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($rekap as $row)
                                            <tr class="hover:bg-gray-50/80 transition">
                                                <td class="py-3 px-4">
                                                    <a href="{{ route(auth()->user()->role . '.assignments.show', $row['assignment']) }}"
                                                       class="font-semibold text-gray-800 hover:text-blue-600 transition">
                                                        {{ $row['assignment']->title }}
                                                    </a>
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    @if($row['assignment']->status === 'published')
                                                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-bold rounded-full">Aktif</span>
                                                    @else
                                                        <span class="px-2 py-0.5 bg-gray-200 text-gray-600 text-[10px] font-bold rounded-full">Draft</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    <span class="font-bold text-gray-800">{{ $row['total'] }}</span>
                                                    <span class="text-xs text-gray-400 ml-1">mahasiswa</span>
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    @if($row['total'] > 0)
                                                        <span class="font-bold {{ $row['graded'] === $row['total'] ? 'text-green-600' : 'text-amber-600' }}">
                                                            {{ $row['graded'] }}
                                                        </span>
                                                        <span class="text-xs text-gray-400">/ {{ $row['total'] }}</span>
                                                    @else
                                                        <span class="text-xs text-gray-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    @if($row['avg_score'] !== null)
                                                        <span class="font-bold text-blue-700">{{ $row['avg_score'] }}</span>
                                                        <span class="text-xs text-gray-400">/ {{ $row['max_score'] }}</span>
                                                    @else
                                                        <span class="text-xs text-gray-400">Belum ada nilai</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="py-6 text-center text-gray-400 bg-gray-50 rounded-xl">
                                <p class="text-sm">Belum ada tugas pada mata kuliah ini.</p>
                            </div>
                        @endif

                    @elseif(auth()->user()->role === 'mahasiswa')
                        {{-- Rekap Mahasiswa: kartu statistik + tabel --}}
                        @php
                            $mySubmissions  = $rekap['mySubmissions'];
                            $totalCollected = $rekap['totalCollected'];
                            $totalGraded    = $rekap['totalGraded'];
                            $avgScore       = $rekap['avgScore'];
                        @endphp

                        <div class="grid grid-cols-3 gap-4 mb-5">
                            <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 text-center">
                                <p class="text-2xl font-extrabold text-blue-700">{{ $totalCollected }}</p>
                                <p class="text-xs text-blue-500 mt-1 font-medium">Tugas Dikumpulkan</p>
                            </div>
                            <div class="p-4 bg-green-50 rounded-xl border border-green-100 text-center">
                                <p class="text-2xl font-extrabold text-green-700">{{ $totalGraded }}</p>
                                <p class="text-xs text-green-500 mt-1 font-medium">Sudah Dinilai</p>
                            </div>
                            <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100 text-center">
                                <p class="text-2xl font-extrabold text-indigo-700">
                                    {{ $avgScore !== null ? $avgScore : '—' }}
                                </p>
                                <p class="text-xs text-indigo-500 mt-1 font-medium">Rata-rata Nilai</p>
                            </div>
                        </div>

                        @if($mySubmissions->isNotEmpty())
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                            <th class="py-3 px-4">Tugas</th>
                                            <th class="py-3 px-4 text-center">Waktu Kumpul</th>
                                            <th class="py-3 px-4 text-center">Status</th>
                                            <th class="py-3 px-4 text-center">Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($mySubmissions as $row)
                                            <tr class="hover:bg-gray-50/80 transition">
                                                <td class="py-3 px-4 font-semibold text-gray-800">
                                                    {{ $row['assignment']->title }}
                                                </td>
                                                <td class="py-3 px-4 text-center text-xs text-gray-500">
                                                    {{ $row['submission']->submitted_at
                                                        ? \Carbon\Carbon::parse($row['submission']->submitted_at)->translatedFormat('d M Y, H:i')
                                                        : '-' }}
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    @if($row['submission']->is_late)
                                                        <span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Terlambat</span>
                                                    @else
                                                        <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Tepat Waktu</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    @if($row['grade'])
                                                        <span class="font-bold text-blue-700">{{ $row['grade']->score }}</span>
                                                        <span class="text-xs text-gray-400">/ {{ $row['assignment']->max_score ?? 100 }}</span>
                                                    @else
                                                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded-full">Belum Dinilai</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="py-6 text-center text-gray-400 bg-gray-50 rounded-xl">
                                <p class="text-sm">Anda belum mengumpulkan tugas apa pun pada mata kuliah ini.</p>
                            </div>
                        @endif
                    @endif
                </div>
            @endif

            <div class="course-detail__actions gap-3">
                {{-- Edit & Hapus: HANYA Admin --}}
                @can('update', $course)
                    <a href="{{ route('admin.courses.edit', $course) }}" class="btn-primary">
                        ✎ Edit Mata Kuliah
                    </a>
                @endcan

                {{-- Kelola Peserta: Admin dan Dosen pengampu --}}
                @can('manageEnrollments', $course)
                    <a href="{{ route(auth()->user()->role . '.courses.enrollments', $course) }}"
                       class="px-5 py-2 rounded-full bg-indigo-100 text-indigo-700 font-semibold hover:bg-indigo-200 transition">
                        👥 Kelola Peserta
                    </a>
                @endcan

                {{-- Hapus: HANYA Admin --}}
                @can('delete', $course)
                    <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-5 py-2 rounded-full bg-red-100 text-red-700 font-semibold hover:bg-red-200 transition"
                            onclick="return confirm('Yakin ingin menghapus mata kuliah ini?')">
                            🗑 Hapus
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</x-layout>
