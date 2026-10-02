<x-layout title="Pengumpulan Tugas | KampusLMS">
    <div class="page-container max-w-2xl text-center py-12">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <span class="text-4xl block mb-3">📝</span>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Pengumpulan Tugas</h1>
            <p class="text-gray-500 text-sm mb-6 leading-relaxed">
                Untuk mengumpulkan tugas, silakan buka halaman detail mata kuliah dan pilih tugas spesifik yang ingin dikerjakan.
            </p>
            <a href="{{ route('mahasiswa.courses.index') }}" class="btn-primary">
                Buka Daftar Mata Kuliah
            </a>
        </div>
    </div>
</x-layout>
