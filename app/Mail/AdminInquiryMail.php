<?php

namespace App\Mail;

use App\Models\Inquiry;
use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminInquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inquiry;
    public $property;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry;
        $this->property = Property::find($inquiry->property_id);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject('New inquiry #[' . $this->inquiry->inquiry_id . '] - Availability request')
                    ->view('emails.admin_inquiry');

        if (!empty($this->inquiry->email)) {
            $mail->replyTo($this->inquiry->email, $this->inquiry->name ?? '');
        }

        return $mail;
    }
}
