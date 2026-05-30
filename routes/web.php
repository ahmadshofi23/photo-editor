<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/fix-admin-password', function () {
    $user = \App\Models\User::where('email', 'admin@admin.com')->first();
    
    if ($user) {
        $user->update([
            'password' => Hash::make('password') // Password Anda akan menjadi: password
        ]);
        return 'Sip! Password admin berhasil di-hash ulang oleh Laravel!';
    }
    
    return 'User admin@admin.com tidak ditemukan di database.';
});

Route::get('/dashboard', function () {
    $user = request()->user();
    $images = \App\Models\Image::where('user_id', $user->id)->with('histories')->latest()->get();

    $imageIds = $images->pluck('id');
    $stats = [
        'total_upload'     => $images->count(),
        'total_processed'  => $images->whereNotNull('edited_path')->count(),
        'storage_used'     => $images->sum('size'),
        'total_downloads'  => $imageIds->isNotEmpty()
            ? \App\Models\Download::whereIn('image_id', $imageIds)->count()
            : 0,
    ];

    return view('dashboard', compact('images', 'stats'));
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/upload', function () {
        return view('upload');
    })->name('upload.index');

    Route::get('/editor/blackwhite/{image}', [\App\Modules\BlackWhite\Controllers\BlackWhiteController::class, 'editView'])->name('editor.blackwhite');
    Route::get('/editor/resize/{image}', [\App\Modules\Resize\Controllers\ResizeController::class, 'editView'])->name('editor.resize');
    Route::get('/editor/compress/{image}', [\App\Modules\Compress\Controllers\CompressController::class, 'editView'])->name('editor.compress');
    Route::get('/editor/remove-background/{image}', [\App\Modules\RemoveBackground\Controllers\RemoveBackgroundController::class, 'editView'])->name('editor.remove-background');
    Route::get('/editor/print', [\App\Http\Controllers\PrintController::class, 'index'])->name('editor.print');
});

require __DIR__.'/auth.php';
