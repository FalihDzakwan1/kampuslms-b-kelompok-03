<x-layout title="Daftar Pengguna">

<h1>Daftar Pengguna</h1>

<a href="{{ route('users.create') }}">
    Tambah User
</a>


<table border="1">

<tr>
    <th>Nama</th>
    <th>Email</th>
    <th>Role</th>
</tr>


@foreach($users as $user)

<tr>

<td>
    <a href="{{ route('users.show', $user) }}">
    {{ $user->name }}
    </a>
</td>

<td>
    {{ $user->email }}
</td>

<td>
    {{ $user->role }}
</td>

</tr>

@endforeach


</table>


</x-layout>