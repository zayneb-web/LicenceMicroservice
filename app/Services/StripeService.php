<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Payment;
use App\Models\Licence;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Crée une session de paiement Stripe
     */
    public function createCheckoutSession(Licence $licence)
    {
        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Licence ' . $licence->type,
                            'description' => $licence->description,
                        ],
                        'unit_amount' => $licence->price * 100, // Stripe utilise les centimes
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => config('app.frontend_url') . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.frontend_url') . '/payment/cancel',
                'metadata' => [
                    'licence_id' => $licence->id
                ]
            ]);

            // Mettre à jour la licence avec l'ID de session Stripe
            $licence->update([
                'stripe_checkout_id' => $session->id
            ]);

            return $session;
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la création de la session Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Vérifie le statut d'une session de paiement
     */
    public function checkSessionStatus($sessionId)
    {
        try {
            $session = Session::retrieve($sessionId);
            return $session->payment_status;
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la vérification du statut: ' . $e->getMessage());
        }
    }

    /**
     * Traite le webhook de Stripe
     */
    public function handleWebhook($payload)
    {
        $event = $payload['type'];
        $session = $payload['data']['object'];

        if ($event === 'checkout.session.completed') {
            $licence = Licence::where('stripe_checkout_id', $session->id)->first();
            
            if (!$licence) {
                throw new \Exception('Licence non trouvée pour cette session');
            }

            // Créer l'enregistrement de paiement
            Payment::create([
                'licence_id' => $licence->id,
                'amount' => $session->amount_total / 100, // Convertir les centimes en euros
                'payment_date' => now(),
                'payment_method' => 'stripe',
                'stripe_payment_intent_id' => $session->payment_intent,
                'stripe_checkout_session_id' => $session->id,
                'status' => Payment::STATUS_SUCCEEDED,
                'currency' => $session->currency
            ]);

            // Mettre à jour le statut de la licence
            $licence->update([
                'status' => Licence::STATUS_PAID,
                'activated_at' => now()
            ]);

            return true;
        }

        return false;
    }
}