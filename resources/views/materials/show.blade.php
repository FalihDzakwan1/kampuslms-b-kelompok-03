<x-layout title="{{ $material->title }} | KampusLMS">
    <div class="page-container max-w-4xl">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route(auth()->user()->role . '.courses.materials.index', $material->course) }}" class="text-blue-600 hover:underline text-sm font-medium">
                ← Kembali ke Daftar Materi
            </a>

            @can('update', $material)
                <div class="space-x-2">
                    <a href="{{ route(auth()->user()->role . '.materials.edit', $material) }}" class="btn-secondary text-xs px-4 py-2">
                        ✎ Edit Materi
                    </a>
                </div>
            @endcan
        </div>

        {{-- Kartu Detail Materi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-6">
            <div class="flex items-center gap-2 mb-3 flex-wrap">
                <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-full">
                    {{ $material->course->code }} — {{ $material->course->name }}
                </span>
                @if($material->type === 'file')
                    @php
                        $ext = strtoupper(pathinfo($material->original_name ?? '', PATHINFO_EXTENSION));
                    @endphp
                    <span class="px-2.5 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold flex items-center gap-1">
                        @if($ext === 'PDF')
                            📕 Dokumen PDF
                        @elseif(in_array($ext, ['PPTX', 'PPT']))
                            📊 Presentasi {{ $ext }}
                        @elseif(in_array($ext, ['DOCX', 'DOC']))
                            📝 Dokumen {{ $ext }}
                        @else
                            📄 Berkas File ({{ $ext ?: 'Dokumen' }})
                        @endif
                    </span>
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-xs font-medium flex items-center gap-1">
                        🔒 Disk Privat
                    </span>
                @else
                    <span class="px-2.5 py-1 bg-cyan-100 text-cyan-700 rounded-full text-xs font-semibold flex items-center gap-1">
                        🔗 Tautan Eksternal
                    </span>
                @endif
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ $material->title }}</h1>

            <div class="flex items-center gap-4 text-xs text-gray-500 pb-6 border-b border-gray-100 flex-wrap">
                <span>Diunggah oleh: <strong class="text-gray-700">{{ $material->uploader->name ?? 'Dosen Pengampu' }}</strong></span>
                <span>•</span>
                <span>Waktu Unggah: <strong class="text-gray-700">{{ $material->created_at->translatedFormat('d M Y, H:i') }}</strong></span>
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
                            <span class="text-4xl">
                                @php
                                    $ext = strtoupper(pathinfo($material->original_name ?? '', PATHINFO_EXTENSION));
                                @endphp
                                @if($ext === 'PDF') 📕 @elseif(in_array($ext, ['PPTX', 'PPT'])) 📊 @elseif(in_array($ext, ['DOCX', 'DOC'])) 📝 @else 📄 @endif
                            </span>
                            <div>
                                <h4 class="text-base font-bold text-gray-800">{{ $material->original_name ?? 'Berkas Materi Perkuliahan' }}</h4>
                                <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                                    <span>
                                        {{ $material->formatted_file_size ?? 'Dokumen Perkuliahan' }}
                                    </span>
                                    <span>•</span>
                                    <span class="text-purple-700 font-medium">Tersimpan di Disk Privat KampusLMS</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($ext === 'PDF')
                                <a href="{{ route('materials.preview', $material) }}" target="_blank"
                                    class="px-4 py-2.5 rounded-xl bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 font-semibold text-xs transition flex items-center gap-1.5 shadow-sm">
                                    <span>⛶ Buka di Tab Baru</span>
                                </a>
                            @endif
                            <a href="{{ route('materials.download', $material) }}"
                                class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs transition flex items-center gap-2 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>Unduh Berkas</span>
                            </a>
                        </div>
                    </div>
                @elseif($material->type === 'link' && $material->external_url)
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">🔗</span>
                            <div>
                                <h4 class="text-sm font-bold text-gray-800">Tautan Pembelajaran Eksternal</h4>
                                <p class="text-xs text-blue-600 mt-0.5 break-all">{{ $material->external_url }}</p>
                            </div>
                        </div>

                        <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer"
                            class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-semibold text-xs transition flex items-center gap-2 shadow-sm">
                            <span>Buka Tautan Eksternal</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </div>
                @else
                    <p class="text-gray-500 text-sm">Tidak ada berkas atau tautan yang terlampir.</p>
                @endif
            </div>

            {{-- Area Pratinjau / Preview Dokumen Materi --}}
            @if($material->type === 'file' && $material->file_path)
                @php
                    $ext = strtoupper(pathinfo($material->original_name ?? '', PATHINFO_EXTENSION));
                @endphp
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">👁</span>
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Pratinjau Materi</h3>
                                <p class="text-xs text-gray-500">Membaca isi berkas materi secara langsung di halaman perkuliahan.</p>
                            </div>
                        </div>

                        @if($ext === 'PDF')
                            <div class="flex items-center gap-2">
                                <a href="{{ route('materials.preview', $material) }}" target="_blank"
                                    class="px-3.5 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold flex items-center gap-1.5 transition">
                                    <span>⛶ Layar Penuh</span>
                                </a>
                                <a href="{{ route('materials.download', $material) }}"
                                    class="px-3.5 py-1.5 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 text-xs font-semibold flex items-center gap-1.5 transition">
                                    <span>⬇ Unduh</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    @if($ext === 'PDF')
                        {{-- Viewer PDF Interaktif --}}
                        <div class="rounded-2xl overflow-hidden border border-gray-200 bg-gray-900 shadow-sm">
                            <div class="p-3 bg-gray-800 text-gray-200 text-xs flex justify-between items-center px-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                    <span class="font-medium text-gray-300">{{ $material->original_name }}</span>
                                    <span class="text-gray-400">({{ $material->formatted_file_size ?? 'Dokumen PDF' }})</span>
                                </div>
                                <span class="text-[11px] text-gray-400">Disk Privat Terproteksi</span>
                            </div>
                            <iframe src="{{ route('materials.preview', $material) }}#toolbar=1"
                                class="w-full h-[750px] border-0 bg-white"
                                title="Pratinjau PDF {{ $material->title }}">
                                <div class="p-8 text-center bg-white text-gray-600 text-sm">
                                    <p class="mb-3">Peramban Anda tidak mendukung penampil PDF tertanam.</p>
                                    <a href="{{ route('materials.download', $material) }}" class="btn-primary inline-flex items-center gap-2 text-xs">
                                        ⬇ Unduh Berkas PDF
                                    </a>
                                </div>
                            </iframe>
                        </div>
                    @else
                        {{-- Card Preview Format Lain (PPTX / DOCX) --}}
                        <div class="p-8 bg-gray-50 rounded-2xl border border-gray-200 text-center">
                            <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-white shadow-sm flex items-center justify-center text-3xl border border-gray-100">
                                @if(in_array($ext, ['PPTX', 'PPT'])) 📊 @elseif(in_array($ext, ['DOCX', 'DOC'])) 📝 @else 📄 @endif
                            </div>
                            <h4 class="text-base font-bold text-gray-800 mb-1">
                                Berkas Presentasi / Dokumen: {{ $material->original_name }}
                            </h4>
                            <p class="text-xs text-gray-500 max-w-md mx-auto mb-4 leading-relaxed">
                                Berkas berformat <strong>{{ $ext }}</strong> disimpan aman di disk privat KampusLMS. Untuk membuka slide presentasi dan membaca dokumen ini secara optimal, silakan unduh berkas ke perangkat Anda.
                            </p>
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('materials.download', $material) }}" class="btn-primary inline-flex items-center gap-2 text-xs py-2.5 px-6">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    <span>Unduh {{ $ext }} ({{ $material->formatted_file_size ?? 'Berkas' }})</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-layout>
