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

        // Reset edited_path for the specific image being printed (if image_id is passed).
        // This marks the "end of a session" — when user returns to dashboard, status shows Original.
        // The actual edited files remain on disk and in history; only the model pointer is cleared.
        $printImageId = $request->query('image_id');
        if ($printImageId) {
            Image::where('user_id', $user->id)
                 ->where('id', $printImageId)
                 ->whereNotNull('edited_path')
                 ->update(['edited_path' => null, 'status' => 'pending']);
        }

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

            // ── Session-based approach ────────────────────────────────────────────
            // A "session" = everything between two consecutive resize operations.
            // Each resize defines one print slot. Post-resize styling (B&W, auto_contrast)
            // within the same session updates that slot's file but doesn't create a new slot.
            // Pre-resize styling (applied before ANY resize) is excluded — the resize
            // captures that content as its input.
            //
            // Session boundary rules:
            //   resize(A) → [styling…] → resize(B) → [styling…]
            //   Session 1 output = last entry ≤ id of (resize(B) - 1) that has generated_path
            //   Session 2 output = last entry after resize(B) that has generated_path
            //
            // For images with no resize at all, we show the latest meaningful entry.
            // ─────────────────────────────────────────────────────────────────────

            $postResizeStylingTypes = ['black_white_converted', 'auto_contrast'];

            // Collect all resize entries as session boundary markers
            $resizeEntries = $allChronological->where('action_type', 'resized')->values();

            // Entries eligible to appear in print library (have a file, not BG-only ops)
            $eligible = $allChronological->filter(
                fn ($h) => isset($h->metadata['generated_path'])
                    && !in_array($h->action_type, $excludedActions)
            )->values();

            if ($resizeEntries->isEmpty()) {
                // No resize at all — show latest eligible entry only
                $printHistories = $eligible->isEmpty() ? collect() : collect([$eligible->last()]);
            } else {
                // Build one slot per resize session.
                // Slot = the last eligible entry WITHIN that session window.
                // Session window for resize[i] = (resize[i].id … resize[i+1].id - 1)
                // i.e., from the resize itself up to (but not including) the next resize.
                $slots = collect();
                foreach ($resizeEntries as $idx => $resize) {
                    $nextResizeId = $resizeEntries->get($idx + 1)?->id ?? PHP_INT_MAX;
                    $isLastSession = $nextResizeId === PHP_INT_MAX;

                    // All eligible entries in this session window (>= resize.id, < nextResizeId)
                    $sessionEntries = $eligible->filter(
                        fn ($h) => $h->id >= $resize->id && $h->id < $nextResizeId
                    );

                    // If there is a next resize, any post-resize styling entries (B&W, auto_contrast)
                    // inside this window were applied as INPUT to that next resize, not as the
                    // final output of this session. Exclude them so the slot falls back to the
                    // resize entry itself (the only safe representative for a non-terminal session).
                    if (!$isLastSession) {
                        $sessionEntries = $sessionEntries->filter(
                            fn ($h) => !in_array($h->action_type, $postResizeStylingTypes)
                        );
                    }

                    if ($sessionEntries->isNotEmpty()) {
                        $slots->push($sessionEntries->last());
                    }
                }
                $printHistories = $slots;
            }

            // ── Build a stable group key per slot ─────────────────────────────────
            // Key = resize dimensions + background fingerprint + B&W flag.
            // Computed from the resize entry that opened the session (stable across re-edits).
            if ($printHistories->isNotEmpty()) {

                $grouped = $printHistories->groupBy(function ($h) use ($allChronological, $resizeEntries, $postResizeStylingTypes) {
                    // Find the resize that opened this session
                    $sessionResize = $resizeEntries->last(fn ($r) => $r->id <= $h->id);

                    $w  = $sessionResize ? ($sessionResize->metadata['width']  ?? 0) : ($h->metadata['width']  ?? 0);
                    $hh = $sessionResize ? ($sessionResize->metadata['height'] ?? 0) : ($h->metadata['height'] ?? 0);
                    $mode = $sessionResize
                        ? ($sessionResize->metadata['mode'] ?? $sessionResize->action_type)
                        : ($h->metadata['mode'] ?? $h->action_type);

                    // Styling fingerprint: only count ops strictly within this session's window.
                    // Session window = [sessionResize.id, nextResize.id)
                    // Anything outside this window belongs to another session.
                    $sessionStart = $sessionResize ? $sessionResize->id : 0;

                    // Find the next resize after this session to close the window
                    $nextResizeId = $resizeEntries->first(fn ($r) => $r->id > $sessionStart)?->id ?? PHP_INT_MAX;

                    // Only look at entries within this session's exact window
                    $windowEntries = $allChronological->filter(
                        fn ($p) => $p->id >= $sessionStart && $p->id < $nextResizeId
                    );

                    // BG was applied BEFORE the resize (as input). Look in the window just before sessionStart.
                    $prevSessionStart = $resizeEntries->last(fn ($r) => $r->id < $sessionStart)?->id ?? 0;
                    $lastBg = $allChronological->filter(
                        fn ($p) => in_array($p->action_type, ['change_background', 'remove_background'])
                                   && $p->id > $prevSessionStart
                                   && $p->id <= $sessionStart
                    )->last();

                    // B&W / auto_contrast within this session's window only.
                    // For non-terminal sessions, exclude post-resize styling entries that were
                    // applied as input to the NEXT resize (same exclusion as slot selection above).
                    $isLastSession = ($nextResizeId === PHP_INT_MAX);
                    $stylingWindow = $isLastSession
                        ? $windowEntries
                        : $windowEntries->filter(
                            fn ($p) => !in_array($p->action_type, $postResizeStylingTypes)
                        );

                    $lastBw = $stylingWindow->filter(
                        fn ($p) => $p->action_type === 'black_white_converted'
                    )->last();

                    $lastAc = $stylingWindow->filter(
                        fn ($p) => $p->action_type === 'auto_contrast'
                    )->last();

                    $bwThisSession = (bool) $lastBw;
                    $acThisSession = (bool) $lastAc;
                    $bgThisSession = (bool) $lastBg;

                    $bwSuffix = $bwThisSession ? '_bw' : '';
                    $acSuffix = $acThisSession ? '_ac' : '';

                    $bgSuffix = '';
                    if ($bgThisSession) {
                        if ($lastBg->action_type === 'remove_background') {
                            $bgSuffix = '_transparent';
                        } elseif (($lastBg->metadata['bg_type'] ?? '') === 'image') {
                            $bgSuffix = '_bg_img_' . substr(md5($lastBg->metadata['generated_path'] ?? ''), 0, 8);
                        } else {
                            $color    = $lastBg->metadata['bg_color'] ?? 'unknown';
                            $bgSuffix = '_bg_' . ltrim(strtolower($color), '#');
                        }
                    }

                    // Include the session's resize ID to ensure each resize session
                    // always gets its own unique slot, even if dimensions happen to match a previous session.
                    $sessionId = $sessionResize ? $sessionResize->id : 0;
                    return 'sess' . $sessionId . '_' . $mode . '_' . $w . 'x' . $hh . $bwSuffix . $acSuffix . $bgSuffix;
                });

                // Within each group take the LATEST entry (highest ID = most recent)
                foreach ($grouped as $groupKey => $groupHistories) {
                    $history  = $groupHistories->sortByDesc('id')->first();
                    $meta     = $history->metadata;
                    $path     = $meta['generated_path'];
                    $url      = asset('storage/' . $path);
                    $fileName = basename($path);
                    $nameOnly = pathinfo($fileName, PATHINFO_FILENAME);

                    // Always use the session's resize dimensions for consistent sizing.
                    // Post-resize styling (B&W, auto_contrast) doesn't change pixel dimensions.
                    $sessionResize = $resizeEntries->last(fn ($r) => $r->id <= $history->id);
                    if ($sessionResize) {
                        $widthPx  = $sessionResize->metadata['width']  ?? ($meta['width']  ?? null);
                        $heightPx = $sessionResize->metadata['height'] ?? ($meta['height'] ?? null);
                    } else {
                        $widthPx  = $meta['width']  ?? null;
                        $heightPx = $meta['height'] ?? null;
                    }

                    $widthCm  = $widthPx  ? round($widthPx  / $pxPerCm, 2) : null;
                    $heightCm = $heightPx ? round($heightPx / $pxPerCm, 2) : null;
                    $ar       = ($widthPx && $heightPx)
                                ? round($widthPx / $heightPx, 4)
                                : 1;

                    // Label comes from the resize entry that opened this session
                    $modeRaw   = $sessionResize
                        ? ($sessionResize->metadata['mode'] ?? $sessionResize->action_type)
                        : ($meta['mode'] ?? $history->action_type);
                    $baseLabel = ucwords(str_replace(['preset_', '_'], ['', ' '], $modeRaw));

                    // Append styling badges so library cards are distinguishable
                    $styleLabel = '';
                    if (str_contains($groupKey, '_bw'))          $styleLabel .= ' · Hitam Putih';
                    if (str_contains($groupKey, '_ac'))          $styleLabel .= ' · Auto Kontras';
                    if (str_contains($groupKey, '_transparent'))  $styleLabel .= ' · Transparan';
                    elseif (str_contains($groupKey, '_bg_img_'))  $styleLabel .= ' · Gambar Latar';
                    elseif (preg_match('/_bg_([0-9a-f]+)(_|$)/', $groupKey, $m)) $styleLabel .= ' · Latar #' . strtoupper($m[1]);

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

