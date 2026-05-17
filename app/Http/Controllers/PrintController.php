<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    /**
     * Show the print preview page.
     *
     * Each processed/cropped version (stored in image_histories) is exposed
     * as a SEPARATE photo card in the Print Library. This means:
     *  - Dashboard stays clean (1 record per upload)
     *  - Print Library shows ALL versions (3x4, 2x3, B&W, etc.)
     */
    public function index(Request $request)
    {
        $user   = $request->user();
        $images = Image::where('user_id', $user->id)
                       ->with(['histories' => fn ($q) => $q->orderBy('id', 'asc')])
                       ->latest()
                       ->get();

        // 300 DPI: 1 inch = 2.54 cm  →  1 cm = 300 / 2.54 ≈ 118.11 px
        $pxPerCm = 118.11;

        $photos = collect();

        foreach ($images as $img) {
            $resizeHistories = $img->histories->filter(
                fn ($h) => isset($h->metadata['generated_path'])
            );

            if ($resizeHistories->isNotEmpty()) {
                // ── Expand: one card per history entry ──────────────────
                foreach ($resizeHistories as $history) {
                    $meta     = $history->metadata;
                    $path     = $meta['generated_path'];
                    $url      = asset('storage/' . $path);
                    $fileName = basename($path);
                    $nameOnly = pathinfo($fileName, PATHINFO_FILENAME);

                    $widthPx  = $meta['width']  ?? null;
                    $heightPx = $meta['height'] ?? null;
                    $widthCm  = $widthPx  ? round($widthPx  / $pxPerCm, 2) : null;
                    $heightCm = $heightPx ? round($heightPx / $pxPerCm, 2) : null;
                    $ar       = ($widthPx && $heightPx)
                                ? round($widthPx / $heightPx, 4)
                                : 1;

                    // Human-readable type label from the history mode field
                    $modeRaw   = $meta['mode'] ?? $history->action_type;
                    $typeLabel = ucwords(str_replace(['preset_', '_'], ['', ' '], $modeRaw));

                    $photos->push([
                        'id'          => $img->id,        // original image id (for delete etc.)
                        'historyId'   => $history->id,    // unique per crop — used as queue key
                        'name'        => $nameOnly,
                        'url'         => $url,
                        'type'        => $typeLabel,
                        'aspectRatio' => $ar,
                        'widthPx'     => $widthPx,
                        'heightPx'    => $heightPx,
                        'widthCm'     => $widthCm,
                        'heightCm'    => $heightCm,
                    ]);
                }
            } else {
                // ── Fallback: image never edited → show as-is ───────────
                $path     = $img->edited_path ?? $img->original_path;
                $url      = asset('storage/' . $path);
                $fileName = basename($path);
                $nameOnly = pathinfo($fileName, PATHINFO_FILENAME);

                $widthPx  = $img->width  ?? null;
                $heightPx = $img->height ?? null;
                $widthCm  = $widthPx  ? round($widthPx  / $pxPerCm, 2) : null;
                $heightCm = $heightPx ? round($heightPx / $pxPerCm, 2) : null;
                $ar       = ($widthPx && $heightPx)
                            ? round($widthPx / $heightPx, 4)
                            : 1;

                $photos->push([
                    'id'          => $img->id,
                    'historyId'   => 'orig_' . $img->id,  // pseudo-id for unedited photos
                    'name'        => $nameOnly,
                    'url'         => $url,
                    'type'        => $img->edited_path ? 'Edited' : 'Original',
                    'aspectRatio' => $ar,
                    'widthPx'     => $widthPx,
                    'heightPx'    => $heightPx,
                    'widthCm'     => $widthCm,
                    'heightCm'    => $heightCm,
                ]);
            }
        }

        // Pre-selected image id (from query param — set by the "Print This Photo" button)
        $selectedId = $request->query('image_id');

        return view('editor.print', compact('photos', 'selectedId'));
    }
}

