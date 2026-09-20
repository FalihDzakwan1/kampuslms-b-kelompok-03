<x-layout title="Daftar Pengguna | KampusLMS">
    <div class="page-container">
        @if (session('success'))
            <div class="success-alert">{{ session('success') }}</div>
        @endif

        <section class="ocean-banner">
            <div class="ocean-banner-content">
                <div class="ocean-banner-left">
                    <div class="academic-badge">
                        <span class="academic-badge-dot"></span>
                        MANAJEMEN PENGGUNA
                    </div>
                    <h1 class="ocean-title">Daftar Pengguna</h1>
                    <p class="ocean-description">Kelola data admin, dosen, dan mahasiswa dalam sistem KampusLMS.</p>
                </div>
            </div>
        </section>

        <div class="floating-toolbar">
            <div class="course-search">
                <span class="course-search-icon">⌕</span>
                <input type="text" placeholder="Cari pengguna...">
            </div>
            <div class="toolbar-actions">
                <a href="{{ route('users.create') }}" class="btn-primary">+ Tambah Pengguna</a>
            </div>
        </div>

        <section class="table-card">
            <div class="table-card-header p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center rounded-t-3xl">
                <div class="font-bold text-gray-700">Data Pengguna</div>
                <span class="text-sm text-gray-500">{{ count($users) }} Pengguna</span>
            </div>

            @if ($users->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-4">Nama</th>
                                <th class="px-6 py-4">NIM / NIP</th>
                                <th class="px-6 py-4">Email</th>
                                <th class="px-6 py-4">Peran</th>
                                <th class="px-6 py-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-medium text-gray-800">
                                        <a href="{{ route('users.show', $user) }}" class="hover:text-blue-600">
                                            {{ $user->name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ $user->nim_nip ?? '-' }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                                    <td class="px-6 py-4">
                                        @if($user->role === 'admin')
                                            <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-lg text-xs font-bold uppercase">Admin</span>
                                        @elseif($user->role === 'dosen')
                                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs font-bold uppercase">Dosen</span>
                                        @else
                                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-bold uppercase">Mahasiswa</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <a href="{{ route('users.edit', $user) }}" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs font-medium">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-16 text-center text-gray-500">
                    <div class="text-5xl mb-4">👥</div>
                    <p class="font-semibold">Belum ada data pengguna.</p>
                </div>
            @endif
        </section>
    </div>
</x-layout>