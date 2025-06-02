<?php

namespace App\Http\Controllers;

use App\Models\LicenceRequest;
use Illuminate\Http\Request;

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
            'price' => 'required|numeric',
            'duration_months' => 'required|integer',
            'status' => 'pending|string',
            'requested_at' => 'nullable|date',
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
            'validated_by' => 'nullable|integer',
            'validated_at' => 'nullable|date',
            'rejected_at' => 'nullable|date',
            'rejection_reason' => 'nullable|string',
        ]);

        $licenceRequest->update($data);

        return response()->json($licenceRequest);
    }

    public function destroy(LicenceRequest $licenceRequest)
    {
        $licenceRequest->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
