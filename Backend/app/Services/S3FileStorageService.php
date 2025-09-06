<?php

namespace App\Services;

use App\Contracts\FileStorageService;
use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3FileStorageService implements FileStorageService
{
    protected string $disk;

    public function __construct(string $disk = 's3')
    {
        $this->disk = $disk;
    }

    public function store(UploadedFile $file, string $folder): File
    {
        // Generate unique filename to prevent conflicts
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $path = $folder . '/' . $filename;

        // Store file in S3
        Storage::disk($this->disk)->putFileAs($folder, $file, $filename);

        return File::create([
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'mimetype' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $this->disk,
        ]);
    }

    public function replace(int $fileId, UploadedFile $newFile): File
    {
        $existing = File::findOrFail($fileId);
    
        // Delete old file from S3
        Storage::disk($existing->disk ?? $this->disk)->delete($existing->path);
    
        // Generate new unique filename
        $extension = $newFile->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $folder = dirname($existing->path);
        $newPath = $folder . '/' . $filename;

        // Store new file in S3
        Storage::disk($this->disk)->putFileAs($folder, $newFile, $filename);
    
        // Update DB record in-place
        $existing->update([
            'path' => $newPath,
            'name' => $newFile->getClientOriginalName(),
            'mimetype' => $newFile->getMimeType(),
            'size' => $newFile->getSize(),
            'disk' => $this->disk,
        ]);
    
        return $existing;
    }

    public function delete(int $fileId): void
    {
        $file = File::findOrFail($fileId);
        Storage::disk($file->disk ?? $this->disk)->delete($file->path);
        $file->delete();
    }

    public function getUrl(int $fileId): string
    {
        $file = File::findOrFail($fileId);
        /** @var \Illuminate\Contracts\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($file->disk ?? $this->disk);
        
        // Generate temporary URL for S3 (expires in 1 hour)
        return $disk->temporaryUrl($file->path, now()->addHour());
    }

    /**
     * Get permanent URL for S3 (if bucket is public)
     */
    public function getPermanentUrl(int $fileId): string
    {
        $file = File::findOrFail($fileId);
        /** @var \Illuminate\Contracts\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($file->disk ?? $this->disk);
        
        return $disk->url($file->path);
    }
}
