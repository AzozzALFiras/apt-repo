<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\tweaks\TweaksController;

Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('tweaks', [TweaksController::class, 'index'])->name('dashboard.tweaks.index');
    Route::get('tweaks/create', [TweaksController::class, 'create'])->name('dashboard.tweaks.create');
    Route::post('tweaks', [TweaksController::class, 'store'])->name('dashboard.tweaks.store');
    Route::get('tweaks/{tweak}', [TweaksController::class, 'show'])->name('dashboard.tweaks.show');
    Route::delete('tweaks/{tweak}', [TweaksController::class, 'destroy'])->name('dashboard.tweaks.destroy');
});
