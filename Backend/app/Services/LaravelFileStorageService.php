<?php

namespace App\Services;

use App\Contracts\FileStorageService;
use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LaravelFileStorageService implements FileStorageService
{
    public function store(UploadedFile $file, string $folder): File
    {
        $path = Storage::disk('public')->putFile($folder, $file);

        return File::create([
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'mimetype' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function replace(int $fileId, UploadedFile $newFile): File
    {
        $existing = File::findOrFail($fileId);
    
        // Delete old file
        Storage::disk('public')->delete($existing->path);
    
        // Store new file in the same folder as original
        $folder = dirname($existing->path); // e.g., 'documents' from 'documents/foo.pdf'
        $path = Storage::disk('public')->putFile($folder, $newFile);
    
        // Update DB record in-place
        $existing->update([
            'path' => $path,
            'name' => $newFile->getClientOriginalName(),
            'mimetype' => $newFile->getMimeType(),
            'size' => $newFile->getSize(),
        ]);
    
        return $existing;
    }
    

    public function delete(int $fileId): void
    {
        $file = File::findOrFail($fileId);
        Storage::disk('public')->delete($file->path);
        $file->delete();
    }

    public function getUrl(int $fileId): string
    {
        /** @var \Illuminate\Contracts\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $file = File::findOrFail($fileId);
        return $disk->url($file->path);
    }
}
