<?php

namespace App\Mail;

use App\Models\ContactSupport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminSupportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $support;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ContactSupport $support)
    {
        $this->support = $support;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject('New support message #[' . $this->support->id . '] - ' . ucfirst($this->support->subject))
                    ->view('emails.admin_support');

        if (!empty($this->support->email)) {
            $mail->replyTo($this->support->email, $this->support->name ?? '');
        }

        return $mail;
    }
}
