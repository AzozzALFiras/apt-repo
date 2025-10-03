<?php

namespace App\Http\Controllers\Dashboard\Tweaks;

use App\Models\Tweak;
use App\Models\Changelog;
use App\Enums\ActiveEnums;
use App\Services\DebFileService;
use App\Services\TweakFileService;
use App\Services\TweakStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use App\Http\Requests\Dashboard\Tweaks\{StoreTweakRequest, UpdateTweakRequest, ChangeLogRequest};

class TweaksController extends Controller
{
    public function __construct(
        private readonly DebFileService $debFileService,
        private readonly TweakFileService $fileService,
        private readonly TweakStatsService $statsService
    ) {
    }

    public function index(): View
    {
        return view('dashboard.tweaks.index', [
            'tweaks' => Tweak::with('changeLogs')->latest()->paginate(15),
            'stats' => $this->statsService->getStatistics(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.tweaks.create');
    }

    public function store(StoreTweakRequest $request): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $file = $request->file('file');
                $filePath = $this->fileService->storeDebFile($file);
                $debInfo = $this->debFileService->extractDebFile($file, basename($filePath));

                $existingTweak = Tweak::where('package', $debInfo['package'])->first();

                if ($existingTweak) {
                    return $this->handleExistingTweak($existingTweak, $debInfo, $filePath, $request);
                }

                $tweak = $this->createTweak($debInfo, $filePath);
                $this->createChangelogIfProvided($tweak, $request);

                return redirect()
                    ->route('dashboard.tweaks.index')
                    ->with('success', "Tweak \"{$tweak->name}\" uploaded successfully!");
            });
        } catch (\Exception $e) {
            $this->fileService->cleanup($filePath ?? null);

            Log::error('Tweak upload failed', [
                'file' => $file?->getClientOriginalName() ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', "Failed to process tweak file: {$e->getMessage()}");
        }
    }

    public function show(Tweak $tweak): View
    {
        return view('dashboard.tweaks.show', [
            'tweak' => $tweak->load(['changeLogs' => fn ($q) => $q->latest()]),
        ]);
    }

    public function edit(Tweak $tweak): View
    {
        return view('dashboard.tweaks.edit', [
            'tweak' => $tweak->load(['changeLogs' => fn ($q) => $q->latest()]),
        ]);
    }

    public function update(UpdateTweakRequest $request, Tweak $tweak): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($request, $tweak) {
                $oldVersion = $tweak->version;
                $versionChanged = false;

                if ($request->hasFile('file')) {
                    $versionChanged = $this->updateFromDebFile($request, $tweak);
                } else {
                    $versionChanged = $this->updateManually($request, $tweak);
                }

                $this->handleChangelogCreation($request, $tweak, $versionChanged);

                $message = $versionChanged
                    ? "Tweak \"{$tweak->name}\" updated from version {$oldVersion} to {$tweak->version}!"
                    : "Tweak \"{$tweak->name}\" updated successfully!";

                return redirect()
                    ->route('dashboard.tweaks.show', $tweak)
                    ->with('success', $message);
            });
        } catch (\Exception $e) {
            Log::error('Tweak update failed', [
                'tweak_id' => $tweak->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', "Failed to update tweak: {$e->getMessage()}");
        }
    }

    public function destroy(Tweak $tweak): RedirectResponse
    {
        try {
            DB::transaction(function () use ($tweak) {
                $this->fileService->cleanupTweakFiles($tweak);
                $tweak->changeLogs()->delete();
                $tweak->delete();
            });

            return redirect()
                ->route('dashboard.tweaks.index')
                ->with('success', "Tweak \"{$tweak->name}\" deleted successfully!");
        } catch (\Exception $e) {
            Log::error('Tweak deletion failed', [
                'tweak_id' => $tweak->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', "Failed to delete tweak: {$e->getMessage()}");
        }
    }

    public function addChangelog(ChangeLogRequest $request, Tweak $tweak): RedirectResponse
    {
        try {
            $this->createChangelog($tweak, $request->version, $request->changelog);
            return back()->with('success', 'Changelog added successfully!');
        } catch (\Exception $e) {
            return back()->with('error', "Failed to add changelog: {$e->getMessage()}");
        }
    }

    public function updateChangelog(ChangeLogRequest $request, Tweak $tweak, Changelog $changelog): RedirectResponse
    {
        try {
            $changelog->update([
                'version' => $request->version,
                'changelog' => $this->formatChangelog($request->changelog),
            ]);

            return back()->with('success', 'Changelog updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', "Failed to update changelog: {$e->getMessage()}");
        }
    }

    public function deleteChangelog(Tweak $tweak, Changelog $changelog): RedirectResponse
    {
        try {
            $changelog->delete();
            return back()->with('success', 'Changelog deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', "Failed to delete changelog: {$e->getMessage()}");
        }
    }

    private function handleExistingTweak(
        Tweak $existingTweak,
        array $debInfo,
        string $filePath,
        StoreTweakRequest $request
    ): RedirectResponse {
        $comparison = version_compare($debInfo['version'], $existingTweak->version);

        return match (true) {
            $comparison > 0 => $this->upgradeExistingTweak($existingTweak, $debInfo, $filePath, $request),
            $comparison === 0 => $this->handleDuplicateVersion($debInfo, $existingTweak, $filePath),
            default => $this->handleOlderVersion($debInfo, $existingTweak, $filePath),
        };
    }

    private function upgradeExistingTweak(
        Tweak $tweak,
        array $debInfo,
        string $filePath,
        StoreTweakRequest $request
    ): RedirectResponse {
        $oldVersion = $tweak->version;

        $this->fileService->cleanupTweakFiles($tweak);
        $tweak->update($this->prepareTweakData($debInfo, $filePath));
        $this->createChangelogIfProvided($tweak, $request, $debInfo['version']);

        return redirect()
            ->route('dashboard.tweaks.index')
            ->with('success', "Tweak \"{$tweak->name}\" updated from version {$oldVersion} to {$debInfo['version']}!");
    }

    private function handleDuplicateVersion(array $debInfo, Tweak $tweak, string $filePath): RedirectResponse
    {
        $this->fileService->cleanup($filePath);

        return back()
            ->withInput()
            ->with('error', "A tweak with package \"{$debInfo['package']}\" already exists with the same version {$tweak->version}.");
    }

    private function handleOlderVersion(array $debInfo, Tweak $tweak, string $filePath): RedirectResponse
    {
        $this->fileService->cleanup($filePath);

        return back()
            ->withInput()
            ->with('error', "A tweak with package \"{$debInfo['package']}\" already exists with a newer version {$tweak->version}. Uploaded version: {$debInfo['version']}");
    }

    private function createTweak(array $debInfo, string $filePath): Tweak
    {
        return Tweak::create($this->prepareTweakData($debInfo, $filePath));
    }

    private function prepareTweakData(array $debInfo, string $filePath): array
    {
        return [
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
        ];
    }

    private function updateFromDebFile(UpdateTweakRequest $request, Tweak $tweak): bool
    {
        $file = $request->file('file');
        $filePath = $this->fileService->storeDebFile($file);
        $debInfo = $this->debFileService->extractDebFile($file, basename($filePath));

        $this->validatePackageMatch($debInfo['package'], $tweak->package, $filePath);
        $versionChanged = $this->validateVersionUpdate($debInfo['version'], $tweak->version, $filePath);

        if ($versionChanged) {
            $this->fileService->cleanupTweakFiles($tweak);
        }

        $tweak->update($this->prepareTweakData($debInfo, $filePath));

        return $versionChanged;
    }

    private function updateManually(UpdateTweakRequest $request, Tweak $tweak): bool
    {
        $updateData = $request->only(['name', 'description', 'author', 'maintainer', 'section', 'homepage']);
        $versionChanged = false;

        if ($request->filled('version') && $request->version !== $tweak->version) {
            $this->validateVersionIsNewer($request->version, $tweak->version);
            $updateData['version'] = $request->version;
            $versionChanged = true;
        }

        $tweak->update($updateData);

        return $versionChanged;
    }

    private function validatePackageMatch(string $newPackage, string $currentPackage, string $filePath): void
    {
        if ($newPackage !== $currentPackage) {
            $this->fileService->cleanup($filePath);
            throw new \Exception("Package ID mismatch. Expected: {$currentPackage}, Got: {$newPackage}");
        }
    }

    private function validateVersionUpdate(string $newVersion, string $currentVersion, string $filePath): bool
    {
        $comparison = version_compare($newVersion, $currentVersion);

        if ($comparison < 0) {
            $this->fileService->cleanup($filePath);
            throw new \Exception("New version ({$newVersion}) cannot be older than current version ({$currentVersion})");
        }

        return $comparison > 0;
    }

    private function validateVersionIsNewer(string $newVersion, string $currentVersion): void
    {
        if (version_compare($newVersion, $currentVersion) < 0) {
            throw new \Exception('New version cannot be older than current version');
        }
    }

    private function handleChangelogCreation(UpdateTweakRequest $request, Tweak $tweak, bool $versionChanged): void
    {
        if ($request->filled('changelog') && ($versionChanged || $request->boolean('force_changelog'))) {
            $this->createChangelog($tweak, $tweak->version, $request->changelog);
        }
    }

    private function createChangelogIfProvided(Tweak $tweak, StoreTweakRequest $request, ?string $version = null): void
    {
        if ($request->filled('changelog')) {
            $this->createChangelog($tweak, $version ?? $tweak->version, $request->changelog);
        }
    }

    private function createChangelog(Tweak $tweak, string $version, string $changelog): void
    {
        Changelog::create([
            'tweak_id' => $tweak->id,
            'version' => $version,
            'changelog' => $this->formatChangelog($changelog),
            'is_active' => ActiveEnums::YES,
        ]);
    }

    private function formatChangelog(string $changelog): string
    {
        return json_encode(preg_split('/\r\n|\r|\n/', $changelog));
    }
}
