<?php

namespace App\Http\Controllers;

use Stripe\StripeClient;
use Stripe\Checkout\Session;
use App\Models\Payement;
use App\Models\Licence;
use Illuminate\Http\Request;
use App\Notifications\PaymentStatusUpdated;
use App\Notifications\PaymentVerificationCode;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


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

        // Générer un code de vérification unique
        $verificationCode = Str::random(6);
        
        // Mettre à jour la licence avec le code de vérification
        $licence->update([
            'verification_code' => $verificationCode,
            'status' => Licence::STATUS_PENDING_VERIFICATION
        ]);

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
            'success_url' => route('payment.verify') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel'),
            'metadata' => [
                'licence_id' => $licence->id,
                'verification_code' => $verificationCode
            ],
            'customer_email' => $licence->company_email,
        ]);

        return redirect($session->url);
    }

    public function verify(Request $request)
    {
        try {
            $session = $this->stripe->checkout->sessions->retrieve($request->session_id);
            $licenceId = $session->metadata->licence_id ?? null;
            $verificationCode = $session->metadata->verification_code ?? null;
            
            if (!$licenceId || !$verificationCode) {
                throw new \Exception('Informations de session invalides');
            }
            
            $licence = Licence::findOrFail($licenceId);

            // Vérifier si le code correspond
            if ($licence->verification_code !== $verificationCode) {
                throw new \Exception('Code de vérification invalide');
            }

            // Créer le paiement
            $payment = Payement::create([
                'licence_id' => $licence->id,
                'amount' => $session->amount_total / 100,
                'payment_date' => now(),
                'payment_method' => 'stripe',
                'stripe_payment_intent_id' => $session->payment_intent,
                'stripe_checkout_session_id' => $session->id,
                'status' => Payement::STATUS_PENDING_VERIFICATION,
                'currency' => $session->currency
            ]);

            // Mettre à jour le statut de la licence
            $licence->update([
                'status' => Licence::STATUS_PENDING_VERIFICATION,
                'stripe_checkout_id' => $session->id
            ]);

            // Envoyer le code de vérification par email
            if ($licence->company_email) {
                Notification::route('mail', $licence->company_email)
                    ->notify(new PaymentVerificationCode($licence, $verificationCode));
            }

            return view('payment.verify', [
                'licence' => $licence,
                'payment' => $payment,
                'session' => $session
            ]);

        } catch (\Exception $e) {
            return redirect()->route('payment.error')->with('error', $e->getMessage());
        }
    }

    public function success(Request $request)
    {
        return view('payment.success');
    }

    public function confirmVerification(Request $request)
    {
        try {
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

            // Mettre à jour le statut de la licence
            $licence->update([
                'status' => Licence::STATUS_PAID,
                'activated_at' => now(),
                'verification_code' => null
            ]);

            // Mettre à jour le statut du paiement
            $payment = Payement::where('licence_id', $licence->id)
                ->where('status', Payement::STATUS_PENDING_VERIFICATION)
                ->first();

            if ($payment) {
                $payment->update([
                    'status' => Payement::STATUS_SUCCEEDED
                ]);
            }

            // Envoyer la notification de confirmation
            if ($licence->company_email && $payment) {
                Notification::route('mail', $licence->company_email)
                    ->notify(new PaymentStatusUpdated($payment, $licence));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Paiement vérifié avec succès'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la vérification du paiement: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la vérification du paiement'
            ], 500);
        }
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
            $existingPayment = Payement::where('stripe_checkout_session_id', $session->id)->first();
            
            if (!$existingPayment) {
                // Créer l'enregistrement de paiement
                $payment = Payement::create([
                    'licence_id' => $licence->id,
                    'amount' => $session->amount_total / 100,
                    'payment_date' => now(),
                    'payment_method' => 'stripe',
                    'stripe_payment_intent_id' => $session->payment_intent,
                    'stripe_checkout_session_id' => $session->id,
                    'status' => Payement::STATUS_PENDING_VERIFICATION,
                    'currency' => $session->currency
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }
} 