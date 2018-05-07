<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Efriandika\LaravelSettings\Facades\Settings;

class SendQuotation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $sitemail;
    public $message_body;

    public function __construct($message_body)
    {
        $this->sitemail = Settings::get('site_email');
        $this->message_body = $message_body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('admin@admin.com')
            ->view('emails.quotation');
    }
}
