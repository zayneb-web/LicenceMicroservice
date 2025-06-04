<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeController;
use App\Models\Licence;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $licences = Licence::all();
    return view('welcome', compact('licences'));
});

// Route::resource('licencesrequest', LicenceRequestController::class);
// Routes pour Stripe
Route::post('/checkout', [StripeController::class, 'checkout'])->name('checkout');
Route::get('/payment/success', [StripeController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel', [StripeController::class, 'cancel'])->name('payment.cancel');
Route::post('/stripe/webhook', [StripeController::class, 'webhook'])->name('stripe.webhook');
Route::get('/payment/error', function () {
    return view('payment.error');
})->name('payment.error');
Route::post('/verify-payment', [StripeController::class, 'verifyPayment'])->name('payment.verify');
Route::get('/payment/verify', [StripeController::class, 'verify'])->name('payment.verify');
Route::post('/payment/confirm-verification', [StripeController::class, 'confirmVerification'])->name('payment.confirm-verification');

