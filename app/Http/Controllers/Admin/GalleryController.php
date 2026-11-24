<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGalleryRequest;
use App\Http\Requests\UpdateGalleryRequest;
use App\Models\Gallery;
use App\Services\GoogleDriveService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    /**
     * Display a listing of client galleries.
     */
    public function index(Request $request, GoogleDriveService $driveService): View
    {
        $query = Gallery::query()
            ->where('user_id', auth()->id())
            ->withCount(['photos', 'selectedPhotos'])
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $galleries = $query->paginate(15)->withQueryString();

        $isGoogleConnected = $driveService->isConnected();
        $googleConnectionType = $driveService->getConnectionType();
        $googleConnectedEmail = $driveService->getConnectedEmail();

        return view('admin.galleries.index', compact(
            'galleries',
            'isGoogleConnected',
            'googleConnectionType',
            'googleConnectedEmail'
        ));
    }

    /**
     * Get live JSON stats for real-time dashboard auto-refresh.
     */
    public function liveStats(): JsonResponse
    {
        $galleries = Gallery::query()
            ->where('user_id', auth()->id())
            ->withCount(['photos', 'selectedPhotos'])
            ->get(['id', 'client_name', 'status', 'max_select', 'submitted_at'])
            ->map(function ($g) {
                return [
                    'id'             => $g->id,
                    'status'         => $g->status,
                    'selected_count' => $g->selected_photos_count,
                    'max_select'     => $g->max_select,
                    'submitted_at'   => $g->submitted_at ? $g->submitted_at->format('d M Y, H:i') : null,
                ];
            });

        return response()->json($galleries);
    }

    /**
     * Show the form for creating a new gallery.
     */
    public function create(): View
    {
        return view('admin.galleries.create');
    }

    /**
     * Store a newly created gallery in storage.
     */
    public function store(StoreGalleryRequest $request, GoogleDriveService $driveService): RedirectResponse
    {
        $validated = $request->validated();
        $autoSync = $request->boolean('auto_sync');
        unset($validated['auto_sync']);

        if (!empty($validated['gdrive_folder_id'])) {
            $validated['gdrive_folder_id'] = GoogleDriveService::cleanFolderId($validated['gdrive_folder_id']);
        }

        $validated['user_id'] = auth()->id();

        $gallery = Gallery::create($validated);

        if ($autoSync) {
            try {
                $result = $driveService->syncGalleryPhotos($gallery);
                return redirect()
                    ->route('admin.galleries.show', $gallery)
                    ->with('success', "Galeri berhasil dibuat dan {$result['synced']} foto berhasil disinkronkan dari Google Drive.");
            } catch (Exception $e) {
                return redirect()
                    ->route('admin.galleries.show', $gallery)
                    ->with('warning', "Galeri berhasil dibuat, namun sinkronisasi otomatis gagal: " . $e->getMessage());
            }
        }

        return redirect()
            ->route('admin.galleries.show', $gallery)
            ->with('success', 'Galeri berhasil dibuat. Silakan lakukan sinkronisasi foto dari Google Drive.');
    }

    /**
     * Display the specified gallery with photos, stats, and Lightroom export query.
     */
    public function show(Gallery $gallery): View
    {
        $this->authorizeGallery($gallery);

        $gallery->load(['photos']);

        $totalPhotos = $gallery->photos->count();
        $selectedPhotos = $gallery->photos->where('is_selected', true);
        $selectedCount = $selectedPhotos->count();
        $photosWithNotes = $gallery->photos->filter(function ($photo) {
            return !empty(trim($photo->client_note ?? ''));
        });

        $lightroomQuery = $selectedPhotos->pluck('filename')->implode(', ');
        $lightroomQueryFilenamesOnly = $selectedPhotos->map(function ($photo) {
            return pathinfo($photo->filename, PATHINFO_FILENAME);
        })->implode(', ');

        return view('admin.galleries.show', compact(
            'gallery',
            'totalPhotos',
            'selectedPhotos',
            'selectedCount',
            'photosWithNotes',
            'lightroomQuery',
            'lightroomQueryFilenamesOnly'
        ));
    }

    /**
     * Show the form for editing the specified gallery.
     */
    public function edit(Gallery $gallery): View
    {
        $this->authorizeGallery($gallery);
        return view('admin.galleries.edit', compact('gallery'));
    }

    /**
     * Update the specified gallery in storage.
     */
    public function update(UpdateGalleryRequest $request, Gallery $gallery): RedirectResponse
    {
        $this->authorizeGallery($gallery);

        $validated = $request->validated();
        if (!empty($validated['gdrive_folder_id'])) {
            $validated['gdrive_folder_id'] = GoogleDriveService::cleanFolderId($validated['gdrive_folder_id']);
        }

        $gallery->update($validated);

        return redirect()
            ->route('admin.galleries.show', $gallery)
            ->with('success', 'Pengaturan galeri berhasil diperbarui.');
    }

    /**
     * Remove the specified gallery from storage.
     */
    public function destroy(Gallery $gallery): RedirectResponse
    {
        $this->authorizeGallery($gallery);

        $clientName = $gallery->client_name;
        $gallery->delete();

        return redirect()
            ->route('admin.galleries.index')
            ->with('success', "Galeri '{$clientName}' beserta seluruh datanya berhasil dihapus.");
    }

    /**
     * Trigger Google Drive metadata synchronization.
     */
    public function sync(Gallery $gallery, GoogleDriveService $driveService): RedirectResponse
    {
        $this->authorizeGallery($gallery);

        try {
            $result = $driveService->syncGalleryPhotos($gallery);

            return redirect()
                ->route('admin.galleries.show', $gallery)
                ->with('success', "Sinkronisasi Google Drive sukses! {$result['synced']} foto diperbarui (Total: {$result['total']} foto).");
        } catch (Exception $e) {
            return redirect()
                ->route('admin.galleries.show', $gallery)
                ->with('error', "Gagal melakukan sinkronisasi: " . $e->getMessage());
        }
    }

    /**
     * Reopen a submitted or completed gallery so the client can edit selections again.
     */
    public function reopen(Gallery $gallery): RedirectResponse
    {
        $this->authorizeGallery($gallery);

        $gallery->update([
            'status' => 'open',
        ]);

        return redirect()
            ->route('admin.galleries.show', $gallery)
            ->with('success', 'Galeri berhasil dibuka kembali. Klien sekarang dapat mengubah pilihan foto.');
    }

    /**
     * Mark a gallery as completed (proofing finalized and edited).
     */
    public function complete(Gallery $gallery): RedirectResponse
    {
        $this->authorizeGallery($gallery);

        $gallery->update([
            'status' => 'completed',
        ]);

        return redirect()
            ->route('admin.galleries.show', $gallery)
            ->with('success', 'Status galeri ditandai sebagai Selesai (Completed).');
    }

    /**
     * Ensure the authenticated user owns this gallery.
     */
    protected function authorizeGallery(Gallery $gallery): void
    {
        if ($gallery->user_id !== null && $gallery->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki hak akses ke galeri milik admin lain.');
        }
    }
}
