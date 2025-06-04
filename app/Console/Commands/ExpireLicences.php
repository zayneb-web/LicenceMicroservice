<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Licence;
use Carbon\Carbon;

class ExpireLicences extends Command
{
    protected $signature = 'licences:expire';
    protected $description = 'Expire licences that are older than 7 days';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);
        
        $expiredLicences = Licence::where('created_at', '<', $sevenDaysAgo)
            ->where('status', '!=', 'expired')
            ->get();

        foreach ($expiredLicences as $licence) {
            $licence->update([
                'status' => 'expired',
                'expired_at' => Carbon::now()
            ]);

            $this->info("Licence ID {$licence->id} has been expired.");
        }

        $this->info("Expired {$expiredLicences->count()} licences.");
    }
} 