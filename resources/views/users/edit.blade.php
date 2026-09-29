<x-layout title="Edit Pengguna | KampusLMS">
    <div class="page-container max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('users.index') }}" class="text-blue-600 hover:underline text-sm">← Kembali ke Daftar Pengguna</a>
        </div>

        <section class="course-card">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-800 m-0">Edit Pengguna</h1>
                <p class="text-gray-500 text-sm mt-1">Perbarui informasi untuk pengguna <strong>{{ $user->name }}</strong></p>
            </div>

            <form action="{{ route('users.update', $user) }}" method="POST" class="flex flex-col gap-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Peran (Role)</label>
                    <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                        <option value="admin"     {{ (old('role') ?? $user->role) == 'admin'     ? 'selected' : '' }}>Admin</option>
                        <option value="dosen"     {{ (old('role') ?? $user->role) == 'dosen'     ? 'selected' : '' }}>Dosen</option>
                        <option value="mahasiswa" {{ (old('role') ?? $user->role) == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                    </select> @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="px-5 py-2 rounded-xl bg-gray-100 text-gray-600 font-medium hover:bg-gray-200 transition">Batal</a>
                    <button type="submit" class="btn-primary border-none cursor-pointer">Perbarui Pengguna</button>
                </div>
            </form>
        </section>
    </div>
</x-layout>