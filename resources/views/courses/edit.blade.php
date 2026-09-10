<x-layout title="Edit Mata Kuliah">

    <h1>Edit Mata Kuliah</h1>


    <form action="{{ route('courses.update', $course) }}" method="POST">

        @csrf
        @method('PUT')


        <div>
            <label>Kode Mata Kuliah</label>

            <input 
                type="text" 
                name="code"
                value="{{ $course->code }}"
            >
        </div>


        <div>
            <label>Nama Mata Kuliah</label>

            <input 
                type="text"
                name="name"
                value="{{ $course->name }}"
            >
        </div>


        <div>
            <label>Deskripsi</label>

            <textarea name="description">{{ $course->description }}</textarea>
        </div>


        <div>
            <label>SKS</label>

            <input 
                type="number"
                name="sks"
                value="{{ $course->sks }}"
            >
        </div>


        <div>
            <label>Dosen Pengampu</label>

            <select name="lecturer_id">

                @foreach($lecturers as $lecturer)

                    <option 
                        value="{{ $lecturer->id }}"
                        {{ $course->lecturer_id == $lecturer->id ? 'selected' : '' }}
                    >
                        {{ $lecturer->name }}
                    </option>

                @endforeach

            </select>

        </div>


        <div>
            <label>Status</label>

            <select name="status">

                <option value="draft"
                    {{ $course->status == 'draft' ? 'selected' : '' }}>
                    Draft
                </option>

                <option value="active"
                    {{ $course->status == 'active' ? 'selected' : '' }}>
                    Active
                </option>

                <option value="archived"
                    {{ $course->status == 'archived' ? 'selected' : '' }}>
                    Archived
                </option>

            </select>

        </div>


        <button type="submit">
            Update
        </button>

    </form>


</x-layout>