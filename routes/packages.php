<?php

// routes for Packages, Packages.gz, Packages.bz2, Release, and Release.gpg

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get("/Packages", function () {
    $path = 'public/Packages';
    if (!Storage::exists($path)) {
        abort(404);
    }
    return response(Storage::get($path))
        ->header("Content-Type", "text/plain");
});

Route::get("/Packages.gz", function () {
    $path = 'public/Packages.gz';
    if (!Storage::exists($path)) {
        abort(404);
    }
    return response(Storage::get($path))
        ->header("Content-Type", "application/gzip");
});

Route::get("/Packages.bz2", function () {
    $path = 'public/Packages.bz2';
    if (!Storage::exists($path)) {
        abort(404);
    }
    return response(Storage::get($path))
        ->header("Content-Type", "application/x-bzip2");
});

Route::get("/Release", function () {
    $path = 'public/Release';
    if (!Storage::exists($path)) {
        abort(404);
    }
    return response(Storage::get($path))
        ->header("Content-Type", "text/plain");
});

Route::get("/Release.gpg", function () {
    $path = 'public/Release.gpg';
    if (!Storage::exists($path)) {
        abort(404);
    }
    return response(Storage::get($path))
        ->header("Content-Type", "application/pgp-signature");
});
