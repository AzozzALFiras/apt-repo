<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\tweaks\TweaksController;

Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('tweaks', [TweaksController::class, 'index'])->name('dashboard.tweaks.index');
    Route::get('tweaks/create', [TweaksController::class, 'create'])->name('dashboard.tweaks.create');
    Route::post('tweaks', [TweaksController::class, 'store'])->name('dashboard.tweaks.store');
    Route::get('tweaks/{tweak}', [TweaksController::class, 'show'])->name('dashboard.tweaks.show');
    Route::delete('tweaks/{tweak}', [TweaksController::class, 'destroy'])->name('dashboard.tweaks.destroy');
    Route::get('tweaks/{tweak}/edit', [TweaksController::class, 'edit'])->name('dashboard.tweaks.edit');
    Route::put('tweaks/{tweak}', [TweaksController::class, 'update'])->name('dashboard.tweaks.update');
    // Changelog management routes
    Route::post('tweaks/{tweak}/changelog', [TweaksController::class, 'addChangelog'])->name('dashboard.tweaks.changelog.add');
    Route::put('tweaks/{tweak}/changelog/{changelog}', [TweaksController::class, 'updateChangelog'])->name('dashboard.tweaks.changelog.update');
    Route::delete('tweaks/{tweak}/changelog/{changelog}', [TweaksController::class, 'deleteChangelog'])->name('dashboard.tweaks.changelog.delete');
});
