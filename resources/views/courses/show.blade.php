{{-- resources/views/courses/show.blade.php --}}
<x-layout title="Detail Mata Kuliah">

    <div class="container course-detail">
        <div class="course-detail__header">
            <p class="course-detail__eyebrow">Detail mata kuliah</p>
            <h1>{{ $course->nama }}</h1>
        </div>

        <div class="course-detail__info">
            <div>
                <span>Kode Mata Kuliah</span>
                <strong>{{ $course->kode }}</strong>
            </div>
            <div>
                <span>SKS</span>
                <strong>{{ $course->sks }}</strong>
            </div>
            <div>
                <span>Dosen</span>
                <strong>{{ $course->dosen }}</strong>
            </div>
        </div>

        <div class="course-detail__actions">
            <a class="btn-secondary" href="{{ route('courses.index') }}">
                <span aria-hidden="true">←</span> Kembali
            </a>
        </div>
    </div>

</x-layout>