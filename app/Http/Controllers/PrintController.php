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
     * Shows exactly ONE card per uploaded photo — the most recent edited version.
     * This prevents the library from accumulating duplicate entries every time
     * the user re-edits the same photo.
     */
    public function index(Request $request)
    {
        $user   = $request->user();
        $images = Image::where('user_id', $user->id)
                       ->with(['histories' => fn ($q) => $q->orderBy('id', 'desc')])
                       ->latest()
                       ->get();

        // 300 DPI: 1 inch = 2.54 cm  →  1 cm = 300 / 2.54 ≈ 118.11 px
        $pxPerCm = 118.11;

        $photos = collect();

        // Intermediate styling steps that are NOT meaningful standalone print outputs
        $excludedActions = ['remove_background', 'change_background'];

        foreach ($images as $img) {
            // All histories sorted oldest → newest (used for session analysis below)
            $allChronological = $img->histories->sortBy('id')->values();

            // IDs of resize histories — used to detect whether a B&W step was intermediate
            $resizeIds = $allChronological->where('action_type', 'resized')->pluck('id');

            // A black_white_converted entry is INTERMEDIATE if any resize (higher ID) follows it.
            // Intermediate B&W entries must be excluded — their visual result is already
            // captured in the subsequent resize entry (the resize was applied to the B&W image).
            $intermediateBwIds = $allChronological
                ->where('action_type', 'black_white_converted')
                ->filter(fn ($bw) => $resizeIds->some(fn ($rid) => $rid > $bw->id))
                ->pluck('id');

            $printHistories = $allChronological->filter(
                fn ($h) => isset($h->metadata['generated_path'])
                    && !in_array($h->action_type, $excludedActions)
                    && !$intermediateBwIds->contains($h->id)
            );

            if ($printHistories->isNotEmpty()) {

                // Group by: size + preset + full styling fingerprint.
                // Rules:
                //   • Same size + same style (done twice)  → ONE slot (latest wins)
                //   • Same size + B&W vs colored           → separate slots
                //   • Same size + red BG vs blue BG        → separate slots
                //   • Same size + no BG (transparent) vs colored → separate slots
                $grouped = $printHistories->groupBy(function ($h) use ($allChronological) {
                    $mode = $h->metadata['mode'] ?? $h->action_type;
                    $w    = $h->metadata['width']  ?? 0;
                    $hh   = $h->metadata['height'] ?? 0;

                    // Walk back through ALL histories before this entry to build a styling fingerprint.
                    // We look for the most recent change_background and black_white_converted
                    // that were applied in the same "session" (before the next resize).
                    $precedingAll = $allChronological->filter(fn ($p) => $p->id < $h->id);

                    // Most recent B&W flag
                    $lastBw = $precedingAll->last(fn ($p) => $p->action_type === 'black_white_converted');

                    // Most recent background change (color or transparent from remove_bg)
                    $lastBg = $precedingAll->last(
                        fn ($p) => in_array($p->action_type, ['change_background', 'remove_background'])
                    );

                    // Find the last RESIZE before $h — only resize entries define session boundaries.
                    // B&W and other styling steps must NOT count as session boundaries,
                    // otherwise the B&W suffix detection breaks when B&W precedes resize.
                    $prevResize = $precedingAll->last(
                        fn ($p) => $p->action_type === 'resized'
                    );
                    $sessionStart = $prevResize ? $prevResize->id : 0;

                    // Only count styling applied AFTER the previous resize (= this session)
                    $bwThisSession = $lastBw && $lastBw->id > $sessionStart;
                    $bgThisSession = $lastBg && $lastBg->id > $sessionStart;

                    $bwSuffix = $bwThisSession ? '_bw' : '';

                    $bgSuffix = '';
                    if ($bgThisSession) {
                        if ($lastBg->action_type === 'remove_background') {
                            $bgSuffix = '_transparent';
                        } elseif (($lastBg->metadata['bg_type'] ?? '') === 'image') {
                            // Image background: hash the generated path as a unique fingerprint
                            $bgSuffix = '_bg_img_' . substr(md5($lastBg->metadata['generated_path'] ?? ''), 0, 8);
                        } else {
                            // Color background: use hex value as fingerprint (e.g. _bg_ff0000)
                            $color    = $lastBg->metadata['bg_color'] ?? 'unknown';
                            $bgSuffix = '_bg_' . ltrim(strtolower($color), '#');
                        }
                    }

                    return $mode . '_' . $w . 'x' . $hh . $bwSuffix . $bgSuffix;
                });

                // Within each group take the LATEST entry (highest ID = most recent)
                foreach ($grouped as $groupKey => $groupHistories) {
                    $history  = $groupHistories->sortByDesc('id')->first();
                    $meta     = $history->metadata;
                    $path     = $meta['generated_path'];
                    $url      = asset('storage/' . $path);
                    $fileName = basename($path);
                    $nameOnly = pathinfo($fileName, PATHINFO_FILENAME);

                    if ($history->action_type === 'black_white_converted') {
                        $widthPx  = $img->width  ?? ($meta['width']  ?? null);
                        $heightPx = $img->height ?? ($meta['height'] ?? null);
                    } else {
                        $widthPx  = $meta['width']  ?? null;
                        $heightPx = $meta['height'] ?? null;
                    }

                    $widthCm  = $widthPx  ? round($widthPx  / $pxPerCm, 2) : null;
                    $heightCm = $heightPx ? round($heightPx / $pxPerCm, 2) : null;
                    $ar       = ($widthPx && $heightPx)
                                ? round($widthPx / $heightPx, 4)
                                : 1;

                    $modeRaw   = $meta['mode'] ?? $history->action_type;
                    $baseLabel = ucwords(str_replace(['preset_', '_'], ['', ' '], $modeRaw));

                    // Append styling badges so library cards are distinguishable
                    $styleLabel = '';
                    if (str_contains($groupKey, '_bw'))          $styleLabel .= ' · Hitam Putih';
                    if (str_contains($groupKey, '_transparent'))  $styleLabel .= ' · Transparan';
                    elseif (str_contains($groupKey, '_bg_img_'))  $styleLabel .= ' · Gambar Latar';
                    elseif (preg_match('/_bg_([0-9a-f]+)$/', $groupKey, $m)) $styleLabel .= ' · Latar #' . strtoupper($m[1]);

                    $typeLabel = $baseLabel . $styleLabel;

                    $photos->push([
                        'id'                => $img->id,
                        // Stable key per image+size+style: same photo+size+style = same slot, no duplicates
                        'historyId'         => 'img_' . $img->id . '_' . md5($groupKey),
                        // Raw DB id of the latest history in this group — used by print page to auto-select
                        'latestHistoryDbId' => $history->id,
                        'name'              => $nameOnly,
                        'url'               => $url,
                        'type'              => $typeLabel,
                        'aspectRatio'       => $ar,
                        'widthPx'           => $widthPx,
                        'heightPx'          => $heightPx,
                        'widthCm'           => $widthCm,
                        'heightCm'          => $heightCm,
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

