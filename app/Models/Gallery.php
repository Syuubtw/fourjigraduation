<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'slug',
        'pin',
        'gdrive_folder_id',
        'max_select',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'max_select'   => 'integer',
        'submitted_at' => 'datetime',
    ];

    /**
     * Boot model events for automatic slug generation if omitted.
     */
    protected static function booted(): void
    {
        static::creating(function (Gallery $gallery) {
            if (empty($gallery->slug)) {
                $baseSlug = Str::slug($gallery->client_name);
                $uniqueSlug = $baseSlug;
                $counter = 1;

                while (static::where('slug', $uniqueSlug)->exists()) {
                    $uniqueSlug = "{$baseSlug}-{$counter}";
                    $counter++;
                }

                $gallery->slug = $uniqueSlug;
            }
        });
    }

    /**
     * Relationship: All photos in this gallery.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('filename', 'asc');
    }

    /**
     * Relationship: Photos selected by client.
     */
    public function selectedPhotos(): HasMany
    {
        return $this->hasMany(Photo::class)
                    ->where('is_selected', true)
                    ->orderBy('filename', 'asc');
    }

    /**
     * Status Helper Checks
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPinProtected(): bool
    {
        return !empty($this->pin);
    }

    /**
     * Get Lightroom filename filter query string with extensions: "JER00004.DNG, JER00045.JPG".
     */
    public function getLightroomFilterQueryAttribute(): string
    {
        return $this->selectedPhotos()
                    ->pluck('filename')
                    ->implode(', ');
    }

    /**
     * Get Lightroom filename filter query string without extensions: "JER00004, JER00045".
     * Essential when client proofing is done on DNG/JPG but photographer searches across any format in Lightroom.
     */
    public function getLightroomFilterQueryFilenamesOnlyAttribute(): string
    {
        return $this->selectedPhotos()
                    ->get()
                    ->map(fn($p) => pathinfo($p->filename, PATHINFO_FILENAME))
                    ->implode(', ');
    }

    /**
     * Get public client gallery URL.
     */
    public function getClientUrlAttribute(): string
    {
        return url("/g/{$this->slug}");
    }
}
