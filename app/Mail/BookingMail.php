<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class BookingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking; // Make the booking data accessible to the view

    /**
     * Create a new message instance.
     */
    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if ($this->booking->email_type === 'user') {
            return new Envelope(
                subject: 'Booking Request Received',
                replyTo: [
                    new Address('info@tunbridgewells888.co.uk', 'Tunbridge Wells 888'),
                ],
            );
        } else {
            return new Envelope(
                subject: 'New Booking Request Received',
                replyTo: [
                    new Address($this->booking->email, $this->booking->name ?? 'Customer'),
                ],
            );
        }
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.bookingMail',
            with: [
                'booking' => $this->booking, // Pass the booking data to the view
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
