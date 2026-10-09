<x-layout>

    <x-slot:title>
        Daftar Mata Kuliah | KampusLMS
    </x-slot:title>

    <div class="page-container">

        @php
            $courseTotal = is_countable($courses) ? count($courses) : (method_exists($courses, 'total') ? $courses->total() : $courses->count());
        @endphp

        {{-- Flash message ditangani oleh layout global --}}

        {{-- Hero Banner --}}
        <section class="ocean-banner">
            <div class="ocean-banner-content">
                <div class="ocean-banner-left">
                    <div class="academic-badge">
                        <span class="academic-badge-dot"></span>
                        SISTEM INFORMASI AKADEMIK • 2024/2025
                    </div>

                    <h1 class="ocean-title">
                        Daftar Mata Kuliah
                    </h1>

                    <p class="ocean-description">
                        Kelola, jelajahi, dan ikuti mata kuliah aktif di lingkungan Institut Teknologi Kalimantan semester ini secara terpadu.
                    </p>

                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="hero-stat-icon">📚</div>
                            <div class="hero-stat-body">
                                <span class="hero-stat-label">TOTAL MATA KULIAH</span>
                                <span class="hero-stat-value">{{ $courseTotal }} Mata Kuliah</span>
                            </div>
                        </div>

                        <div class="hero-stat">
                            <div class="hero-stat-icon">🎓</div>
                            <div class="hero-stat-body">
                                <span class="hero-stat-label">PERIODE</span>
                                <span class="hero-stat-value">Semester Ganjil</span>
                            </div>
                        </div>

                        <div class="hero-stat">
                            <div class="hero-stat-icon">⭐</div>
                            <div class="hero-stat-body">
                                <span class="hero-stat-label">BEBAN MAKSIMAL</span>
                                <span class="hero-stat-value">24 SKS</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Floating Toolbar --}}
        <form
            action="{{ auth()->check() ? route(auth()->user()->role . '.courses.index') : route('courses.index') }}"
            method="GET"
            class="floating-toolbar"
        >
            <div class="course-search">
                <span class="course-search-icon">🔍</span>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari kode mata kuliah, nama kelas, atau dosen..."
                >
            </div>

            <div class="toolbar-actions">
                <select
                    name="status"
                    class="status-filter"
                    onchange="this.form.submit()"
                >
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Arsip</option>
                </select>

                @if(auth()->check() && auth()->user()->role === 'admin')
                    <a
                        href="{{ route('admin.courses.create') }}"
                        class="btn-primary"
                    >
                        + Tambah Mata Kuliah
                    </a>
                @endif
            </div>
        </form>

        {{-- Table Card --}}
        <section class="table-card">
            <div class="table-card-header">
                <div class="table-card-heading">
                    <div class="table-card-title">
                        <span class="table-card-title-dot"></span>
                        Mata Kuliah
                    </div>
                    <p class="table-card-subtitle">
                        Daftar mata kuliah yang tersedia di sistem KampusLMS
                    </p>
                </div>

                <span class="table-card-count">
                    {{ $courseTotal }} Mata Kuliah
                </span>
            </div>

            @if ($courses->count())
                <div class="course-table-wrapper">
                    <table class="course-table">
                        <thead>
                            <tr>
                                <th>KODE</th>
                                <th>MATA KULIAH</th>
                                <th>BOBOT</th>
                                <th>DOSEN PENGAMPU</th>
                                <th>STATUS</th>
                                <th>AKSI</th>
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

                                    {{-- Mata Kuliah --}}
                                    <td>
                                        <div class="course-name">
                                            <a href="{{ route(auth()->user()->role . '.courses.show', $course->id) }}">
                                                {{ $course->name }}
                                            </a>
                                        </div>
                                        <div class="course-badges">
                                            <a href="{{ route(auth()->user()->role . '.courses.materials.index', $course->id) }}" class="badge-materi">
                                                <span>📂</span> {{ $course->materials()->count() }} Materi
                                            </a>
                                            <a href="{{ route(auth()->user()->role . '.courses.assignments.index', $course->id) }}" class="badge-tugas">
                                                <span>✏️</span> {{ $course->assignments()->count() }} Tugas
                                            </a>
                                        </div>
                                    </td>

                                    {{-- Bobot --}}
                                    <td>
                                        <span class="sks-badge">
                                            {{ $course->sks ?? $course->credits ?? 3 }} SKS
                                        </span>
                                    </td>

                                    {{-- Dosen Pengampu --}}
                                    <td>
                                        <div class="lecturer-info">
                                            <div class="lecturer-avatar">
                                                {{ strtoupper(substr($course->lecturer->name ?? 'DO', 0, 2)) }}
                                            </div>
                                            <div class="lecturer-details">
                                                <span class="lecturer-name">
                                                    {{ $course->lecturer->name ?? 'Dosen Pengampu' }}
                                                </span>
                                                <span class="lecturer-role">
                                                    Dosen Pengampu
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        @if ($course->status === 'active')
                                            <span class="status-active">
                                                <span class="status-active-dot"></span>
                                                Aktif
                                            </span>
                                        @elseif ($course->status === 'archived')
                                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#f3f4f6;color:#6b7280;border:1px solid #e5e7eb;">
                                                <span style="width:6px;height:6px;border-radius:50%;background:#9ca3af;display:inline-block;"></span>
                                                Arsip
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
                                            @if(auth()->user()->role === 'mahasiswa')
                                                <a href="{{ route('mahasiswa.courses.show', $course->id) }}" class="btn-buka-kelas">
                                                    <span>Buka Kelas</span>
                                                    <span>&rarr;</span>
                                                </a>
                                            @elseif(auth()->user()->role === 'dosen')
                                                <a href="{{ route('dosen.courses.show', $course->id) }}" class="btn-buka-kelas">
                                                    <span>Buka Kelas</span>
                                                    <span>&rarr;</span>
                                                </a>
                                            @elseif(auth()->user()->role === 'admin')
                                                <a href="{{ route('admin.courses.show', $course->id) }}" class="btn-buka-kelas">
                                                    <span>Buka Kelas</span>
                                                    <span>&rarr;</span>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (is_object($courses) && method_exists($courses, 'hasPages') && $courses->hasPages())
                    <div class="pagination-wrapper">
                        {{ $courses->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <div class="empty-icon">📚</div>
                    <h3 class="empty-title">Belum Ada Mata Kuliah</h3>
                    <p class="empty-description">Belum terdapat data mata kuliah yang tersedia.</p>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.courses.create') }}" class="btn-primary">
                            + Tambah Mata Kuliah
                        </a>
                    @endif
                </div>
            @endif
        </section>

    </div>

</x-layout>