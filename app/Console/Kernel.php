<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Annuler les licences pending depuis plus de 7 jours (tous les jours à 2h du matin)
        $schedule->command('licences:cancel')->dailyAt('02:00');
        
        // Expirer les licences dont la date de fin est dépassée (tous les jours à 3h du matin)
        $schedule->command('licences:expire')->dailyAt('03:00');
        
        // Expirer les demandes de licence en attente depuis plus d'un mois (tous les jours à 4h du matin)
        $schedule->command('licence-requests:expire-pending')->dailyAt('04:00');
        
        // Mettre à jour les licences expirées (tous les jours à 5h du matin)
        $schedule->command('licences:update-expired')->dailyAt('05:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
