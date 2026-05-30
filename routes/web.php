<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/debug-image/{id}', function ($id) {
    $image = \App\Models\Image::find($id);
    if (!$image) return response()->json(['error' => 'Image not found in DB']);

    $sourcePath   = $image->edited_path ?? $image->original_path;
    $absolutePath = \Illuminate\Support\Facades\Storage::disk('public')->path($sourcePath);
    $storageRoot  = \Illuminate\Support\Facades\Storage::disk('public')->path('');

    return response()->json([
        'image_id'       => $image->id,
        'original_path'  => $image->original_path,
        'edited_path'    => $image->edited_path,
        'source_path'    => $sourcePath,
        'absolute_path'  => $absolutePath,
        'file_exists'    => file_exists($absolutePath),
        'storage_root'   => $storageRoot,
        'storage_root_is_symlink' => is_link(rtrim($storageRoot, '/')),
        'symlink_target' => is_link(rtrim($storageRoot, '/')) ? readlink(rtrim($storageRoot, '/')) : null,
        'data_dir_exists'=> is_dir('/data'),
        'data_storage_exists' => is_dir('/data/storage'),
        'data_files'     => is_dir('/data/storage') ? scandir('/data/storage') : 'N/A',
    ]);
});

Route::get('/debug-rembg', function () {
    $testInput  = sys_get_temp_dir() . '/test_input.png';
    $testOutput = sys_get_temp_dir() . '/test_output.png';

    $img = imagecreatetruecolor(10, 10);
    imagefill($img, 0, 0, imagecolorallocate($img, 255, 0, 0));
    imagepng($img, $testInput);
    imagedestroy($img);

    // Cek apakah exec() dinonaktifkan
    $disabledFunctions = explode(',', ini_get('disable_functions'));
    $execDisabled = in_array('exec', array_map('trim', $disabledFunctions));

    // Pakai U2NET_HOME dinamis sama seperti service
    $u2netHome = is_dir('/opt/rembg-models') ? '/opt/rembg-models' : (getenv('HOME') ?: sys_get_temp_dir());
    $cmd       = "U2NET_HOME=" . escapeshellarg($u2netHome) . " /usr/local/bin/rembg i " . escapeshellarg($testInput) . " " . escapeshellarg($testOutput) . " 2>&1";

    $output = [];
    $exit   = -1;
    if (!$execDisabled) {
        exec($cmd, $output, $exit);
    }

    return response()->json([
        'exec_disabled'  => $execDisabled,
        'disable_functions' => ini_get('disable_functions'),
        'rembg_bin'      => trim(shell_exec('which rembg 2>&1')),
        'rembg_version'  => trim(shell_exec('/usr/local/bin/rembg --version 2>&1')),
        'model_dir'      => is_dir('/opt/rembg-models') ? array_values(array_filter(scandir('/opt/rembg-models'), fn($f) => $f !== '.' && $f !== '..')) : 'NOT FOUND',
        'u2net_home'     => $u2netHome,
        'test_cmd'       => $cmd,
        'test_exit_code' => $exit,
        'test_output'    => implode("\n", $output),
        'output_exists'  => file_exists($testOutput),
        'tmp_writable'   => is_writable(sys_get_temp_dir()),
        'env_HOME'       => getenv('HOME'),
        'env_PATH'       => getenv('PATH'),
    ]);
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
