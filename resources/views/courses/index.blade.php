<x-layout>

    <x-slot:title>
        Daftar Mata Kuliah
    </x-slot:title>


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

                            <div class="hero-stat-icon">
                                📚
                            </div>

                            <div>
                                <span class="hero-stat-label">
                                    Mata Kuliah
                                </span>

                                <span class="hero-stat-value">
                                    {{ is_countable($courses) ? count($courses) : $courses->total() }}
                                </span>
                            </div>

                        </div>


                        <div class="hero-stat">

                            <div class="hero-stat-icon">
                                🎓
                            </div>

                            <div>
                                <span class="hero-stat-label">
                                    Semester
                                </span>

                                <span class="hero-stat-value">
                                    Ganjil
                                </span>
                            </div>

                        </div>


                        <div class="hero-stat">

                            <div class="hero-stat-icon">
                                ⭐
                            </div>

                            <div>
                                <span class="hero-stat-label">
                                    IPK
                                </span>

                                <span class="hero-stat-value">
                                    3.82
                                </span>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>





        {{-- Toolbar --}}
        <form
            action="{{ auth()->user()->role === 'admin'
                ? route(auth()->user()->role . '.courses.index')
                : route(auth()->user()->role . '.courses.index') }}"
            method="GET"
            class="floating-toolbar"
        >

            <div class="course-search">

                <span class="course-search-icon"></span>

                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari mata kuliah..."
                >

            </div>



            <div class="toolbar-actions">


                <select
                    name="status"
                    class="status-filter"
                    onchange="this.form.submit()"
                >

                    <option value="">
                        Semua Status
                    </option>


                    <option value="active"
                        {{ request('status') === 'active' ? 'selected' : '' }}>
                        Aktif
                    </option>


                    <option value="draft"
                        {{ request('status') === 'draft' ? 'selected' : '' }}>
                        Draft
                    </option>


                    <option value="archived"
                        {{ request('status') === 'archived' ? 'selected' : '' }}>
                        Arsip
                    </option>


                </select>



                @if(auth()->user()->role !== 'mahasiswa')
                <a
                    href="{{ route(auth()->user()->role . '.courses.create') }}"
                    class="btn-primary"
                >
                    + Tambah Mata Kuliah
                </a>
                @endif


            </div>


        </form>

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
                                            <a href="{{ route(auth()->user()->role . '.courses.show', $course->id) }}" class="text-blue-900 hover:text-blue-600 font-bold transition">
                                                {{ $course->name }}
                                            </a>
                                        </div>

                                        <div class="flex items-center gap-2 mt-1.5 text-xs">
                                            <a href="{{ route(auth()->user()->role . '.courses.materials.index', $course->id) }}" class="inline-flex items-center gap-1 text-purple-700 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 px-2 py-0.5 rounded-md font-medium transition">
                                                📂 {{ $course->materials()->count() }} Materi
                                            </a>
                                            <a href="{{ route(auth()->user()->role . '.courses.assignments.index', $course->id) }}" class="inline-flex items-center gap-1 text-blue-700 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-2 py-0.5 rounded-md font-medium transition">
                                                📝 {{ $course->assignments()->count() }} Tugas
                                            </a>
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

                                                {{ strtoupper(substr($course->lecturer->name ?? 'D',0,1)) }}

                                            </div>


                                            <span class="lecturer-name">

                                                {{ $course->lecturer->name ?? 'Dosen Pengampu' }}

                                            </span>


                                        </div>

                                    </td>




                                    {{-- Status --}}
                                    <td>


                                        @if (($course->status ?? 'active') === 'active')


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



                                            @if(auth()->user()->role === 'admin')


                                                <a
                                                    href="{{ route(auth()->user()->role . '.courses.edit', $course->id) }}"
                                                    class="action-btn action-btn-edit"
                                                >
                                                    Edit
                                                </a>



                                                <form
                                                    action="{{ route(auth()->user()->role . '.courses.destroy', $course->id) }}"
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



                                            @elseif(auth()->user()->role === 'dosen')

                                                <a
                                                    href="{{ route('dosen.courses.show', $course->id) }}"
                                                    class="action-btn action-btn-edit bg-blue-50 text-blue-700 hover:bg-blue-100 font-semibold"
                                                >
                                                    Buka Kelas
                                                </a>

                                                <a
                                                    href="{{ route(auth()->user()->role . '.courses.edit', $course->id) }}"
                                                    class="action-btn action-btn-edit"
                                                >
                                                    Edit
                                                </a>



                                                <form
                                                    action="{{ route(auth()->user()->role . '.courses.destroy', $course->id) }}"
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



                                            @elseif(auth()->user()->role === 'mahasiswa')

                                                @php
                                                    $isEnrolled = $course->students()->where('users.id', auth()->id())->exists();
                                                @endphp

                                                @if($isEnrolled)
                                                    <a
                                                        href="{{ route('mahasiswa.courses.show', $course->id) }}"
                                                        class="action-btn action-btn-edit bg-blue-50 text-blue-700 hover:bg-blue-100 font-semibold"
                                                    >
                                                        Buka Kelas
                                                    </a>
                                                @else
                                                    <form action="{{ route('mahasiswa.courses.enroll', $course->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="action-btn action-btn-edit bg-green-50 text-green-700 hover:bg-green-100 font-semibold">
                                                            + Gabung
                                                        </button>
                                                    </form>
                                                @endif

                                            @endif



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



                    @if(auth()->user()->role !== 'mahasiswa')
                    <a
                        href="{{ route(auth()->user()->role . '.courses.create') }}"
                        class="btn-primary"
                    >

                        + Tambah Mata Kuliah

                    </a>
                    @endif


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

                    <strong>
                        Informasi Akademik
                    </strong>


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
