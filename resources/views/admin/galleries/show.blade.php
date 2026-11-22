<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.galleries.index') }}" class="text-zinc-400 hover:text-zinc-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center space-x-2.5">
                        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">
                            {{ $gallery->client_name }}
                        </h1>
                        @if($gallery->status === 'draft')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-700">Draft</span>
                        @elseif($gallery->status === 'open')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">Open</span>
                        @elseif($gallery->status === 'submitted')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200">Submitted</span>
                        @elseif($gallery->status === 'completed')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-900 text-white">Completed</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500 mt-1">
                        <span class="font-mono">/g/{{ $gallery->slug }}</span>
                        @if($gallery->pin)
                            <span>PIN: <strong class="font-mono text-zinc-700">{{ $gallery->pin }}</strong></span>
                        @endif
                        @if($gallery->submitted_at)
                            <span>Dikunci: {{ $gallery->submitted_at->format('d M Y, H:i') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Sync GDrive -->
                <form method="POST" action="{{ route('admin.galleries.sync', $gallery) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-3.5 py-2 bg-white border border-zinc-200 hover:bg-zinc-50 text-zinc-800 text-xs font-medium rounded-xl shadow-xs transition">
                        <svg class="w-3.5 h-3.5 mr-1.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Sync Google Drive
                    </button>
                </form>

                <!-- Copy Client Link -->
                <button type="button"
                        x-data="{ copied: false }"
                        @click="navigator.clipboard.writeText('{{ $gallery->client_url }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="inline-flex items-center px-3.5 py-2 bg-white border border-zinc-200 hover:bg-zinc-50 text-zinc-800 text-xs font-medium rounded-xl shadow-xs transition">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span x-text="copied ? 'Link Tersalin!' : 'Salin Link Klien'">Salin Link Klien</span>
                </button>

                <!-- Open in New Tab -->
                <a href="{{ $gallery->client_url }}"
                   target="_blank"
                   class="inline-flex items-center px-3.5 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-xs font-medium rounded-xl transition">
                    Lihat Galeri
                    <svg class="w-3.5 h-3.5 ml-1 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>

                <!-- Edit Settings -->
                <a href="{{ route('admin.galleries.edit', $gallery) }}"
                   class="inline-flex items-center px-3 py-2 bg-white border border-zinc-200 hover:bg-zinc-50 text-zinc-700 text-xs font-medium rounded-xl transition">
                    Edit
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ activeTab: 'all', filterQuery: '{{ $lightroomQuery }}' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Notifications -->
            @if(session('success'))
                <div class="p-4 bg-zinc-900 text-white text-sm rounded-xl flex items-center space-x-2.5 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
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

            <!-- Summary KPI Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-xs">
                    <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Total Foto Drive</div>
                    <div class="text-2xl font-bold text-zinc-900 mt-1.5">{{ $totalPhotos }}</div>
                    <div class="text-[11px] text-zinc-400 mt-1">Folder ID: {{ Str::limit($gallery->gdrive_folder_id, 12) }}</div>
                </div>

                <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-xs">
                    <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Pilihan Klien</div>
                    <div class="flex items-baseline space-x-1.5 mt-1.5">
                        <span class="text-2xl font-bold text-zinc-900">{{ $selectedCount }}</span>
                        <span class="text-sm text-zinc-400">/ {{ $gallery->max_select }} Foto</span>
                    </div>
                    <div class="w-full bg-zinc-100 rounded-full h-1.5 mt-2 overflow-hidden">
                        @php
                            $pct = $gallery->max_select > 0 ? min(100, round(($selectedCount / $gallery->max_select) * 100)) : 0;
                        @endphp
                        <div class="bg-zinc-900 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>

                <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-xs">
                    <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Catatan Retouch</div>
                    <div class="text-2xl font-bold text-zinc-900 mt-1.5">{{ $photosWithNotes->count() }}</div>
                    <div class="text-[11px] text-zinc-400 mt-1">Dari foto yang dipilih</div>
                </div>

                <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Status Proofing</div>
                        <div class="text-base font-semibold text-zinc-900 capitalize mt-1.5">{{ $gallery->status }}</div>
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        @if($gallery->isSubmitted())
                            <form method="POST" action="{{ route('admin.galleries.reopen', $gallery) }}" onsubmit="return confirm('PENTING: Apakah Anda yakin ingin MEMBUKA KEMBALI kunci galeri ini? Klien akan dapat mengubah dan mengganti pilihan foto lagi.');">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium underline">
                                    Buka Kunci (Reopen)
                                </button>
                            </form>
                            <span class="text-zinc-300">•</span>
                            <form method="POST" action="{{ route('admin.galleries.complete', $gallery) }}">
                                @csrf
                                <button type="submit" class="text-xs text-zinc-900 hover:text-black font-semibold underline">
                                    Selesai
                                </button>
                            </form>
                        @elseif($gallery->isCompleted())
                            <form method="POST" action="{{ route('admin.galleries.reopen', $gallery) }}" onsubmit="return confirm('PENTING: Apakah Anda yakin ingin membuka kembali galeri yang sudah selesai ini?');">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium underline">
                                    Buka Kembali
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ADOBE LIGHTROOM EXPORT CARD (Core Feature) -->
            <div class="bg-zinc-950 text-white rounded-2xl p-6 sm:p-7 shadow-md"
                 x-data="{ 
                     queryMode: 'with_ext',
                     queryWithExt: '{{ addslashes($lightroomQuery) }}',
                     queryNoExt: '{{ addslashes($lightroomQueryFilenamesOnly) }}',
                     copiedWithExt: false,
                     copiedNoExt: false
                 }">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-zinc-800 text-zinc-300 tracking-wider uppercase">
                                Lightroom Quick Filter
                            </span>
                            @if($selectedCount > 0)
                                <span class="text-xs text-emerald-400 font-medium">● {{ $selectedCount }} Foto Terpilih Siap Di-filter</span>
                            @else
                                <span class="text-xs text-zinc-400">Belum ada foto yang dipilih klien</span>
                            @endif
                        </div>
                        <h2 class="text-lg font-bold tracking-tight mt-1.5">
                            Ekspor Nama File ke Adobe Lightroom
                        </h2>
                        <p class="text-xs text-zinc-400 max-w-2xl mt-1 leading-relaxed">
                            Mendukung file <strong>JPG, DNG, dan RAW</strong>. Cukup salin teks query di bawah, buka Lightroom > Filter Bar (<kbd class="px-1.5 py-0.5 bg-zinc-800 rounded font-mono text-[10px]">\</kbd>) > Text > pilih <em>Filenames Contain</em> > Paste.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Copy With Extension -->
                        <button type="button"
                                @click="navigator.clipboard.writeText(queryWithExt); copiedWithExt = true; setTimeout(() => copiedWithExt = false, 2500)"
                                :disabled="{{ $selectedCount === 0 ? 'true' : 'false' }}"
                                class="inline-flex items-center px-4 py-2.5 bg-white hover:bg-zinc-100 text-zinc-950 disabled:bg-zinc-800 disabled:text-zinc-500 text-xs font-bold uppercase tracking-wider rounded-xl shadow transition duration-150 shrink-0">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                            <span x-text="copiedWithExt ? '✓ Tersalin!' : 'Copy Query Lengkap'">Copy Query Lengkap</span>
                        </button>

                        <!-- Copy Filenames Only (No Extension) -->
                        <button type="button"
                                @click="navigator.clipboard.writeText(queryNoExt); copiedNoExt = true; setTimeout(() => copiedNoExt = false, 2500)"
                                :disabled="{{ $selectedCount === 0 ? 'true' : 'false' }}"
                                title="Cocok untuk mencari file RAW (DNG/CR3/ARW) yang namanya sama"
                                class="inline-flex items-center px-4 py-2.5 bg-zinc-800 hover:bg-zinc-700 text-white disabled:bg-zinc-900 disabled:text-zinc-600 text-xs font-semibold rounded-xl border border-zinc-700 transition duration-150 shrink-0">
                            <span x-text="copiedNoExt ? '✓ Tersalin!' : 'Copy Nama Saja (Tanpa Ekstensi)'">Copy Nama Saja (Tanpa Ekstensi)</span>
                        </button>
                    </div>
                </div>

                @if($selectedCount > 0)
                    <!-- Formatted Query Preview Box -->
                    <div class="mt-5 space-y-2">
                        <div class="flex items-center space-x-2 text-[11px] text-zinc-400">
                            <button type="button" @click="queryMode = 'with_ext'" :class="queryMode === 'with_ext' ? 'text-white underline font-semibold' : 'text-zinc-500 hover:text-zinc-300'">Dengan Ekstensi (.dng/.jpg)</button>
                            <span>•</span>
                            <button type="button" @click="queryMode = 'no_ext'" :class="queryMode === 'no_ext' ? 'text-white underline font-semibold' : 'text-zinc-500 hover:text-zinc-300'">Tanpa Ekstensi</button>
                        </div>
                        <div class="p-3.5 bg-zinc-900 border border-zinc-800 rounded-xl font-mono text-xs text-zinc-300 break-all select-all">
                            <span x-show="queryMode === 'with_ext'">{{ $lightroomQuery }}</span>
                            <span x-show="queryMode === 'no_ext'">{{ $lightroomQueryFilenamesOnly }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Client Retouching Notes Accordion -->
            @if($photosWithNotes->count() > 0)
                <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs" x-data="{ openNotes: true }">
                    <div class="flex items-center justify-between cursor-pointer" @click="openNotes = !openNotes">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-base font-semibold text-zinc-900">Catatan Retouching dari Klien</h3>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 text-zinc-700">
                                {{ $photosWithNotes->count() }} Foto
                            </span>
                        </div>
                        <button type="button" class="text-zinc-400 hover:text-zinc-600">
                            <svg class="w-5 h-5 transform transition-transform" :class="{ 'rotate-180': openNotes }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>

                    <div x-show="openNotes" x-transition class="mt-5 divide-y divide-zinc-100">
                        @foreach($photosWithNotes as $item)
                            <div class="py-3 flex items-start space-x-3.5">
                                <img src="{{ $item->thumbnail_url }}"
                                     alt="{{ $item->filename }}"
                                     referrerpolicy="no-referrer"
                                     class="w-14 h-14 object-cover rounded-lg border border-zinc-200 shrink-0">
                                <div class="flex-1">
                                    <div class="text-xs font-semibold text-zinc-900 font-mono">{{ $item->filename }}</div>
                                    <p class="text-xs text-zinc-600 mt-1 bg-zinc-50 p-2.5 rounded-lg border border-zinc-100 leading-relaxed">
                                        "{{ $item->client_note }}"
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Photos Gallery Inspection Grid -->
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs space-y-6">
                <!-- Tabs -->
                <div class="flex items-center justify-between border-b border-zinc-100 pb-4">
                    <div class="flex space-x-2">
                        <button type="button"
                                @click="activeTab = 'all'"
                                :class="activeTab === 'all' ? 'bg-zinc-950 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'"
                                class="px-4 py-2 rounded-xl text-xs font-semibold transition">
                            Semua Foto ({{ $totalPhotos }})
                        </button>
                        <button type="button"
                                @click="activeTab = 'selected'"
                                :class="activeTab === 'selected' ? 'bg-zinc-950 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'"
                                class="px-4 py-2 rounded-xl text-xs font-semibold transition">
                            Foto Terpilih ({{ $selectedCount }})
                        </button>
                    </div>

                    <div class="text-xs text-zinc-400">
                        Klik gambar untuk pratinjau resolusi tinggi
                    </div>
                </div>

                @if($totalPhotos > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3.5">
                        @foreach($gallery->photos as $photo)
                            <div x-show="activeTab === 'all' || (activeTab === 'selected' && {{ $photo->is_selected ? 'true' : 'false' }})"
                                 class="relative group rounded-xl overflow-hidden border {{ $photo->is_selected ? 'border-zinc-900 ring-2 ring-zinc-900' : 'border-zinc-200' }} bg-zinc-50 transition">

                                <!-- Photo Thumbnail -->
                                <a href="{{ $photo->preview_url }}"
                                   class="glightbox block aspect-square overflow-hidden bg-zinc-100"
                                   data-gallery="admin-gallery"
                                   data-type="image"
                                   data-title="{{ $photo->filename }}">
                                    <img src="{{ $photo->thumbnail_url }}"
                                         alt="{{ $photo->filename }}"
                                         loading="lazy"
                                         referrerpolicy="no-referrer"
                                         onerror="if(!this.dataset.retry){this.dataset.retry=1;this.src='{{ route('photo.stream', ['photo' => $photo->id, 'type' => 'thumb']) }}';}"
                                         class="w-full h-full object-cover transition group-hover:scale-105 duration-300">
                                </a>

                                <!-- Selection Badge -->
                                @if($photo->is_selected)
                                    <div class="absolute top-2 right-2 bg-zinc-950 text-white p-1 rounded-full shadow">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                @endif

                                <!-- Note Indicator Badge -->
                                @if(!empty($photo->client_note))
                                    <div class="absolute top-2 left-2 bg-amber-500 text-white p-1 rounded-full shadow" title="{{ $photo->client_note }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                        </svg>
                                    </div>
                                @endif

                                <!-- Filename Footer -->
                                <div class="p-2 bg-white border-t border-zinc-100 flex items-center justify-between">
                                    <span class="text-[11px] font-mono text-zinc-700 truncate mr-1.5" title="{{ $photo->filename }}">
                                        {{ $photo->filename }}
                                    </span>
                                    @if($photo->isDng())
                                        <span class="px-1.5 py-0.5 bg-zinc-950 text-white rounded text-[8px] font-bold uppercase shrink-0">DNG</span>
                                    @elseif($photo->isRaw())
                                        <span class="px-1.5 py-0.5 bg-zinc-700 text-white rounded text-[8px] font-bold uppercase shrink-0">{{ strtoupper($photo->extension) }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 text-zinc-400">
                        <svg class="w-8 h-8 mx-auto mb-2 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm">Belum ada foto yang disinkronkan dari Google Drive.</p>
                        <form method="POST" action="{{ route('admin.galleries.sync', $gallery) }}" class="mt-3">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-zinc-950 text-white text-xs font-semibold rounded-lg shadow-sm">
                                Tarik Foto Sekarang
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Delete Danger Zone -->
            <div class="border border-red-200 bg-red-50/50 rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h4 class="text-sm font-semibold text-red-900">Hapus Sesi Galeri Ini</h4>
                    <p class="text-xs text-red-700 mt-0.5">
                        Menghapus galeri akan menghapus seluruh data pilihan dan catatan klien terkait secara permanen. File di Google Drive tidak akan terhapus.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.galleries.destroy', $gallery) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus galeri {{ $gallery->client_name }} secara permanen?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition shrink-0">
                        Hapus Galeri
                    </button>
                </form>
            </div>

        </div>
    </div>

    <!-- Initialize GLightbox -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.GLightbox) {
                window.GLightbox({
                    selector: '.glightbox',
                    touchNavigation: true,
                    loop: true,
                    zoomable: true
                });
            }
        });
    </script>
</x-app-layout>
