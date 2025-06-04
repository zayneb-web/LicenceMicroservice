<?php

use Illuminate\Support\Facades\Route;
use App\Models\Licence;
use App\Http\Controllers\LicenceRequestController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\StripeController;

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
Route::get('/payment/success', [StripeController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel', [StripeController::class, 'cancel'])->name('payment.cancel');
Route::post('/stripe/webhook', [StripeWebhookController::class, 'webhook'])->name('stripe.webhook');
Route::post('/checkout', [StripeController::class, 'checkout'])->name('checkout');
Route::get('/payment/error', function () {
    return view('payment.error');
})->name('payment.error');

Route::post('/payment/confirm-verification', [StripeController::class, 'confirmVerification'])->name('payment.confirm-verification');
Route::get('/payment/verify', [StripeController::class, 'verify'])->name('payment.verify');
Route::get('/test-nginx', function () {
    return 'Nginx OK';
});
