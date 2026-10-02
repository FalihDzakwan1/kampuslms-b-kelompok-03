<x-layout title="Riwayat Pengumpulan Tugas | KampusLMS">
    <div class="page-container">
        {{-- Header --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-6 flex justify-between items-center flex-wrap gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Aktivitas Akademik</span>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Riwayat Pengumpulan Tugas</h1>
                <p class="text-gray-500 text-sm mt-1">Daftar seluruh tugas yang telah Anda kumpulkan beserta status penilaiannya.</p>
            </div>
            <a href="{{ route('mahasiswa.courses.index') }}" class="btn-secondary text-xs px-4 py-2">
                ← Kembali ke Daftar Kelas
            </a>
        </div>

        {{-- Tabel Riwayat Submissions --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @if($submissions->count())
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="py-4 px-6">Mata Kuliah & Tugas</th>
                            <th class="py-4 px-6">Waktu Submit</th>
                            <th class="py-4 px-6">Status Waktu</th>
                            <th class="py-4 px-6">Nilai</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @foreach($submissions as $sub)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="py-4 px-6">
                                    <div class="font-bold text-gray-900">{{ $sub->assignment->title ?? 'Tugas' }}</div>
                                    <div class="text-xs text-blue-600 mt-0.5">
                                        {{ $sub->assignment->course->code ?? '' }} — {{ $sub->assignment->course->name ?? 'Mata Kuliah' }}
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-xs text-gray-600">
                                    {{ $sub->submitted_at ? \Carbon\Carbon::parse($sub->submitted_at)->translatedFormat('d M Y, H:i') : '-' }}
                                </td>
                                <td class="py-4 px-6">
                                    @if($sub->is_late)
                                        <span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                                            Terlambat
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                            Tepat Waktu
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if($sub->grade)
                                        <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">
                                            {{ $sub->grade->score }} / {{ $sub->assignment->max_score ?? 100 }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">
                                            Belum Dinilai
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <a href="{{ route('mahasiswa.submissions.show', $sub) }}" class="text-blue-600 hover:text-blue-800 font-medium text-xs">
                                        Detail Pengumpulan
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-12 text-center text-gray-500">
                    <p class="text-4xl mb-2">📥</p>
                    <p class="font-medium text-gray-700">Anda belum mengumpulkan tugas apa pun.</p>
                    <p class="text-xs text-gray-400 mt-1">Buka menu Mata Kuliah untuk melihat tugas yang tersedia.</p>
                </div>
            @endif
        </div>
    </div>
</x-layout>
