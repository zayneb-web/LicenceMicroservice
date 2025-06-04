<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LicenceRequest;
use Carbon\Carbon;

class ExpirePendingLicenceRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licence-requests:expire-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire les demandes de licence en attente depuis plus d\'un mois';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        $expiredRequests = LicenceRequest::where('status', 'pending')
            ->where('requested_at', '<', $oneMonthAgo)
            ->get();

        foreach ($expiredRequests as $request) {
            $request->update([
                'status' => 'expired',
                'rejection_reason' => 'Demande expirée automatiquement après un mois d\'inactivité'
            ]);

            // Envoyer une notification
            $request->notify(new \App\Notifications\LicenceRequestStatusUpdated($request));
        }

        $this->info(count($expiredRequests) . ' demandes de licence ont été expirées.');
    }
}
