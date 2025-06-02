<?php

namespace App\Http\Controllers;

use Stripe\StripeClient;
use Stripe\Checkout\Session;
use App\Models\Payement;
use App\Models\Licence;
use Illuminate\Http\Request;
use App\Notifications\PaymentStatusUpdated;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class StripeController extends Controller
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function checkout(Request $request)
    {
        $licence = Licence::findOrFail($request->licence_id);

        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Licence ' . $licence->type,
                        'description' => 'Licence valide pour ' . $licence->duration_months . ' mois'
                    ],
                    'unit_amount' => $licence->price * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel'),
            'metadata' => [
                'licence_id' => $licence->id
            ],
            'customer_email' => $licence->company_email,
      
        ]);

        return redirect($session->url);
    }

    public function success(Request $request)
    {
        try {
            $session = $this->stripe->checkout->sessions->retrieve($request->session_id);
            $licenceId = $session->metadata->licence_id ?? null;
            
            if (!$licenceId) {
                throw new \Exception('Licence ID not found in session metadata');
            }
            
            $licence = Licence::findOrFail($licenceId);

            // Create the payment
            $payment = Payement::create([
                'licence_id' => $licence->id,
                'amount' => $session->amount_total / 100,
                'payment_date' => now(),
                'payment_method' => 'stripe',
                'stripe_payment_intent_id' => $session->payment_intent,
                'stripe_checkout_session_id' => $session->id,
                'status' => Payement::STATUS_SUCCEEDED,
                'currency' => $session->currency
            ]);

            // Update the licence status
            $licence->update([
                'status' => Licence::STATUS_PAID,
                'activated_at' => now(),
                'stripe_checkout_id' => $session->id
            ]);

            // Envoyer la notification de paiement
            if ($licence->company_email) {
                Notification::route('mail', $licence->company_email)
                    ->notify(new PaymentStatusUpdated($payment, $licence));
            }

            return view('payment.success', [
                'licence' => $licence,
                'payment' => $payment,
                'session' => $session
            ]);
        } catch (\Exception $e) {
            return redirect()->route('payment.error')->with('error', $e->getMessage());
        }
    }

    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'licence_id' => 'required|exists:licences,id',
            'verification_code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $licence = Licence::findOrFail($request->licence_id);

        if ($licence->verification_code !== $request->verification_code) {
            return response()->json([
                'status' => 'error',
                'message' => 'Code de vérification invalide'
            ], 400);
        }

        // Update licence status
        $licence->update([
            'status' => Licence::STATUS_PAID,
            'activated_at' => now(),
            'verification_code' => null
        ]);

        // Update payment status
        $payment = Payment::where('licence_id', $licence->id)
            ->where('status', Payment::STATUS_PENDING_VERIFICATION)
            ->first();

        if ($payment) {
            $payment->update([
                'status' => Payment::STATUS_SUCCEEDED
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Paiement vérifié avec succès'
        ]);
    }

    public function cancel()
    {
        return view('payment.cancel');
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $licence = Licence::where('stripe_checkout_id', $session->id)->first();

            if (!$licence) {
                return response()->json(['error' => 'Licence non trouvée'], 404);
            }

            // Vérifier si le paiement existe déjà
            $existingPayment = Payment::where('stripe_checkout_session_id', $session->id)->first();
            
            if (!$existingPayment) {
                // Créer l'enregistrement de paiement
                $payment = Payment::create([
                    'licence_id' => $licence->id,
                    'amount' => $session->amount_total / 100,
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

                // Envoyer la notification de paiement
                if ($licence->company_email) {
                    Notification::route('mail', $licence->company_email)
                        ->notify(new PaymentStatusUpdated($payment, $licence));
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
} 