<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.galleries.index') }}" class="text-zinc-400 hover:text-zinc-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">
                    Tambah Galeri Wisuda Baru
                </h1>
                <p class="text-sm text-zinc-500 mt-0.5">
                    Konfigurasi sesi proofing foto wisuda dan sambungkan ke Google Drive folder.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white border border-zinc-200/80 rounded-2xl shadow-xs overflow-hidden">
                <form method="POST" action="{{ route('admin.galleries.store') }}" class="p-6 sm:p-8 space-y-6">
                    @csrf

                    <!-- Client Name -->
                    <div>
                        <label for="client_name" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-2">
                            Nama Klien / Wisudawan <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="client_name"
                               id="client_name"
                               value="{{ old('client_name') }}"
                               placeholder="Contoh: Amanda Clarissa, S.Ked"
                               required
                               class="w-full px-4 py-2.5 bg-zinc-50/70 border @error('client_name') border-red-400 @else border-zinc-200 @enderror rounded-xl text-sm text-zinc-900 placeholder-zinc-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition">
                        @error('client_name')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Custom Slug & PIN -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="slug" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-2">
                                Slug URL (Opsional)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs text-zinc-400 font-mono">
                                    /g/
                                </span>
                                <input type="text"
                                       name="slug"
                                       id="slug"
                                       value="{{ old('slug') }}"
                                       placeholder="amanda-wisuda-ui"
                                       class="w-full pl-10 pr-4 py-2.5 bg-zinc-50/70 border @error('slug') border-red-400 @else border-zinc-200 @enderror rounded-xl text-sm font-mono text-zinc-900 placeholder-zinc-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition">
                            </div>
                            <p class="mt-1.5 text-[11px] text-zinc-400">Kosongkan jika ingin dibuat otomatis dari nama klien.</p>
                            @error('slug')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="pin" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-2">
                                PIN Akses (Opsional)
                            </label>
                            <input type="text"
                                   name="pin"
                                   id="pin"
                                   value="{{ old('pin') }}"
                                   placeholder="Contoh: 2026 atau 4digit"
                                   maxlength="10"
                                   class="w-full px-4 py-2.5 bg-zinc-50/70 border @error('pin') border-red-400 @else border-zinc-200 @enderror rounded-xl text-sm font-mono text-zinc-900 placeholder-zinc-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition">
                            <p class="mt-1.5 text-[11px] text-zinc-400">Jika diisi, klien wajib memasukkan PIN ini sebelum melihat galeri.</p>
                            @error('pin')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- GDrive Folder ID -->
                    <div>
                        <label for="gdrive_folder_id" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-2">
                            Google Drive Folder ID <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="gdrive_folder_id"
                               id="gdrive_folder_id"
                               value="{{ old('gdrive_folder_id') }}"
                               placeholder="Contoh: 1BxyZ09aBcDeFgHiJkLmNoPqRsTuVw"
                               required
                               class="w-full px-4 py-2.5 bg-zinc-50/70 border @error('gdrive_folder_id') border-red-400 @else border-zinc-200 @enderror rounded-xl text-sm font-mono text-zinc-900 placeholder-zinc-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition">
                        <div class="mt-2 text-[11px] text-zinc-500 leading-relaxed bg-zinc-50 p-3 rounded-lg border border-zinc-100">
                            <strong>Cara mengambil Folder ID:</strong> Buka folder foto di Google Drive melalui browser. Salin kode huruf/angka di ujung URL setelah <code class="bg-zinc-200/70 px-1 py-0.5 rounded text-zinc-800">/folders/KODE_DISINI</code>.
                            Pastikan folder sudah di-share ke email Service Account Google Drive Anda!
                        </div>
                        @error('gdrive_folder_id')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Quota & Initial Status -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="max_select" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-2">
                                Batas Kuota Foto Edit <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   name="max_select"
                                   id="max_select"
                                   value="{{ old('max_select', 20) }}"
                                   min="1"
                                   max="500"
                                   required
                                   class="w-full px-4 py-2.5 bg-zinc-50/70 border @error('max_select') border-red-400 @else border-zinc-200 @enderror rounded-xl text-sm text-zinc-900 placeholder-zinc-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition">
                            <p class="mt-1.5 text-[11px] text-zinc-400">Maksimum foto yang dapat dipilih oleh klien.</p>
                            @error('max_select')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-2">
                                Status Galeri <span class="text-red-500">*</span>
                            </label>
                            <select name="status"
                                    id="status"
                                    required
                                    class="w-full px-4 py-2.5 bg-zinc-50/70 border @error('status') border-red-400 @else border-zinc-200 @enderror rounded-xl text-sm text-zinc-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition">
                                <option value="open" {{ old('status', 'open') === 'open' ? 'selected' : '' }}>Open (Klien dapat memilih langsung)</option>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Disiapkan terlebih dahulu)</option>
                            </select>
                            <p class="mt-1.5 text-[11px] text-zinc-400">Pilih "Open" agar klien bisa langsung memilih foto saat link dibagikan.</p>
                            @error('status')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Auto Sync Checkbox -->
                    <div class="pt-2">
                        <label class="flex items-start space-x-3 cursor-pointer">
                            <input type="checkbox"
                                   name="auto_sync"
                                   value="1"
                                   {{ old('auto_sync', '1') == '1' ? 'checked' : '' }}
                                   class="mt-0.5 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
                            <span class="text-xs text-zinc-600 leading-normal">
                                <strong>Sinkronisasi Otomatis Sekarang:</strong> Langsung tarik daftar file foto dari Google Drive begitu galeri ini disimpan.
                            </span>
                        </label>
                    </div>

                    <!-- Form Actions -->
                    <div class="pt-6 border-t border-zinc-100 flex items-center justify-end space-x-3">
                        <a href="{{ route('admin.galleries.index') }}"
                           class="px-5 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-sm font-medium rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-6 py-2.5 bg-zinc-950 hover:bg-zinc-800 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                            Simpan & Buat Galeri
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
