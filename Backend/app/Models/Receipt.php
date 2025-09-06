<?php

namespace App\Models;

use App\Contracts\FileStorageService;
use App\Models\Budget;
use App\Models\File;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $fillable = [
        'budget_id',
        'file_id',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    /**
     * Get the file size in human readable format
     */
    public function getSizeFormattedAttribute(): string
    {
        return $this->file?->size_formatted ?? '0 B';
    }

    /**
     * Get the file extension
     */
    public function getExtensionAttribute(): string
    {
        return $this->file?->extension ?? '';
    }

    /**
     * Check if receipt is an image
     */
    public function isImage(): bool
    {
        return $this->file?->isImage() ?? false;
    }

    /**
     * Check if receipt is a PDF
     */
    public function isPdf(): bool
    {
        return $this->file?->isPdf() ?? false;
    }

    /**
     * Get the URL for this receipt
     */
    public function getUrl(): ?string
    {
        if (!$this->file) {
            return null;
        }

        try {
            return app(FileStorageService::class)->getUrl($this->file->id);
        } catch (\Exception $e) {
            return null;
        }
    }
}