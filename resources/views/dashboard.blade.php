<x-layout title="Dashboard | KampusLMS">
    <div class="page-container">
        <section class="ocean-banner">
            <div class="ocean-banner-content">
                <div class="ocean-banner-left">
                    <div class="academic-badge">
                        <span class="academic-badge-dot"></span>
                        BERANDA
                    </div>
                    <h1 class="ocean-title">Selamat Datang di KampusLMS</h1>
                    <p class="ocean-description">Sistem Informasi Akademik dan Pembelajaran terpadu untuk kemudahan akses materi, tugas, dan nilai kuliah Anda.</p>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <div class="course-card flex flex-col items-center text-center">
                <div class="text-4xl mb-4">👥</div>
                <h3 class="text-xl font-bold text-gray-800">Manajemen Pengguna</h3>
                <p class="text-sm text-gray-600 mt-2">Kelola data dosen, mahasiswa, dan admin sistem.</p>
                <a href="{{ route('users.index') }}" class="mt-4 btn-primary w-full text-center">Lihat Pengguna</a>
            </div>

            <div class="course-card flex flex-col items-center text-center">
                <div class="text-4xl mb-4">📚</div>
                <h3 class="text-xl font-bold text-gray-800">Mata Kuliah</h3>
                <p class="text-sm text-gray-600 mt-2">Daftar mata kuliah, enrollment, dan materi kuliah.</p>
                <a href="{{ route('courses.index') }}" class="mt-4 btn-primary w-full text-center">Lihat Mata Kuliah</a>
            </div>

            <div class="course-card flex flex-col items-center text-center">
                <div class="text-4xl mb-4">📑</div>
                <h3 class="text-xl font-bold text-gray-800">Transkrip Nilai</h3>
                <p class="text-sm text-gray-600 mt-2">Cetak laporan dan ekspor transkrip mahasiswa.</p>
                <a href="#" class="mt-4 btn-secondary w-full text-center" onclick="alert('Fitur segera hadir!')">Buka Transkrip</a>
            </div>
        </div>
    </div>
</x-layout>