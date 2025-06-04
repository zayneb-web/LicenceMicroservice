<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LicenceRequest;
use Carbon\Carbon;

class TestExpiredLicenceRequestSeeder extends Seeder
{
    public function run()
    {
        // Créer une demande de licence avec une date de demande d'il y a plus d'un mois
        LicenceRequest::create([
            'company_name' => 'Test Company',
            'company_email' => 'test@example.com',
            'company_phone' => '0123456789',
            'company_address' => '123 Test Street',
            'type' => 'basic',
            'description' => 'Test pour l\'expiration automatique',
            'price' => 100,
            'duration_months' => 12,
            'status' => 'pending',
            'requested_at' => Carbon::now()->subMonths(2), // Date d'il y a 2 mois
        ]);

        $this->command->info('Demande de licence de test créée avec succès !');
    }
} 