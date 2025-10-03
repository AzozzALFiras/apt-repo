<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DebFileService
{
    protected $tempPath;
    protected $extractPath;

    public function __construct()
    {
        $this->tempPath = storage_path('app/temp');
        $this->extractPath = storage_path('app/public/tweaks/extracted');

        // Create directories if they don't exist
        if (!file_exists($this->tempPath)) {
            mkdir($this->tempPath, 0755, true);
        }
        if (!file_exists($this->extractPath)) {
            mkdir($this->extractPath, 0755, true);
        }
    }

    /**
     * Extract and parse .deb file
     */
    public function extractDebFile(UploadedFile $file, string $fileName): array
    {
        $tempFilePath = $this->tempPath . '/' . $fileName;
        $extractDir = $this->extractPath . '/' . pathinfo($fileName, PATHINFO_FILENAME);

        try {
            // Move uploaded file to temp location
            $file->move($this->tempPath, $fileName);

            // Create extraction directory
            if (!file_exists($extractDir)) {
                mkdir($extractDir, 0755, true);
            }

            // Extract .deb file using ar
            $this->extractWithAr($tempFilePath, $extractDir);

            // Extract control.tar.* files
            $controlData = $this->extractControlData($extractDir);

            // Extract data.tar.* files (optional, for file listing)
            $dataFiles = $this->extractDataFiles($extractDir);

            // Parse control file
            $controlInfo = $this->parseControlFile($extractDir . '/control');

            // Get icon if exists
            $icon = $this->extractIcon($extractDir);

            // Clean up temp file
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }

            $relativePath = 'tweaks/extracted/' . pathinfo($fileName, PATHINFO_FILENAME);

            return [
                'name' => $controlInfo['Package'] ?? null,
                'package' => $controlInfo['Package'] ?? null,
                'version' => $controlInfo['Version'] ?? null,
                'author' => $controlInfo['Author'] ?? null,
                'maintainer' => $controlInfo['Maintainer'] ?? null,
                'description' => $controlInfo['Description'] ?? null,
                'section' => $controlInfo['Section'] ?? null,
                'architecture' => $controlInfo['Architecture'] ?? null,
                'depends' => $controlInfo['Depends'] ?? null,
                'homepage' => $controlInfo['Homepage'] ?? null,
                'control' => $controlInfo,
                'data_files' => $dataFiles,
                'icon_path' => $icon,
                'extracted_path' => $relativePath,
            ];

        } catch (\Exception $e) {
            Log::error('DEB extraction failed: ' . $e->getMessage());

            // Cleanup on error
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            if (file_exists($extractDir)) {
                $this->recursiveDelete($extractDir);
            }

            throw $e;
        }
    }

    /**
     * Extract .deb file using ar command
     */
    protected function extractWithAr(string $debFile, string $extractDir): void
    {
        // Check if ar command is available
        $arAvailable = shell_exec('which ar') !== null;

        if ($arAvailable) {
            // Use ar command to extract
            $command = sprintf('ar x %s --output=%s 2>&1', escapeshellarg($debFile), escapeshellarg($extractDir));
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Failed to extract .deb file with ar: ' . implode("\n", $output));
            }
        } else {
            // Fallback: Manual extraction (basic implementation)
            $this->manualDebExtraction($debFile, $extractDir);
        }
    }

    /**
     * Manual .deb extraction (fallback method)
     */
    protected function manualDebExtraction(string $debFile, string $extractDir): void
    {
        // .deb files are ar archives containing control.tar.* and data.tar.*
        // This is a simplified extraction
        $handle = fopen($debFile, 'rb');
        if (!$handle) {
            throw new \Exception('Cannot open .deb file');
        }

        // Read ar header
        $globalHeader = fread($handle, 8);
        if ($globalHeader !== "!<arch>\n") {
            fclose($handle);
            throw new \Exception('Invalid .deb file format');
        }

        // Extract each member
        while (!feof($handle)) {
            $fileHeader = fread($handle, 60);
            if (strlen($fileHeader) < 60) {
                break;
            }

            $fileName = trim(substr($fileHeader, 0, 16));
            $fileSize = (int)trim(substr($fileHeader, 48, 10));

            if ($fileName && $fileSize > 0) {
                $fileData = fread($handle, $fileSize);
                $cleanFileName = str_replace(['/', ' '], '', $fileName);

                if ($cleanFileName) {
                    file_put_contents($extractDir . '/' . $cleanFileName, $fileData);
                }

                // ar entries are padded to even bytes
                if ($fileSize % 2 == 1) {
                    fread($handle, 1);
                }
            }
        }

        fclose($handle);
    }

    /**
     * Extract control.tar.* file
     */
    protected function extractControlData(string $extractDir): bool
    {
        $controlFiles = glob($extractDir . '/control.tar.*');

        foreach ($controlFiles as $controlFile) {
            $extension = pathinfo($controlFile, PATHINFO_EXTENSION);

            $command = match($extension) {
                'gz' => sprintf('tar -xzf %s -C %s 2>&1', escapeshellarg($controlFile), escapeshellarg($extractDir)),
                'xz' => sprintf('tar -xJf %s -C %s 2>&1', escapeshellarg($controlFile), escapeshellarg($extractDir)),
                'bz2' => sprintf('tar -xjf %s -C %s 2>&1', escapeshellarg($controlFile), escapeshellarg($extractDir)),
                default => sprintf('tar -xf %s -C %s 2>&1', escapeshellarg($controlFile), escapeshellarg($extractDir))
            };

            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract data.tar.* file and get file list
     */
    protected function extractDataFiles(string $extractDir): array
    {
        $dataFiles = glob($extractDir . '/data.tar.*');
        $fileList = [];

        foreach ($dataFiles as $dataFile) {
            $extension = pathinfo($dataFile, PATHINFO_EXTENSION);

            $command = match($extension) {
                'gz' => sprintf('tar -tzf %s 2>&1', escapeshellarg($dataFile)),
                'xz' => sprintf('tar -tJf %s 2>&1', escapeshellarg($dataFile)),
                'bz2' => sprintf('tar -tjf %s 2>&1', escapeshellarg($dataFile)),
                default => sprintf('tar -tf %s 2>&1', escapeshellarg($dataFile))
            };

            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                $fileList = $output;
                break;
            }
        }

        return $fileList;
    }

    /**
     * Parse control file
     */
    protected function parseControlFile(string $controlPath): array
    {
        if (!file_exists($controlPath)) {
            return [];
        }

        $content = file_get_contents($controlPath);
        $lines = explode("\n", $content);
        $data = [];
        $currentKey = null;

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            // Check if line starts with a space (continuation of previous field)
            if (preg_match('/^\s+(.*)$/', $line, $matches)) {
                if ($currentKey) {
                    $data[$currentKey] .= "\n" . trim($matches[1]);
                }
            } elseif (preg_match('/^([^:]+):\s*(.*)$/', $line, $matches)) {
                $currentKey = trim($matches[1]);
                $data[$currentKey] = trim($matches[2]);
            }
        }

        return $data;
    }

    /**
     * Extract icon from package
     */
    protected function extractIcon(string $extractDir): ?string
    {
        // Common icon locations in iOS tweaks
        $iconPatterns = [
            'icon.png',
            'Icon.png',
            '*/icon.png',
            '*/Icon.png',
        ];

        foreach ($iconPatterns as $pattern) {
            $icons = glob($extractDir . '/' . $pattern);
            if (!empty($icons)) {
                $iconPath = $icons[0];
                $relativePath = str_replace(storage_path('app/public/'), '', $iconPath);
                return $relativePath;
            }
        }

        return null;
    }

    /**
     * Recursively delete directory
     */
    protected function recursiveDelete(string $dir): void
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->recursiveDelete($path) : unlink($path);
        }

        rmdir($dir);
    }
}
