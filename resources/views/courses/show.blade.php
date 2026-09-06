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

        <a class="btn-secondary" href="{{ route('courses.index') }}">
             ← Kembali
        </a>
    </div>

</x-layout>