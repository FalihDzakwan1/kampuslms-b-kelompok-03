{{-- resources/views/courses/show.blade.php --}}
<x-layout title="Detail Mata Kuliah">

    <div class="container course-detail">

        <div class="course-detail__header">

            <p class="course-detail__eyebrow">
                Detail mata kuliah
            </p>

            <h1>
                {{ $course->name }}
            </h1>

        </div>


        <div class="course-detail__info">

            <div>
                <span>Kode Mata Kuliah</span>

                <strong>
                    {{ $course->code }}
                </strong>
            </div>


            <div>
                <span>SKS</span>

                <strong>
                    {{ $course->sks }}
                </strong>
            </div>


            <div>
                <span>Dosen</span>

                <strong>
                    {{ $course->lecturer->name ?? '-' }}
                </strong>
            </div>


            <div>
                <span>Status</span>

                <strong>
                    {{ $course->status }}
                </strong>
            </div>


            <div>
                <span>Deskripsi</span>

                <strong>
                    {{ $course->description ?? '-' }}
                </strong>
            </div>

        </div>


        <div class="course-detail__actions">

            <a class="btn-secondary" 
                href="{{ route('courses.edit', $course) }}">
                    Edit
            </a>

            <a class="btn-secondary" href="{{ route('courses.index') }}">
                ← Kembali
            </a>

            <form action="{{ route('courses.destroy', $course) }}" method="POST">

                @csrf
                @method('DELETE')

                <button type="submit"
                    onclick="return confirm('Yakin ingin menghapus mata kuliah ini?')">
                    Hapus
                </button>

            </form>

    </div>

</x-layout>
