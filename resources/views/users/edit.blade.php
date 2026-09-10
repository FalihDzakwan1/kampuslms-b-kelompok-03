<x-layout title="Edit Pengguna">

<h1>Edit Pengguna</h1>


<form action="{{ route('users.update', $user) }}" method="POST">

    @csrf
    @method('PUT')


    <div>
        <label>Nama</label>

        <input 
            type="text"
            name="name"
            value="{{ $user->name }}"
        >
    </div>


    <div>
        <label>Email</label>

        <input 
            type="email"
            name="email"
            value="{{ $user->email }}"
        >
    </div>


    <div>
        <label>Role</label>

        <select name="role">

            <option value="admin"
                {{ $user->role == 'admin' ? 'selected' : '' }}>
                Admin
            </option>


            <option value="dosen"
                {{ $user->role == 'dosen' ? 'selected' : '' }}>
                Dosen
            </option>


            <option value="mahasiswa"
                {{ $user->role == 'mahasiswa' ? 'selected' : '' }}>
                Mahasiswa
            </option>

        </select>

    </div>


    <button type="submit">
        Update
    </button>


</form>


</x-layout>