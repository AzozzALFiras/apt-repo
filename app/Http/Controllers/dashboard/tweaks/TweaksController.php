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
use App\Http\Requests\Dashboard\Tweaks\UpdateTweakRequest;
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
            $fileName = time() . '_' . $originalName;
            $filePath = $file->storeAs('tweaks/deb_files', $fileName, 'public');
            $debInfo = $this->debFileService->extractDebFile($file, $fileName);
            $existingTweak = Tweak::where('package', $debInfo['package'])->first();

            if ($existingTweak) {
                return $this->handleExistingTweak($existingTweak, $debInfo, $filePath, $request);
            }

            $tweak = $this->createNewTweak($debInfo, $filePath);

            if ($request->filled('changelog')) {
                Changelog::create([
                    'tweak_id' => $tweak->id,
                    'version' => $tweak->version,
                    'changelog' => json_encode(preg_split('/\r\n|\r|\n/', $request->changelog)),
                    'is_active' => ActiveEnums::YES,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('dashboard.tweaks.index')
                ->with('success', 'Tweak "' . $tweak->name . '" uploaded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
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

    public function edit(Tweak $tweak)
    {
        $tweak->load(['changeLogs' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return view('dashboard.tweaks.edit', compact('tweak'));
    }

    public function update(UpdateTweakRequest $request, Tweak $tweak)
    {
        DB::beginTransaction();

        try {
            $oldVersion = $tweak->version;
            $versionChanged = false;

            // Check if new .deb file is uploaded
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . $originalName;
                $filePath = $file->storeAs('tweaks/deb_files', $fileName, 'public');
                $debInfo = $this->debFileService->extractDebFile($file, $fileName);

                // Verify package ID matches
                if ($debInfo['package'] !== $tweak->package) {
                    $this->cleanupFile($filePath);
                    throw new \Exception('Package ID mismatch. Expected: ' . $tweak->package . ', Got: ' . $debInfo['package']);
                }

                // Check version
                $versionComparison = version_compare($debInfo['version'], $oldVersion);
                if ($versionComparison < 0) {
                    $this->cleanupFile($filePath);
                    throw new \Exception('New version (' . $debInfo['version'] . ') cannot be older than current version (' . $oldVersion . ')');
                }

                if ($versionComparison > 0) {
                    $versionChanged = true;
                }

                // Clean up old files
                $this->cleanupOldTweakFiles($tweak);

                // Update with .deb file data
                $tweak->update([
                    'version' => $debInfo['version'],
                    'description' => $debInfo['description'] ?? $tweak->description,
                    'author' => $debInfo['author'] ?? $tweak->author,
                    'maintainer' => $debInfo['maintainer'] ?? $tweak->maintainer,
                    'section' => $debInfo['section'] ?? $tweak->section,
                    'architecture' => $debInfo['architecture'] ?? $tweak->architecture,
                    'depends' => $debInfo['depends'] ?? $tweak->depends,
                    'homepage' => $debInfo['homepage'] ?? $tweak->homepage,
                    'icon_url' => $debInfo['control']['Icon'] ?? $tweak->icon_url,
                    'header_url' => $debInfo['control']['Header'] ?? $tweak->header_url,
                    'sileo_depiction' => $debInfo['control']['SileoDepiction'] ?? $tweak->sileo_depiction,
                    'installed_size' => $debInfo['control']['Installed-Size'] ?? $tweak->installed_size,
                    'deb_file_path' => $filePath,
                    'extracted_path' => $debInfo['extracted_path'] ?? null,
                    'icon_path' => $debInfo['icon_path'] ?? null,
                    'data_files' => json_encode($debInfo['data_files'] ?? []),
                    'control_data' => json_encode($debInfo['control'] ?? []),
                ]);

            } else {
                // Manual update without .deb file
                $updateData = $request->only([
                    'name', 'description', 'author', 'maintainer',
                    'section', 'homepage'
                ]);

                // Check if version is being manually changed
                if ($request->filled('version') && $request->version !== $oldVersion) {
                    $versionComparison = version_compare($request->version, $oldVersion);
                    if ($versionComparison < 0) {
                        throw new \Exception('New version cannot be older than current version');
                    }
                    if ($versionComparison > 0) {
                        $versionChanged = true;
                        $updateData['version'] = $request->version;
                    }
                }

                $tweak->update($updateData);
            }

            // Create changelog if version changed or changelog provided
            if ($request->filled('changelog') && ($versionChanged || $request->force_changelog)) {
                Changelog::create([
                    'tweak_id' => $tweak->id,
                    'version' => $tweak->version,
                    'changelog' => json_encode(preg_split('/\r\n|\r|\n/', $request->changelog)),
                    'is_active' => ActiveEnums::YES,
                ]);
            }

            DB::commit();

            $message = $versionChanged
                ? 'Tweak "' . $tweak->name . '" updated from version ' . $oldVersion . ' to ' . $tweak->version . '!'
                : 'Tweak "' . $tweak->name . '" updated successfully!';

            return redirect()
                ->route('dashboard.tweaks.show', $tweak)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tweak update failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to update tweak: ' . $e->getMessage());
        }
    }

    public function destroy(Tweak $tweak)
    {
        try {
            DB::beginTransaction();
            $this->cleanupOldTweakFiles($tweak);
            $tweak->changeLogs()->delete();
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

    // Changelog Management Methods
    public function addChangelog(Request $request, Tweak $tweak)
    {
        $request->validate([
            'version' => 'required|string|max:50',
            'changelog' => 'required|string',
        ]);

        try {
            Changelog::create([
                'tweak_id' => $tweak->id,
                'version' => $request->version,
                'changelog' => json_encode(preg_split('/\r\n|\r|\n/', $request->changelog)),
                'is_active' => ActiveEnums::YES,
            ]);

            return back()->with('success', 'Changelog added successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add changelog: ' . $e->getMessage());
        }
    }

    public function updateChangelog(Request $request, Tweak $tweak, Changelog $changelog)
    {
        $request->validate([
            'version' => 'required|string|max:50',
            'changelog' => 'required|string',
        ]);

        try {
            $changelog->update([
                'version' => $request->version,
                'changelog' => json_encode(preg_split('/\r\n|\r|\n/', $request->changelog)),
            ]);

            return back()->with('success', 'Changelog updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update changelog: ' . $e->getMessage());
        }
    }

    public function deleteChangelog(Tweak $tweak, Changelog $changelog)
    {
        try {
            $changelog->delete();
            return back()->with('success', 'Changelog deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete changelog: ' . $e->getMessage());
        }
    }

    protected function handleExistingTweak($existingTweak, $debInfo, $filePath, $request)
    {
        $versionComparison = version_compare($debInfo['version'], $existingTweak->version);

        if ($versionComparison > 0) {
            $oldVersion = $existingTweak->version;
            $this->cleanupOldTweakFiles($existingTweak);

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

            if ($request->filled('changelog')) {
                Changelog::create([
                    'tweak_id' => $existingTweak->id,
                    'version' => $debInfo['version'],
                    'changelog' => json_encode(preg_split('/\r\n|\r|\n/', $request->changelog)),
                    'is_active' => ActiveEnums::YES,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('dashboard.tweaks.index')
                ->with('success', 'Tweak "' . $existingTweak->name . '" updated from version ' . $oldVersion . ' to ' . $debInfo['version'] . '!');

        } elseif ($versionComparison === 0) {
            DB::rollBack();
            $this->cleanupFile($filePath);

            return back()
                ->withInput()
                ->with('error', 'A tweak with package "' . $debInfo['package'] . '" already exists with the same version ' . $existingTweak->version . '.');
        } else {
            DB::rollBack();
            $this->cleanupFile($filePath);

            return back()
                ->withInput()
                ->with('error', 'A tweak with package "' . $debInfo['package'] . '" already exists with a newer version ' . $existingTweak->version . '. Uploaded version: ' . $debInfo['version']);
        }
    }

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

        if ($tweak->extracted_path) {
            try {
                Storage::disk('public')->deleteDirectory($tweak->extracted_path);
            } catch (\Exception $e) {
                Log::warning('Failed to delete old extracted directory: ' . $tweak->extracted_path, ['error' => $e->getMessage()]);
            }
        }
    }

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
