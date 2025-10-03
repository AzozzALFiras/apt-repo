<?php

namespace App\Services;

use App\Models\Tweak;
use App\Enums\ActiveEnums;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PackageRepositoryService
{
    protected string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.default', 'public');
    }

    /**
     * Generate all package repository files
     *
     * @return array Statistics about the generation
     */
    public function generatePackagesFiles(): array
    {
        try {
            // Get all active tweaks
            $tweaks = Tweak::where('is_active', ActiveEnums::YES)->get();

            if ($tweaks->isEmpty()) {
                Log::warning('No active tweaks found for package generation');
                return [
                    'success' => true,
                    'tweaks_count' => 0,
                    'files_generated' => [],
                    'message' => 'No active tweaks to publish'
                ];
            }

            // Generate Packages content
            $packagesContent = $this->buildPackagesContent($tweaks);

            // Write all package files
            $filesGenerated = $this->writePackageFiles($packagesContent);

            Log::info('Package files generated successfully', [
                'tweaks_count' => $tweaks->count(),
                'files' => $filesGenerated
            ]);

            return [
                'success' => true,
                'tweaks_count' => $tweaks->count(),
                'files_generated' => $filesGenerated,
                'message' => "Repository updated with {$tweaks->count()} active tweaks"
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate package files', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build the Packages file content from tweaks
     *
     * @param \Illuminate\Database\Eloquent\Collection $tweaks
     * @return string
     */
    protected function buildPackagesContent($tweaks): string
    {
        $packagesContent = '';

        foreach ($tweaks as $tweak) {
            $packagesContent .= $this->formatTweakForPackages($tweak) . "\n";
        }

        return $packagesContent;
    }

    /**
     * Write all package file formats
     *
     * @param string $content
     * @return array List of generated files
     */
    protected function writePackageFiles(string $content): array
    {
        $disk = Storage::disk($this->disk);
        $filesGenerated = [];

        // Write plain Packages file
        $disk->put('Packages', $content);
        $filesGenerated[] = 'Packages';

        // Write Packages.gz
        $disk->put('Packages.gz', gzencode($content, 9));
        $filesGenerated[] = 'Packages.gz';

        // Write Packages.bz2 (if bzip2 is available)
        if (function_exists('bzcompress')) {
            $disk->put('Packages.bz2', bzcompress($content, 9));
            $filesGenerated[] = 'Packages.bz2';
        }

        // Write Packages.xz (if xz compression is available)
        if ($this->isXzAvailable()) {
            $xzContent = $this->compressXz($content);
            if ($xzContent !== false) {
                $disk->put('Packages.xz', $xzContent);
                $filesGenerated[] = 'Packages.xz';
            }
        }

        return $filesGenerated;
    }

    /**
     * Format a tweak into Packages file format
     *
     * @param Tweak $tweak
     * @return string
     */
    protected function formatTweakForPackages(Tweak $tweak): string
    {
        $disk = Storage::disk($this->disk);
        $filePath = $tweak->deb_file_path;

        // Get file information
        $size = $this->getFileSize($filePath);
        $installedSize = $this->getInstalledSize($tweak, $filePath, $size);

        // Calculate checksums
        $checksums = $this->calculateChecksums($filePath);

        // Build package entry
        $lines = [];

        // Required fields
        $lines[] = "Package: {$tweak->package}";
        $lines[] = "Version: {$tweak->version}";
        $lines[] = "Architecture: {$tweak->architecture}";
        $lines[] = "Maintainer: {$tweak->maintainer}";

        // Optional fields
        if ($installedSize > 0) {
            $lines[] = "Installed-Size: {$installedSize}";
        }

        if (!empty($tweak->depends)) {
            $lines[] = "Depends: {$tweak->depends}";
        }

        if (!empty($tweak->conflicts)) {
            $lines[] = "Conflicts: {$tweak->conflicts}";
        }

        if (!empty($tweak->breaks)) {
            $lines[] = "Breaks: {$tweak->breaks}";
        }

        if (!empty($tweak->replaces)) {
            $lines[] = "Replaces: {$tweak->replaces}";
        }

        if (!empty($tweak->provides)) {
            $lines[] = "Provides: {$tweak->provides}";
        }

        if (!empty($tweak->enhances)) {
            $lines[] = "Enhances: {$tweak->enhances}";
        }

        // File information
        $lines[] = "Filename: {$filePath}";
        $lines[] = "Size: {$size}";
        $lines[] = "MD5sum: {$checksums['md5']}";
        $lines[] = "SHA1: {$checksums['sha1']}";
        $lines[] = "SHA256: {$checksums['sha256']}";

        // Package metadata
        $lines[] = "Section: {$tweak->section}";
        $lines[] = "Description: {$tweak->description}";
        $lines[] = "Author: {$tweak->author}";

        if (!empty($tweak->name)) {
            $lines[] = "Name: {$tweak->name}";
        }

        if (!empty($tweak->homepage)) {
            $lines[] = "Homepage: {$tweak->homepage}";
            $lines[] = "Depiction: {$tweak->homepage}";
        }

        if (!empty($tweak->icon_url)) {
            $lines[] = "Icon: {$tweak->icon_url}";
        }

        // Modern depiction URLs
        $lines[] = "SileoDepiction: " . route('api.v1.description.sileo', ['tweak' => $tweak->package]);

        if (!empty($tweak->native_depiction_url)) {
            $lines[] = "Native-Depiction: {$tweak->native_depiction_url}";
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * Get file size in bytes
     *
     * @param string|null $filePath
     * @return int
     */
    protected function getFileSize(?string $filePath): int
    {
        if (!$filePath) {
            return 0;
        }

        $disk = Storage::disk($this->disk);

        if (!$disk->exists($filePath)) {
            return 0;
        }

        return $disk->size($filePath);
    }

    /**
     * Get or estimate installed size
     *
     * @param Tweak $tweak
     * @param string|null $filePath
     * @param int $fileSize
     * @return int
     */
    protected function getInstalledSize(Tweak $tweak, ?string $filePath, int $fileSize): int
    {
        // Use stored installed size if available
        if (isset($tweak->installed_size) && $tweak->installed_size > 0) {
            return $tweak->installed_size;
        }

        // Estimate: installed size is typically 2-3x the compressed size
        // Return in KB
        if ($fileSize > 0) {
            return (int) round(($fileSize * 2.5) / 1024);
        }

        return 0;
    }

    /**
     * Calculate file checksums
     *
     * @param string|null $filePath
     * @return array
     */
    protected function calculateChecksums(?string $filePath): array
    {
        $checksums = [
            'md5' => '',
            'sha1' => '',
            'sha256' => ''
        ];

        if (!$filePath) {
            return $checksums;
        }

        $disk = Storage::disk($this->disk);

        if (!$disk->exists($filePath)) {
            return $checksums;
        }

        try {
            $fileContent = $disk->get($filePath);

            $checksums['md5'] = md5($fileContent);
            $checksums['sha1'] = sha1($fileContent);
            $checksums['sha256'] = hash('sha256', $fileContent);

        } catch (\Exception $e) {
            Log::error('Failed to calculate checksums', [
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
        }

        return $checksums;
    }

    /**
     * Check if XZ compression is available
     *
     * @return bool
     */
    protected function isXzAvailable(): bool
    {
        // Check if xz command is available
        $output = null;
        $returnVar = null;
        @exec('which xz', $output, $returnVar);

        return $returnVar === 0;
    }

    /**
     * Compress content using XZ
     *
     * @param string $content
     * @return string|false
     */
    protected function compressXz(string $content)
    {
        // Try to use xz command line tool
        $tempInput = tempnam(sys_get_temp_dir(), 'pkg_');
        $tempOutput = tempnam(sys_get_temp_dir(), 'pkg_');

        try {
            file_put_contents($tempInput, $content);

            $command = sprintf(
                'xz -9 -c %s > %s',
                escapeshellarg($tempInput),
                escapeshellarg($tempOutput)
            );

            exec($command, $output, $returnVar);

            if ($returnVar === 0 && file_exists($tempOutput)) {
                $compressed = file_get_contents($tempOutput);
                return $compressed;
            }

            return false;

        } finally {
            @unlink($tempInput);
            @unlink($tempOutput);
        }
    }
}
