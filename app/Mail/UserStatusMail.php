<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $statusKey;

    public function __construct(User $user, string $statusKey)
    {
        $this->user = $user;
        $this->statusKey = $statusKey; 
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.user_status_subject'), 
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-status',
            with: [
                'name' => $this->user->name,
                'status' => __('messages.' . $this->statusKey), 
            ],
        );
    }
}
