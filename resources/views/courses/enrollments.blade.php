<x-layout>
    <x-slot:title>Kelola Peserta — {{ $course->name }} | KampusLMS</x-slot:title>

    <div class="page-container max-w-4xl">

        <div class="mb-6">
            <a href="{{ route(auth()->user()->role . '.courses.show', $course) }}"
               class="text-blue-600 hover:underline text-sm">
                &larr; Kembali ke Detail Mata Kuliah
            </a>
        </div>

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Kelola Peserta Mata Kuliah</h1>
            <p class="text-gray-500 text-sm mt-1">{{ $course->code }} — {{ $course->name }}</p>
        </div>

        {{-- Form Tambah Mahasiswa --}}
        <section class="course-card mb-6">
            <div class="border-b border-gray-200 pb-4 mb-5">
                <h2 class="text-lg font-bold text-gray-700">Tambah Mahasiswa ke Kelas</h2>
                <p class="text-sm text-gray-500 mt-0.5">Pilih mahasiswa yang belum terdaftar untuk ditambahkan.</p>
            </div>

            @if($available->count() > 0)
                <form action="{{ route(auth()->user()->role . '.courses.enrollments.store', $course) }}" method="POST" class="flex gap-3 items-end">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Mahasiswa</label>
                        <select name="user_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                            <option value="">-- Pilih Mahasiswa --</option>
                            @foreach($available as $mhs)
                                <option value="{{ $mhs->id }}">{{ $mhs->name }} ({{ $mhs->nim_nip ?? '-' }})</option>
                            @endforeach
                        </select>
                        @error('user_id')<p class="text-red-500 text-xs mt-1" style="color:#ef4444">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium whitespace-nowrap">
                        + Tambahkan
                    </button>
                </form>
            @else
                <p class="text-sm text-gray-500 italic">Semua mahasiswa sudah terdaftar di mata kuliah ini.</p>
            @endif
        </section>

        {{-- Daftar Mahasiswa Terdaftar --}}
        <section class="course-card">
            <div class="border-b border-gray-200 pb-4 mb-5 flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-bold text-gray-700">Mahasiswa Terdaftar</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Total {{ $course->students->count() }} mahasiswa</p>
                </div>
            </div>

            @if($course->students->count() > 0)
                <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-800 text-white text-xs uppercase tracking-wide">
                            <tr>
                                <th class="py-3 px-4 text-left">#</th>
                                <th class="py-3 px-4 text-left">Nama</th>
                                <th class="py-3 px-4 text-left">NIM</th>
                                <th class="py-3 px-4 text-left">Email</th>
                                <th class="py-3 px-4 text-left">Terdaftar Sejak</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @foreach($course->students as $index => $student)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 text-gray-400">{{ $index + 1 }}</td>
                                    <td class="py-3 px-4 font-semibold">{{ $student->name }}</td>
                                    <td class="py-3 px-4 text-gray-500">{{ $student->nim_nip ?? '-' }}</td>
                                    <td class="py-3 px-4 text-gray-500">{{ $student->email }}</td>
                                    <td class="py-3 px-4 text-gray-400 text-xs">
                                        {{ $student->pivot->enrolled_at
                                            ? \Carbon\Carbon::parse($student->pivot->enrolled_at)->translatedFormat('d M Y')
                                            : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <form action="{{ route(auth()->user()->role . '.courses.enrollments.destroy', [$course, $student]) }}"
                                              method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                onclick="return confirm('Yakin ingin mengeluarkan {{ $student->name }} dari mata kuliah ini?')"
                                                class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs font-medium transition">
                                                Keluarkan
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center text-gray-400">
                    <div class="text-5xl mb-3">👥</div>
                    <p class="font-semibold">Belum ada mahasiswa yang terdaftar.</p>
                    <p class="text-sm mt-1">Tambahkan mahasiswa menggunakan form di atas.</p>
                </div>
            @endif
        </section>

    </div>
</x-layout>
