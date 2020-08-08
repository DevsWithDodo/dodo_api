<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\User;

class ReportBug extends Mailable
{
    use Queueable, SerializesModels;

    public $reporter_id;
    public $bug_description;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reporter, $bug_description)
    {
        $this->reporter_id = ($reporter) ? $reporter->id : "Unathenticated.";
        $this->bug_description = $bug_description ?? "Not provided.";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Lender Bug')
                    ->text('mails.bugreport');
    }
}