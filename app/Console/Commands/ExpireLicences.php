<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Licence;
use Carbon\Carbon;

class ExpireLicences extends Command
{
    protected $signature = 'licences:expire';
    protected $description = 'Expire licences past their end_date or with duration > 30 days';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $now = Carbon::now();
        
        // 1. Expire licences past their end_date
        $licencesToExpire = Licence::where('end_date', '<', $now)
            ->whereNotIn('status', ['expired', 'cancelled'])
            ->get();

        foreach ($licencesToExpire as $licence) {
            $licence->update([
                'status' => 'expired'
            ]);

            $this->info("Licence ID {$licence->id} has been expired (end_date: {$licence->end_date}).");
        }

        // 2. Expire licences with duration > 30 days
        $licencesWithLongDuration = Licence::whereRaw('DATEDIFF(end_date, start_date) > 30')
            ->whereNotIn('status', ['expired', 'cancelled'])
            ->get();

        foreach ($licencesWithLongDuration as $licence) {
            $licence->update([
                'status' => 'expired'
            ]);

            $duration = Carbon::parse($licence->start_date)->diffInDays($licence->end_date);
            $this->info("Licence ID {$licence->id} has been expired - duration was {$duration} days (> 30 days).");
        }

        $this->info("Expired {$licencesToExpire->count()} past-due licences and {$licencesWithLongDuration->count()} long-duration licences.");
    }
}