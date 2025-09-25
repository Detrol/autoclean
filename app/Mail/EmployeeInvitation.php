<?php

namespace App\Mail;

use App\Models\EmployeeInvitation as EmployeeInvitationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeeInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public EmployeeInvitationModel $invitation
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Inbjudan till AutoClean - Stationshanteringssystem',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.employee-invitation',
            with: [
                'invitationUrl' => $this->invitation->getInvitationUrl(),
                'inviterName' => $this->invitation->inviter->name,
                'recipientName' => $this->invitation->name,
                'expiresAt' => $this->invitation->expires_at,
            ],
        );
    }
}