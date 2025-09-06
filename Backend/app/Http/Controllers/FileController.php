<?php

namespace App\Http\Controllers;

use App\Contracts\FileStorageService;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    protected FileStorageService $fileStorageService;

    public function __construct(FileStorageService $fileStorageService)
    {
        $this->fileStorageService = $fileStorageService;
    }

    /**
     * Upload a file
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // 10MB max
            'folder' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $this->fileStorageService->store(
                $request->file('file'),
                $request->input('folder')
            );

            return response()->json([
                'message' => 'File uploaded successfully',
                'file' => $file,
                'url' => $this->fileStorageService->getUrl($file->id)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'File upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Replace a file
     */
    public function replace(Request $request, int $fileId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $this->fileStorageService->replace(
                $fileId,
                $request->file('file')
            );

            return response()->json([
                'message' => 'File replaced successfully',
                'file' => $file,
                'url' => $this->fileStorageService->getUrl($file->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'File replacement failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a file
     */
    public function delete(int $fileId): JsonResponse
    {
        try {
            $this->fileStorageService->delete($fileId);

            return response()->json([
                'message' => 'File deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'File deletion failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get file URL
     */
    public function getUrl(int $fileId): JsonResponse
    {
        try {
            $url = $this->fileStorageService->getUrl($fileId);

            return response()->json([
                'url' => $url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get file URL: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get file details
     */
    public function show(int $fileId): JsonResponse
    {
        try {
            $file = File::findOrFail($fileId);

            return response()->json([
                'file' => $file,
                'url' => $this->fileStorageService->getUrl($file->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'File not found'
            ], 404);
        }
    }
}
