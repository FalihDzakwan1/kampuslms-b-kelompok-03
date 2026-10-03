<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'KampusLMS' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <nav class="main-nav">

        <a href="{{ route('dashboard') }}" class="text-2xl font-bold mr-auto text-white no-underline tracking-wide hover:opacity-90 transition">
             KampusLMS
        </a>

        <a href="{{ route('dashboard') }}">
            Dashboard
        </a>

        @auth
        <a href="{{ route(auth()->user()->role . '.courses.index') }}">
            Mata Kuliah
        </a>

        @if(auth()->user()->role === 'admin')
        <a href="{{ route('admin.users.index') }}">
            Pengguna
        </a>
        @endif

        @if(auth()->user()->role === 'mahasiswa')
        <a href="{{ route('mahasiswa.submissions.index') }}">
            Tugas Saya
        </a>
        @endif

        <a href="{{ route('tentang') }}">
            Tentang
        </a>

        <div class="flex items-center gap-3 ml-auto">
            <span class="text-xs bg-blue-100 text-blue-800 font-semibold px-2.5 py-1 rounded-full uppercase">
                {{ auth()->user()->role }}
            </span>
            <span class="text-sm font-medium text-gray-700 hidden sm:inline">
                {{ auth()->user()->name }}
            </span>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-semibold px-2 py-1 rounded border border-red-200 hover:bg-red-50 transition">
                    Keluar
                </button>
            </form>
        </div>
        @else
        <a href="{{ route('tentang') }}">
            Tentang
        </a>
        <a href="{{ route('login') }}" class="btn-primary text-xs ml-auto">
            Masuk
        </a>
        @endauth

    </nav>

    <main class="p-4 md:p-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        {{ $slot }}
    </main>

</body>
</html>
