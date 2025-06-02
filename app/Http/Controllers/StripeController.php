<?php

namespace App\Http\Controllers;

use Stripe\StripeClient;
use Stripe\Checkout\Session;
use App\Models\Payement;
use App\Models\Licence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccess;
use Illuminate\Support\Str;
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
            'customer_email' => $request->email ?? ($licence->user ? $licence->user->email : null),
            'billing_address_collection' => 'required',
            'shipping_address_collection' => [
                'allowed_countries' => ['FR', 'BE', 'CH', 'CA', 'MA', 'TN', 'DZ'],
            ],
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

            // Generate verification code
            $verificationCode = Str::random(6);

            // Update the licence status
            $licence->update([
                'status' => Licence::STATUS_PENDING_VERIFICATION,
                'verification_code' => $verificationCode,
                'stripe_checkout_id' => $session->id
            ]);

            // Create the payment
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

            // Get customer email from Stripe session
            $customerEmail = $session->customer_email;
            
            // If no customer email in session, try to get it from the licence's user
            if (!$customerEmail && $licence->user) {
                $customerEmail = $licence->user->email;
            }

            // Send confirmation email if we have an email address
            if ($customerEmail) {
                Mail::to($customerEmail)->send(new PaymentSuccess($payment, $licence, $verificationCode));
            }

            return view('payment.success', [
                'licence' => $licence,
                'session' => $session,
                'verificationCode' => $verificationCode
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
        $payment = Payement::where('licence_id', $licence->id)
            ->where('status', Payement::STATUS_PENDING_VERIFICATION)
            ->first();

        if ($payment) {
            $payment->update([
                'status' => Payement::STATUS_SUCCEEDED
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

            // Créer l'enregistrement de paiement
            Payment::create([
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
        }

        return response()->json(['status' => 'success']);
    }
} 