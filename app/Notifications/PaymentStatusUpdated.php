<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Payement;
use App\Models\Licence;

class PaymentStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payement;
    protected $licence;

    /**
     * Create a new notification instance.
     */
    public function __construct(Payement $payement, Licence $licence)
    {
        $this->payement = $payement;
        $this->licence = $licence;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $message = new MailMessage;
        
        if ($this->payement->status === 'succeeded') {
            $message->subject('Confirmation de paiement - Licence')
                   ->greeting('Bonjour,')
                   ->line('Nous confirmons la réception de votre paiement pour votre licence.')
                   ->line('Type de licence : ' . $this->licence->type)
                   ->line('Prix payé : ' . $this->payement->amount . ' ' . $this->payement->currency)
                   ->line('Durée : ' . $this->licence->duration_months . ' mois')
                   ->line('Date de paiement : ' . $this->payement->payment_date->format('d/m/Y H:i'))
                   ->line('Méthode de paiement : ' . $this->payement->payment_method)
                   ->action('Voir les détails de votre licence', url('/licences'))
                   ->line('Merci de votre confiance !')
                   ->salutation('Cordialement,Abshore');
        } else if ($this->payement->status === 'failed') {
            $message->subject('Échec du paiement - Licence')
                   ->greeting('Bonjour,')
                   ->line('Nous regrettons de vous informer que votre paiement a échoué.')
                   ->line('Type de licence : ' . $this->licence->type)
                   ->line('Montant : ' . $this->payement->amount . ' ' . $this->payement->currency)
                   ->action('Réessayer le paiement', url('/payement/retry/' . $this->payement->id))
                   ->line('Si vous rencontrez des difficultés, n\'hésitez pas à nous contacter.');
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
} 