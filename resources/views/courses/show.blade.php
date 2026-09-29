<x-layout title="Detail Mata Kuliah | KampusLMS">
    <div class="page-container">
        <div class="mb-6">
            <a href="{{ route('courses.index') }}" class="text-blue-600 hover:underline text-sm">← Kembali ke Daftar Mata Kuliah</a>
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

            <div class="course-detail__actions gap-3">
                <a href="{{ route('courses.edit', $course) }}" class="btn-primary">
                    ✎ Edit Mata Kuliah
                </a>

                <form action="{{ route('courses.destroy', $course) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-5 py-2 rounded-full bg-red-100 text-red-700 font-semibold hover:bg-red-200 transition"
                        onclick="return confirm('Yakin ingin menghapus mata kuliah ini?')">
                        🗑 Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layout>
