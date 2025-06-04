<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LicenceRequestController;
use App\Http\Controllers\LicenceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\StripeController;
use App\Models\Licence;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes pour les demandes de licence (publiques)
Route::get('/licence-requests', [LicenceRequestController::class, 'index']);
Route::get('/licence-requests/manager/{email}', [LicenceRequestController::class, 'getByCompanyManagerEmail']);
Route::post('/licence-requests', [LicenceRequestController::class, 'store']);
    Route::put('/licence-requests/{licenceRequest}', [LicenceRequestController::class, 'update']);
    Route::delete('/licence-requests/{licenceRequest}', [LicenceRequestController::class, 'destroy']);

// Routes pour les licences (publiques)
// Route::apiResource('licences', LicenceController::class);
Route::post('/licences', [LicenceController::class, 'store']);
Route::get('/licences', [LicenceController::class, 'index']);
Route::get('/licences/{id}', [LicenceController::class, 'showbyid']);
Route::get('/licences/company/{companyId}', [LicenceController::class, 'getLicenceByCompanyId']); //get licence by company id   
Route::get('/licences/company/{mongoCompanyId}', [LicenceController::class, 'getLicenceByMongoCompanyId']); //get licence by mongo company id
Route::get('/licences/check/{mongoCompanyId}', [LicenceController::class, 'checkLicence']);       //check licence status is expired or activated by mongo company id

Route::get('/licences/{licence}/status', [LicenceController::class, 'status']); //get licences qui sont activé

Route::delete('/licences/{licence}', [LicenceController::class, 'destroy']);
// Routes pour les paiements
Route::prefix('payments')->group(function () {
    Route::get('/', [PaymentController::class, 'index']);
    Route::post('/', [PaymentController::class, 'store']);
    Route::get('/{id}', [PaymentController::class, 'show']);
    Route::put('/{id}', [PaymentController::class, 'update']);
    Route::delete('/{id}', [PaymentController::class, 'destroy']);
    Route::put('/{id}/status', [PaymentController::class, 'updateStatus']);
    Route::get('/licence/{licenceId}', [PaymentController::class, 'getLicencePayments']);
});

Route::get('/payments/licence/{licenceId}', [PaymentController::class, 'getLicencePayments']);

// Route pour le webhook Stripe
Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);

Route::get('/pay', [StripeWebhookController::class, 'pay'])->name('pay');


Route::get('/', function () {
    $licences = Licence::all();
    return view('welcome', compact('licences'));
});

Route::post('/licences/{id}/renew', [LicenceRequestController::class, 'renew']);



