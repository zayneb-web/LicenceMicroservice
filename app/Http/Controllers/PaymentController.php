<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Licence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Affiche la liste des paiements
     */
    public function index()
    {
        $payments = Payment::with('licence')->latest()->paginate(10);
        return response()->json([
            'status' => 'success',
            'data' => $payments
        ]);
    }

    /**
     * Affiche un paiement spécifique
     */
    public function show($id)
    {
        $payment = Payment::with('licence')->find($id);
        
        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $payment
        ]);
    }

    /**
     * Crée un nouveau paiement
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'licence_id' => 'required|exists:licences,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'currency' => 'required|string|size:3',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::create([
            'licence_id' => $request->licence_id,
            'amount' => $request->amount,
            'payment_date' => now(),
            'payment_method' => $request->payment_method,
            'currency' => $request->currency,
            'status' => Payment::STATUS_PENDING,
            'notes' => $request->notes
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment created successfully',
            'data' => $payment
        ], 201);
    }

    /**
     * Met à jour un paiement
     */
    public function update(Request $request, $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,succeeded,failed,refunded',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment->update($request->only(['status', 'notes']));

        return response()->json([
            'status' => 'success',
            'message' => 'Payment updated successfully',
            'data' => $payment
        ]);
    }

    /**
     * Supprime un paiement
     */
    public function destroy($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found'
            ], 404);
        }

        $payment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment deleted successfully'
        ]);
    }

    /**
     * Liste les paiements d'une licence spécifique
     */
    public function getLicencePayments($licenceId)
    {
        $licence = Licence::find($licenceId);

        if (!$licence) {
            return response()->json([
                'status' => 'error',
                'message' => 'Licence not found'
            ], 404);
        }

        $payments = $licence->payments()->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $payments
        ]);
    }

    /**
     * Met à jour le statut d'un paiement
     */
    public function updateStatus(Request $request, $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,succeeded,failed,refunded'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment->update([
            'status' => $request->status
        ]);

        // Si le paiement est réussi, on peut mettre à jour le statut de la licence
        if ($request->status === Payment::STATUS_SUCCEEDED) {
            $payment->licence->update([
                'status' => Licence::STATUS_PAID
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Payment status updated successfully',
            'data' => $payment
        ]);
    }
<<<<<<< Updated upstream
}
=======
} 
>>>>>>> Stashed changes
