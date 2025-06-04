<?php

namespace App\Http\Controllers;

use App\Models\Licence;
use App\Models\LicenceRequest;
use Illuminate\Http\Request;

class LicenceController extends Controller
{
    public function index()
    {
        return Licence::with('licenceRequest')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'licence_request_id' => 'required|exists:licence_requests,id',
            'type' => 'required|in:basic,professional,enterprise',
            'status' => 'required|in:pending,paid,expired,cancelled',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'license_key' => 'nullable|string',
            'validated_at' => 'nullable|date',
            'activated_at' => 'nullable|date',
            'mongo_company_id' => 'nullable|string',
        ]);

        $data['license_key'] = $data['license_key'] ?? strtoupper(uniqid('LIC-'));
        $data['mongo_company_id'] = $data['mongo_company_id'] ?? 'default';

        $licence = Licence::create($data);

        // Mettre à jour le statut de la demande de licence
        $licenceRequest = LicenceRequest::find($data['licence_request_id']);
        $oldStatus = $licenceRequest->status;
        
        $licenceRequest->update([
            'status' => 'validated',
            'validated_at' => now(),
        ]);

        // Envoyer la notification si le statut a changé
        if ($oldStatus !== 'validated') {
            $licenceRequest->notify(new \App\Notifications\LicenceRequestStatusUpdated($licenceRequest));
        }

        return response()->json($licence, 201);
    }

    public function show(Licence $licence)
    {
        return $licence->load('licenceRequest', 'payments');
    }

    public function update(Request $request, Licence $licence)
    {
        $data = $request->validate([
            'status' => 'sometimes|in:pending,paid,expired,cancelled',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'price' => 'sometimes|numeric',
            'description' => 'nullable|string',
            'license_key' => 'sometimes|string',
            'validated_at' => 'nullable|date',
            'activated_at' => 'nullable|date',
        ]);

        $licence->update($data);

        return response()->json($licence);
    }

    public function destroy(Licence $licence)
    {
        $licence->delete();

        return response()->json(['message' => 'Licence supprimée avec succès']);
    }

    /**
     * Vérifier si la licence est active ou expirée
     */
    public function status(Licence $licence)
    {
        if ($licence->isExpired()) {
            return response()->json(['status' => 'expired']);
        }

        return response()->json(['status' => $licence->status]);
    }

    /**
     * Récupérer une licence par mongo_company_id
     */
    public function getLicenceByMongoCompanyId($mongoCompanyId)
    {
        $licence = Licence::where('mongo_company_id', $mongoCompanyId)
            ->with('licenceRequest')
            ->first();

        if (!$licence) {
            return response()->json(['message' => 'Licence non trouvée'], 404);
        }

        return response()->json($licence);
    }


    // Dans un contrôleur
public function checkLicence($mongoCompanyId)
{
    $licence = Licence::where('mongo_company_id', $mongoCompanyId)->first();
    
    if (!$licence) {
        return response()->json(['error' => 'Licence non trouvée'], 404);
    }

    if ($licence->isExpired()) {
        return response()->json([
            'status' => 'expired',
            'message' => 'Votre licence a expiré'
        ]);
    }

    if ($licence->isPaid()) {
        return response()->json([
            'status' => 'paid',
            'message' => 'Votre licence est active'
        ]);
    }

    return response()->json([
        'status' => $licence->status,
        'message' => 'Votre licence n\'est pas active'
    ]);
}
}
