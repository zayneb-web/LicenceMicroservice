<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Licence;
use App\Models\Payement;
use Carbon\Carbon;

class CancelLicences extends Command
{
    protected $signature = 'licences:cancel';
    protected $description = 'Cancel pending licences older than 7 days';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);
        
        // Cancel pending/pending_verification licences older than 7 days
        $licencesToCancel = Licence::where('created_at', '<', $sevenDaysAgo)
            ->whereIn('status', ['pending', 'pending_verification'])
            ->get();

        foreach ($licencesToCancel as $licence) {
            $licence->update([
                'status' => 'cancelled'
            ]);

            $this->info("Licence ID {$licence->id} has been cancelled (was {$licence->getOriginal('status')}) - pending for more than 7 days.");
        }

        $this->info("Cancelled {$licencesToCancel->count()} pending licences.");
    }
} 