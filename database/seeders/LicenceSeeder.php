<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Licence;
use Carbon\Carbon;

class LicenceSeeder extends Seeder
{
    public function run(): void
    {
        // Créer une licence expirée
        Licence::create([
            'licence_request_id' => 1,
            'type' => Licence::TYPE_BASIC,
            'status' => 'active',
            'start_date' => Carbon::now()->subMonths(2),
            'end_date' => Carbon::now()->subDay(), // Date d'hier
            'price' => Licence::getPriceForType(Licence::TYPE_BASIC),
            'description' => 'Licence de test expirée',
            'mongo_company_id' => 'test-company-1',
            'license_key' => 'LIC-' . uniqid()
        ]);

        // Créer une licence active
        Licence::create([
            'licence_request_id' => 1,
            'type' => Licence::TYPE_PROFESSIONAL,
            'status' => 'active',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(1), // Date dans un mois
            'price' => Licence::getPriceForType(Licence::TYPE_PROFESSIONAL),
            'description' => 'Licence de test active',
            'mongo_company_id' => 'test-company-2',
            'license_key' => 'LIC-' . uniqid()
        ]);

        // Créer une licence expirée
        Licence::create([
            'licence_request_id' => 1,
            'type' => Licence::TYPE_ENTERPRISE,
            'status' => 'active',
            'start_date' => Carbon::now()->subMonths(2),
            'end_date' => Carbon::now()->subDay(), // Date d'hier
            'price' => Licence::getPriceForType(Licence::TYPE_ENTERPRISE),
            'description' => 'Licence de test expirée',
            'mongo_company_id' => 'test-company-3',
            'license_key' => 'LIC-' . uniqid()
        ]);
    }
} 