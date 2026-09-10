<h1>Tambah User</h1>

<form action="{{ route('users.store') }}" method="POST">

    @csrf

    <input type="text" name="name" placeholder="Nama">

    <input type="email" name="email" placeholder="Email">

    <input type="password" name="password" placeholder="Password">

    <select name="role">
        <option value="admin">Admin</option>
        <option value="dosen">Dosen</option>
        <option value="mahasiswa">Mahasiswa</option>
    </select>

    <button type="submit">
        Simpan
    </button>

</form>