<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationResult extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public string $type // 'approved' or 'rejected'
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->type === 'approved' 
            ? 'Congratulations! Your application has been approved' 
            : 'Admission Application Update';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.application_result',
            with: [
                'name' => $this->application->student->full_name ?? 'Student',
                'status' => $this->type,
                'program' => $this->application->program->name ?? 'Academic Program',
            ],
        );
    }
}
