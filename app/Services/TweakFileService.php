<?php

namespace App\Services;

use App\Models\Tweak;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling tweak file operations
 */
class TweakFileService
{
    public function __construct(
        private readonly string $disk = 'public',
        private readonly string $basePath = 'tweaks/deb_files'
    ) {
    }

    public function storeDebFile(UploadedFile $file): string
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs($this->basePath, $fileName, $this->disk);
    }

    public function cleanupTweakFiles(Tweak $tweak): void
    {
        $this->deleteFiles([
            $tweak->deb_file_path,
            $tweak->icon_path,
        ]);

        if ($tweak->extracted_path) {
            $this->deleteDirectory($tweak->extracted_path);
        }
    }

    public function cleanup(?string $filePath): void
    {
        if ($filePath) {
            $this->deleteFile($filePath);
        }
    }

    private function deleteFiles(array $paths): void
    {
        foreach (array_filter($paths) as $path) {
            $this->deleteFile($path);
        }
    }

    private function deleteFile(string $path): void
    {
        try {
            Storage::disk($this->disk)->delete($path);
        } catch (\Exception $e) {
            Log::warning("Failed to delete file: {$path}", ['error' => $e->getMessage()]);
        }
    }

    private function deleteDirectory(string $path): void
    {
        try {
            Storage::disk($this->disk)->deleteDirectory($path);
        } catch (\Exception $e) {
            Log::warning("Failed to delete directory: {$path}", ['error' => $e->getMessage()]);
        }
    }
}
