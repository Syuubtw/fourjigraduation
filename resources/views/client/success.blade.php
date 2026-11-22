<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pilihan Foto Diterima — Fourji Graduation</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAFAFA] text-[#09090b] font-sans antialiased min-h-screen flex flex-col justify-between">

    <!-- Minimal Header -->
    <header class="py-5 border-b border-[#E4E4E7] bg-white flex items-center justify-center">
        <img src="{{ asset('images/logo-dark.png') }}" alt="Fourji Picture | 4everlove" class="h-5 sm:h-6 w-auto object-contain">
    </header>

    <!-- Success Card -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-lg bg-white border border-[#E4E4E7] rounded-3xl p-8 sm:p-12 shadow-sm text-center">
            <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <div class="text-[11px] font-semibold uppercase tracking-widest text-emerald-600 mb-1">
                Pilihan Foto Berhasil Dikunci
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-[#09090b]">
                Terima Kasih, {{ $gallery->client_name }}!
            </h1>

            <p class="text-sm text-zinc-600 mt-3 leading-relaxed">
                Kami telah mengamankan <strong class="text-zinc-900">{{ $selectedCount }} foto wisuda</strong> pilihan Anda beserta seluruh catatan retouching. Galeri ini telah dikunci untuk proses editing.
            </p>

            <div class="mt-8 p-4 bg-zinc-50 border border-zinc-200/80 rounded-2xl text-xs text-zinc-600 text-left space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-zinc-400">Status Sesi:</span>
                    <span class="font-semibold text-zinc-900 uppercase">Terkunci (Submitted)</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-zinc-400">Total Terpilih:</span>
                    <span class="font-semibold text-zinc-900">{{ $selectedCount }} / {{ $gallery->max_select }} Foto</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-zinc-400">Waktu Submit:</span>
                    <span class="font-mono text-zinc-900">{{ optional($gallery->submitted_at)->format('d M Y, H:i') ?? now()->format('d M Y, H:i') }}</span>
                </div>
            </div>

            <div class="mt-8">
                <a href="{{ route('client.gallery.show', ['slug' => $gallery->slug]) }}"
                   class="inline-flex items-center px-6 py-3 bg-[#09090b] hover:bg-zinc-800 text-white text-xs font-semibold uppercase tracking-widest rounded-2xl transition shadow-sm">
                    Lihat Kembali Galeri
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-xs text-zinc-400">
        &copy; {{ date('Y') }} Fourji Graduation. All rights reserved.
    </footer>

</body>
</html>
