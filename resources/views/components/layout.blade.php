<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'KampusLMS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <nav class="main-nav">

        <div class="text-2xl font-bold mr-auto">
             KampusLMS
        </div>


        <a href="{{ route('dashboard') }}">
            Dashboard
        </a>


        <a href="{{ route('courses.index') }}">
            Mata Kuliah
        </a>


        <a href="{{ route('tentang') }}">
            Tentang
     </a>

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