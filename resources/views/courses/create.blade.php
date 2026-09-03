{{-- resources/views/courses/create.blade.php --}}
<x-layout title="Tambah Mata Kuliah">

    <h1>Tambah Mata Kuliah</h1>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('courses.store') }}" method="POST">
        @csrf

        <div>
            <label for="kode">Kode Mata Kuliah</label>
            <input type="text" id="kode" name="kode">
        </div>

        <div>
            <label for="nama">Nama Mata Kuliah</label>
            <input type="text" id="nama" name="nama">
        </div>

        <div>
            <label for="sks">SKS</label>
            <input type="number" id="sks" name="sks">
        </div>

        <div>
            <label for="dosen">Dosen Pengampu</label>
            <input type="text" id="dosen" name="dosen">
        </div>

        <button type="submit">Simpan</button>
    </form>

</x-layout>