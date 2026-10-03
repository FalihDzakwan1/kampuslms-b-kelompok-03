<x-layout title="Masuk | KampusLMS">
    <div class="auth-container">
        <div class="auth-card">
            
            <!-- Panel Kiri: Informasi Singkat -->
            <div class="auth-showcase">
                <div>
                    <div class="academic-badge">
                        <span class="academic-badge-dot"></span>
                        PORTAL KULIAH
                    </div>

                    <div class="auth-showcase-content">
                        <h2 class="auth-showcase-title">PORTAL KAMPUS</h2>
                        <p class="auth-showcase-desc">
                            Semua materi kuliah, pengumpulan tugas, dan nilai perkuliahan kamu bisa diakses langsung di satu tempat.
                        </p>

                        <div class="auth-feature-list">
                            <div class="auth-feature-item">
                                <div class="auth-feature-icon">📚</div>
                                <div class="auth-feature-info">
                                    <h4>Materi Kuliah</h4>
                                    <p>Download slide dan modul ajar dari dosen kapan saja.</p>
                                </div>
                            </div>

                            <div class="auth-feature-item">
                                <div class="auth-feature-icon">📝</div>
                                <div class="auth-feature-info">
                                    <h4>Tugas Kuliah</h4>
                                    <p>Kirim tugas secara online dan cek nilai yang diberikan dosen.</p>
                                </div>
                            </div>

                            <div class="auth-feature-item">
                                <div class="auth-feature-icon">👥</div>
                                <div class="auth-feature-info">
                                    <h4>Akses Akun</h4>
                                    <p>Masuk sesuai peran kamu sebagai Mahasiswa, Dosen, atau Admin.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="auth-showcase-footer">
                    <span>Dibuat oleh: <strong>Kelompok 03</strong></span>
                    <span>T.A. 2025/2026</span>
                </div>
            </div>

            <!-- Panel Kanan: Form Login -->
            <div class="auth-form-panel">
                <div>
                    <div class="auth-form-header">
                        <h1 class="auth-form-title">Masuk ke Akun 👋</h1>
                        <p class="auth-form-subtitle">Masukkan email dan password kamu untuk melanjutkan.</p>
                    </div>

                    <!-- Notifikasi Error jika login gagal -->
                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-5 text-sm flex items-start gap-2.5" role="alert">
                            <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <span class="font-semibold">Login gagal:</span> {{ $errors->first() }}
                            </div>
                        </div>
                    @endif

                    <!-- Form Login -->
                    <form method="POST" action="{{ route('login') }}" id="loginForm" onsubmit="handleLoginSubmit(event)">
                        @csrf

                        <!-- Email -->
                        <div class="auth-group">
                            <label for="email" class="auth-label">Email</label>
                            <div class="auth-input-wrapper">
                                <svg class="auth-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    class="auth-input @error('email') is-invalid @enderror" 
                                    placeholder="nama@email.com" 
                                    value="{{ old('email') }}" 
                                    required 
                                    autocomplete="email"
                                    autofocus
                                >
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="auth-group">
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="auth-label m-0">Password</label>
                                <span class="text-xs text-blue-600 hover:underline cursor-pointer" onclick="alert('Silakan hubungi dosen atau admin untuk bantuan reset password ya.')">
                                    Lupa password?
                                </span>
                            </div>
                            <div class="auth-input-wrapper">
                                <svg class="auth-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="auth-input" 
                                    placeholder="••••••••" 
                                    required 
                                    autocomplete="current-password"
                                >
                                <button 
                                    type="button" 
                                    class="auth-toggle-pwd" 
                                    onclick="togglePassword()" 
                                    title="Lihat / Sembunyikan Password"
                                    aria-label="Lihat password"
                                >
                                    <svg id="eyeShow" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eyeHide" class="w-4 h-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Ingat Saya -->
                        <div class="flex items-center justify-between text-xs text-gray-600 mt-3 mb-4">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                                <span>Ingat saya</span>
                            </label>
                        </div>

                        <!-- Tombol Masuk -->
                        <button type="submit" class="auth-submit-btn" id="submitBtn">
                            <span id="btnText">Masuk</span>
                            <svg id="btnArrow" class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </form>
                </div>

                <div class="mt-6 text-center text-xs text-gray-400">
                    &copy; {{ date('Y') }} KampusLMS &bull; Kelompok 03
                </div>
            </div>

        </div>
    </div>

    <!-- Script Show/Hide Password & Submit State -->
    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const eyeShow = document.getElementById('eyeShow');
            const eyeHide = document.getElementById('eyeHide');

            if (pwd.type === 'password') {
                pwd.type = 'text';
                eyeShow.classList.add('hidden');
                eyeHide.classList.remove('hidden');
            } else {
                pwd.type = 'password';
                eyeShow.classList.remove('hidden');
                eyeHide.classList.add('hidden');
            }
        }

        function handleLoginSubmit(e) {
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            btnText.innerText = 'Memproses...';
            btn.classList.add('loading');
        }
    </script>
</x-layout>