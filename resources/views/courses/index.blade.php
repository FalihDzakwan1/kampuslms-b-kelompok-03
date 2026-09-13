<x-layout>
    <x-slot:title>Daftar Mata Kuliah</x-slot:title>

    <div class="page-container">

        {{-- Success Message --}}
        @if (session('success'))
            <div class="success-alert">
                {{ session('success') }}
            </div>
        @endif


        {{-- Hero Banner --}}
        <section class="ocean-banner">
            <div class="ocean-banner-content">

                <div class="ocean-banner-left">
                    <div class="academic-badge">
                        <span class="academic-badge-dot"></span>
                        SISTEM INFORMASI
                    </div>

                    <h1 class="ocean-title">
                        Daftar Mata Kuliah
                    </h1>

                    <p class="ocean-description">
                        Kelola dan lihat daftar mata kuliah yang tersedia
                        dalam sistem KampusLMS.
                    </p>

                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="hero-stat-icon">📚</div>
                            <div>
                                <span class="hero-stat-label">Mata Kuliah</span>
                                <span class="hero-stat-value">
                                    {{ is_countable($courses) ? count($courses) : $courses->total() }}
                                </span>
                            </div>
                        </div>

                        <div class="hero-stat">
                            <div class="hero-stat-icon">🎓</div>
                            <div>
                                <span class="hero-stat-label">Semester</span>
                                <span class="hero-stat-value">Ganjil</span>
                            </div>
                        </div>

                        <div class="hero-stat">
                            <div class="hero-stat-icon">⭐</div>
                            <div>
                                <span class="hero-stat-label">IPK</span>
                                <span class="hero-stat-value">3.82</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>


        {{-- Toolbar --}}
        <div class="floating-toolbar">

            <form action="{{ route('courses.index') }}" method="GET" class="course-search">
                <span class="course-search-icon">⌕</span>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari mata kuliah..."
                >
            </form>

            <div class="toolbar-actions">

                <select class="status-filter">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="draft">Draft</option>
                </select>

                <a href="{{ route('courses.create') }}" class="btn-primary">
                    + Tambah Mata Kuliah
                </a>

            </div>
        </div>


        {{-- Course Table --}}
        <section class="table-card">

            <div class="table-card-header">
                <div class="table-card-title">
                    <span class="table-card-title-dot"></span>
                    Daftar Mata Kuliah
                </div>

                <span class="table-card-count">
                    {{ is_countable($courses) ? count($courses) : $courses->total() }}
                    Mata Kuliah
                </span>
            </div>


            @if ($courses->count())

                <div class="course-table-wrapper">

                    <table class="course-table">

                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Mata Kuliah</th>
                                <th>SKS</th>
                                <th>Dosen Pengampu</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($courses as $course)

                                <tr>

                                    {{-- Kode --}}
                                    <td>
                                        <span class="course-code">
                                            {{ $course->code }}
                                        </span>
                                    </td>


                                    {{-- Nama --}}
                                    <td>
                                        <div class="course-name">
                                            {{ $course->name }}
                                        </div>

                                        <div class="course-subtitle">
                                            Mata Kuliah Sistem Informasi
                                        </div>
                                    </td>


                                    {{-- SKS --}}
                                    <td>
                                        <span class="sks-badge">
                                            {{ $course->sks ?? $course->credits ?? 3 }} SKS
                                        </span>
                                    </td>


                                    {{-- Dosen --}}
                                    <td>
                                        <div class="lecturer-info">

                                            <div class="lecturer-avatar">
                                                {{ strtoupper(substr($course->lecturer->name ?? 'D', 0, 1)) }}
                                            </div>

                                            <span class="lecturer-name">
                                                {{ $course->lecturer->name ?? 'Dosen Pengampu' }}
                                            </span>

                                        </div>
                                    </td>


                                    {{-- Status --}}
                                    <td>

                                        @if (($course->status ?? 'aktif') === 'aktif')

                                            <span class="status-active">
                                                <span class="status-active-dot"></span>
                                                Aktif
                                            </span>

                                        @else

                                            <span class="status-draft">
                                                <span class="status-draft-dot"></span>
                                                Draft
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Aksi --}}
                                    <td>

                                        <div class="table-actions">

                                            <a
                                                href="{{ route('courses.edit', $course->id) }}"
                                                class="action-btn action-btn-edit"
                                            >
                                                Edit
                                            </a>

                                            <form
                                                action="{{ route('courses.destroy', $course->id) }}"
                                                method="POST"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="action-btn action-btn-delete"
                                                    onclick="return confirm('Yakin ingin menghapus mata kuliah ini?')"
                                                >
                                                    Hapus
                                                </button>
                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                {{-- Empty State --}}
                <div class="empty-state">

                    <div class="empty-icon">
                        📚
                    </div>

                    <h3 class="empty-title">
                        Belum Ada Mata Kuliah
                    </h3>

                    <p class="empty-description">
                        Belum terdapat data mata kuliah yang tersedia.
                    </p>

                    <a href="{{ route('courses.create') }}" class="btn-primary">
                        + Tambah Mata Kuliah
                    </a>

                </div>

            @endif


            {{-- Pagination --}}
            @if (is_object($courses) && method_exists($courses, 'hasPages') && $courses->hasPages())

                <div class="pagination-wrapper">
                    {{ $courses->links() }}
                </div>

            @endif

        </section>


        {{-- Information Card --}}
        <div class="info-card">

            <div class="info-card-left">

                <div class="info-card-icon">
                    💡
                </div>

                <div>
                    <strong>Informasi Akademik</strong>

                    <p>
                        Pastikan data mata kuliah yang dimasukkan
                        sudah sesuai dengan informasi akademik.
                    </p>
                </div>

            </div>

            <div class="info-card-semester">
                Semester Ganjil 2026/2027
            </div>

        </div>

    </div>

</x-layout>