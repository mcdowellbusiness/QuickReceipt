<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;
use App\Models\File;

interface FileStorageService
{
    public function store(UploadedFile $file, string $folder): File;

    public function replace(int $fileId, UploadedFile $newFile): File;
    
    public function delete(int $fileId): void;

    public function getUrl(int $fileId): string;
}
