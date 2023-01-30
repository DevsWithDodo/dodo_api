<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportBug extends Mailable
{
    use Queueable, SerializesModels;

    public $reporter;
    public $bug_description;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reporter, $bug_description)
    {
        $this->reporter = $reporter;
        $this->bug_description = $bug_description ?? "Not provided.";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Dodo Bug')
            ->markdown('mails.bugreport');
    }
}
