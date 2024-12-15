<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $password; // Mật khẩu mới

    public function __construct($password)
    {
        $this->password = $password; // Truyền mật khẩu vào email
    }
    public function build()
    {
        return $this->subject('Your New Password')
                    ->view('emails.reset-password')
                    ->with([
                        'password' => $this->password, // Gửi mật khẩu tới view
                    ]);
    }
}

