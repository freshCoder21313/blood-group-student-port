<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public string $status
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Application Status Updated',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.application.status-updated',
        );
    }
}
