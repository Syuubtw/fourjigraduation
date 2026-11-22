<?php

namespace App\Services;

use App\Models\Gallery;
use App\Models\Photo;
use Exception;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GoogleDriveService
{
    protected ?GoogleDrive $driveService = null;
    protected ?GoogleClient $googleClient = null;
    protected ?string $connectionType = null;
    protected ?string $connectedEmail = null;

    public function __construct()
    {
        $this->initializeClient();
    }

    /**
     * Attempt to initialize the Google Client via OAuth Token or Service Account.
     */
    protected function initializeClient(): void
    {
        // Priority 0: Environment Variable JSON (for Vercel / Cloud serverless)
        $envToken = env('GOOGLE_OAUTH_TOKEN_JSON');
        if (!empty($envToken)) {
            try {
                $tokenData = json_decode($envToken, true);
                if (!empty($tokenData)) {
                    $this->setupOAuthClientWithToken($tokenData);
                    return;
                }
            } catch (Exception $e) {
                Log::warning("Failed to initialize Google Drive via GOOGLE_OAUTH_TOKEN_JSON: " . $e->getMessage());
            }
        }

        // Priority 1: Persistent Database Token in TiDB Cloud
        try {
            $row = \Illuminate\Support\Facades\DB::table('cache')->where('key', 'like', '%google_oauth_token%')->first();
            if ($row && !empty($row->value)) {
                $raw = @unserialize($row->value);
                $tokenData = is_array($raw) ? $raw : json_decode($row->value, true);
                if (!empty($tokenData) && is_array($tokenData)) {
                    $this->setupOAuthClientWithToken($tokenData);
                    return;
                }
            }
        } catch (\Throwable $e) {
            // DB may not be reachable yet
        }

        // Priority 2: Direct Google Account Login (OAuth 2.0 file)
        $oauthTokenPath = config('services.google.oauth_token_path');
        if (!empty($oauthTokenPath) && file_exists($oauthTokenPath)) {
            try {
                $tokenData = json_decode(file_get_contents($oauthTokenPath), true);
                if (!empty($tokenData)) {
                    $this->setupOAuthClientWithToken($tokenData);
                    return;
                }
            } catch (Exception $e) {
                Log::warning("Failed to initialize Google Drive via OAuth token file: " . $e->getMessage());
            }
        }

        // Priority 3: Google Service Account from ENV
        $envSa = env('GOOGLE_SERVICE_ACCOUNT_JSON');
        if (!empty($envSa)) {
            try {
                $serviceAccountData = json_decode($envSa, true);
                if (!empty($serviceAccountData) && isset($serviceAccountData['client_email'])) {
                    $client = new GoogleClient();
                    $client->setAuthConfig($serviceAccountData);
                    $client->addScope([GoogleDrive::DRIVE_READONLY]);
                    $client->setApplicationName('Fourji Graduation Photo Proofing');

                    $this->googleClient = $client;
                    $this->driveService = new GoogleDrive($client);
                    $this->connectionType = 'service_account';
                    $this->connectedEmail = $serviceAccountData['client_email'];
                    return;
                }
            } catch (Exception $e) {
                Log::warning("Failed to initialize Google Drive via GOOGLE_SERVICE_ACCOUNT_JSON: " . $e->getMessage());
            }
        }

        // Priority 4: Google Service Account File
        $serviceAccountPath = config('services.google.service_account_json');
        if (!empty($serviceAccountPath) && file_exists($serviceAccountPath)) {
            try {
                $serviceAccountData = json_decode(file_get_contents($serviceAccountPath), true);
                if (!empty($serviceAccountData) && isset($serviceAccountData['client_email'])) {
                    $client = new GoogleClient();
                    $client->setAuthConfig($serviceAccountPath);
                    $client->addScope([GoogleDrive::DRIVE_READONLY]);
                    $client->setApplicationName('Fourji Graduation Photo Proofing');

                    $this->googleClient = $client;
                    $this->driveService = new GoogleDrive($client);
                    $this->connectionType = 'service_account';
                    $this->connectedEmail = $serviceAccountData['client_email'];
                    return;
                }
            } catch (Exception $e) {
                Log::warning("Failed to initialize Google Drive via Service Account: " . $e->getMessage());
            }
        }
    }

    /**
     * Configure Google Client with OAuth token data and handle token auto-refresh.
     */
    protected function setupOAuthClientWithToken(array $tokenData): void
    {
        $client = $this->createBaseOAuthClient();
        $client->setAccessToken($tokenData);

        // Auto-refresh expired token if refresh token exists
        if ($client->isAccessTokenExpired() && $client->getRefreshToken()) {
            try {
                $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                $tokenData = array_merge($tokenData, $newToken);

                $oauthTokenPath = config('services.google.oauth_token_path');
                if (!empty($oauthTokenPath)) {
                    @file_put_contents($oauthTokenPath, json_encode($tokenData));
                }

                try {
                    \Illuminate\Support\Facades\DB::table('cache')->updateOrInsert(
                        ['key' => 'fourji_cache_google_oauth_token'],
                        ['value' => serialize($tokenData), 'expiration' => 2147483647]
                    );
                } catch (\Throwable $t) {
                    // Ignore DB error
                }
            } catch (Exception $e) {
                Log::warning("Failed to refresh Google OAuth token: " . $e->getMessage());
            }
        }

        $this->googleClient = $client;
        $this->driveService = new GoogleDrive($client);
        $this->connectionType = 'oauth';
        $this->connectedEmail = $tokenData['email'] ?? 'Akun Google Pribadi';
    }

    /**
     * Create base Google Client configured for OAuth 2.0.
     */
    public function createBaseOAuthClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $redirectUri = config('services.google.redirect_uri');
        if (isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL']) || getenv('VERCEL')) {
            $redirectUri = 'https://fourjigraduation-umber.vercel.app/admin/google/callback';
        } elseif (empty($redirectUri) || str_contains($redirectUri, 'localhost')) {
            if (isset($_SERVER['HTTP_HOST']) && !str_contains($_SERVER['HTTP_HOST'], 'localhost')) {
                $redirectUri = 'https://' . $_SERVER['HTTP_HOST'] . '/admin/google/callback';
            }
        }
        $client->setRedirectUri($redirectUri);
        $client->setScopes([
            'https://www.googleapis.com/auth/drive.readonly',
            'https://www.googleapis.com/auth/userinfo.email',
        ]);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        $client->setIncludeGrantedScopes(true);
        $client->setApplicationName('Fourji Graduation Photo Proofing');

        return $client;
    }

    /**
     * Check if Google Drive is connected.
     */
    public function isConnected(): bool
    {
        return $this->driveService !== null;
    }

    /**
     * Get connection mode: 'oauth', 'service_account', or null.
     */
    public function getConnectionType(): ?string
    {
        return $this->connectionType;
    }

    /**
     * Get connected email address if available.
     */
    public function getConnectedEmail(): ?string
    {
        return $this->connectedEmail;
    }

    /**
     * Ensure the drive service is properly authenticated.
     */
    protected function ensureAuthenticated(): GoogleDrive
    {
        if ($this->driveService === null) {
            throw new RuntimeException(
                "Google Drive belum terhubung! Silakan pilih salah satu: Hubungkan akun Google Drive asli Anda (OAuth) atau upload file service-account.json di panel Admin."
            );
        }

        return $this->driveService;
    }

    /**
     * Sync metadata from a Google Drive folder into the photos table.
     * Handles pagination (nextPageToken) for 500+ photos.
     * Performs idempotent upserts without overwriting client selections or retouching notes.
     *
     * @param Gallery $gallery
     * @return array{synced: int, total: int}
     * @throws Exception
     */
    public function syncGalleryPhotos(Gallery $gallery): array
    {
        $drive = $this->ensureAuthenticated();
        $folderId = trim($gallery->gdrive_folder_id);
        $pageToken = null;
        $syncedCount = 0;
        $now = now()->toDateTimeString();

        $supportedExtensions = [
            'jpg', 'jpeg', 'png', 'webp', 'heic', 'tiff', 'tif',
            'dng', 'cr2', 'cr3', 'arw', 'nef', 'raf', 'rw2', 'orf', 'raw'
        ];

        try {
            do {
                $optParams = [
                    'q'         => sprintf("'%s' in parents and mimeType != 'application/vnd.google-apps.folder' and trashed = false", $folderId),
                    'fields'    => 'nextPageToken, files(id, name, mimeType, size, thumbnailLink)',
                    'pageSize'  => 100,
                    'pageToken' => $pageToken,
                    'orderBy'   => 'name asc',
                    'supportsAllDrives' => true,
                    'includeItemsFromAllDrives' => true,
                ];

                $results = $drive->files->listFiles($optParams);
                $files = $results->getFiles();

                if (!empty($files)) {
                    $records = [];

                    foreach ($files as $file) {
                        $ext = strtolower(pathinfo($file->getName(), PATHINFO_EXTENSION));
                        $mime = strtolower($file->getMimeType() ?? '');

                        // Accept recognized image MIME types OR photography & RAW camera formats (including .dng)
                        if (!str_starts_with($mime, 'image/') && !in_array($ext, $supportedExtensions)) {
                            continue;
                        }

                        $records[] = [
                            'gallery_id'     => $gallery->id,
                            'gdrive_file_id' => $file->getId(),
                            'filename'       => $file->getName(),
                            'thumbnail_url'  => $file->getThumbnailLink(),
                            'created_at'     => $now,
                            'updated_at'     => $now,
                        ];
                    }

                    if (!empty($records)) {
                        // Idempotent upsert: matches unique(['gallery_id', 'gdrive_file_id'])
                        // Only updates 'filename', 'thumbnail_url', and 'updated_at'; preserves 'is_selected' & 'client_note'
                        Photo::upsert(
                            $records,
                            ['gallery_id', 'gdrive_file_id'],
                            ['filename', 'thumbnail_url', 'updated_at']
                        );

                        $syncedCount += count($records);
                    }
                }

                $pageToken = $results->getNextPageToken();
            } while ($pageToken !== null);

            $totalPhotos = $gallery->photos()->count();

            Log::info("Synced {$syncedCount} photos for gallery [{$gallery->id} - {$gallery->client_name}]. Total: {$totalPhotos}.");

            return [
                'synced' => $syncedCount,
                'total'  => $totalPhotos,
            ];
        } catch (Exception $e) {
            Log::error("Google Drive Sync Error for Gallery [{$gallery->id}]: " . $e->getMessage(), [
                'folder_id' => $folderId,
                'exception' => $e,
            ]);

            throw new RuntimeException("Gagal melakukan sinkronisasi Google Drive: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Download a file's binary content directly from Google Drive API.
     */
    public function downloadFileContent(string $fileId): ?string
    {
        try {
            $drive = $this->ensureAuthenticated();
            $response = $drive->files->get($fileId, [
                'alt' => 'media',
                'supportsAllDrives' => true,
            ]);

            return $response->getBody()->getContents();
        } catch (Exception $e) {
            Log::warning("Failed to download media content for file [{$fileId}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Test folder access and return basic folder details.
     */
    public function verifyFolderAccess(string $folderId): array
    {
        try {
            $drive = $this->ensureAuthenticated();
            $folder = $drive->files->get($folderId, [
                'fields'            => 'id, name, mimeType',
                'supportsAllDrives' => true,
            ]);

            return [
                'valid' => $folder->getMimeType() === 'application/vnd.google-apps.folder',
                'name'  => $folder->getName(),
            ];
        } catch (Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Disconnect any stored Google Drive session.
     */
    public function disconnect(): void
    {
        $oauthTokenPath = config('services.google.oauth_token_path');
        if (!empty($oauthTokenPath) && file_exists($oauthTokenPath)) {
            File::delete($oauthTokenPath);
        }

        try {
            \Illuminate\Support\Facades\DB::table('cache')->where('key', 'like', '%google_oauth_token%')->delete();
        } catch (\Throwable $e) {
            // Ignore DB error
        }

        $this->driveService = null;
        $this->googleClient = null;
        $this->connectionType = null;
        $this->connectedEmail = null;
    }
}
