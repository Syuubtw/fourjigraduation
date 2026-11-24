<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Galeri — Fourji Picture</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAFAFA] text-[#09090b] font-sans antialiased min-h-screen flex flex-col justify-between">

    <!-- Minimal Header -->
    <header class="py-5 border-b border-[#E4E4E7] bg-white flex items-center justify-center">
        <img src="{{ asset('images/logo-dark.png') }}" alt="Fourji Picture | 4everlove" class="h-5 sm:h-6 w-auto object-contain">
    </header>

    <!-- PIN Card -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white border border-[#E4E4E7] rounded-3xl p-8 sm:p-10 shadow-sm text-center">
            <div class="w-12 h-12 bg-[#F4F4F5] rounded-2xl flex items-center justify-center mx-auto mb-5 text-[#09090b]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>

            <div class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400 mb-1">
                Private Client Proofing
            </div>
            <h1 class="text-xl font-bold tracking-tight text-[#09090b]">
                {{ $gallery->client_name }}
            </h1>
            <p class="text-xs text-zinc-500 mt-2 leading-relaxed">
                Galeri foto ini diproteksi. Masukkan PIN akses yang telah dikirimkan oleh tim Fourji Picture.
            </p>

            <form method="POST" action="{{ route('client.pin.verify', ['slug' => $gallery->slug]) }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <input type="password"
                           name="pin"
                           id="pin"
                           inputmode="numeric"
                           placeholder="••••"
                           maxlength="10"
                           autofocus
                           required
                           class="w-full text-center tracking-[0.5em] font-mono text-2xl py-3.5 px-4 bg-zinc-50 border @error('pin') border-red-500 @else border-zinc-200 @enderror rounded-2xl text-zinc-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-zinc-900 transition">
                    @error('pin')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full py-3.5 px-6 bg-[#09090b] hover:bg-zinc-800 text-white text-xs font-semibold uppercase tracking-widest rounded-2xl transition duration-150 shadow-sm">
                    Buka Galeri Foto
                </button>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-xs text-zinc-400">
        &copy; {{ date('Y') }} Fourji Picture. All rights reserved.
    </footer>

</body>
</html>
