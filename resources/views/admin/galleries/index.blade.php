<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">
                        Client Photo Proofing
                    </h1>
                    <!-- Real-time Live Status Pulse -->
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse mr-1.5"></span>
                        Live Auto-Update
                    </span>
                </div>
                <p class="text-sm text-zinc-500 mt-0.5">
                    Kelola sesi galeri foto, sinkronisasi Google Drive, dan ekspor nama file ke Adobe Lightroom secara real-time.
                </p>
            </div>
            <div>
                <a href="{{ route('admin.galleries.create') }}"
                   class="inline-flex items-center px-4 py-2.5 bg-zinc-950 hover:bg-zinc-800 text-white text-xs font-semibold uppercase tracking-widest rounded-lg shadow-sm transition duration-150">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Galeri Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Notifications -->
            @if(session('success'))
                <div class="p-4 bg-zinc-900 text-white text-sm rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2.5">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div class="p-4 bg-amber-50 border border-amber-200 text-amber-900 text-sm rounded-xl flex items-center space-x-2.5">
                    <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-900 text-sm rounded-xl flex items-center space-x-2.5">
                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Google Drive Account Connection Card -->
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
                @if($isGoogleConnected)
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center space-x-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-sm font-bold text-zinc-900">Akun Google Drive Terhubung</h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                </div>
                                <p class="text-xs text-zinc-600 font-medium mt-0.5">
                                    {{ $googleConnectedEmail }}
                                </p>
                                <p class="text-[11px] text-zinc-400 mt-0.5">
                                    Seluruh sesi galeri dapat langsung melakukan sinkronisasi foto dari akun ini.
                                </p>
                            </div>
                        </div>

                        <div>
                            <form method="POST" action="{{ route('admin.google.disconnect') }}" onsubmit="return confirm('Apakah Anda ingin memutuskan koneksi akun Google Drive ini?');">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold rounded-xl transition">
                                    Putuskan / Ganti Akun Google
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center space-x-3.5">
                                <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-zinc-900">Hubungkan Akun Google Drive Fotografer</h3>
                                    <p class="text-xs text-zinc-500 mt-0.5">
                                        Login dengan akun Google yang memiliki folder photoshoot agar galeri dapat langsung menarik foto.
                                    </p>
                                </div>
                            </div>

                            @if(!empty(config('services.google.client_id')) && !empty(config('services.google.client_secret')))
                                <div>
                                    <a href="{{ route('admin.google.connect') }}"
                                       class="inline-flex items-center px-5 py-2.5 bg-zinc-950 hover:bg-zinc-800 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                                        Login dengan Akun Google Sekarang
                                    </a>
                                </div>
                            @endif
                        </div>

                        @if(empty(config('services.google.client_id')) || empty(config('services.google.client_secret')))
                            <!-- Setup Credentials Form -->
                            <div class="mt-4 p-5 bg-zinc-50 rounded-2xl border border-zinc-200/80">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-zinc-900 text-white text-[11px] font-bold">!</span>
                                    <h4 class="text-xs font-bold text-zinc-900 uppercase tracking-wider">Konfigurasi Singkat: Masukkan Kunci Google OAuth</h4>
                                </div>
                                <p class="text-xs text-zinc-600 leading-relaxed">
                                    Untuk mengaktifkan tombol login Google, masukkan <strong>Client ID</strong> dan <strong>Client Secret</strong> Anda dari Google Cloud Console:
                                </p>

                                <form method="POST" action="{{ route('admin.google.save_credentials') }}" class="mt-4 space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-[11px] font-bold text-zinc-700 uppercase tracking-wider mb-1">
                                            Google Client ID
                                        </label>
                                        <input type="text"
                                               name="client_id"
                                               value="{{ old('client_id', config('services.google.client_id')) }}"
                                               placeholder="Contoh: 1234567890-abcdefg.apps.googleusercontent.com"
                                               required
                                               class="w-full px-3.5 py-2 bg-white border border-zinc-200 rounded-xl text-xs font-mono text-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-zinc-700 uppercase tracking-wider mb-1">
                                            Google Client Secret
                                        </label>
                                        <input type="password"
                                               name="client_secret"
                                               placeholder="Contoh: GOCSPX-xxxxxxxxxxxxxxxx"
                                               required
                                               class="w-full px-3.5 py-2 bg-white border border-zinc-200 rounded-xl text-xs font-mono text-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                                    </div>

                                    <div class="pt-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div class="text-[11px] text-zinc-400">
                                            Redirect URI Anda: <code class="bg-zinc-200 px-1 py-0.5 rounded text-zinc-800">http://localhost:8000/admin/google/callback</code>
                                        </div>
                                        <button type="submit"
                                                class="px-5 py-2.5 bg-zinc-950 hover:bg-zinc-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm transition">
                                            Simpan & Langsung Hubungkan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 shadow-xs">
                <form method="GET" action="{{ route('admin.galleries.index') }}" class="flex flex-col md:flex-row gap-3">
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari nama klien atau slug..."
                               class="w-full pl-10 pr-4 py-2 bg-zinc-50/70 border border-zinc-200 rounded-xl text-sm text-zinc-900 placeholder-zinc-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition">
                    </div>

                    <div class="w-full md:w-48">
                        <select name="status"
                                onchange="this.form.submit()"
                                class="w-full py-2 px-3 bg-zinc-50/70 border border-zinc-200 rounded-xl text-sm text-zinc-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open (Klien Memilih)</option>
                            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted (Terkunci)</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-900 text-sm font-medium rounded-xl transition">
                            Terapkan
                        </button>
                        @if(request()->hasAny(['search', 'status']))
                            <a href="{{ route('admin.galleries.index') }}"
                               class="px-3 py-2 text-zinc-500 hover:text-zinc-800 text-sm transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Galleries Table with Live Polling -->
            <div class="bg-white border border-zinc-200/80 rounded-2xl shadow-xs overflow-hidden"
                 x-data="galleryLiveMonitor({
                     endpoint: '{{ route('admin.galleries.live_stats') }}',
                     galleries: {{ json_encode($galleries->mapWithKeys(fn($g) => [$g->id => ['status' => $g->status, 'selected_count' => $g->selected_photos_count, 'max_select' => $g->max_select, 'client_name' => $g->client_name]])) }}
                 })">

                <!-- Toast Notification for Live Client Submissions -->
                <div x-show="toastMessage"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="p-3 bg-emerald-600 text-white text-xs font-semibold flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="toastMessage"></span>
                    </div>
                    <button type="button" @click="toastMessage = ''" class="text-emerald-200 hover:text-white">✕</button>
                </div>

                @if($galleries->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-zinc-100 bg-zinc-50/50 text-[11px] font-semibold text-zinc-400 uppercase tracking-wider">
                                    <th class="py-3.5 px-6">Klien & Sesi Galeri</th>
                                    <th class="py-3.5 px-6">Folder GDrive</th>
                                    <th class="py-3.5 px-6">Pilihan / Kuota</th>
                                    <th class="py-3.5 px-6">Status</th>
                                    <th class="py-3.5 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 text-sm">
                                @foreach($galleries as $gallery)
                                    <tr class="hover:bg-zinc-50/60 transition group"
                                        :class="changedMap[{{ $gallery->id }}] ? 'bg-amber-50/80' : ''">
                                        <!-- Client Info -->
                                        <td class="py-4 px-6">
                                            <div class="font-medium text-zinc-900 group-hover:text-black">
                                                <a href="{{ route('admin.galleries.show', $gallery) }}" class="hover:underline">
                                                    {{ $gallery->client_name }}
                                                </a>
                                            </div>
                                            <div class="flex items-center space-x-2 text-xs text-zinc-400 mt-1">
                                                <span class="font-mono text-zinc-500">/g/{{ $gallery->slug }}</span>
                                                @if($gallery->pin)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-600 font-mono text-[10px]">
                                                        PIN: {{ $gallery->pin }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Folder GDrive -->
                                        <td class="py-4 px-6">
                                            <div class="text-xs font-mono text-zinc-600 truncate max-w-[180px]" title="{{ $gallery->gdrive_folder_id }}">
                                                {{ $gallery->gdrive_folder_id }}
                                            </div>
                                            <div class="text-[11px] text-zinc-400 mt-0.5">
                                                {{ $gallery->photos_count }} foto tersinkron
                                            </div>
                                        </td>

                                        <!-- Quota Progress (Live) -->
                                        <td class="py-4 px-6">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-semibold text-zinc-900"
                                                      x-text="galleries[{{ $gallery->id }}]?.selected_count ?? {{ $gallery->selected_photos_count }}">
                                                    {{ $gallery->selected_photos_count }}
                                                </span>
                                                <span class="text-zinc-400">/</span>
                                                <span class="text-zinc-600">{{ $gallery->max_select }}</span>
                                            </div>
                                            <div class="w-28 bg-zinc-100 rounded-full h-1.5 mt-1.5 overflow-hidden">
                                                <div class="bg-zinc-900 h-1.5 rounded-full transition-all duration-500"
                                                     :style="'width: ' + getPct({{ $gallery->id }}) + '%'"></div>
                                            </div>
                                        </td>

                                        <!-- Status Badge (Live) -->
                                        <td class="py-4 px-6">
                                            <template x-if="getStatus({{ $gallery->id }}) === 'draft'">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-zinc-100 text-zinc-700">
                                                    Draft
                                                </span>
                                            </template>
                                            <template x-if="getStatus({{ $gallery->id }}) === 'open'">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200/60">
                                                    Open
                                                </span>
                                            </template>
                                            <template x-if="getStatus({{ $gallery->id }}) === 'submitted'">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200/80 font-bold animate-pulse">
                                                    Submitted
                                                </span>
                                            </template>
                                            <template x-if="getStatus({{ $gallery->id }}) === 'completed'">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-zinc-900 text-white">
                                                    Completed
                                                </span>
                                            </template>
                                        </td>

                                        <!-- Actions -->
                                        <td class="py-4 px-6 text-right space-x-2 whitespace-nowrap">
                                            <a href="{{ route('admin.galleries.show', $gallery) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium rounded-lg transition">
                                                Kelola
                                            </a>
                                            <a href="{{ route('client.gallery.show', $gallery->slug) }}"
                                               target="_blank"
                                               class="inline-flex items-center px-2.5 py-1.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-medium rounded-lg transition"
                                               title="Buka tampilan klien">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($galleries->hasPages())
                        <div class="p-4 border-t border-zinc-100 bg-zinc-50/30">
                            {{ $galleries->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-16 px-4">
                        <div class="w-12 h-12 rounded-2xl bg-zinc-100 text-zinc-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-medium text-zinc-900">Belum ada sesi galeri</h3>
                        <p class="text-sm text-zinc-500 max-w-sm mx-auto mt-1 mb-6">
                            Mulai dengan membuat sesi galeri baru dan hubungkan dengan Google Drive.
                        </p>
                        <a href="{{ route('admin.galleries.create') }}"
                           class="inline-flex items-center px-4 py-2.5 bg-zinc-950 text-white text-xs font-semibold uppercase tracking-wider rounded-lg shadow-sm hover:bg-zinc-800 transition">
                            Tambah Galeri Pertama
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Live Auto-Update Script (Every 5s) -->
    <script>
        function galleryLiveMonitor(config) {
            return {
                endpoint: config.endpoint,
                galleries: config.galleries || {},
                changedMap: {},
                toastMessage: '',
                pollTimer: null,

                init() {
                    this.pollTimer = setInterval(() => {
                        this.fetchLiveStats();
                    }, 5000);
                },

                getStatus(id) {
                    return this.galleries[id]?.status || 'draft';
                },

                getPct(id) {
                    const g = this.galleries[id];
                    if (!g || !g.max_select) return 0;
                    return Math.min(100, Math.round(((g.selected_count || 0) / g.max_select) * 100));
                },

                async fetchLiveStats() {
                    try {
                        const res = await fetch(this.endpoint, {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) return;
                        const data = await res.json();

                        data.forEach(item => {
                            const existing = this.galleries[item.id];
                            if (existing) {
                                // If status just transitioned to submitted, notify with highlight toast
                                if (existing.status !== 'submitted' && item.status === 'submitted') {
                                    this.toastMessage = `Klien "${item.client_name}" baru saja mengunci ${item.selected_count} foto pilihan!`;
                                    this.changedMap[item.id] = true;
                                    setTimeout(() => { this.changedMap[item.id] = false; }, 8000);
                                }
                                existing.status = item.status;
                                existing.selected_count = item.selected_count;
                                existing.max_select = item.max_select;
                            }
                        });
                    } catch (e) {
                        // Silent background retry
                    }
                }
            };
        }
    </script>
</x-app-layout>
