<x-layout title="{{ $material->title }} | KampusLMS">
    <div class="page-container max-w-4xl">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route(auth()->user()->role . '.courses.materials.index', $material->course) }}" class="text-blue-600 hover:underline text-sm font-medium">
                ← Kembali ke Daftar Materi
            </a>

            @if(auth()->user()->role === 'dosen' && $material->course->lecturer_id === auth()->id())
                <div class="space-x-2">
                    <a href="{{ route('dosen.materials.edit', $material) }}" class="btn-secondary text-xs px-4 py-2">
                        ✎ Edit Materi
                    </a>
                </div>
            @endif
        </div>

        {{-- Kartu Detail Materi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-6">
            <div class="flex items-center gap-2 mb-3">
                <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-full">
                    {{ $material->course->code }} — {{ $material->course->name }}
                </span>
                @if($material->type === 'file')
                    <span class="px-2.5 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">
                        📄 Berkas File
                    </span>
                @else
                    <span class="px-2.5 py-1 bg-cyan-100 text-cyan-700 rounded-full text-xs font-semibold">
                        🔗 Tautan Eksternal
                    </span>
                @endif
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ $material->title }}</h1>

            <div class="flex items-center gap-4 text-xs text-gray-500 pb-6 border-b border-gray-100">
                <span>Diunggah oleh: <strong class="text-gray-700">{{ $material->uploader->name ?? 'Dosen Pengampu' }}</strong></span>
                <span>•</span>
                <span>Waktu: <strong class="text-gray-700">{{ $material->created_at->translatedFormat('d M Y, H:i') }}</strong></span>
            </div>

            {{-- Deskripsi --}}
            @if($material->description)
                <div class="my-6">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Deskripsi Materi</h3>
                    <div class="p-5 bg-gray-50 rounded-xl text-gray-700 text-sm leading-relaxed whitespace-pre-line border border-gray-100">
                        {{ $material->description }}
                    </div>
                </div>
            @endif

            {{-- Konten Utama Materi (File atau Link) --}}
            <div class="mt-6 p-6 rounded-2xl border {{ $material->type === 'file' ? 'bg-purple-50/50 border-purple-100' : 'bg-cyan-50/50 border-cyan-100' }}">
                @if($material->type === 'file' && $material->file_path)
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">📄</span>
                            <div>
                                <h4 class="text-sm font-bold text-gray-800">{{ $material->original_name ?? 'Berkas Materi' }}</h4>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $material->file_size ? number_format($material->file_size / 1024, 1) . ' KB' : 'Dokumen' }}
                                </p>
                            </div>
                        </div>

                        <a href="{{ asset('storage/' . $material->file_path) }}" target="_blank" download
                            class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs transition flex items-center gap-2 shadow-sm">
                            ⬇ Unduh Berkas
                        </a>
                    </div>
                @elseif($material->type === 'link' && $material->external_url)
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">🔗</span>
                            <div>
                                <h4 class="text-sm font-bold text-gray-800">Tautan Pembelajaran</h4>
                                <p class="text-xs text-blue-600 mt-0.5 break-all">{{ $material->external_url }}</p>
                            </div>
                        </div>

                        <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer"
                            class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-semibold text-xs transition flex items-center gap-2 shadow-sm">
                            Buka Tautan ↗
                        </a>
                    </div>
                @else
                    <p class="text-gray-500 text-sm">Tidak ada berkas atau tautan yang terlampir.</p>
                @endif
            </div>
        </div>
    </div>
</x-layout>
