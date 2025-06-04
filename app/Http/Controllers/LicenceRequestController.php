<?php

namespace App\Http\Controllers;

use App\Models\LicenceRequest;
use Illuminate\Http\Request;
use App\Models\Licence;


class LicenceRequestController extends Controller
{
    public function index()
    {
        return LicenceRequest::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string',
            'company_email' => 'required|email',
            'company_phone' => 'required|string',
            'company_address' => 'required|string',
            'type' => 'required|in:basic,professional,enterprise',
            'description' => 'nullable|string',
            'price' => 'required|in:50,100,150',
            'duration_months' => 'required|integer',
            'status' => 'nullable|in:pending',
            'requested_at' => 'nullable|date',
            'company_manager_email' => 'required|email',
        ]);

        $data['requested_at'] = now();
        $data['status'] = 'pending';

        $licenceRequest = LicenceRequest::create($data);

        return response()->json($licenceRequest, 201);
    }

    public function show(LicenceRequest $licenceRequest)
    {
        return $licenceRequest;
    }

    public function update(Request $request, LicenceRequest $licenceRequest)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,validated,rejected,expired',
            'validated_by' => 'nullable|exists:users,id',
            'validated_at' => 'nullable|date',
            'rejected_at' => 'nullable|date',
            'rejection_reason' => 'nullable|string|required_if:status,rejected',
        ]);

        $oldStatus = $licenceRequest->status;
        $licenceRequest->update($data);

        // Envoyer la notification si le statut a changé vers validated ou rejected
        if (($data['status'] === 'validated' || $data['status'] === 'rejected') && $oldStatus !== $data['status']) {
            $licenceRequest->notify(new \App\Notifications\LicenceRequestStatusUpdated($licenceRequest));
        }

        return response()->json($licenceRequest);
    }

    public function destroy(LicenceRequest $licenceRequest)
    {
        $licenceRequest->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function getByCompanyManagerEmail($email)
    {
        $licenceRequests = LicenceRequest::where('company_manager_email', $email)->get();
        
        if ($licenceRequests->isEmpty()) {
            return response()->json(['message' => 'No licence requests found for this manager email'], 404);
        }
        return response()->json($licenceRequests);
    }

    public function renew(Request $request, $licenceId)
    {
        $oldLicence = Licence::findOrFail($licenceId);

        $licenceRequest = LicenceRequest::create([
            'company_name' => $oldLicence->licenceRequest->company_name,
            'company_email' => $oldLicence->licenceRequest->company_email,
            'company_phone' => $oldLicence->licenceRequest->company_phone,
            'company_address' => $oldLicence->licenceRequest->company_address,
            'type' => $oldLicence->type,
            'description' => 'Renouvellement de licence',
            'price' => $oldLicence->price,
            'duration_months' => $oldLicence->licenceRequest->duration_months,
            'status' => 'pending',
            'requested_at' => now(),
            'company_manager_email' => $oldLicence->licenceRequest->company_manager_email,
        ]);

        if ($oldLicence->company_email) {
            \Illuminate\Support\Facades\Notification::route('mail', $oldLicence->company_email)
                ->notify(new \App\Notifications\LicenceRenewalRequested($oldLicence));
        }

        return response()->json($licenceRequest, 201);
    }
}
