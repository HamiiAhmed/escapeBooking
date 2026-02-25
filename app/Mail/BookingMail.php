<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class BookingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $booking; // Make the booking data accessible to the view
    public $recipientType; // To determine if it's for user or admin

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, string $recipientType)
    {
        $this->booking = $booking;
        $this->recipientType = $recipientType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->recipientType === 'customer' 
            ? 'Your Booking Confirmation'
            : 'New Booking Received';
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-notification',
            with: [
                'booking' => $this->booking, // Pass the booking data to the view
                'recipientType' => $this->recipientType, // Pass the recipient type to the view
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
