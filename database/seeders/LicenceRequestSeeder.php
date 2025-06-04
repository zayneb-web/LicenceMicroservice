<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LicenceRequest;
use Carbon\Carbon;

class LicenceRequestSeeder extends Seeder
{
    public function run()
    {
        // Basic Licence Request
        LicenceRequest::create([
            'company_name' => 'Alpha Company',
            'company_email' => 'alpha@example.com',
            'company_phone' => '11111111',
            'company_address' => '1 Alpha Street',
            'type' => 'basic',
            'description' => 'Basic licence request for Alpha Company',
            'price' => 50,
            'duration_months' => 12,
            'status' => 'pending',
            'requested_at' => Carbon::now()->subDays(5),
        ]);

        // Professional Licence Request
        LicenceRequest::create([
            'company_name' => 'Beta Company',
            'company_email' => 'beta@example.com',
            'company_phone' => '22222222',
            'company_address' => '2 Beta Avenue',
            'type' => 'professional',
            'description' => 'Professional licence request for Beta Company',
            'price' => 100,
            'duration_months' => 24,
            'status' => 'validated',
            'requested_at' => Carbon::now()->subDays(10),
            'validated_at' => Carbon::now()->subDays(8),
            'validated_by' => null, // Set to a valid user ID
        ]);

        // Enterprise Licence Request
        LicenceRequest::create([
            'company_name' => 'Gamma Corp',
            'company_email' => 'gamma@example.com',
            'company_phone' => '33333333',
            'company_address' => '3 Gamma Blvd',
            'type' => 'enterprise',
            'description' => 'Enterprise licence request for Gamma Corp',
            'price' => 150,
            'duration_months' => 36,
            'status' => 'rejected',
            'requested_at' => Carbon::now()->subDays(20),
            'rejected_at' => Carbon::now()->subDays(18),
            'rejection_reason' => 'Incomplete documents',
        ]);
    }
}