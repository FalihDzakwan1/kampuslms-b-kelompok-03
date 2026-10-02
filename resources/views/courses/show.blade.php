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

            @if(auth()->user()->role === 'mahasiswa' && !$isEnrolled)
                <div class="mb-8 p-8 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-200 text-center shadow-xs">
                    <span class="text-4xl block mb-2">🎓</span>
                    <h3 class="text-xl font-bold text-amber-900 mb-1">Anda Belum Terdaftar di Mata Kuliah Ini</h3>
                    <p class="text-sm text-amber-700 max-w-lg mx-auto mb-5 leading-relaxed">
                        Silakan bergabung dengan mata kuliah ini untuk mengunduh materi kuliah, melihat rincian tugas, dan mengumpulkan berkas tugas Anda.
                    </p>
                    <form action="{{ route('mahasiswa.courses.enroll', $course) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-primary px-8 py-3 text-sm font-bold shadow-md">
                            + Gabung / Ikuti Mata Kuliah Ini Sekarang
                        </button>
                    </form>
                </div>
            @endif

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
                        @if(auth()->user()->role === 'dosen' && $course->lecturer_id === auth()->id())
                            <a href="{{ route('dosen.courses.materials.create', $course) }}" class="btn-primary text-xs px-4 py-2">
                                + Tambah Materi
                            </a>
                        @endif
                        <a href="{{ route(auth()->user()->role . '.courses.materials.index', $course) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-2 rounded-xl transition">
                            Lihat Semua ({{ $course->materials->count() }}) →
                        </a>
                    </div>
                </div>

                @if($course->materials->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($course->materials->take(6) as $material)
                            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/60 hover:bg-gray-50 hover:border-blue-200 transition flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <span class="text-2xl mt-0.5">{{ $material->type === 'file' ? '📄' : '🔗' }}</span>
                                    <div>
                                        <h4 class="font-bold text-sm text-gray-900 line-clamp-1">{{ $material->title }}</h4>
                                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $material->description ?? 'Tidak ada deskripsi.' }}</p>
                                        <span class="text-[11px] text-gray-400 mt-1 block">Oleh: {{ $material->uploader->name ?? 'Dosen' }}</span>
                                    </div>
                                </div>
                                <a href="{{ route(auth()->user()->role . '.materials.show', $material) }}" class="shrink-0 text-xs font-semibold text-blue-600 hover:underline bg-white border border-gray-200 px-3 py-1.5 rounded-lg shadow-2xs">
                                    Buka
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-gray-400 bg-gray-50 rounded-xl">
                        <p class="text-3xl mb-1">📂</p>
                        <p class="text-sm font-medium text-gray-600">Belum ada materi pembelajaran yang diunggah.</p>
                        @if(auth()->user()->role === 'dosen' && $course->lecturer_id === auth()->id())
                            <a href="{{ route('dosen.courses.materials.create', $course) }}" class="mt-2 inline-block text-xs text-blue-600 font-semibold hover:underline">
                                + Unggah materi pertama sekarang
                            </a>
                        @endif
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
                        @if(auth()->user()->role === 'dosen' && $course->lecturer_id === auth()->id())
                            <a href="{{ route('dosen.courses.assignments.create', $course) }}" class="btn-primary text-xs px-4 py-2">
                                + Buat Tugas Baru
                            </a>
                        @endif
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
                        @if(auth()->user()->role === 'dosen' && $course->lecturer_id === auth()->id())
                            <a href="{{ route('dosen.courses.assignments.create', $course) }}" class="mt-2 inline-block text-xs text-blue-600 font-semibold hover:underline">
                                + Buat tugas pertama sekarang
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="course-detail__actions gap-3">
                @if(auth()->user()->role !== 'mahasiswa')
                    <a href="{{ route(auth()->user()->role . '.courses.edit', $course) }}" class="btn-primary">
                        ✎ Edit Mata Kuliah
                    </a>

                    <form action="{{ route(auth()->user()->role . '.courses.destroy', $course) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-5 py-2 rounded-full bg-red-100 text-red-700 font-semibold hover:bg-red-200 transition"
                            onclick="return confirm('Yakin ingin menghapus mata kuliah ini?')">
                            🗑 Hapus
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-layout>
