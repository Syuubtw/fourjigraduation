<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'gallery_id',
        'gdrive_file_id',
        'filename',
        'thumbnail_url',
        'is_selected',
        'client_note',
    ];

    protected $casts = [
        'is_selected' => 'boolean',
    ];

    /**
     * Relationship: Associated gallery.
     */
    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    /**
     * High-speed thumbnail URL (w600).
     * Uses signed thumbnailLink from Google Drive API if available, or fallback to stream proxy.
     */
    public function getThumbnailUrlAttribute(): string
    {
        $raw = $this->attributes['thumbnail_url'] ?? null;
        if (!empty($raw)) {
            return preg_replace('/=s\d+$/', '=s600', $raw);
        }

        return route('photo.stream', ['photo' => $this->id, 'type' => 'thumb']);
    }

    /**
     * High-resolution lightbox preview URL (w1600).
     */
    public function getPreviewUrlAttribute(): string
    {
        $raw = $this->attributes['thumbnail_url'] ?? null;
        if (!empty($raw)) {
            return preg_replace('/=s\d+$/', '=s1600', $raw);
        }

        return route('photo.stream', ['photo' => $this->id, 'type' => 'preview']);
    }

    /**
     * Full-size preview URL (w2400).
     */
    public function getFullUrlAttribute(): string
    {
        $raw = $this->attributes['thumbnail_url'] ?? null;
        if (!empty($raw)) {
            return preg_replace('/=s\d+$/', '=s2400', $raw);
        }

        return route('photo.stream', ['photo' => $this->id, 'type' => 'full']);
    }

    /**
     * Stream URL served through local application cache proxy.
     */
    public function getStreamUrl(string $type = 'thumb'): string
    {
        return route('photo.stream', ['photo' => $this->id, 'type' => $type]);
    }

    /**
     * File extension in lowercase (e.g. 'dng', 'jpg').
     */
    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));
    }

    /**
     * Filename without extension (e.g. 'JER00004' from 'JER00004.DNG').
     */
    public function getFilenameWithoutExtensionAttribute(): string
    {
        return pathinfo($this->filename, PATHINFO_FILENAME);
    }

    /**
     * Check if photo is Adobe DNG format.
     */
    public function isDng(): bool
    {
        return $this->extension === 'dng';
    }

    /**
     * Check if photo is RAW camera format (DNG, CR2, CR3, ARW, NEF, etc.).
     */
    public function isRaw(): bool
    {
        return in_array($this->extension, ['dng', 'cr2', 'cr3', 'arw', 'nef', 'raf', 'rw2', 'orf', 'raw']);
    }
}
