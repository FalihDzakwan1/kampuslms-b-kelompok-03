<x-layout title="Daftar Materi - {{ $course->name }} | KampusLMS">
    <div class="page-container">
        {{-- Navigasi Balik --}}
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route(auth()->user()->role . '.courses.show', $course) }}" class="text-blue-600 hover:underline text-sm font-medium">
                ← Kembali ke Detail Mata Kuliah
            </a>

            @if(auth()->user()->role === 'dosen' && $course->lecturer_id === auth()->id())
                <a href="{{ route('dosen.courses.materials.create', $course) }}" class="btn-primary">
                    + Tambah Materi Baru
                </a>
            @endif
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
                                        <span class="px-2.5 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">
                                            📄 Berkas File
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-cyan-100 text-cyan-700 rounded-full text-xs font-semibold">
                                            🔗 Tautan Link
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-gray-600 text-xs">
                                    {{ $material->uploader->name ?? 'Dosen' }}
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <a href="{{ route(auth()->user()->role . '.materials.show', $material) }}" class="text-blue-600 hover:text-blue-800 font-medium text-xs">
                                        Buka Materi
                                    </a>

                                    @if(auth()->user()->role === 'dosen' && $course->lecturer_id === auth()->id())
                                        <a href="{{ route('dosen.materials.edit', $material) }}" class="text-amber-600 hover:text-amber-800 font-medium text-xs">
                                            Edit
                                        </a>

                                        <form action="{{ route('dosen.materials.destroy', $material) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-xs" onclick="return confirm('Yakin ingin menghapus materi ini?')">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
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
</x-layout>
