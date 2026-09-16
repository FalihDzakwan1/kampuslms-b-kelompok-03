<x-layout title="Detail Pengguna | KampusLMS">
    <div class="page-container max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('users.index') }}" class="text-blue-600 hover:underline text-sm">← Kembali ke Daftar Pengguna</a>
        </div>

        <section class="course-card">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <p class="text-xs font-bold text-gray-400 tracking-widest uppercase mb-1">Detail Profil</p>
                <h1 class="text-2xl font-bold text-gray-800 m-0">{{ $user->name }}</h1>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <span class="block text-xs text-gray-500 mb-1 uppercase tracking-wider">Email</span>
                    <strong class="text-gray-800">{{ $user->email }}</strong>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <span class="block text-xs text-gray-500 mb-1 uppercase tracking-wider">NIM / NIP</span>
                    <strong class="text-gray-800">{{ $user->nim_nip ?? 'Belum diatur' }}</strong>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <span class="block text-xs text-gray-500 mb-1 uppercase tracking-wider">Peran</span>
                    @if($user->role === 'admin')
                        <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-lg text-xs font-bold uppercase">Admin</span>
                    @elseif($user->role === 'dosen')
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs font-bold uppercase">Dosen</span>
                    @else
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-bold uppercase">Mahasiswa</span>
                    @endif
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <span class="block text-xs text-gray-500 mb-1 uppercase tracking-wider">Terdaftar Sejak</span>
                    <strong class="text-gray-800">{{ $user->created_at->format('d M Y') }}</strong>
                </div>
            </div>

            <div class="flex gap-3 border-t border-gray-100 pt-5">
                <a href="{{ route('users.edit', $user) }}" class="btn-primary">✎ Edit Profil</a>
            </div>
        </section>
    </div>
</x-layout>