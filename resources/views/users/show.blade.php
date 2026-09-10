<x-layout title="Detail Pengguna">

<h1>Detail Pengguna</h1>

<p>
    <strong>Nama:</strong>
    {{ $user->name }}
</p>


<p>
    <strong>Email:</strong>
    {{ $user->email }}
</p>


<p>
    <strong>Role:</strong>
    {{ $user->role }}
</p>

<a href="{{ route('users.edit', $user) }}">
    Edit
</a>

<a href="{{ route('users.index') }}">
    Kembali
</a>


</x-layout>