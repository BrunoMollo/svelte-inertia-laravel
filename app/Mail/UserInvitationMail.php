<?php

namespace App\Mail;

use App\Mail\Concerns\UsesUserLocale;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserInvitationMail extends Mailable
{
    use Queueable, SerializesModels, UsesUserLocale;

    public function __construct(
        public User $user,
        public string $resetUrl,
    ) {
        $this->setUserLocale();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Has sido invitado a :app', ['app' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-invitation',
        );
    }
}
