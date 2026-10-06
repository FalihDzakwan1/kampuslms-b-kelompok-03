<x-layout title="Edit Pengguna | KampusLMS">
    <div class="page-container max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke Daftar Pengguna</a>
        </div>

        <section class="course-card">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-800 m-0">Edit Pengguna</h1>
                <p class="text-gray-500 text-sm mt-1">Ubah data pengguna sistem</p>
            </div>

            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="flex flex-col gap-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">NIM / NIP</label>
                    <input type="text" name="nim_nip" value="{{ old('nim_nip', $user->nim_nip) }}" placeholder="Contoh: 10241028"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    @error('nim_nip')<p class="text-red-500 text-xs mt-1" style="color: #ef4444; font-weight: 500;">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="Contoh: Budi Santoso"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    @error('name')<p class="text-red-500 text-xs mt-1" style="color: #ef4444; font-weight: 500;">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Contoh: budi@kampuslms.test"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    @error('email')<p class="text-red-500 text-xs mt-1" style="color: #ef4444; font-weight: 500;">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Peran (Role)</label>
                    <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                        <option value="">Pilih Peran</option>
                        @foreach($allowedRoles as $role)
                            <option value="{{ $role }}" {{ old('role', $user->role) == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="text-red-500 text-xs mt-1" style="color: #ef4444; font-weight: 500;">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.users.index') }}" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">Batal</a>
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium shadow-sm shadow-blue-500/30">Simpan Perubahan</button>
                </div>
            </form>
        </section>
    </div>
</x-layout>
