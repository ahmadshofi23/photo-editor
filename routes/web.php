<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/debug-rembg', function () {
    // Buat test image kecil (1x1 px PNG)
    $testInput  = sys_get_temp_dir() . '/test_input.png';
    $testOutput = sys_get_temp_dir() . '/test_output.png';

    // Buat gambar 10x10 merah sebagai test
    $img = imagecreatetruecolor(10, 10);
    $red = imagecolorallocate($img, 255, 0, 0);
    imagefill($img, 0, 0, $red);
    imagepng($img, $testInput);
    imagedestroy($img);

    $cmd    = "U2NET_HOME=/opt/rembg-models /usr/local/bin/rembg i " . escapeshellarg($testInput) . " " . escapeshellarg($testOutput) . " 2>&1";
    $output = [];
    $exit   = -1;
    exec($cmd, $output, $exit);

    return response()->json([
        'rembg_version'  => trim(shell_exec('/usr/local/bin/rembg --version 2>&1')),
        'model_dir'      => array_values(array_filter(scandir('/opt/rembg-models') ?? [], fn($f) => $f !== '.' && $f !== '..')),
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
