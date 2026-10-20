<x-layout title="Daftar Materi - {{ $course->name }} | KampusLMS">
    <div class="page-container">
        {{-- Navigasi Balik --}}
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route(auth()->user()->role . '.courses.show', $course) }}" class="text-blue-600 hover:underline text-sm font-medium">
                ← Kembali ke Detail Mata Kuliah
            </a>

            @can('create', [App\Models\Material::class, $course])
                <a href="{{ route(auth()->user()->role . '.courses.materials.create', $course) }}" class="btn-primary">
                    + Tambah Materi Baru
                </a>
            @endcan
        </div>

        {{-- Header Kartu Mata Kuliah --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-6">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600">{{ $course->code }}</span>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">{{ $course->name }}</h1>
            <p class="text-gray-500 text-sm mt-1">Koleksi modul, slide presentasi, dokumen, dan tautan referensi perkuliahan.</p>
        </div>

        {{-- Daftar Materi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @if($materials->count())
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="py-4 px-6">Materi</th>
                            <th class="py-4 px-6">Tipe</th>
                            <th class="py-4 px-6">Pengunggah</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @foreach($materials as $material)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="py-4 px-6">
                                    <div class="font-medium text-gray-900">{{ $material->title }}</div>
                                    @if($material->description)
                                        <div class="text-gray-500 text-xs mt-0.5 line-clamp-1">{{ $material->description }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if($material->type === 'file')
                                        @php
                                            $ext = strtoupper(pathinfo($material->original_name ?? '', PATHINFO_EXTENSION));
                                        @endphp
                                        <div class="flex flex-col gap-1 items-start">
                                            <span class="px-2.5 py-0.5 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold flex items-center gap-1">
                                                @if($ext === 'PDF') 📕 PDF @elseif(in_array($ext, ['PPTX', 'PPT'])) 📊 PPTX @elseif(in_array($ext, ['DOCX', 'DOC'])) 📝 DOCX @else 📄 {{ $ext ?: 'Berkas' }} @endif
                                            </span>
                                            @if($material->original_name)
                                                <span class="text-[11px] text-gray-600 font-medium line-clamp-1 max-w-[220px]" title="{{ $material->original_name }}">{{ $material->original_name }}</span>
                                            @endif
                                            @if($material->formatted_file_size)
                                                <span class="text-[11px] text-gray-400">
                                                    {{ $material->formatted_file_size }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="px-2.5 py-0.5 bg-cyan-100 text-cyan-700 rounded-full text-xs font-semibold flex items-center gap-1">
                                            🔗 Tautan Eksternal
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-gray-600 text-xs">
                                    {{ $material->uploader->name ?? 'Dosen' }}
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    @if($material->type === 'file')
                                        @php
                                            $ext = strtoupper(pathinfo($material->original_name ?? '', PATHINFO_EXTENSION));
                                        @endphp
                                        @if($ext === 'PDF')
                                            <button type="button"
                                                onclick="openPreviewModal('{{ route('materials.preview', $material) }}', '{{ addslashes($material->title) }}', '{{ addslashes($material->original_name ?? '') }}', '{{ route('materials.download', $material) }}')"
                                                class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-800 font-semibold text-xs transition">
                                                👁 Pratinjau
                                            </button>
                                        @endif
                                    @endif

                                    <a href="{{ route(auth()->user()->role . '.materials.show', $material) }}" class="text-blue-600 hover:text-blue-800 font-medium text-xs">
                                        Buka Materi
                                    </a>

                                    @if($material->type === 'file')
                                        <a href="{{ route('materials.download', $material) }}" class="text-purple-600 hover:text-purple-800 font-medium text-xs font-semibold">
                                            ⬇ Unduh
                                        </a>
                                    @endif

                                    @can('update', $material)
                                        <a href="{{ route(auth()->user()->role . '.materials.edit', $material) }}" class="text-amber-600 hover:text-amber-800 font-medium text-xs">
                                            Edit
                                        </a>

                                        <form action="{{ route(auth()->user()->role . '.materials.destroy', $material) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-xs" onclick="return confirm('Yakin ingin menghapus materi ini?')">
                                                Hapus
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-12 text-center text-gray-500">
                    <p class="text-4xl mb-2">📂</p>
                    <p class="font-medium text-gray-700">Belum ada materi pembelajaran untuk mata kuliah ini.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Pratinjau Dokumen PDF Interaktif --}}
    <div id="preview-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6 transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl h-[90vh] flex flex-col overflow-hidden border border-gray-200 animate-in fade-in zoom-in-95 duration-200">
            {{-- Header Modal --}}
            <div class="p-4 bg-gray-900 text-white flex items-center justify-between gap-3 border-b border-gray-800 shrink-0">
                <div class="flex items-center gap-3 overflow-hidden">
                    <span class="text-2xl">📕</span>
                    <div class="truncate">
                        <h3 id="modal-title" class="font-bold text-sm text-gray-100 truncate">Pratinjau Materi</h3>
                        <p id="modal-subtitle" class="text-xs text-gray-400 truncate">Dokumen PDF</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a id="modal-fullscreen-btn" href="#" target="_blank"
                        class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-semibold transition flex items-center gap-1.5 border border-gray-700">
                        <span>⛶ Tab Baru</span>
                    </a>
                    <a id="modal-download-btn" href="#"
                        class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold transition flex items-center gap-1.5">
                        <span>⬇ Unduh</span>
                    </a>
                    <button type="button" onclick="closePreviewModal()"
                        class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition"
                        title="Tutup (Esc)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body Modal (Iframe PDF Viewer) --}}
            <div class="flex-1 bg-gray-800 relative">
                <iframe id="modal-iframe" src="" class="w-full h-full border-0 bg-white" title="Pratinjau PDF"></iframe>
            </div>
        </div>
    </div>

    <script>
        function openPreviewModal(url, title, filename, downloadUrl) {
            document.getElementById('modal-title').innerText = title;
            document.getElementById('modal-subtitle').innerText = filename || 'Dokumen PDF';
            document.getElementById('modal-fullscreen-btn').href = url;
            document.getElementById('modal-download-btn').href = downloadUrl;
            document.getElementById('modal-iframe').src = url + '#toolbar=1';
            
            const modal = document.getElementById('preview-modal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePreviewModal() {
            const modal = document.getElementById('preview-modal');
            modal.classList.add('hidden');
            document.getElementById('modal-iframe').src = '';
            document.body.style.overflow = 'auto';
        }

        // Tutup modal jika tombol Escape ditekan atau klik backdrop luar
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closePreviewModal();
        });
        document.getElementById('preview-modal')?.addEventListener('click', function(e) {
            if (e.target === this) closePreviewModal();
        });
    </script>
</x-layout>
