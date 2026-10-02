<x-layout title="Daftar Tugas - {{ $course->name }} | KampusLMS">
    <div class="page-container">
        {{-- Navigasi Balik --}}
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route(auth()->user()->role . '.courses.show', $course) }}" class="text-blue-600 hover:underline text-sm font-medium">
                ← Kembali ke Detail Mata Kuliah
            </a>

            @if(auth()->user()->role === 'dosen' && $course->lecturer_id === auth()->id())
                <a href="{{ route('dosen.courses.assignments.create', $course) }}" class="btn-primary">
                    + Buat Tugas Baru
                </a>
            @endif
        </div>

        {{-- Header Kartu Mata Kuliah --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-6">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600">{{ $course->code }}</span>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">{{ $course->name }}</h1>
            <p class="text-gray-500 text-sm mt-1">Daftar tugas kuliah dan aktivitas pembelajaran terstruktur.</p>
        </div>

        {{-- Daftar Tugas --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @if($assignments->count())
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="py-4 px-6">Judul Tugas</th>
                            <th class="py-4 px-6">Batas Waktu</th>
                            <th class="py-4 px-6">Status</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @foreach($assignments as $assignment)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="py-4 px-6 font-medium text-gray-900">
                                    {{ $assignment->title }}
                                </td>
                                <td class="py-4 px-6 text-gray-600">
                                    {{ $assignment->due_at ? \Carbon\Carbon::parse($assignment->due_at)->translatedFormat('d M Y, H:i') : '-' }}
                                </td>
                                <td class="py-4 px-6">
                                    @if($assignment->status === 'published')
                                        <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Tersedia</span>
                                    @else
                                        <span class="px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">Draft</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <a href="{{ route(auth()->user()->role . '.assignments.show', $assignment) }}" class="text-blue-600 hover:text-blue-800 font-medium text-xs">
                                        Lihat Detail
                                    </a>

                                    @if(auth()->user()->role === 'dosen' && $course->lecturer_id === auth()->id())
                                        <a href="{{ route('dosen.assignments.edit', $assignment) }}" class="text-amber-600 hover:text-amber-800 font-medium text-xs">
                                            Edit
                                        </a>

                                        <form action="{{ route('dosen.assignments.destroy', $assignment) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-xs" onclick="return confirm('Hapus tugas ini?')">
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
                    <p class="text-4xl mb-2">📝</p>
                    <p class="font-medium text-gray-700">Belum ada tugas untuk mata kuliah ini.</p>
                </div>
            @endif
        </div>
    </div>
</x-layout>
