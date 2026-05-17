<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Route::get('/dashboard', function () {
    $user = request()->user();
    $images = \App\Models\Image::where('user_id', $user->id)->with('histories')->latest()->get();
    
    $stats = [
        'total_upload' => $images->count(),
        'total_processed' => $images->whereNotNull('edited_path')->count(),
        'storage_used' => $images->sum('size'),
    ];

    return view('dashboard', compact('images', 'stats'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/upload', function () {
        return view('upload');
    })->name('upload.index');

    Route::get('/editor/blackwhite/{image}', [\App\Modules\BlackWhite\Controllers\BlackWhiteController::class, 'editView'])->name('editor.blackwhite');
    Route::get('/editor/resize/{image}', [\App\Modules\Resize\Controllers\ResizeController::class, 'editView'])->name('editor.resize');
    Route::get('/editor/compress/{image}', [\App\Modules\Compress\Controllers\CompressController::class, 'editView'])->name('editor.compress');
    Route::get('/editor/print', [\App\Http\Controllers\PrintController::class, 'index'])->name('editor.print');
});

require __DIR__.'/auth.php';
