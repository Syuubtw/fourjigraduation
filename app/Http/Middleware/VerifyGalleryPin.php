<?php

namespace App\Http\Middleware;

use App\Models\Gallery;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyGalleryPin
{
    /**
     * Handle an incoming request.
     * Checks if the requested gallery requires a PIN and whether the current session has unlocked it.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('slug');
        $gallery = Gallery::where('slug', $slug)->first();

        if (!$gallery) {
            abort(404, 'Galeri foto tidak ditemukan.');
        }

        // Pass along gallery in request attribute for downstream efficiency
        $request->attributes->set('gallery', $gallery);

        // If no PIN protection is configured, grant immediate access
        if (!$gallery->isPinProtected()) {
            return $next($request);
        }

        // Check if session has unlocked this specific gallery
        $sessionKey = 'unlocked_gallery_' . $gallery->id;
        if ($request->session()->has($sessionKey)) {
            return $next($request);
        }

        // If this is an AJAX request, respond with 401 requiring PIN
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'PIN diperlukan untuk mengakses galeri ini.',
                'pin_required' => true,
            ], 401);
        }

        // Redirect to PIN entry form
        return redirect()->route('client.pin.show', ['slug' => $slug]);
    }
}
