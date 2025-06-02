<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Licence;
use Carbon\Carbon;

class UpdateExpiredLicences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licences:update-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Met à jour le statut des licences expirées';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        
        $expiredLicences = Licence::where('end_date', '<', $now)
            ->where('status', '!=', 'expired')
            ->get();

        foreach ($expiredLicences as $licence) {
            $licence->update([
                'status' => 'expired'
            ]);
            
            $this->info("Licence ID {$licence->id} marquée comme expirée");
        }

        $this->info("Mise à jour terminée. {$expiredLicences->count()} licences expirées.");
    }
}
