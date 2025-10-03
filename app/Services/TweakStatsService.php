<?php

namespace App\Services;

use App\Enums\ActiveEnums;
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
    public function generatePackagesFiles()
    {
        // Get all active tweaks
        $tweaks = Tweak::where('is_active', ActiveEnums::YES)->get();

        // Generate Packages content
        $packagesContent = '';
        foreach ($tweaks as $tweak) {
            $packagesContent .= $this->formatTweakForPackages($tweak) . "\n";
        }

        // Write Packages file
        Storage::disk($this->disk)->put('Packages', $packagesContent);

        // Write Packages.gz
        Storage::disk($this->disk)->put('Packages.gz', gzencode($packagesContent));

        // Write Packages.bz2
        if (function_exists('bzcompress')) {
            Storage::disk($this->disk)->put('Packages.bz2', bzcompress($packagesContent));
        }

    }

    private function formatTweakForPackages(Tweak $tweak): string
    {
        $filePath = $tweak->deb_file_path;
        $disk = Storage::disk($this->disk);

        $size = $this->getFileSize($filePath);
        $installedSize = isset($tweak->installed_size) ? $tweak->installed_size : 0;
        if (!$installedSize && $filePath && $disk->exists($filePath)) {
            // Rough estimate: installed size in KB (deb size / 1024, rounded)
            $installedSize = (int) round($size / 1024);
        }

        $md5 = $sha1 = $sha256 = '';
        if ($filePath && $disk->exists($filePath)) {
            $fileContent = $disk->get($filePath);
            $md5 = md5($fileContent);
            $sha1 = sha1($fileContent);
            $sha256 = hash('sha256', $fileContent);
        }

        $lines = [];

        $lines[] = "Package: {$tweak->package}";
        $lines[] = "Version: {$tweak->version}";
        $lines[] = "Architecture: {$tweak->architecture}";
        $lines[] = "Maintainer: {$tweak->maintainer}";
        if (isset($tweak->installed_size)) {
            $lines[] = "Installed-Size: {$installedSize}";
        }
        if (!empty($tweak->depends)) {
            $lines[] = "Depends: {$tweak->depends}";
        }
        if (!empty($tweak->conflicts)) {
            $lines[] = "Conflicts: {$tweak->conflicts}";
        }
        if (!empty($tweak->enhances)) {
            $lines[] = "Enhances: {$tweak->enhances}";
        }
        $lines[] = "Filename: {$filePath}";
        $lines[] = "Size: {$size}";
        $lines[] = "MD5sum: {$md5}";
        $lines[] = "SHA1: {$sha1}";
        $lines[] = "SHA256: {$sha256}";
        $lines[] = "Section: {$tweak->section}";
        $lines[] = "Description: {$tweak->description}";
        $lines[] = "Author: {$tweak->author}";
        if (!empty($tweak->homepage)) {
            $lines[] = "Depiction: {$tweak->homepage}";
        }
        if (!empty($tweak->icon_url)) {
            $lines[] = "Icon: {$tweak->icon_url}";
        }
        if (!empty($tweak->name)) {
            $lines[] = "Name: {$tweak->name}";
        }
        // SileoDepiction (always present)
        $lines[] = "SileoDepiction: " . route('api.v1.description.sileo', ['tweak' => $tweak->package]);

        return implode("\n", $lines) . "\n";
    }
}
