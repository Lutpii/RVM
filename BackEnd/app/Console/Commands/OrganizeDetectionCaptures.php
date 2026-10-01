<?php

namespace App\Console\Commands;

use App\Models\DetectionLog;
use App\Services\DetectionCaptureStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * One-off (safe to re-run) move of already-reviewed captures into the
 * per-class layout new reviews use (see DetectionCaptureStorage): the flat
 * captures/correct|incorrect folders and the oldest reviews left in the
 * captures root. Pending and mock captures are left alone.
 */
class OrganizeDetectionCaptures extends Command
{
    protected $signature = 'detection:organize-captures {--dry-run : List the moves without changing anything}';

    protected $description = 'File reviewed detection captures under captures/{correct,incorrect}/<class>/';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');
        $moved = $alreadyFiled = $missing = 0;

        $logs = DetectionLog::whereNotNull('reviewed_at')
            ->whereNotNull('ground_truth_correct')
            ->where('is_mock', false)
            ->whereNotNull('image_path')
            ->orderBy('id')
            ->get();

        foreach ($logs as $log) {
            $source = str_replace('\\', '/', trim($log->image_path));
            $directory = DetectionCaptureStorage::directoryFor(
                (bool) $log->ground_truth_correct,
                // a correct review's class is the prediction itself
                $log->ground_truth_label ?: ($log->ground_truth_correct ? $log->ai_detected_type : null)
            );

            if (dirname($source) === $directory) {
                $alreadyFiled++;
                continue;
            }
            if (!$disk->exists($source)) {
                $missing++;
                $this->warn("#{$log->id}: file missing, skipped ({$source})");
                continue;
            }

            if ($dryRun) {
                $this->line("#{$log->id}: {$source} -> {$directory}/");
                $moved++;
                continue;
            }

            $target = DetectionCaptureStorage::moveInto($source, $directory, $log->id);
            if (!$log->update(['image_path' => $target])) {
                $disk->move($target, $source); // keep file and database in step
                throw new \RuntimeException("Could not save the new path for detection log #{$log->id}.");
            }
            $moved++;
        }

        $verb = $dryRun ? 'Would move' : 'Moved';
        $this->info("{$verb} {$moved}, already filed {$alreadyFiled}, missing {$missing} (of {$logs->count()} reviewed).");

        return self::SUCCESS;
    }
}
