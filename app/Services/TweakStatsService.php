<?php

namespace App\Services;

use App\Models\Tweak;
use Illuminate\Support\Facades\Storage;

/**
 * Service for calculating tweak statistics
 */
class TweakStatsService
{
    public function __construct(
        private readonly string $disk = 'public'
    ) {
    }

    public function getStatistics(): array
    {
        return [
            'total_tweaks' => $this->getTotalTweaks(),
            'total_size' => $this->getTotalSize(),
            'sections' => $this->getTotalSections(),
            'recent_uploads' => $this->getRecentUploads(),
        ];
    }

    private function getTotalTweaks(): int
    {
        return Tweak::count();
    }

    private function getTotalSize(): string
    {
        $totalBytes = Tweak::all()->sum(function ($tweak) {
            return $this->getFileSize($tweak->deb_file_path);
        });

        return $this->formatBytes($totalBytes);
    }

    private function getTotalSections(): int
    {
        return Tweak::distinct('section')->count('section');
    }

    private function getRecentUploads(int $days = 7): int
    {
        return Tweak::where('created_at', '>=', now()->subDays($days))->count();
    }

    private function getFileSize(?string $path): int
    {
        if (!$path || !Storage::disk($this->disk)->exists($path)) {
            return 0;
        }

        return Storage::disk($this->disk)->size($path);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
