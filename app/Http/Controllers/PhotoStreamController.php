<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Services\GoogleDriveService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class PhotoStreamController extends Controller
{
    /**
     * Stream a photo thumbnail or preview with local disk caching.
     */
    public function stream(Request $request, Photo $photo, GoogleDriveService $driveService): Response
    {
        $type = $request->query('type', 'thumb');
        $cacheDir = storage_path('app/public/thumbnails');
        $cacheFile = "{$cacheDir}/photo_{$photo->id}_{$type}.jpg";

        // 1. Return from local cache if already downloaded
        if (File::exists($cacheFile) && File::size($cacheFile) > 0) {
            return response()->file($cacheFile, [
                'Content-Type'  => 'image/jpeg',
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        // Ensure cache directory exists
        if (!File::isDirectory($cacheDir)) {
            File::makeDirectory($cacheDir, 0755, true, true);
        }

        // 2. Try fast CDN URL first
        $targetUrl = match ($type) {
            'preview' => $photo->preview_url,
            'full'    => $photo->full_url,
            default   => $photo->thumbnail_url,
        };

        $imageContent = $this->fetchUrl($targetUrl);

        // 3. Fallback: fetch via Google Drive thumbnail endpoint
        if (empty($imageContent)) {
            $fallbackUrl = "https://drive.google.com/thumbnail?id={$photo->gdrive_file_id}&sz=" . ($type === 'preview' ? 'w1600' : 'w600');
            $imageContent = $this->fetchUrl($fallbackUrl);
        }

        // 4. Infallible fallback: download directly via authenticated Google Drive API
        if (empty($imageContent)) {
            $rawContent = $driveService->downloadFileContent($photo->gdrive_file_id);
            if (!empty($rawContent)) {
                $imageContent = $this->extractJpegFromRaw($rawContent) ?? $rawContent;
            }
        }

        // 5. If retrieved, cache it and serve
        if (!empty($imageContent)) {
            File::put($cacheFile, $imageContent);

            return response($imageContent, 200, [
                'Content-Type'  => 'image/jpeg',
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        // 6. Last resort placeholder SVG
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="800" viewBox="0 0 600 800"><rect fill="#F4F4F5" width="600" height="800"/><text fill="#A1A1AA" font-family="sans-serif" font-size="14" x="50%" y="50%" text-anchor="middle">Foto sedang dimuat...</text></svg>';
        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * Download content from a URL with timeout and redirect handling.
     */
    protected function fetchUrl(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
            ],
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($httpCode === 200 && !empty($result) && (str_contains($contentType, 'image') || str_contains($contentType, 'octet-stream'))) {
            return $result;
        }

        return null;
    }

    /**
     * Extract embedded preview JPEG from RAW/DNG binary data if needed.
     */
    protected function extractJpegFromRaw(string $binaryData): ?string
    {
        // 1. If it's already a standard JPEG / PNG / WebP, return as is
        if (str_starts_with($binaryData, "\xFF\xD8\xFF") || str_starts_with($binaryData, "\x89PNG") || str_starts_with($binaryData, "RIFF")) {
            return $binaryData;
        }

        // 2. Try extracting largest embedded JPEG from DNG/TIFF stream
        $soi = strpos($binaryData, "\xFF\xD8\xFF");
        if ($soi !== false) {
            $eoi = strrpos($binaryData, "\xFF\xD9");
            if ($eoi !== false && $eoi > $soi) {
                return substr($binaryData, $soi, ($eoi - $soi) + 2);
            }
        }

        return null;
    }
}
