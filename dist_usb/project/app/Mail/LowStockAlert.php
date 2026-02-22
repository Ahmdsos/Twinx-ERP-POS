<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Low Stock Alert Email - Notifies admin of low stock products
 */
class LowStockAlert extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Collection $products
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ تنبيه: منتجات منخفضة المخزون - Twinx ERP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.low-stock',
            with: [
                'products' => $this->products,
                'count' => $this->products->count(),
            ],
        );
    }
}
