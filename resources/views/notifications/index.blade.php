<x-layout title="Daftar Notifikasi | KampusLMS">
    <div class="page-container">

        {{-- Hero Ocean Banner (Konsisten dengan Desain KampusLMS) --}}
        <section class="ocean-banner">
            <div class="ocean-banner-content">
                <div class="ocean-banner-left">
                    <div class="academic-badge">
                        <span class="academic-badge-dot"></span>
                        NOTIFIKASI AKADEMIK
                    </div>

                    <h1 class="ocean-title">
                        Daftar Notifikasi
                    </h1>

                    <p class="ocean-description">
                        Pantau pembaruan nilai, tugas baru yang diterbitkan, dan informasi aktivitas akademik Anda secara terpadu.
                    </p>
                </div>

                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="hero-stat-icon">🔔</div>
                        <div class="hero-stat-body">
                            <span class="hero-stat-label">TOTAL NOTIFIKASI</span>
                            <span class="hero-stat-value">{{ $totalCount }} Notifikasi</span>
                        </div>
                    </div>

                    @if($unreadCount > 0)
                        <div class="hero-stat">
                            <div class="hero-stat-icon" style="background: rgba(244, 63, 94, 0.25); color: #fda4af;">📫</div>
                            <div class="hero-stat-body">
                                <span class="hero-stat-label">BELUM DIBACA</span>
                                <span class="hero-stat-value" style="color: #fecdd3;">{{ $unreadCount }} Baru</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- Floating Toolbar (Filter Status & Aksi Massal) --}}
        <div class="floating-toolbar">
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ ($filter ?? 'all') === 'all' ? 'bg-[#0a2540] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>Semua</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] {{ ($filter ?? 'all') === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 font-semibold' }}">
                        {{ $totalCount }}
                    </span>
                </a>

                <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ ($filter ?? 'all') === 'unread' ? 'bg-[#0a2540] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>Belum Dibaca</span>
                    @if($unreadCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[11px] {{ ($filter ?? 'all') === 'unread' ? 'bg-rose-500 text-white font-bold' : 'bg-rose-100 text-rose-700 font-bold' }}">
                            {{ $unreadCount }}
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[11px] bg-slate-100 text-slate-600 font-semibold">0</span>
                    @endif
                </a>

                <a href="{{ route('notifications.index', ['filter' => 'read']) }}" 
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ ($filter ?? 'all') === 'read' ? 'bg-[#0a2540] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <span>Sudah Dibaca</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] {{ ($filter ?? 'all') === 'read' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 font-semibold' }}">
                        {{ max(0, $totalCount - $unreadCount) }}
                    </span>
                </a>
            </div>

            @if($unreadCount > 0)
                <form action="{{ route('notifications.mark-as-read', 'all') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-95 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Tandai Semua Sudah Dibaca
                    </button>
                </form>
            @endif
        </div>

        {{-- Kontainer Daftar Notifikasi (Panel Bersatu Modern) --}}
        @if($notifications->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden divide-y divide-slate-100">
                @foreach($notifications as $n)
                    @php
                        $isUnread = is_null($n->read_at);
                        $type = $n->data['type'] ?? 'general';
                        $courseName = $n->data['course_name'] ?? null;
                        $title = $n->data['title'] ?? 'Pemberitahuan';
                        $isUpdate = !empty($n->data['is_update']);
                    @endphp

                    <div class="p-4 sm:p-5 flex items-start justify-between gap-4 transition-colors {{ $isUnread ? 'bg-sky-50/30 border-l-4 border-l-sky-500' : 'hover:bg-slate-50/70' }}">
                        <div class="flex items-start gap-3.5 flex-1 min-w-0">
                            {{-- Ikon Tipe --}}
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-lg shadow-xs
                                @if($type === 'grade_given')
                                    bg-slate-50 text-slate-700 border border-slate-200
                                @elseif($type === 'assignment_published')
                                    bg-sky-50 text-sky-700 border border-sky-200
                                @else
                                    bg-slate-50 text-slate-700 border border-slate-200
                                @endif
                            ">
                                @if($type === 'grade_given')
                                    🎯
                                @elseif($type === 'assignment_published')
                                    📝
                                @else
                                    🔔
                                @endif
                            </div>

                            {{-- Isi Notifikasi --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    {{-- Badge Tipe --}}
                                    @if($type === 'grade_given')
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-white text-slate-800 border border-slate-700">
                                            {{ $isUpdate ? 'Nilai Diperbarui' : 'Nilai Keluar' }}
                                        </span>
                                    @elseif($type === 'assignment_published')
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-white text-slate-800 border border-slate-700">
                                            Tugas Baru
                                        </span>
                                    @endif

                                    {{-- Nama Mata Kuliah --}}
                                    @if($courseName)
                                        <span class="text-slate-300 text-xs">•</span>
                                        <span class="text-xs font-semibold text-slate-600">
                                            {{ $courseName }}
                                        </span>
                                    @endif

                                    {{-- Status Belum Dibaca --}}
                                    @if($isUnread)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span> Belum Dibaca
                                        </span>
                                    @endif
                                </div>

                                {{-- Judul Tugas / Link --}}
                                <a href="{{ route('notifications.open', $n->id) }}" class="group block">
                                    <h2 class="text-sm sm:text-base font-bold text-slate-900 group-hover:text-sky-600 transition truncate">
                                        {{ $title }}
                                    </h2>
                                </a>

                                {{-- Keterangan Waktu --}}
                                <div class="flex items-center gap-2 mt-1.5 text-xs text-slate-400">
                                    <span title="{{ $n->created_at->translatedFormat('d F Y, H:i') }}">
                                        🕒 {{ $n->created_at->diffForHumans() }}
                                    </span>
                                    @if(!$isUnread && $n->read_at)
                                        <span>•</span>
                                        <span class="text-emerald-600 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Dibaca {{ $n->read_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Aksi Kanan --}}
                        <div class="flex items-center gap-2 flex-shrink-0 self-center">
                            @if($isUnread)
                                <form action="{{ route('notifications.read', $n->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="hidden sm:inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition text-xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Tandai Dibaca
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('notifications.open', $n->id) }}" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0a2540] hover:bg-[#12355b] text-white rounded-lg text-xs font-semibold shadow-xs transition">
                                <span>Buka</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Paginasi --}}
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @else
            {{-- Kondisi Kosong --}}
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-200/80 shadow-sm">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-3xl mx-auto mb-3">
                    📭
                </div>
                <h3 class="text-base font-bold text-slate-800">Tidak ada notifikasi</h3>
                <p class="text-slate-500 text-xs mt-1 max-w-sm mx-auto">
                    @if(($filter ?? 'all') === 'unread')
                        Semua notifikasi telah Anda baca.
                    @elseif(($filter ?? 'all') === 'read')
                        Belum ada notifikasi yang ditandai sudah dibaca.
                    @else
                        Saat ini belum ada notifikasi baru untuk Anda.
                    @endif
                </p>

                @if(($filter ?? 'all') !== 'all')
                    <a href="{{ route('notifications.index', ['filter' => 'all']) }}" class="mt-4 inline-block btn-secondary text-xs px-4 py-2">
                        Lihat Semua Notifikasi
                    </a>
                @endif
            </div>
        @endif

    </div>
</x-layout>
