<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleDriveService;
use Exception;
use Google\Service\Oauth2 as GoogleOauth2;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class GoogleAuthController extends Controller
{
    /**
     * Save OAuth Client credentials directly into .env and immediately initiate login.
     */
    public function saveCredentials(Request $request, GoogleDriveService $driveService): RedirectResponse
    {
        $request->validate([
            'client_id'     => ['required', 'string'],
            'client_secret' => ['required', 'string'],
        ], [
            'client_id.required'     => 'Google Client ID wajib diisi.',
            'client_secret.required' => 'Google Client Secret wajib diisi.',
        ]);

        $clientId = trim($request->input('client_id'));
        $clientSecret = trim($request->input('client_secret'));

        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            if (preg_match('/^GOOGLE_CLIENT_ID=.*/m', $envContent)) {
                $envContent = preg_replace('/^GOOGLE_CLIENT_ID=.*/m', "GOOGLE_CLIENT_ID={$clientId}", $envContent);
            } else {
                $envContent .= "\nGOOGLE_CLIENT_ID={$clientId}";
            }

            if (preg_match('/^GOOGLE_CLIENT_SECRET=.*/m', $envContent)) {
                $envContent = preg_replace('/^GOOGLE_CLIENT_SECRET=.*/m', "GOOGLE_CLIENT_SECRET={$clientSecret}", $envContent);
            } else {
                $envContent .= "\nGOOGLE_CLIENT_SECRET={$clientSecret}";
            }

            file_put_contents($envPath, $envContent);
            Artisan::call('config:clear');
        }

        config([
            'services.google.client_id' => $clientId,
            'services.google.client_secret' => $clientSecret,
        ]);

        return $this->redirect($driveService);
    }

    /**
     * Redirect to Google OAuth consent screen.
     */
    public function redirect(GoogleDriveService $driveService): RedirectResponse
    {
        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            return redirect()
                ->route('admin.galleries.index')
                ->with('error', 'Google Client ID atau Client Secret belum diisi.');
        }

        $client = $driveService->createBaseOAuthClient();
        $authUrl = $client->createAuthUrl();

        return redirect()->away($authUrl);
    }

    /**
     * Handle OAuth callback from Google.
     */
    public function callback(Request $request, GoogleDriveService $driveService): RedirectResponse
    {
        $code = $request->input('code');

        if (!$code) {
            return redirect()
                ->route('admin.galleries.index')
                ->with('error', 'Otentikasi Google dibatalkan atau tidak ada kode otorisasi.');
        }

        try {
            $client = $driveService->createBaseOAuthClient();
            $token = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                throw new Exception($token['error_description'] ?? $token['error']);
            }

            // Verify Google Drive scope was checked and granted
            $grantedScope = $token['scope'] ?? '';
            if (!str_contains($grantedScope, 'drive')) {
                return redirect()
                    ->route('admin.galleries.index')
                    ->with('error', 'Izin Google Drive belum dicentang! Saat login Google, Anda wajib mencentang kotak "Lihat dan download semua file Google Drive Anda" agar sistem dapat membaca foto.');
            }

            // Fetch authenticated user's email
            $client->setAccessToken($token);
            $oauth2 = new GoogleOauth2($client);
            $userInfo = $oauth2->userinfo->get();
            $token['email'] = $userInfo->getEmail();

            $tokenPath = config('services.google.oauth_token_path');
            File::ensureDirectoryExists(dirname($tokenPath));
            File::put($tokenPath, json_encode($token, JSON_PRETTY_PRINT));

            return redirect()
                ->route('admin.galleries.index')
                ->with('success', "Akun Google Drive asli ({$token['email']}) berhasil terhubung!");
        } catch (Exception $e) {
            return redirect()
                ->route('admin.galleries.index')
                ->with('error', "Gagal menghubungkan akun Google: " . $e->getMessage());
        }
    }

    /**
     * Disconnect OAuth Google account.
     */
    public function disconnect(GoogleDriveService $driveService): RedirectResponse
    {
        $driveService->disconnect();

        return redirect()
            ->route('admin.galleries.index')
            ->with('success', 'Koneksi akun Google Drive berhasil diputuskan.');
    }

    /**
     * Upload Service Account JSON key directly from browser.
     */
    public function uploadServiceAccount(Request $request): RedirectResponse
    {
        $request->validate([
            'service_account_file' => ['required', 'file', 'mimes:json,txt', 'max:2048'],
        ], [
            'service_account_file.required' => 'Pilih file JSON Service Account dari Google Cloud Console.',
            'service_account_file.mimes'    => 'File harus berformat JSON (.json).',
        ]);

        try {
            $fileContent = file_get_contents($request->file('service_account_file')->getRealPath());
            $jsonData = json_decode($fileContent, true);

            if (!is_array($jsonData) || empty($jsonData['client_email']) || empty($jsonData['private_key'])) {
                return redirect()
                    ->route('admin.galleries.index')
                    ->with('error', 'Format file tidak valid. Pastikan ini adalah file JSON Service Account dari Google Cloud.');
            }

            $targetPath = config('services.google.service_account_json');
            File::ensureDirectoryExists(dirname($targetPath));
            File::put($targetPath, $fileContent);

            return redirect()
                ->route('admin.galleries.index')
                ->with('success', "Service Account ({$jsonData['client_email']}) berhasil disimpan!");
        } catch (Exception $e) {
            return redirect()
                ->route('admin.galleries.index')
                ->with('error', 'Gagal menyimpan file Service Account: ' . $e->getMessage());
        }
    }

    /**
     * Delete stored Service Account JSON file.
     */
    public function deleteServiceAccount(): RedirectResponse
    {
        $targetPath = config('services.google.service_account_json');

        if (file_exists($targetPath)) {
            File::delete($targetPath);
        }

        return redirect()
            ->route('admin.galleries.index')
            ->with('success', 'File Service Account berhasil dihapus.');
    }
}
