<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | KampusLMS</title>

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

        .login-button {
            transition: all 0.2s ease;
        }

        .login-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(0, 15, 34, 0.18);
        }

        .login-button:active {
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
            <div class="flex items-center gap-3">

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

            </div>

            {{-- Navigation --}}
            <nav class="flex items-center gap-2">

                <a
                    href="{{ url('/') }}"
                    class="hidden sm:block px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-[#000f22] hover:bg-gray-100 transition">
                    Kembali ke Beranda
                </a>

                <a
                    href="#"
                    class="hidden md:block px-4 py-2 rounded-full text-sm font-semibold text-gray-600 hover:text-[#000f22] hover:bg-gray-100 transition">
                    Pusat Bantuan
                </a>

                <div
                    class="w-9 h-9 rounded-full bg-[#000f22] flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-[18px]">
                        person
                    </span>
                </div>

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
                            SSO Akademik Terpadu
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


                {{-- Academic Information --}}
                <div class="relative z-10 my-10">

                    <div class="glass-card rounded-3xl p-6 border border-white/5">

                        <div class="flex items-center justify-between gap-3">

                            <div class="flex items-center gap-2">

                                <span class="material-symbols-outlined text-[#97cbff]">
                                    event_note
                                </span>

                                <span class="text-sm font-semibold">
                                    Kalender Perkuliahan
                                </span>

                            </div>

                            <span
                                class="px-3 py-1 rounded-full bg-[#cee5ff]/15 text-[#cee5ff] text-[10px] font-semibold whitespace-nowrap">
                                TA 2026/2027
                            </span>

                        </div>


                        <p class="mt-5 text-xs sm:text-sm text-gray-300 leading-relaxed">
                            Informasi akademik, perkuliahan, dan aktivitas pembelajaran
                            mahasiswa dapat diakses melalui sistem KampusLMS.
                        </p>


                        {{-- Progress --}}
                        <div class="mt-6">

                            <div class="flex items-center justify-between text-[11px] text-[#b0c8eb] mb-2">

                                <span>
                                    Sistem Akademik Terintegrasi
                                </span>

                                <span>
                                    Aktif
                                </span>

                            </div>

                            <div class="w-full h-2 rounded-full bg-white/10 overflow-hidden">

                                <div
                                    class="h-full w-full rounded-full bg-[#8ec6fd]">
                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Features --}}
                    <div class="grid grid-cols-2 gap-3 mt-5">

                        <div class="flex items-center gap-2 text-xs text-[#b0c8eb]">

                            <span class="material-symbols-outlined text-[#8ec6fd] text-[18px]">
                                verified_user
                            </span>

                            <span>
                                Sistem Aman
                            </span>

                        </div>


                        <div class="flex items-center gap-2 text-xs text-[#b0c8eb]">

                            <span class="material-symbols-outlined text-[#8ec6fd] text-[18px]">
                                cloud_sync
                            </span>

                            <span>
                                Data Terintegrasi
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
                            Masuk ke Akun Anda
                        </h1>


                        <p class="mt-3 text-sm text-gray-500 leading-relaxed">
                            Gunakan akun KampusLMS Anda untuk mengakses
                            sistem pembelajaran Institut Teknologi Kalimantan.
                        </p>

                    </div>


                    {{-- =================================================
                        ERROR / SUCCESS MESSAGE
                    ================================================== --}}
                    @if (session('success'))

                        <div
                            class="mt-6 p-4 rounded-2xl bg-green-50 border border-green-100 text-green-700 text-sm flex items-start gap-3">

                            <span class="material-symbols-outlined text-[20px]">
                                check_circle
                            </span>

                            <span>
                                {{ session('success') }}
                            </span>

                        </div>

                    @endif


                    @if ($errors->any())

                        <div
                            class="mt-6 p-4 rounded-2xl bg-red-50 border border-red-100 text-red-700">

                            <div class="flex items-start gap-3">

                                <span class="material-symbols-outlined text-[20px]">
                                    error
                                </span>

                                <div>

                                    <p class="font-semibold text-sm">
                                        Login gagal
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
                        LOGIN FORM
                    ================================================== --}}
                    <form
                        action="{{ url('/login') }}"
                        method="POST"
                        class="mt-8 space-y-5">

                        @csrf


                        {{-- EMAIL --}}
                        <div>

                            <label
                                for="email"
                                class="block text-sm font-semibold text-[#191c1e] mb-2">
                                Email
                            </label>

                            <div class="relative">

                                <span
                                    class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">
                                    badge
                                </span>

                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="{{ old('email') }}"
                                    autocomplete="username"
                                    required
                                    autofocus
                                    placeholder="Masukkan email Anda"
                                    class="input-field w-full pl-12 pr-4 py-3.5 bg-[#f2f4f6] border border-transparent rounded-full text-sm text-[#191c1e] placeholder:text-gray-400 focus:outline-none focus:bg-white focus:border-[#216293]">

                            </div>

                            @error('email')

                                <p class="mt-2 ml-3 text-xs text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- PASSWORD --}}
                        <div>

                            <div class="flex items-center justify-between mb-2">

                                <label
                                    for="password"
                                    class="block text-sm font-semibold text-[#191c1e]">
                                    Kata Sandi
                                </label>

                                <a
                                    href="#"
                                    class="text-xs font-semibold text-[#216293] hover:text-[#000f22]">
                                    Lupa Kata Sandi?
                                </a>

                            </div>


                            <div class="relative">

                                <span
                                    class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">
                                    lock
                                </span>


                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    autocomplete="current-password"
                                    required
                                    placeholder="Masukkan kata sandi Anda"
                                    class="input-field w-full pl-12 pr-12 py-3.5 bg-[#f2f4f6] border border-transparent rounded-full text-sm text-[#191c1e] placeholder:text-gray-400 focus:outline-none focus:bg-white focus:border-[#216293]">


                                {{-- Show Password --}}
                                <button
                                    type="button"
                                    id="togglePassword"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#000f22] transition">

                                    <span
                                        class="material-symbols-outlined text-[20px]"
                                        id="passwordIcon">
                                        visibility_off
                                    </span>

                                </button>

                            </div>


                            @error('password')

                                <p class="mt-2 ml-3 text-xs text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- REMEMBER --}}
                        <div class="flex items-center justify-between pt-1">

                            <label
                                for="remember"
                                class="flex items-center gap-2 cursor-pointer">

                                <input
                                    type="checkbox"
                                    name="remember"
                                    id="remember"
                                    value="1"
                                    class="w-4 h-4 rounded accent-[#000f22]"
                                    {{ old('remember') ? 'checked' : '' }}>

                                <span class="text-sm text-gray-500">
                                    Ingat saya di perangkat ini
                                </span>

                            </label>


                            <span class="text-[10px] text-gray-400">
                                Aman • SSL/TLS
                            </span>

                        </div>


                        {{-- BUTTON --}}
                        <button
                            type="submit"
                            class="login-button w-full py-3.5 px-6 rounded-full bg-[#000f22] hover:bg-[#0a2540] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-md">

                            <span>
                                Masuk Sekarang
                            </span>

                            <span class="material-symbols-outlined text-[20px]">
                                arrow_forward
                            </span>

                        </button>

                    </form>

                </div>


                {{-- Bottom CTA --}}
                <div
                    class="mt-10 -mx-8 sm:-mx-10 lg:-mx-12 -mb-8 sm:-mb-10 lg:-mb-12 px-8 sm:px-10 lg:px-12 py-5 bg-[#f2f4f6] text-center">

                    <p class="text-xs sm:text-sm text-gray-500">

                        Belum memiliki akun?

                        <a
                            href="#"
                            class="font-semibold text-[#216293] hover:text-[#000f22]">
                            Hubungi Administrator →
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


    {{-- =====================================================
        SHOW / HIDE PASSWORD
    ====================================================== --}}
    <script>

        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const passwordIcon = document.getElementById('passwordIcon');

        togglePassword.addEventListener('click', function () {

            const isPassword =
                passwordInput.getAttribute('type') === 'password';

            passwordInput.setAttribute(
                'type',
                isPassword ? 'text' : 'password'
            );

            passwordIcon.textContent =
                isPassword ? 'visibility' : 'visibility_off';

        });

    </script>

</body>

</html>
