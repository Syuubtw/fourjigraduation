<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProofingController extends Controller
{
    /**
     * Show PIN entry page if gallery is protected.
     */
    public function showPin(string $slug): View|RedirectResponse
    {
        $gallery = Gallery::where('slug', $slug)->firstOrFail();

        // If gallery has no PIN, redirect straight to gallery view
        if (!$gallery->isPinProtected()) {
            return redirect()->route('client.gallery.show', ['slug' => $slug]);
        }

        // If already unlocked in session, redirect to gallery view
        if (session()->has('unlocked_gallery_' . $gallery->id)) {
            return redirect()->route('client.gallery.show', ['slug' => $slug]);
        }

        return view('client.pin', compact('gallery'));
    }

    /**
     * Verify PIN submitted by client.
     */
    public function verifyPin(Request $request, string $slug): RedirectResponse
    {
        $gallery = Gallery::where('slug', $slug)->firstOrFail();

        $request->validate([
            'pin' => ['required', 'string'],
        ], [
            'pin.required' => 'Masukkan PIN akses untuk membuka galeri.',
        ]);

        if (trim($request->input('pin')) !== trim($gallery->pin)) {
            return back()->withErrors([
                'pin' => 'PIN akses salah. Silakan periksa kembali PIN yang diberikan oleh Fourji Graduation.',
            ])->withInput();
        }

        // Store unlocked state in session
        session(['unlocked_gallery_' . $gallery->id => true]);

        return redirect()->route('client.gallery.show', ['slug' => $slug]);
    }

    /**
     * Display client photo proofing gallery.
     */
    public function show(Request $request, string $slug): View
    {
        $gallery = Gallery::where('slug', $slug)
            ->with(['photos' => function ($q) {
                $q->orderBy('filename', 'asc');
            }])
            ->firstOrFail();

        $selectedCount = $gallery->photos->where('is_selected', true)->count();
        $totalPhotos = $gallery->photos->count();

        return view('client.gallery', compact('gallery', 'selectedCount', 'totalPhotos'));
    }

    /**
     * Toggle photo selection with strict transaction, pessimistic locking & quota validation.
     * Prevents race conditions and quota overflows.
     */
    public function togglePhoto(Request $request, string $slug, Photo $photo): JsonResponse
    {
        return DB::transaction(function () use ($slug, $photo) {
            $gallery = Gallery::where('slug', $slug)->lockForUpdate()->firstOrFail();

            // Ensure photo strictly belongs to this gallery
            if ($photo->gallery_id !== $gallery->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Foto tidak valid untuk galeri ini.',
                ], 404);
            }

            // Lock gallery status
            if ($gallery->status !== 'open') {
                return response()->json([
                    'success' => false,
                    'message' => match ($gallery->status) {
                        'submitted', 'completed' => 'Galeri ini sudah dikunci/selesai. Hubungi admin Fourji Graduation jika perlu mengubah pilihan.',
                        'draft' => 'Galeri ini belum dibuka untuk pemilihan foto.',
                        default => 'Pilihan foto sedang ditutup.',
                    },
                ], 403);
            }

            // Lock target photo record
            $lockedPhoto = Photo::where('id', $photo->id)->lockForUpdate()->firstOrFail();

            if (!$lockedPhoto->is_selected) {
                // Check quota before selecting
                $currentSelectedCount = Photo::where('gallery_id', $gallery->id)
                    ->where('is_selected', true)
                    ->count();

                if ($currentSelectedCount >= $gallery->max_select) {
                    return response()->json([
                        'success' => false,
                        'message' => "Batas kuota foto pilihan ({$gallery->max_select} foto) telah tercapai. Batalkan salah satu pilihan jika ingin memilih foto ini.",
                        'selected_count' => $currentSelectedCount,
                        'max_select' => $gallery->max_select,
                    ], 422);
                }

                $lockedPhoto->is_selected = true;
            } else {
                $lockedPhoto->is_selected = false;
            }

            $lockedPhoto->save();

            $newSelectedCount = Photo::where('gallery_id', $gallery->id)
                ->where('is_selected', true)
                ->count();

            return response()->json([
                'success'        => true,
                'photo_id'       => $lockedPhoto->id,
                'is_selected'    => $lockedPhoto->is_selected,
                'selected_count' => $newSelectedCount,
                'max_select'     => $gallery->max_select,
                'remaining'      => max(0, $gallery->max_select - $newSelectedCount),
            ]);
        });
    }

    /**
     * Save optional client retouching note for a photo.
     */
    public function updateNote(Request $request, string $slug, Photo $photo): JsonResponse
    {
        $gallery = Gallery::where('slug', $slug)->firstOrFail();

        if ($photo->gallery_id !== $gallery->id) {
            return response()->json([
                'success' => false,
                'message' => 'Foto tidak valid.',
            ], 404);
        }

        if ($gallery->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'Galeri telah dikunci. Catatan tidak dapat diubah.',
            ], 403);
        }

        $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $photo->client_note = $request->input('note');
        $photo->save();

        return response()->json([
            'success'     => true,
            'photo_id'    => $photo->id,
            'client_note' => $photo->client_note,
        ]);
    }

    /**
     * Submit and lock selections for final retouching.
     */
    public function submit(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $result = DB::transaction(function () use ($slug) {
            $gallery = Gallery::where('slug', $slug)->lockForUpdate()->firstOrFail();

            if ($gallery->status !== 'open') {
                return [
                    'status'  => 403,
                    'message' => 'Galeri ini sudah dikunci atau belum dibuka.',
                ];
            }

            $selectedCount = Photo::where('gallery_id', $gallery->id)
                ->where('is_selected', true)
                ->count();

            if ($selectedCount === 0) {
                return [
                    'status'  => 422,
                    'message' => 'Silakan pilih minimal 1 foto sebelum mengunci pilihan.',
                ];
            }

            $gallery->status = 'submitted';
            $gallery->submitted_at = now();
            $gallery->save();

            return [
                'status'  => 200,
                'message' => 'Pilihan foto berhasil dikunci!',
            ];
        });

        if ($result['status'] !== 200) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], $result['status']);
            }
            return back()->with('error', $result['message']);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'redirect' => route('client.gallery.success', ['slug' => $slug]),
            ]);
        }

        return redirect()->route('client.gallery.success', ['slug' => $slug]);
    }

    /**
     * Show submission success confirmation page.
     */
    public function success(string $slug): View
    {
        $gallery = Gallery::where('slug', $slug)->firstOrFail();
        $selectedCount = $gallery->photos()->where('is_selected', true)->count();

        return view('client.success', compact('gallery', 'selectedCount'));
    }
}
