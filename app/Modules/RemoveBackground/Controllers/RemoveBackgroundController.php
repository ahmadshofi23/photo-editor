<?php

declare(strict_types=1);

namespace App\Modules\RemoveBackground\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;

class RemoveBackgroundController extends Controller
{
    public function editView(Request $request, Image $image)
    {
        if ($image->user_id !== $request->user()->id) {
            abort(403);
        }

        return view('editor.remove-background', compact('image'));
    }
}
