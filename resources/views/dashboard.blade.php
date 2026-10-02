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
            @auth
                @if(auth()->user()->role === 'admin')
                    <div class="course-card flex flex-col items-center text-center">
                        <div class="text-4xl mb-4">👥</div>
                        <h3 class="text-xl font-bold text-gray-800">Manajemen Pengguna</h3>
                        <p class="text-sm text-gray-600 mt-2">Kelola data dosen, mahasiswa, dan admin sistem.</p>
                        <a href="{{ route('admin.users.index') }}" class="mt-4 btn-primary w-full text-center">Lihat Pengguna</a>
                    </div>

                    <div class="course-card flex flex-col items-center text-center">
                        <div class="text-4xl mb-4">📚</div>
                        <h3 class="text-xl font-bold text-gray-800">Semua Mata Kuliah</h3>
                        <p class="text-sm text-gray-600 mt-2">Daftar seluruh mata kuliah yang terdaftar di sistem.</p>
                        <a href="{{ route('admin.courses.index') }}" class="mt-4 btn-primary w-full text-center">Kelola Mata Kuliah</a>
                    </div>

                    <div class="course-card flex flex-col items-center text-center">
                        <div class="text-4xl mb-4">📑</div>
                        <h3 class="text-xl font-bold text-gray-800">Dokumentasi API</h3>
                        <p class="text-sm text-gray-600 mt-2">Spesifikasi REST API dan pengujian rate limiting.</p>
                        <a href="{{ url('docs/api.md') }}" target="_blank" class="mt-4 btn-secondary w-full text-center">Buka Dokumen API</a>
                    </div>

                @elseif(auth()->user()->role === 'dosen')
                    <div class="course-card flex flex-col items-center text-center">
                        <div class="text-4xl mb-4">📚</div>
                        <h3 class="text-xl font-bold text-gray-800">Mata Kuliah Diampu</h3>
                        <p class="text-sm text-gray-600 mt-2">Akses kelas, kelola materi, dan pantau aktivitas mahasiswa.</p>
                        <a href="{{ route('dosen.courses.index') }}" class="mt-4 btn-primary w-full text-center">Buka Mata Kuliah</a>
                    </div>

                    <div class="course-card flex flex-col items-center text-center">
                        <div class="text-4xl mb-4">📝</div>
                        <h3 class="text-xl font-bold text-gray-800">Tugas & Penilaian</h3>
                        <p class="text-sm text-gray-600 mt-2">Buat tugas baru, periksa submission berkas, dan berikan nilai.</p>
                        <a href="{{ route('dosen.courses.index') }}" class="mt-4 btn-primary w-full text-center">Kelola Tugas</a>
                    </div>

                    <div class="course-card flex flex-col items-center text-center">
                        <div class="text-4xl mb-4">📂</div>
                        <h3 class="text-xl font-bold text-gray-800">Materi Perkuliahan</h3>
                        <p class="text-sm text-gray-600 mt-2">Unggah modul ajar, slide materi, dan dokumen referensi.</p>
                        <a href="{{ route('dosen.courses.index') }}" class="mt-4 btn-secondary w-full text-center">Unggah & Kelola Materi</a>
                    </div>

                @elseif(auth()->user()->role === 'mahasiswa')
                    <div class="course-card flex flex-col items-center text-center">
                        <div class="text-4xl mb-4">📚</div>
                        <h3 class="text-xl font-bold text-gray-800">Mata Kuliah Saya</h3>
                        <p class="text-sm text-gray-600 mt-2">Lihat mata kuliah yang diambil, materi belajar, dan silabus.</p>
                        <a href="{{ route('mahasiswa.courses.index') }}" class="mt-4 btn-primary w-full text-center">Buka Kelas Saya</a>
                    </div>

                    <div class="course-card flex flex-col items-center text-center">
                        <div class="text-4xl mb-4">📝</div>
                        <h3 class="text-xl font-bold text-gray-800">Tugas & Pengumpulan</h3>
                        <p class="text-sm text-gray-600 mt-2">Pantau status pengerjaan tugas, deadline, dan riwayat nilai.</p>
                        <a href="{{ route('mahasiswa.submissions.index') }}" class="mt-4 btn-primary w-full text-center">Buka Tugas Saya</a>
                    </div>

                    <div class="course-card flex flex-col items-center text-center">
                        <div class="text-4xl mb-4">📂</div>
                        <h3 class="text-xl font-bold text-gray-800">Materi Perkuliahan</h3>
                        <p class="text-sm text-gray-600 mt-2">Unduh modul, slide presentasi, dan materi dari dosen.</p>
                        <a href="{{ route('mahasiswa.courses.index') }}" class="mt-4 btn-secondary w-full text-center">Akses Materi</a>
                    </div>
                @endif
            @else
                <div class="course-card flex flex-col items-center text-center">
                    <div class="text-4xl mb-4">📚</div>
                    <h3 class="text-xl font-bold text-gray-800">Sistem Perkuliahan</h3>
                    <p class="text-sm text-gray-600 mt-2">Akses materi kuliah dan aktivitas akademik secara terpadu.</p>
                    <a href="{{ route('login') }}" class="mt-4 btn-primary w-full text-center">Masuk ke Sistem</a>
                </div>

                <div class="course-card flex flex-col items-center text-center">
                    <div class="text-4xl mb-4">📝</div>
                    <h3 class="text-xl font-bold text-gray-800">Penugasan Terjadwal</h3>
                    <p class="text-sm text-gray-600 mt-2">Pengumpulan tugas tepat waktu dengan evaluasi transparan.</p>
                    <a href="{{ route('login') }}" class="mt-4 btn-secondary w-full text-center">Masuk Sekarang</a>
                </div>

                <div class="course-card flex flex-col items-center text-center">
                    <div class="text-4xl mb-4">ℹ️</div>
                    <h3 class="text-xl font-bold text-gray-800">Tentang Aplikasi</h3>
                    <p class="text-sm text-gray-600 mt-2">Informasi pengembang dan spesifikasi sistem LMS.</p>
                    <a href="{{ route('tentang') }}" class="mt-4 btn-secondary w-full text-center">Buka Halaman Tentang</a>
                </div>
            @endauth
        </div>
    </div>
</x-layout>
