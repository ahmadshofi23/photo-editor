<?php

declare(strict_types=1);

use App\Http\Middleware\ValidateImageMime;
use App\Http\Controllers\Api\V1\ImageController;
use App\Http\Controllers\Api\V1\BlackWhiteController;
use App\Http\Controllers\Api\V1\ResizeController;
use App\Http\Controllers\Api\V1\CompressController;
use App\Http\Controllers\Api\V1\RemoveBackgroundController;
use App\Http\Controllers\Api\V1\ChangeBackgroundController;
use App\Http\Controllers\Api\V1\AutoContrastController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('/upload', [ImageController::class, 'upload'])
        ->middleware(['throttle:api-upload', \App\Http\Middleware\ValidateImageMime::class, \App\Http\Middleware\PreventMaliciousUpload::class]);
    Route::post('/batch-upload', [ImageController::class, 'batchUpload'])
        ->middleware(['throttle:api-upload', \App\Http\Middleware\PreventMaliciousUpload::class]);
    Route::get('/history', [ImageController::class, 'history']);
    Route::get('/download/{id}', [ImageController::class, 'download']);

    Route::middleware('throttle:api-editor')->group(function () {
        Route::post('/blackwhite', [BlackWhiteController::class, 'process']);
        Route::post('/resize', [ResizeController::class, 'process']);
        Route::post('/compress', [CompressController::class, 'process']);
        Route::post('/remove-background', [RemoveBackgroundController::class, 'process']);
        Route::post('/change-background', [ChangeBackgroundController::class, 'process']);
        Route::post('/auto-contrast', [AutoContrastController::class, 'process']);
    });

    Route::post('/images/{id}/reset', [ImageController::class, 'reset'])->name('api.images.reset');
    Route::delete('/images/{id}', [ImageController::class, 'destroy'])->name('api.images.destroy');
});

Route::get('/v1/secure-download', [\App\Modules\Download\Controllers\DownloadController::class, 'downloadSecure'])->name('api.download.secure');

