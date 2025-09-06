<?php

namespace App\Providers;

use App\Contracts\FileStorageService;
use App\Services\LaravelFileStorageService;
use App\Services\S3FileStorageService;
use Illuminate\Support\ServiceProvider;

class FileStorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(FileStorageService::class, function ($app) {
            $driver = config('filesystems.default');
            
            return match ($driver) {
                's3' => new S3FileStorageService(),
                'public', 'local' => new LaravelFileStorageService(),
                default => new LaravelFileStorageService(),
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}