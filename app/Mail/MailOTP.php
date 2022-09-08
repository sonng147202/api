<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MailOTP extends Mailable
{
    use Queueable, SerializesModels;

    private $active_code;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($active_code)
    {
        $this->active_code = $active_code;
        $this->queue = "otp";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->subject("Đặt lại mật khẩu")
        ->view('email.forgotPassword')->with([
            'active_code' => $this->active_code
        ]);
    }
}
