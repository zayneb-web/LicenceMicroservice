<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Payment;
use App\Models\Licence;

class PaymentSuccess extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $licence;
    public $verificationCode;

    public function __construct(Payment $payment, Licence $licence, $verificationCode)
    {
        $this->payment = $payment;
        $this->licence = $licence;
        $this->verificationCode = $verificationCode;
    }

    public function build()
    {
        return $this->subject('Confirmation de paiement - Votre licence')
                    ->view('emails.payment-success');
    }
} 