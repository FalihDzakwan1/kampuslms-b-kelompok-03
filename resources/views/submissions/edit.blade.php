<x-layout title="Perbarui Pengumpulan Tugas | KampusLMS">
    <div class="page-container max-w-2xl text-center py-12">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <span class="text-4xl block mb-3">🔄</span>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Perbarui Pengumpulan</h1>
            <p class="text-gray-500 text-sm mb-6 leading-relaxed">
                Jika Anda ingin mengunggah ulang berkas baru, silakan batalkan pengumpulan sebelumnya pada halaman detail tugas terkait.
            </p>
            <a href="{{ route('mahasiswa.submissions.index') }}" class="btn-primary">
                Lihat Riwayat Tugas
            </a>
        </div>
    </div>
</x-layout>
