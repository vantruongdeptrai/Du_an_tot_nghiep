<?php

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\PDF;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $order;
    protected $pdf;

    /**
     * Tạo một instance của email.
     *
     * @param Order $order
     * @param PDF $pdf
     */
    public function __construct(Order $order, PDF $pdf)
    {
        $this->order = $order;
        $this->pdf = $pdf;
    }

    /**
     * Xây dựng nội dung email.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Hóa đơn đơn hàng #' . $this->order->id)
                    ->view('emails.invoice')  // Bạn có thể tạo view email ở đây
                    ->attachData($this->pdf->output(), 'invoice-' . $this->order->id . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}

