<?php

namespace App\Http\Controllers;

<<<<<<< Updated upstream
use Stripe\StripeClient;
use Stripe\Checkout\Session;
use App\Models\Payement;
use App\Models\Licence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StripeWebhookController extends Controller
{
    public $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.api_key.secret'));
    }

    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'licence_id' => 'required|exists:licences,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $licence = Licence::findOrFail($request->licence_id);
        $type = $licence->type;
        $price = $licence->price;

        $session = $this->stripe->checkout->sessions->create([
            'mode' => 'payment',
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Licence ' . ucfirst($type),
                    ],
                    'unit_amount' => $price * 100,
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'licence_id' => $licence->id
            ]
        ]);

        // Update the licence with the Stripe session ID
        $licence->update([
            'stripe_checkout_id' => $session->id
        ]);

        return redirect($session->url);
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
                'success_url' => 'https://example.com/success',
                'cancel_url' => 'https://example.com/cancel',
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
            Payement::create([
                'licence_id' => $licence->id,
                'amount' => $session->amount_total / 100, // Convertir les centimes en euros
                'payment_date' => now(),
                'payment_method' => 'stripe',
                'stripe_payment_intent_id' => $session->payment_intent,
                'stripe_checkout_session_id' => $session->id,
                'status' => Payement::STATUS_SUCCEEDED,
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
=======
use App\Services\StripeService;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        try {
            $this->stripeService->handleWebhook($event);
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 
>>>>>>> Stashed changes
