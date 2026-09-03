{{-- resources/views/courses/index.blade.php --}}
<x-layout title="Daftar Mata Kuliah">

    <h1>Daftar Mata Kuliah</h1>

    <p>Jumlah course: {{ count($courses) }}</p>

    @if (count($courses) === 0)
        <p>Belum ada data mata kuliah.</p>
    @else
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>SKS</th>
                    <th>Dosen</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($courses as $course)
                    <tr>
                        <td>{{ $course->kode }}</td>
                        <td>{{ $course->nama }}</td>
                        <td>{{ $course->sks }}</td>
                        <td>{{ $course->dosen }}</td>
                        <td>
                            <a href="{{ route('courses.show', $course) }}">
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</x-layout>