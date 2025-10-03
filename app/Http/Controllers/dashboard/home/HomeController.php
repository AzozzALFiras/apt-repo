<?php

namespace App\Http\Controllers\Dashboard\home;

use App\Services\TweakStatsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Services\PackageRepositoryService;

class HomeController extends Controller
{
    protected PackageRepositoryService $packageService;

    public function __construct(PackageRepositoryService $packageService)
    {
        $this->packageService = $packageService;
    }
    public function index()
    {
        return view('dashboard.home.index', [
            'stats' => app(TweakStatsService::class)->getStatistics(),
        ]);
    }

    public function push(): RedirectResponse
    {
        $result = $this->packageService->generatePackagesFiles();

        if ($result['success']) {
            $message = $result['tweaks_count'] > 0
                ? "Repository updated successfully! Published {$result['tweaks_count']} active tweaks to " . implode(', ', $result['files_generated'])
                : "No active tweaks found to publish";

            return redirect()
                ->back()
                ->with('success', $message);
        }

        return redirect()
            ->back()
            ->with('error', 'Failed to update repository: ' . ($result['error'] ?? 'Unknown error'));
    }
}
