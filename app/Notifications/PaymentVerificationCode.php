<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Licence;

class PaymentVerificationCode extends Notification implements ShouldQueue
{
    use Queueable;

    protected $licence;
    protected $verificationCode;

    public function __construct(Licence $licence, string $verificationCode)
    {
        $this->licence = $licence;
        $this->verificationCode = $verificationCode;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Code de vérification de paiement')
            ->greeting('Bonjour,')
            ->line('Votre paiement a été reçu. Pour finaliser votre achat, veuillez entrer le code de vérification suivant :')
            ->line($this->verificationCode)
            ->line('Ce code est valide pendant 24 heures.')
            ->action('Vérifier le paiement', url('/payment/verify'))
            ->line('Si vous n\'avez pas effectué cette transaction, veuillez ignorer cet email.');
    }
} 