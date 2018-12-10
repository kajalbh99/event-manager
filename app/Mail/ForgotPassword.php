<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $address = 'eventmanager@gmail.com';
        
               $name = 'Event-manager';
        
               $subject = 'Event-manager';
        
               return $this->view('admin.emails.forgot-password')
        
               ->from($address, $name)
        
               ->subject($subject);
    }
}