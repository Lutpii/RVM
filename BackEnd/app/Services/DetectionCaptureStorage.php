<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Where a detection capture lives on the public disk, by review state:
 *
 *   captures/                          pending (not reviewed yet)
 *   captures/correct/<class>/          AI was right; <class> = its prediction
 *   captures/incorrect/<class>/        AI was wrong; <class> = actual material
 *   captures/incorrect/unlabeled/      old incorrect reviews with no material
 *
 * Shared by the admin review endpoint and `detection:organize-captures`, so
 * new reviews and already-reviewed captures end up in the same layout.
 */
class DetectionCaptureStorage
{
    public const ROOT = 'captures';
    public const UNLABELED = 'unlabeled';

    public static function directoryFor(?bool $correct, ?string $label): string
    {
        if ($correct === null) {
            return self::ROOT;
        }

        $result = $correct ? 'correct' : 'incorrect';
        // Labels come from a fixed list (AdminController::DETECTION_REVIEW_LABELS);
        // anything else must never become part of a path.
        $class = strtolower(trim((string) $label));
        if (!preg_match('/^[a-z_]+$/', $class)) {
            $class = self::UNLABELED;
        }

        return self::ROOT . "/{$result}/{$class}";
    }

    /**
     * Move a capture into $directory, keeping its file name unless a different
     * file already sits there (never overwrite: append the log id + time).
     * Returns the new relative path; throws if the file can't be moved.
     */
    public static function moveInto(string $source, string $directory, int $logId): string
    {
        $disk = Storage::disk('public');
        $target = $directory . '/' . basename($source);

        if ($source === $target) {
            return $source;
        }

        if ($disk->exists($target)) {
            $path = pathinfo($target);
            $extension = isset($path['extension']) ? '.' . $path['extension'] : '';
            $target = $directory . '/' . $path['filename'] . '-' . $logId . '-' . now()->format('YmdHisv') . $extension;
        }

        if (!$disk->move($source, $target)) {
            throw new \RuntimeException('Detection capture could not be moved.');
        }

        return $target;
    }
}
