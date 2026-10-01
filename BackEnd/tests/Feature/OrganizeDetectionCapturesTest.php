<?php

namespace Tests\Feature;

use App\Models\DetectionLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrganizeDetectionCapturesTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, DetectionLog> */
    private function seedCaptures(): array
    {
        $reviewed = ['reviewed_at' => now()];
        $logs = [
            // flat result folders from before per-class filing
            'correct'   => ['captures/correct/a.jpg', ['ai_detected_type' => 'plastic', 'ground_truth_correct' => true, 'ground_truth_label' => 'plastic'] + $reviewed],
            'incorrect' => ['captures/incorrect/b.jpg', ['ai_detected_type' => 'unknown', 'ground_truth_correct' => false, 'ground_truth_label' => 'glass'] + $reviewed],
            // oldest reviews were left in the captures root
            'legacyRoot' => ['captures/c.jpg', ['ai_detected_type' => 'aluminum', 'ground_truth_correct' => true, 'ground_truth_label' => 'aluminum'] + $reviewed],
            // reviewed incorrect before an actual material was required
            'unlabeled' => ['captures/incorrect/u.jpg', ['ai_detected_type' => 'unknown', 'ground_truth_correct' => false, 'ground_truth_label' => null] + $reviewed],
            'pending'   => ['captures/d.jpg', ['ai_detected_type' => 'plastic']],
            'mock'      => ['captures/e.jpg', ['ai_detected_type' => 'plastic', 'is_mock' => true, 'ground_truth_correct' => true, 'ground_truth_label' => 'plastic'] + $reviewed],
            'organized' => ['captures/correct/glass/f.jpg', ['ai_detected_type' => 'glass', 'ground_truth_correct' => true, 'ground_truth_label' => 'glass'] + $reviewed],
            'missing'   => ['captures/correct/gone.jpg', ['ai_detected_type' => 'paper', 'ground_truth_correct' => true, 'ground_truth_label' => 'paper'] + $reviewed],
        ];

        $created = [];
        foreach ($logs as $key => [$path, $attributes]) {
            if ($key !== 'missing') {
                Storage::disk('public')->put($path, "bytes-{$key}");
            }
            $created[$key] = DetectionLog::create(['image_path' => $path] + $attributes);
        }

        return $created;
    }

    public function test_reviewed_captures_are_filed_under_their_class(): void
    {
        Storage::fake('public');
        $logs = $this->seedCaptures();

        $this->artisan('detection:organize-captures')->assertSuccessful();

        $expected = [
            'correct'    => 'captures/correct/plastic/a.jpg',
            'incorrect'  => 'captures/incorrect/glass/b.jpg',
            'legacyRoot' => 'captures/correct/aluminum/c.jpg',
            'unlabeled'  => 'captures/incorrect/unlabeled/u.jpg',
            'pending'    => 'captures/d.jpg',
            'mock'       => 'captures/e.jpg',
            'organized'  => 'captures/correct/glass/f.jpg',
            'missing'    => 'captures/correct/gone.jpg',
        ];
        foreach ($expected as $key => $path) {
            $this->assertSame($path, $logs[$key]->fresh()->image_path, $key);
        }

        $disk = Storage::disk('public');
        $disk->assertExists('captures/correct/plastic/a.jpg');
        $disk->assertMissing('captures/correct/a.jpg');
        $disk->assertExists('captures/incorrect/glass/b.jpg');
        $disk->assertExists('captures/correct/aluminum/c.jpg');
        $disk->assertMissing('captures/c.jpg');
        $disk->assertExists('captures/incorrect/unlabeled/u.jpg');
        $disk->assertExists('captures/d.jpg');
        $disk->assertExists('captures/e.jpg');
        $this->assertSame('bytes-correct', $disk->get('captures/correct/plastic/a.jpg'));
    }

    public function test_dry_run_changes_nothing(): void
    {
        Storage::fake('public');
        $logs = $this->seedCaptures();

        $this->artisan('detection:organize-captures', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame('captures/correct/a.jpg', $logs['correct']->fresh()->image_path);
        Storage::disk('public')->assertExists('captures/correct/a.jpg');
        Storage::disk('public')->assertMissing('captures/correct/plastic/a.jpg');
    }

    public function test_running_twice_moves_nothing_the_second_time(): void
    {
        Storage::fake('public');
        $this->seedCaptures();

        $this->artisan('detection:organize-captures')->assertSuccessful();
        $this->artisan('detection:organize-captures')
            ->expectsOutputToContain('Moved 0')
            ->assertSuccessful();
    }

    public function test_an_existing_file_at_the_target_is_never_overwritten(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('captures/correct/a.jpg', 'new');
        Storage::disk('public')->put('captures/correct/plastic/a.jpg', 'already-there');
        $log = DetectionLog::create([
            'image_path' => 'captures/correct/a.jpg', 'ai_detected_type' => 'plastic',
            'ground_truth_correct' => true, 'ground_truth_label' => 'plastic', 'reviewed_at' => now(),
        ]);

        $this->artisan('detection:organize-captures')->assertSuccessful();

        $path = $log->fresh()->image_path;
        $this->assertStringStartsWith('captures/correct/plastic/a-', $path);
        $this->assertSame('new', Storage::disk('public')->get($path));
        $this->assertSame('already-there', Storage::disk('public')->get('captures/correct/plastic/a.jpg'));
    }
}
