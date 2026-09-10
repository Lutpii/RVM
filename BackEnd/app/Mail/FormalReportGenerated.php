<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormalReportGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $periodLabel,
        public string $xlsxContents,
        public string $filename,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'RVM Recycling Report — ' . $this->periodLabel,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.formal-report-generated',
            with: ['periodLabel' => $this->periodLabel],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->xlsxContents, $this->filename)
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}
