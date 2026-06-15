<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\Organization;

class DailySalesSummaryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $organization;
    public $date;
    public $totalSales;
    public $totalTickets;
    public $eventsSummary;

    /**
     * Create a new message instance.
     */
    public function __construct(Organization $organization, $date, $totalSales, $totalTickets, $eventsSummary)
    {
        $this->organization = $organization;
        $this->date = $date;
        $this->totalSales = $totalSales;
        $this->totalTickets = $totalTickets;
        $this->eventsSummary = $eventsSummary;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laporan Penjualan Harian - ' . $this->date,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.organizer.daily-sales',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
