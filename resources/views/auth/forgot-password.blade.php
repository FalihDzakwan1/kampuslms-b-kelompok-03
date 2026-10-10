<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Lupa Kata Sandi | KampusLMS</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    {{-- Material Icons --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet">

    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            margin: 0;
            background: #f7f9fb;
        }

        .login-bg {
            background:
                radial-gradient(
                    circle at 10% 10%,
                    rgba(33, 98, 147, 0.35),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 90% 90%,
                    rgba(142, 198, 253, 0.15),
                    transparent 35%
                ),
                #0a2540;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .input-field {
            transition: all 0.2s ease;
        }

        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(33, 98, 147, 0.12);
        }

        .btn-action {
            transition: all 0.2s ease;
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(0, 15, 34, 0.18);
        }

        .btn-action:active {
            transform: translateY(0);
        }
    </style>
</head>

<body class="min-h-screen flex flex-col">

    {{-- =====================================================
        HEADER
    ====================================================== --}}
    <header class="w-full bg-white/90 backdrop-blur-xl border-b border-gray-100">

        <div class="max-w-7xl mx-auto px-6 lg:px-10 h-20 flex items-center justify-between">

            {{-- Logo --}}
            <a href="{{ route('login') }}" class="flex items-center gap-3">

                <div
                    class="w-10 h-10 rounded-full bg-[#0a2540] flex items-center justify-center text-white font-bold text-lg">
                    K
                </div>

                <div class="flex flex-col">
                    <span class="text-lg font-bold text-[#000f22] leading-tight">
                        KampusLMS
                    </span>

                    <span class="text-[10px] font-semibold tracking-widest uppercase text-gray-500">
                        Institut Teknologi Kalimantan
                    </span>
                </div>

            </a>

            {{-- Navigation --}}
            <nav class="flex items-center gap-2">

                <a
                    href="{{ route('login') }}"
                    class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-[#000f22] hover:bg-gray-100 transition">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali ke Login
                </a>

                <a
                    href="{{ route('tentang') }}"
                    class="hidden md:block px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-[#000f22] hover:bg-gray-100 transition">
                    Tentang Aplikasi
                </a>

            </nav>

        </div>

    </header>


    {{-- =====================================================
        MAIN
    ====================================================== --}}
    <main class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-10 py-8">

        <div
            class="w-full max-w-6xl bg-white rounded-[28px] overflow-hidden shadow-[0_20px_60px_rgba(0,0,0,0.10)] flex flex-col lg:flex-row">

            {{-- =================================================
                LEFT SIDE
            ================================================== --}}
            <section
                class="w-full lg:w-5/12 login-bg text-white p-8 sm:p-10 lg:p-12 flex flex-col justify-between relative overflow-hidden">

                {{-- Decorative circles --}}
                <div
                    class="absolute -top-24 -left-24 w-80 h-80 rounded-full bg-[#216293] opacity-30 blur-3xl">
                </div>

                <div
                    class="absolute -bottom-28 -right-20 w-96 h-96 rounded-full bg-[#8ec6fd] opacity-10 blur-3xl">
                </div>

                {{-- Branding --}}
                <div class="relative z-10">

                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-md">

                        <span class="w-2.5 h-2.5 rounded-full bg-[#8ec6fd]"></span>

                        <span class="text-[11px] font-semibold tracking-wider uppercase text-[#cee5ff]">
                            Pemulihan Akun Terpadu
                        </span>

                    </div>

                    <div class="mt-6">

                        <h2 class="text-3xl sm:text-4xl font-bold leading-tight">
                            Institut Teknologi Kalimantan
                        </h2>

                        <p class="mt-2 text-sm text-[#b0c8eb] italic">
                            “Untuk Sang Pencipta dan Bumi Etam”
                        </p>

                    </div>

                </div>

                {{-- Information Card --}}
                <div class="relative z-10 my-10">

                    <div class="glass-card rounded-3xl p-6 border border-white/5">

                        <div class="flex items-center gap-2">

                            <span class="material-symbols-outlined text-[#97cbff]">
                                lock_reset
                            </span>

                            <span class="text-sm font-semibold">
                                Panduan Reset Kata Sandi
                            </span>

                        </div>

                        <ul class="mt-4 space-y-3 text-xs sm:text-sm text-gray-300 leading-relaxed">
                            <li class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-white/10 text-cyan-300 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">1</span>
                                <span>Masukkan alamat email akun yang terdaftar pada sistem KampusLMS.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-white/10 text-cyan-300 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">2</span>
                                <span>Sistem akan memvalidasi dan membuat tautan pemulihan resmi.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-white/10 text-cyan-300 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">3</span>
                                <span>Gunakan tautan tersebut untuk membuat kata sandi baru dan login kembali.</span>
                            </li>
                        </ul>

                    </div>

                    {{-- Features --}}
                    <div class="grid grid-cols-2 gap-3 mt-5">

                        <div class="flex items-center gap-2 text-xs text-[#b0c8eb]">

                            <span class="material-symbols-outlined text-[#8ec6fd] text-[18px]">
                                verified_user
                            </span>

                            <span>
                                Tautan Aman & Terenkripsi
                            </span>

                        </div>

                        <div class="flex items-center gap-2 text-xs text-[#b0c8eb]">

                            <span class="material-symbols-outlined text-[#8ec6fd] text-[18px]">
                                timer
                            </span>

                            <span>
                                Berlaku 60 Menit
                            </span>

                        </div>

                    </div>

                </div>

                {{-- Helpdesk --}}
                <div
                    class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-gray-300">

                    <div class="flex items-center gap-2">

                        <span class="material-symbols-outlined text-[#97cbff] text-[18px]">
                            support_agent
                        </span>

                        <span>
                            Helpdesk: 0542-8530800
                        </span>

                    </div>

                    <span class="text-[#b0c8eb]">
                        Balikpapan, Kaltim
                    </span>

                </div>

            </section>


            {{-- =================================================
                RIGHT SIDE
            ================================================== --}}
            <section
                class="w-full lg:w-7/12 bg-white p-8 sm:p-10 lg:p-12 flex flex-col justify-between">

                <div class="max-w-xl mx-auto w-full">

                    {{-- Header --}}
                    <div>

                        <div class="flex items-center gap-3 mb-4">

                            <div
                                class="w-9 h-9 rounded-full bg-[#000f22] flex items-center justify-center text-white font-bold">
                                K
                            </div>

                            <span class="text-lg font-bold text-[#000f22]">
                                Portal Akademik Terpadu
                            </span>

                        </div>

                        <h1
                            class="text-3xl sm:text-4xl font-bold tracking-tight text-[#191c1e]">
                            Lupa Kata Sandi?
                        </h1>

                        <p class="mt-3 text-sm text-gray-500 leading-relaxed">
                            Masukkan email yang terdaftar pada akun KampusLMS Anda. Kami akan mengirimkan tautan untuk membuat kata sandi baru.
                        </p>

                    </div>


                    {{-- =================================================
                        FEEDBACK MESSAGES
                    ================================================== --}}
                    @if (session('status'))

                        <div
                            class="mt-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start gap-3">

                            <span class="material-symbols-outlined text-[20px] text-emerald-600 mt-0.5">
                                check_circle
                            </span>

                            <div class="flex-1">
                                <p class="font-semibold text-emerald-900">
                                    Tautan Berhasil Dikirim
                                </p>
                                <p class="mt-1 text-xs text-emerald-700 leading-relaxed">
                                    {{ session('status') }}
                                </p>
                            </div>

                        </div>

                    @endif

                    @if ($errors->any())

                        <div
                            class="mt-6 p-4 rounded-2xl bg-red-50 border border-red-100 text-red-700">

                            <div class="flex items-start gap-3">

                                <span class="material-symbols-outlined text-[20px] text-red-600 mt-0.5">
                                    error
                                </span>

                                <div>

                                    <p class="font-semibold text-sm">
                                        Permintaan tidak dapat diproses
                                    </p>

                                    <ul class="mt-1 text-xs space-y-1">

                                        @foreach ($errors->all() as $error)

                                            <li>
                                                {{ $error }}
                                            </li>

                                        @endforeach

                                    </ul>

                                </div>

                            </div>

                        </div>

                    @endif


                    {{-- =================================================
                        FORM
                    ================================================== --}}
                    <form
                        action="{{ route('password.email') }}"
                        method="POST"
                        class="mt-8 space-y-5">

                        @csrf

                        {{-- EMAIL --}}
                        <div>

                            <label
                                for="email"
                                class="block text-sm font-semibold text-[#191c1e] mb-2">
                                Alamat Email Akun
                            </label>

                            <div class="relative">

                                <span
                                    class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">
                                    mail
                                </span>

                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="{{ old('email', session('email_sent')) }}"
                                    required
                                    autofocus
                                    placeholder="Contoh: dosen@test.com atau mhs@test.com"
                                    class="input-field w-full pl-12 pr-4 py-3.5 bg-[#f2f4f6] border border-transparent rounded-full text-sm text-[#191c1e] placeholder:text-gray-400 focus:outline-none focus:bg-white focus:border-[#216293]">

                            </div>

                            @error('email')

                                <p class="mt-2 ml-3 text-xs text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                        {{-- SUBMIT BUTTON --}}
                        <button
                            type="submit"
                            class="btn-action w-full py-3.5 px-6 rounded-full bg-[#000f22] hover:bg-[#0a2540] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-md">

                            <span>
                                Kirim Tautan Reset Kata Sandi
                            </span>

                            <span class="material-symbols-outlined text-[20px]">
                                send
                            </span>

                        </button>

                        <div class="text-center pt-2">
                            <a
                                href="{{ route('login') }}"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#216293] hover:text-[#000f22] transition">
                                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                                Kembali ke Halaman Login
                            </a>
                        </div>

                    </form>

                </div>

                {{-- Bottom CTA --}}
                <div
                    class="mt-10 -mx-8 sm:-mx-10 lg:-mx-12 -mb-8 sm:-mb-10 lg:-mb-12 px-8 sm:px-10 lg:px-12 py-5 bg-[#f2f4f6] text-center">

                    <p class="text-xs sm:text-sm text-gray-500">
                        Mengalami kendala pemulihan akun?
                        <a
                            href="#"
                            class="font-semibold text-[#216293] hover:text-[#000f22]">
                            Hubungi Administrator Sistem →
                        </a>
                    </p>

                </div>

            </section>

        </div>

    </main>


    {{-- =====================================================
        FOOTER
    ====================================================== --}}
    <footer class="w-full bg-[#f2f4f6] border-t border-gray-100">

        <div
            class="max-w-7xl mx-auto px-6 lg:px-10 py-5 flex flex-col sm:flex-row items-center justify-between gap-3">

            <p class="text-xs text-gray-500 text-center sm:text-left">
                © {{ date('Y') }} KampusLMS •
                Institut Teknologi Kalimantan (ITK).
                Hak Cipta Dilindungi.
            </p>

            <div class="flex items-center gap-5 text-xs">

                <a
                    href="#"
                    class="text-gray-500 hover:text-[#000f22] transition">
                    Panduan Akademik
                </a>

                <a
                    href="#"
                    class="text-gray-500 hover:text-[#000f22] transition">
                    Kebijakan Privasi
                </a>

                <a
                    href="#"
                    class="text-gray-500 hover:text-[#000f22] transition">
                    Hubungi Helpdesk
                </a>

            </div>

        </div>

    </footer>

</body>

</html>
