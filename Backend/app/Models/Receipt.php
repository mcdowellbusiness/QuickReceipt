<?php

namespace App\Models;

use App\Contracts\FileStorageService;
use App\Models\File;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $fillable = [
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'size_bytes',
        'checksum',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    /**
     * Get the file size in human readable format
     */
    public function getSizeFormattedAttribute(): string
    {
        $bytes = $this->size_bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the file extension
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_filename, PATHINFO_EXTENSION);
    }

    /**
     * Check if receipt is an image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if receipt is a PDF
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Get the URL for this receipt
     */
    public function getUrl(): ?string
    {
        if (!$this->path) {
            return null;
        }

        try {
            $fileRecord = File::where('path', $this->path)->first();
            if (!$fileRecord) {
                return null;
            }

            return app(FileStorageService::class)->getUrl($fileRecord->id);
        } catch (\Exception $e) {
            return null;
        }
    }
}