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
            <label for="code">
                Kode Mata Kuliah
            </label>

            <input 
                type="text" 
                id="code" 
                name="code"
            >
        </div>


        <div>
            <label for="name">
                Nama Mata Kuliah
            </label>

            <input 
                type="text" 
                id="name" 
                name="name"
            >
        </div>


        <div>
            <label for="description">
                Deskripsi
            </label>

            <textarea 
                id="description" 
                name="description">
            </textarea>
        </div>


        <div>
            <label for="sks">
                SKS
            </label>

            <input 
                type="number" 
                id="sks" 
                name="sks"
            >
        </div>


        <div>
            <label for="lecturer_id">
                Dosen Pengampu
            </label>

            <select name="lecturer_id" id="lecturer_id">

                @foreach($lecturers as $lecturer)

                    <option value="{{ $lecturer->id }}">
                        {{ $lecturer->name }}
                    </option>

                @endforeach

            </select>
        </div>


        <div>
            <label for="status">
                Status
            </label>

            <select name="status" id="status">

                <option value="draft">
                    Draft
                </option>

                <option value="active">
                    Active
                </option>

                <option value="archived">
                    Archived
                </option>

            </select>
        </div>


        <button type="submit">
            Simpan
        </button>

    </form>

</x-layout>