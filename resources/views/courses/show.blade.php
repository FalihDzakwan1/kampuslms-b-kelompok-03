{{-- resources/views/courses/show.blade.php --}}
<x-layout title="Detail Mata Kuliah">

    <div class="container">
        <h1>{{ $course['name'] }}</h1>

        <p>
            <strong>Kode Mata Kuliah:</strong>
            {{ $course['code'] }}
        </p>

        <p>
            <strong>Dosen:</strong>
            {{ $course['lecturer'] }}
        </p>

        <p>
            <strong>Deskripsi:</strong>
            {{ $course['description'] }}
        </p>

        <a href="{{ route('courses.index') }}">
            ← Kembali ke Daftar Mata Kuliah
        </a>
    </div>

</x-layout>