<?php

use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\GoogleAuthController;
use App\Http\Controllers\Client\ProofingController;
use App\Http\Controllers\PhotoStreamController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Landing Route
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Photo Stream / Thumbnail Proxy Route (High-Speed Local Cache)
|--------------------------------------------------------------------------
*/
Route::get('/photos/{photo}/stream', [PhotoStreamController::class, 'stream'])
    ->middleware('throttle:120,1')
    ->name('photo.stream');

/*
|--------------------------------------------------------------------------
| Client Photo Proofing Routes (Fourji Graduation)
|--------------------------------------------------------------------------
*/
Route::prefix('g/{slug}')->group(function () {
    // PIN Verification Routes (Protected from Brute Force)
    Route::get('/pin', [ProofingController::class, 'showPin'])->name('client.pin.show');
    Route::post('/pin', [ProofingController::class, 'verifyPin'])
        ->middleware('throttle:10,1')
        ->name('client.pin.verify');
    Route::get('/success', [ProofingController::class, 'success'])->name('client.gallery.success');

    // PIN-Protected Gallery Routes
    Route::middleware('gallery.pin')->group(function () {
        Route::get('/', [ProofingController::class, 'show'])->name('client.gallery.show');
        Route::post('/photos/{photo}/toggle', [ProofingController::class, 'togglePhoto'])
            ->middleware('throttle:60,1')
            ->name('client.gallery.toggle');
        Route::post('/photos/{photo}/note', [ProofingController::class, 'updateNote'])
            ->middleware('throttle:30,1')
            ->name('client.gallery.note');
        Route::post('/submit', [ProofingController::class, 'submit'])
            ->middleware('throttle:10,1')
            ->name('client.gallery.submit');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Portal Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('admin.galleries.index');
    })->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('galleries/live-stats', [GalleryController::class, 'liveStats'])->name('galleries.live_stats');
        Route::resource('galleries', GalleryController::class);
        Route::post('galleries/{gallery}/sync', [GalleryController::class, 'sync'])->name('galleries.sync');
        Route::post('galleries/{gallery}/reopen', [GalleryController::class, 'reopen'])->name('galleries.reopen');
        Route::post('galleries/{gallery}/complete', [GalleryController::class, 'complete'])->name('galleries.complete');

        // Google Drive Integration & Auth Routes
        Route::get('google/connect', [GoogleAuthController::class, 'redirect'])->name('google.connect');
        Route::get('google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
        Route::post('google/credentials', [GoogleAuthController::class, 'saveCredentials'])->name('google.save_credentials');
        Route::post('google/disconnect', [GoogleAuthController::class, 'disconnect'])->name('google.disconnect');
        Route::post('google/upload-service-account', [GoogleAuthController::class, 'uploadServiceAccount'])->name('google.upload_service_account');
        Route::post('google/delete-service-account', [GoogleAuthController::class, 'deleteServiceAccount'])->name('google.delete_service_account');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Cloud Deployment Setup Helpers (Vercel / Serverless)
| Protected strictly by APP_KEY
|--------------------------------------------------------------------------
*/
Route::get('/system/migrate', function (\Illuminate\Http\Request $request) {
    $providedKey = $request->query('key');
    $appKey = config('app.key');
    $normalizedKey = $providedKey ? str_replace(' ', '+', $providedKey) : '';

    if (empty($providedKey) || empty($appKey) || ($providedKey !== $appKey && $normalizedKey !== $appKey)) {
        abort(403, 'Akses ditolak. Secret key tidak valid.');
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        return response('<pre style="background:#09090b;color:#10b981;padding:24px;border-radius:12px;font-family:monospace;font-size:14px;line-height:1.6;">DATABASE MIGRATION SUCCESSFUL:\n\n' . e($output) . '</pre>', 200);
    } catch (\Throwable $e) {
        return response('<pre style="background:#09090b;color:#ef4444;padding:24px;border-radius:12px;font-family:monospace;font-size:14px;line-height:1.6;">DATABASE MIGRATION FAILED:\n\n' . e($e->getMessage()) . '</pre>', 500);
    }
})->name('system.migrate');

Route::get('/system/init-admin', function (\Illuminate\Http\Request $request) {
    $providedKey = $request->query('key');
    $appKey = config('app.key');
    $normalizedKey = $providedKey ? str_replace(' ', '+', $providedKey) : '';

    if (empty($providedKey) || empty($appKey) || ($providedKey !== $appKey && $normalizedKey !== $appKey)) {
        abort(403, 'Akses ditolak. Secret key tidak valid.');
    }

    $email = $request->query('email', 'admin@fourji.com');
    $password = $request->query('password', 'password');
    $name = $request->query('name', 'Fourji Admin');

    try {
        $user = \App\Models\User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        return response('<pre style="background:#09090b;color:#10b981;padding:24px;border-radius:12px;font-family:monospace;font-size:14px;line-height:1.6;">ADMIN ACCOUNT READY:\n\nEmail: ' . e($user->email) . '\nPassword: ' . e($password) . '\nStatus: ' . ($user->wasRecentlyCreated ? 'Created' : 'Already exists') . '</pre>', 200);
    } catch (\Throwable $e) {
        return response('<pre style="background:#09090b;color:#ef4444;padding:24px;border-radius:12px;font-family:monospace;font-size:14px;line-height:1.6;">ADMIN INITIALIZATION FAILED:\n\n' . e($e->getMessage()) . '</pre>', 500);
    }
})->name('system.init_admin');

