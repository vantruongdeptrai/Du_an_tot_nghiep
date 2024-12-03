<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderSuccessMail extends Mailable
{
    use Queueable, SerializesModels;
    public $order;
    /**
     * Create a new message instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the message envelope.
     */
    

    /**
     * Get the message content definition.
     */
    
    public function build()
    {
        return $this->subject('Đơn hàng của bạn đã được đặt thành công!')
                    ->view('emails.order_success')
                    ->with([
                        'order' => $this->order,
                    ]);
    }
}
