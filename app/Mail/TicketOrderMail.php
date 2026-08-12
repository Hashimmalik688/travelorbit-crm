<?php

namespace App\Mail;

use App\Models\TicketOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TicketOrder $ticketOrder)
    {
    }

    public function envelope(): Envelope
    {
        $booking = $this->ticketOrder->booking;
        $lead = $this->ticketOrder->passengers->first();

        return new Envelope(
            from: new Address(
                env('TICKET_ORDER_MAIL_FROM_ADDRESS') ?: (env('REFUND_MAIL_FROM_ADDRESS') ?: config('mail.from.address')),
                env('TICKET_ORDER_MAIL_FROM_NAME', env('REFUND_MAIL_FROM_NAME', config('mail.from.name'))),
            ),
            subject: "PLEASE ISSUE TICKET : " . ($lead->name ?? $booking->booker_name ?? "Booking #{$booking->booking_number}"),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-order',
            with: [
                'ticketOrder' => $this->ticketOrder,
                'booking' => $this->ticketOrder->booking,
            ],
        );
    }
}
