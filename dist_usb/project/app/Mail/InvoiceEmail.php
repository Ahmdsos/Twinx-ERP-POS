<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Sales\Models\SalesInvoice;

/**
 * Invoice Email - Sends invoice to customer
 */
class InvoiceEmail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public SalesInvoice $invoice
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'فاتورة رقم ' . $this->invoice->invoice_number . ' - Twinx ERP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'customer' => $this->invoice->customer,
                'lines' => $this->invoice->lines,
            ],
        );
    }
}
