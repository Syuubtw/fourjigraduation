<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="referrer" content="no-referrer">
    <title>{{ $gallery->client_name }} — Photo Proofing Fourji Graduation</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAFAFA] text-[#09090b] font-sans antialiased selection:bg-[#09090b] selection:text-white"
      x-data="photoProofing({
          slug: '{{ $gallery->slug }}',
          maxSelect: {{ $gallery->max_select }},
          status: '{{ $gallery->status }}',
          initialSelected: {{ json_encode($gallery->photos->where('is_selected', true)->pluck('id')->values()) }},
          initialNotes: {{ json_encode($gallery->photos->whereNotNull('client_note')->where('client_note', '!=', '')->pluck('client_note', 'id')) }}
      })">

    <!-- TOP STICKY BRAND HEADER -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-[#E4E4E7] transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <div class="flex items-center space-x-3">
                <a href="{{ route('client.gallery.show', ['slug' => $gallery->slug]) }}" class="flex flex-col">
                    <img src="{{ asset('images/logo-dark.png') }}" alt="Fourji Picture | 4everlove" class="h-5 sm:h-6 w-auto object-contain">
                    <span class="text-[9px] text-zinc-400 font-semibold tracking-wider uppercase mt-1">
                        Graduation Client Proofing
                    </span>
                </a>
            </div>

            <!-- Client & Live Counter Indicator (Desktop) -->
            <div class="hidden md:flex items-center space-x-6">
                <div class="text-right">
                    <div class="text-xs font-semibold text-zinc-900 truncate max-w-[200px]">
                        {{ $gallery->client_name }}
                    </div>
                    <div class="text-[11px] text-zinc-400">
                        Batas Kuota: {{ $gallery->max_select }} Foto Edit
                    </div>
                </div>

                <!-- Live Counter Pill -->
                <div class="flex items-center px-4 py-2 bg-zinc-100 rounded-full text-xs font-semibold"
                     :class="{
                         'bg-zinc-950 text-white': selectedCount === maxSelect,
                         'bg-zinc-100 text-zinc-900': selectedCount < maxSelect
                     }">
                    <span class="font-bold text-sm" x-text="selectedCount"></span>
                    <span class="mx-1 text-zinc-400">/</span>
                    <span x-text="maxSelect"></span>
                    <span class="ml-2 font-normal text-[11px] opacity-80">Terpilih</span>
                </div>

                <!-- Submit Button -->
                @if($gallery->isOpen())
                    <button type="button"
                            @click="openSubmitModal = true"
                            :disabled="selectedCount === 0"
                            class="px-5 py-2.5 bg-[#09090b] hover:bg-zinc-800 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-semibold uppercase tracking-wider rounded-xl transition shadow-xs">
                        Kunci Pilihan Foto
                    </button>
                @else
                    <span class="px-4 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider bg-zinc-200 text-zinc-600">
                        {{ $gallery->status === 'submitted' ? 'Sudah Dikunci' : ucfirst($gallery->status) }}
                    </span>
                @endif
            </div>

            <!-- Mobile Live Counter & Status -->
            <div class="md:hidden flex items-center space-x-2">
                <div class="px-3 py-1.5 rounded-full text-xs font-semibold bg-zinc-100 text-zinc-900">
                    <span x-text="selectedCount"></span>/<span x-text="maxSelect"></span>
                </div>
                @if(!$gallery->isOpen())
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-semibold uppercase bg-zinc-200 text-zinc-600">
                        {{ $gallery->status === 'submitted' ? 'Terkunci' : $gallery->status }}
                    </span>
                @endif
            </div>
        </div>
    </header>

    <!-- HERO & FILTER BAR -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 border-b border-[#E4E4E7] pb-6">
            <div>
                <span class="text-[11px] font-bold tracking-widest uppercase text-zinc-400">
                    Sesi Foto Wisuda
                </span>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#09090b] mt-1">
                    {{ $gallery->client_name }}
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 mt-1 max-w-xl">
                    Pilih foto terbaik Anda hingga kuota <strong class="text-zinc-800">{{ $gallery->max_select }} foto</strong>. Anda juga dapat menambahkan catatan khusus (revisi/retouching) pada tiap foto.
                </p>
            </div>

            <!-- Filter Tabs -->
            <div class="flex items-center space-x-2 bg-white p-1.5 border border-[#E4E4E7] rounded-2xl shadow-xs self-start md:self-auto">
                <button type="button"
                        @click="activeFilter = 'all'"
                        :class="activeFilter === 'all' ? 'bg-[#09090b] text-white' : 'text-zinc-600 hover:text-black'"
                        class="px-4 py-2 rounded-xl text-xs font-semibold transition">
                    Semua Foto ({{ $totalPhotos }})
                </button>
                <button type="button"
                        @click="activeFilter = 'selected'"
                        :class="activeFilter === 'selected' ? 'bg-[#09090b] text-white' : 'text-zinc-600 hover:text-black'"
                        class="px-4 py-2 rounded-xl text-xs font-semibold transition">
                    Pilihan Saya (<span x-text="selectedCount"></span>)
                </button>
            </div>
        </div>

        <!-- Quota Status Banner -->
        <div x-show="selectedCount >= maxSelect" x-transition
             class="mt-4 p-3 bg-zinc-900 text-white rounded-xl text-xs flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Kuota foto Anda telah tercapai (<strong x-text="maxSelect"></strong> foto). Silakan tinjau kembali atau langsung kunci pilihan.</span>
            </div>
        </div>
    </section>

    <!-- PHOTO GALLERY GRID -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-28 md:pb-16">
        @if($totalPhotos > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-6">
                @foreach($gallery->photos as $photo)
                    <div x-show="activeFilter === 'all' || (activeFilter === 'selected' && isSelected({{ $photo->id }}))"
                         x-transition
                         class="group relative bg-white rounded-2xl overflow-hidden border transition-all duration-200 select-none shadow-xs"
                         :class="isSelected({{ $photo->id }})
                            ? 'border-[#09090b] ring-2 ring-[#09090b]'
                            : 'border-[#E4E4E7] hover:border-zinc-300'">

                        <!-- Image Container with Watermark Overlay -->
                        <div class="relative aspect-[3/4] bg-zinc-100 overflow-hidden">
                            <!-- High-Res Lightbox Click Area -->
                            <a href="{{ $photo->preview_url }}"
                               class="glightbox block w-full h-full"
                               data-gallery="client-gallery"
                               data-type="image"
                               data-title="{{ $photo->filename }}"
                               data-description="{{ $gallery->client_name }} — Fourji Graduation">
                                <img src="{{ $photo->thumbnail_url }}"
                                     alt="{{ $photo->filename }}"
                                     loading="lazy"
                                     referrerpolicy="no-referrer"
                                     onerror="if(!this.dataset.retry){this.dataset.retry=1;this.src='{{ route('photo.stream', ['photo' => $photo->id, 'type' => 'thumb']) }}';}"
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 no-drag">
                            </a>

                            <!-- Subtle Elegant Watermark Overlay -->
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-35 group-hover:opacity-55 transition px-4">
                                <img src="{{ asset('images/logo-white.png') }}" alt="Fourji" class="w-3/4 max-w-[220px] h-auto object-contain filter drop-shadow-md select-none no-drag">
                            </div>

                            <!-- Expand / Zoom Button Icon (top-left) -->
                            <a href="{{ $photo->preview_url }}"
                               class="glightbox absolute top-2.5 left-2.5 w-8 h-8 rounded-full bg-black/40 hover:bg-black/70 backdrop-blur-md text-white flex items-center justify-center transition opacity-0 group-hover:opacity-100"
                               data-gallery="client-gallery-btn"
                               data-type="image"
                               data-title="{{ $photo->filename }}"
                               title="Lihat Pratinjau Besar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                                </svg>
                            </a>

                            <!-- Selection Toggle Button (top-right) -->
                            @if($gallery->isOpen())
                                <button type="button"
                                        @click="togglePhoto({{ $photo->id }})"
                                        :disabled="loadingMap[{{ $photo->id }}]"
                                        class="absolute top-2.5 right-2.5 w-9 h-9 rounded-full flex items-center justify-center transition-all duration-150 shadow-md"
                                        :class="isSelected({{ $photo->id }})
                                            ? 'bg-[#09090b] text-white scale-105'
                                            : 'bg-white/90 hover:bg-white text-zinc-400 hover:text-zinc-700'">
                                    <template x-if="loadingMap[{{ $photo->id }}]">
                                        <svg class="animate-spin w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="!loadingMap[{{ $photo->id }}]">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                </button>
                            @else
                                @if($photo->is_selected)
                                    <div class="absolute top-2.5 right-2.5 w-8 h-8 rounded-full bg-[#09090b] text-white flex items-center justify-center shadow-md">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <!-- Card Footer: Filename & Retouch Note Button -->
                        <div class="p-3 bg-white flex items-center justify-between border-t border-[#E4E4E7]">
                            <div class="flex items-center space-x-1.5 truncate mr-2 min-w-0" title="{{ $photo->filename }}">
                                <span class="text-[11px] font-mono text-zinc-600 truncate">
                                    {{ $photo->filename }}
                                </span>
                                @if($photo->isDng())
                                    <span class="px-1.5 py-0.5 bg-zinc-950 text-white rounded text-[8px] font-bold tracking-wider uppercase shrink-0">DNG</span>
                                @elseif($photo->isRaw())
                                    <span class="px-1.5 py-0.5 bg-zinc-800 text-zinc-200 rounded text-[8px] font-bold tracking-wider uppercase shrink-0">{{ strtoupper($photo->extension) }}</span>
                                @endif
                            </div>

                            <!-- Retouch Note Trigger -->
                            @if($gallery->isOpen())
                                <button type="button"
                                        @click="openNoteModal({{ $photo->id }}, '{{ $photo->filename }}')"
                                        class="p-1 rounded-lg hover:bg-zinc-100 transition relative"
                                        :class="hasNote({{ $photo->id }}) ? 'text-[#09090b]' : 'text-zinc-400 hover:text-zinc-600'"
                                        title="Tambah / Ubah Catatan Revisi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <template x-if="hasNote({{ $photo->id }})">
                                        <span class="absolute -top-1 -right-1 w-2 h-2 bg-amber-500 rounded-full"></span>
                                    </template>
                                </button>
                            @else
                                <template x-if="hasNote({{ $photo->id }})">
                                    <span class="text-[10px] text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">
                                        Catatan
                                    </span>
                                </template>
                            @endif
                        </div>

                        <!-- Display Note Preview if exists -->
                        <template x-if="hasNote({{ $photo->id }})">
                            <div class="px-3 pb-2 text-[10px] text-zinc-600 bg-zinc-50 border-t border-zinc-100 truncate"
                                 :title="notesMap[{{ $photo->id }}]">
                                📝 <span x-text="notesMap[{{ $photo->id }}]"></span>
                            </div>
                        </template>

                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-24 text-zinc-400">
                <p class="text-sm">Galeri ini sedang disiapkan oleh tim fotografer.</p>
            </div>
        @endif
    </main>

    <!-- MOBILE FLOATING ACTION DOCK -->
    <div class="md:hidden fixed bottom-4 inset-x-4 z-40">
        <div class="bg-[#09090b] text-white rounded-2xl p-3.5 shadow-2xl flex items-center justify-between border border-zinc-800">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-full bg-zinc-800 flex items-center justify-center text-xs font-bold text-white">
                    <span x-text="selectedCount"></span>
                </div>
                <div class="text-xs">
                    <div class="font-semibold text-white">
                        <span x-text="selectedCount"></span> / <span x-text="maxSelect"></span> Foto
                    </div>
                    <div class="text-[10px] text-zinc-400">
                        <span x-show="selectedCount < maxSelect">Tersisa <span x-text="maxSelect - selectedCount"></span> foto</span>
                        <span x-show="selectedCount >= maxSelect">Kuota Penuh</span>
                    </div>
                </div>
            </div>

            @if($gallery->isOpen())
                <button type="button"
                        @click="openSubmitModal = true"
                        :disabled="selectedCount === 0"
                        class="px-4 py-2 bg-white text-[#09090b] disabled:bg-zinc-800 disabled:text-zinc-500 text-xs font-bold uppercase tracking-wider rounded-xl transition">
                    Kunci Pilihan
                </button>
            @else
                <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                    Terkunci
                </span>
            @endif
        </div>
    </div>

    <!-- RETOUCH NOTE MODAL -->
    <div x-show="activeNoteModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-zinc-200"
             @click.away="activeNoteModal = false">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-[#09090b]">Catatan Revisi / Retouching</h3>
                    <p class="text-xs text-zinc-400 font-mono mt-0.5" x-text="currentNoteFilename"></p>
                </div>
                <button type="button" @click="activeNoteModal = false" class="text-zinc-400 hover:text-zinc-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="text-xs text-zinc-500 mb-3">
                Tuliskan permintaan khusus untuk foto ini (misal: "Rapikan toga kanan", "Hilangkan pantulan kacamata", dll).
            </p>

            <textarea x-model="currentNoteText"
                      rows="4"
                      maxlength="500"
                      placeholder="Tulis catatan retouching di sini..."
                      class="w-full p-3.5 bg-zinc-50 border border-zinc-200 rounded-2xl text-xs text-zinc-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition"></textarea>

            <div class="mt-4 flex items-center justify-end space-x-2.5">
                <button type="button"
                        @click="activeNoteModal = false"
                        class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold rounded-xl transition">
                    Batal
                </button>
                <button type="button"
                        @click="saveNote()"
                        :disabled="isSavingNote"
                        class="px-5 py-2 bg-[#09090b] hover:bg-zinc-800 text-white text-xs font-semibold rounded-xl transition">
                    <span x-text="isSavingNote ? 'Menyimpan...' : 'Simpan Catatan'">Simpan Catatan</span>
                </button>
            </div>
        </div>
    </div>

    <!-- SUBMIT CONFIRMATION MODAL -->
    <div x-show="openSubmitModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-zinc-200 text-center"
             @click.away="openSubmitModal = false">
            <div class="w-14 h-14 bg-zinc-100 text-[#09090b] rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>

            <h3 class="text-lg font-bold text-[#09090b]">Kunci Pilihan Foto Anda?</h3>
            <p class="text-xs text-zinc-500 mt-2 leading-relaxed">
                Anda telah memilih <strong class="text-zinc-900" x-text="selectedCount"></strong> dari <span x-text="maxSelect"></span> foto kuota.
                Setelah dikunci, pilihan foto akan langsung dikirim ke tim editor Fourji Graduation dan tidak dapat diubah lagi.
            </p>

            <div class="mt-6 flex items-center justify-center space-x-3">
                <button type="button"
                        @click="openSubmitModal = false"
                        class="w-1/2 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold rounded-xl transition">
                    Cek Kembali
                </button>
                <button type="button"
                        @click="submitProofing()"
                        :disabled="isSubmitting"
                        class="w-1/2 py-2.5 bg-[#09090b] hover:bg-zinc-800 text-white text-xs font-semibold rounded-xl transition shadow-sm">
                    <span x-text="isSubmitting ? 'Mengunci...' : 'Ya, Kunci Pilihan'">Ya, Kunci Pilihan</span>
                </button>
            </div>
        </div>
    </div>

    <!-- PROOFING ALPINE SCRIPT -->
    <script>
        function photoProofing(config) {
            return {
                slug: config.slug,
                maxSelect: config.maxSelect,
                status: config.status,
                activeFilter: 'all',
                selectedMap: {},
                notesMap: {},
                loadingMap: {},
                selectedCount: 0,
                openSubmitModal: false,
                isSubmitting: false,
                activeNoteModal: false,
                currentNotePhotoId: null,
                currentNoteFilename: '',
                currentNoteText: '',
                isSavingNote: false,

                init() {
                    // Populate initial selections
                    (config.initialSelected || []).forEach(id => {
                        this.selectedMap[id] = true;
                    });
                    this.selectedCount = Object.keys(this.selectedMap).length;

                    // Populate initial notes
                    this.notesMap = config.initialNotes || {};

                    // Initialize GLightbox
                    this.$nextTick(() => {
                        if (window.GLightbox) {
                            window.GLightbox({
                                selector: '.glightbox',
                                touchNavigation: true,
                                loop: true,
                                zoomable: true
                            });
                        }
                    });
                },

                isSelected(id) {
                    return !!this.selectedMap[id];
                },

                hasNote(id) {
                    return !!(this.notesMap[id] && this.notesMap[id].trim().length > 0);
                },

                async togglePhoto(photoId) {
                    if (this.status !== 'open') return;

                    // Optimistic check: if not selected and already reached max quota
                    if (!this.selectedMap[photoId] && this.selectedCount >= this.maxSelect) {
                        alert(`Batas kuota foto edit (${this.maxSelect} foto) telah tercapai. Silakan batalkan salah satu pilihan jika ingin memilih foto ini.`);
                        return;
                    }

                    this.loadingMap[photoId] = true;

                    try {
                        const response = await fetch(`/g/${this.slug}/photos/${photoId}/toggle`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            if (data.is_selected) {
                                this.selectedMap[photoId] = true;
                            } else {
                                delete this.selectedMap[photoId];
                            }
                            this.selectedCount = data.selected_count;
                        } else {
                            alert(data.message || 'Terjadi kesalahan saat memilih foto.');
                        }
                    } catch (error) {
                        console.error('Toggle error:', error);
                        alert('Gagal menghubungi server. Periksa koneksi internet Anda.');
                    } finally {
                        this.loadingMap[photoId] = false;
                    }
                },

                openNoteModal(photoId, filename) {
                    this.currentNotePhotoId = photoId;
                    this.currentNoteFilename = filename;
                    this.currentNoteText = this.notesMap[photoId] || '';
                    this.activeNoteModal = true;
                },

                async saveNote() {
                    if (!this.currentNotePhotoId) return;

                    this.isSavingNote = true;
                    try {
                        const response = await fetch(`/g/${this.slug}/photos/${this.currentNotePhotoId}/note`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ note: this.currentNoteText })
                        });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            if (data.client_note && data.client_note.trim().length > 0) {
                                this.notesMap[this.currentNotePhotoId] = data.client_note;
                            } else {
                                delete this.notesMap[this.currentNotePhotoId];
                            }
                            this.activeNoteModal = false;
                        } else {
                            alert(data.message || 'Gagal menyimpan catatan.');
                        }
                    } catch (error) {
                        console.error('Save note error:', error);
                        alert('Gagal menghubungi server.');
                    } finally {
                        this.isSavingNote = false;
                    }
                },

                async submitProofing() {
                    if (this.selectedCount === 0) {
                        alert('Silakan pilih minimal 1 foto sebelum mengunci.');
                        return;
                    }

                    this.isSubmitting = true;

                    try {
                        const response = await fetch(`/g/${this.slug}/submit`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            window.location.href = data.redirect;
                        } else {
                            alert(data.message || 'Gagal mengunci pilihan foto.');
                            this.isSubmitting = false;
                            this.openSubmitModal = false;
                        }
                    } catch (error) {
                        console.error('Submit error:', error);
                        alert('Gagal mengunci pilihan foto. Silakan coba kembali.');
                        this.isSubmitting = false;
                        this.openSubmitModal = false;
                    }
                }
            };
        }
    </script>

</body>
</html>
