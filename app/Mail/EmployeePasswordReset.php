<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class EmployeePasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    public $newPassword;
    public $employeeName;
    public $employeeEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(string $newPassword, string $employeeName, string $employeeEmail)
    {
        $this->newPassword = $newPassword;
        $this->employeeName = $employeeName;
        $this->employeeEmail = $employeeEmail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Сброс пароля - PurrfectCare',
            from: new Address('y4ndex.us@yandex.ru', 'PurrfectCare'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.employee-password-reset',
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
