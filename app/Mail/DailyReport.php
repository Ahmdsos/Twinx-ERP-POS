<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Daily Report Email - Sends daily summary to management
 */
class DailyReport extends Mailable
{
    use SerializesModels;

    public function __construct(
        public array $stats
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📊 التقرير اليومي - ' . now()->format('Y-m-d') . ' - Twinx ERP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-report',
            with: [
                'stats' => $this->stats,
                'date' => now()->format('Y-m-d'),
            ],
        );
    }
}
