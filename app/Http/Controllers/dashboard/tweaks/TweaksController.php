<?php

namespace App\Http\Controllers\Dashboard\Tweaks;

use App\Models\Tweak;
use App\Models\Changelog;
use App\Enums\ActiveEnums;
use App\Services\DebFileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Dashboard\Tweaks\StoreTweakRequest;
use Illuminate\Http\Request;

class TweaksController extends Controller
{
    protected $debFileService;

    public function __construct(DebFileService $debFileService)
    {
        $this->debFileService = $debFileService;
    }

    public function index()
    {
        // Get statistics
        $stats = [
            'total_tweaks' => Tweak::count(),
            'total_size' => $this->getTotalSize(),
            'sections' => Tweak::distinct('section')->count('section'),
            'recent_uploads' => Tweak::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        $tweaks = Tweak::with('changeLogs')
            ->latest()
            ->paginate(15);

        return view('dashboard.tweaks.index', compact('tweaks', 'stats'));
    }

    public function create()
    {
        return view('dashboard.tweaks.create');
    }

    public function store(StoreTweakRequest $request)
    {
        DB::beginTransaction();

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();

            // Generate unique filename
            $fileName = time() . '_' . $originalName;

            // Store the original .deb file
            $filePath = $file->storeAs('tweaks/deb_files', $fileName, 'public');

            // Extract and parse .deb file
            $debInfo = $this->debFileService->extractDebFile($file, $fileName);

            // Check if package already exists
            $existingTweak = Tweak::where('package', $debInfo['package'])->first();

            if ($existingTweak) {
                return $this->handleExistingTweak($existingTweak, $debInfo, $filePath, $request);
            }

            // Create new tweak record
            $tweak = $this->createNewTweak($debInfo, $filePath);

            // Create initial changelog if provided
            if ($request->filled('changelog')) {
                Changelog::create([
                    'tweak_id' => $tweak->id,
                    'version' => $tweak->version,
                    'changelog' => $request->changelog,
                    'is_active' => ActiveEnums::YES,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('dashboard.tweaks.index')
                ->with('success', 'Tweak "' . $tweak->name . '" uploaded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up uploaded file if exists
            $this->cleanupFile($filePath ?? null);

            Log::error('Tweak upload failed: ' . $e->getMessage(), [
                'file' => $originalName ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to process tweak file: ' . $e->getMessage());
        }
    }

    public function show(Tweak $tweak)
    {
        $tweak->load(['changeLogs' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return view('dashboard.tweaks.show', compact('tweak'));
    }

    public function destroy(Tweak $tweak)
    {
        try {
            DB::beginTransaction();

            // Clean up files
            $this->cleanupOldTweakFiles($tweak);

            // Delete associated changelogs
            $tweak->changeLogs()->delete();

            // Delete tweak
            $tweak->delete();

            DB::commit();

            return redirect()
                ->route('dashboard.tweaks.index')
                ->with('success', 'Tweak "' . $tweak->name . '" deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Tweak deletion failed: ' . $e->getMessage());

            return back()
                ->with('error', 'Failed to delete tweak: ' . $e->getMessage());
        }
    }

    /**
     * Handle existing tweak update
     */
    protected function handleExistingTweak($existingTweak, $debInfo, $filePath, $request)
    {
        // Compare versions
        $versionComparison = version_compare($debInfo['version'], $existingTweak->version);

        if ($versionComparison > 0) {
            // New version is higher - update the existing tweak
            $oldVersion = $existingTweak->version;

            // Clean up old files
            $this->cleanupOldTweakFiles($existingTweak);

            // Update tweak with new information
            $existingTweak->update([
                'version' => $debInfo['version'],
                'description' => $debInfo['description'] ?? $existingTweak->description,
                'author' => $debInfo['author'] ?? $existingTweak->author,
                'maintainer' => $debInfo['maintainer'] ?? $existingTweak->maintainer,
                'section' => $debInfo['section'] ?? 'Tweaks',
                'architecture' => $debInfo['architecture'] ?? $existingTweak->architecture,
                'depends' => $debInfo['depends'] ?? $existingTweak->depends,
                'homepage' => $debInfo['homepage'] ?? $existingTweak->homepage,
                'icon_url' => $debInfo['control']['Icon'] ?? $existingTweak->icon_url,
                'header_url' => $debInfo['control']['Header'] ?? null,
                'sileo_depiction' => $debInfo['control']['SileoDepiction'] ?? null,
                'installed_size' => $debInfo['control']['Installed-Size'] ?? $existingTweak->installed_size,
                'deb_file_path' => $filePath,
                'extracted_path' => $debInfo['extracted_path'] ?? null,
                'icon_path' => $debInfo['icon_path'] ?? null,
                'data_files' => json_encode($debInfo['data_files'] ?? []),
                'control_data' => json_encode($debInfo['control'] ?? []),
            ]);

            // Create changelog for update
            if ($request->filled('changelog')) {
                Changelog::create([
                    'tweak_id' => $existingTweak->id,
                    'version' => $debInfo['version'],
                    'changelog' => $request->changelog,
                    'is_active' => ActiveEnums::YES,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('dashboard.tweaks.index')
                ->with('success', 'Tweak "' . $existingTweak->name . '" updated from version ' . $oldVersion . ' to ' . $debInfo['version'] . '!');

        } elseif ($versionComparison === 0) {
            // Same version
            DB::rollBack();
            $this->cleanupFile($filePath);

            return back()
                ->withInput()
                ->with('error', 'A tweak with package "' . $debInfo['package'] . '" already exists with the same version ' . $existingTweak->version . '.');
        } else {
            // Older version
            DB::rollBack();
            $this->cleanupFile($filePath);

            return back()
                ->withInput()
                ->with('error', 'A tweak with package "' . $debInfo['package'] . '" already exists with a newer version ' . $existingTweak->version . '. Uploaded version: ' . $debInfo['version']);
        }
    }

    /**
     * Create a new tweak record
     */
    protected function createNewTweak($debInfo, $filePath)
    {
        return Tweak::create([
            'package' => $debInfo['package'],
            'name' => $debInfo['name'],
            'version' => $debInfo['version'],
            'description' => $debInfo['description'] ?? null,
            'author' => $debInfo['author'] ?? null,
            'maintainer' => $debInfo['maintainer'] ?? null,
            'section' => $debInfo['section'] ?? 'Tweaks',
            'architecture' => $debInfo['architecture'] ?? null,
            'depends' => $debInfo['depends'] ?? null,
            'homepage' => $debInfo['homepage'] ?? null,
            'icon_url' => $debInfo['control']['Icon'] ?? null,
            'header_url' => $debInfo['control']['Header'] ?? null,
            'sileo_depiction' => $debInfo['control']['SileoDepiction'] ?? null,
            'installed_size' => $debInfo['control']['Installed-Size'] ?? null,
            'deb_file_path' => $filePath,
            'extracted_path' => $debInfo['extracted_path'] ?? null,
            'icon_path' => $debInfo['icon_path'] ?? null,
            'data_files' => json_encode($debInfo['data_files'] ?? []),
            'control_data' => json_encode($debInfo['control'] ?? []),
        ]);
    }

    /**
     * Clean up old tweak files
     */
    protected function cleanupOldTweakFiles($tweak)
    {
        $filesToDelete = array_filter([
            $tweak->deb_file_path,
            $tweak->icon_path,
        ]);

        foreach ($filesToDelete as $file) {
            try {
                Storage::disk('public')->delete($file);
            } catch (\Exception $e) {
                Log::warning('Failed to delete old file: ' . $file, ['error' => $e->getMessage()]);
            }
        }

        // Clean up extracted directory if exists
        if ($tweak->extracted_path) {
            try {
                Storage::disk('public')->deleteDirectory($tweak->extracted_path);
            } catch (\Exception $e) {
                Log::warning('Failed to delete old extracted directory: ' . $tweak->extracted_path, ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Clean up a single file
     */
    protected function cleanupFile($filePath)
    {
        if ($filePath) {
            try {
                Storage::disk('public')->delete($filePath);
            } catch (\Exception $e) {
                Log::warning('Failed to cleanup file: ' . $filePath, ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Get total size of all tweaks
     */
    protected function getTotalSize()
    {
        $totalBytes = 0;
        $tweaks = Tweak::all();

        foreach ($tweaks as $tweak) {
            if ($tweak->deb_file_path && Storage::disk('public')->exists($tweak->deb_file_path)) {
                $totalBytes += Storage::disk('public')->size($tweak->deb_file_path);
            }
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $totalBytes > 0 ? floor(log($totalBytes, 1024)) : 0;
        return number_format($totalBytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
