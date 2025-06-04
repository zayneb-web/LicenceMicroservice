<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Licence;
use App\Models\Payement;
use Carbon\Carbon;

class CancelLicences extends Command
{
    protected $signature = 'licences:cancel';
    protected $description = 'Cancel and delete licences that are older than 30 days';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        
        $licencesToCancel = Licence::where('created_at', '<', $thirtyDaysAgo)
            ->where('status', '!=', 'canceled')
            ->get();

        foreach ($licencesToCancel as $licence) {
            try {
                // Supprimer d'abord les paiements associés
                Payement::where('licence_id', $licence->id)->delete();
                
                // Ensuite supprimer la licence
                $licence->delete();
                
                $this->info("Successfully canceled and deleted Licence ID {$licence->id}");
            } catch (\Exception $e) {
                $this->error("Error processing Licence ID {$licence->id}: " . $e->getMessage());
            }
        }

        $this->info("Processed {$licencesToCancel->count()} licences.");
    }
} 