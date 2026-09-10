<?php

namespace App\Mail;

use App\Models\RvmMachine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BinCollectionRequested extends Mailable
{
    use Queueable, SerializesModels;

    private const FULL_THRESHOLD = 90;

    private const MATERIAL_LABELS = [
        'aluminum_level' => 'Aluminum',
        'plastic_level'  => 'Plastic',
        'glass_level'    => 'Glass',
        'paper_level'    => 'Paper',
    ];

    /** @var array<int, array{name: string, location_name: ?string, machine_code: string, full_materials: array<int, array{label: string, level: int}>}> */
    public array $machineSummaries;

    /** @param Collection<int, RvmMachine> $machines */
    public function __construct(public Collection $machines)
    {
        $this->machineSummaries = $machines->map(fn (RvmMachine $machine) => [
            'name'           => $machine->name,
            'location_name'  => $machine->location_name,
            'machine_code'   => $machine->machine_code,
            'full_materials' => collect(self::MATERIAL_LABELS)
                ->filter(fn ($label, $field) => $machine->$field >= self::FULL_THRESHOLD)
                ->map(fn ($label, $field) => ['label' => $label, 'level' => $machine->$field])
                ->values()
                ->all(),
        ])->all();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'RVM: Bin Collection Requested (' . $this->machines->count() . ' machine(s))',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bin-collection-requested',
            with: ['machineSummaries' => $this->machineSummaries],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
