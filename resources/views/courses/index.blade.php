<x-layout title="Daftar Mata Kuliah">

    <h1>Daftar Mata Kuliah</h1>

    <p>Jumlah course: {{ count($courses) }}</p>

    @foreach ($courses as $course)
        <a href="{{ route('courses.show', $course) }}">
            {{ $course->name }}
        </a>
    @endforeach

</x-layout>