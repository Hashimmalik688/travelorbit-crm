<?php

namespace App\Mail;

use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RefundRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Refund $refund)
    {
    }

    public function envelope(): Envelope
    {
        $booking = $this->refund->booking;

        return new Envelope(
            from: new Address(
                env('REFUND_MAIL_FROM_ADDRESS') ?: config('mail.from.address'),
                env('REFUND_MAIL_FROM_NAME', config('mail.from.name')),
            ),
            subject: "Flight Refund Request — Booking #{$booking->booking_number} — " . ($booking->booker_name ?: ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.refund-request',
            with: [
                'refund' => $this->refund,
                'booking' => $this->refund->booking,
            ],
        );
    }
}
