<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sauvegarder les données existantes
        $payements = DB::table('payements')->get();
        
        // Supprimer la colonne status
        Schema::table('payements', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Recréer la colonne status avec les nouvelles valeurs
        Schema::table('payements', function (Blueprint $table) {
            $table->enum('status', ['pending', 'succeeded', 'failed', 'refunded', 'pending_verification'])
                  ->default('pending')
                  ->after('payment_method');
        });

        // Restaurer les données
        foreach ($payements as $payment) {
            DB::table('payements')
              ->where('id', $payment->id)
              ->update(['status' => $payment->status]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Sauvegarder les données existantes
        $payements = DB::table('payements')->get();
        
        // Supprimer la colonne status
        Schema::table('payements', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Recréer la colonne status avec les anciennes valeurs
        Schema::table('payements', function (Blueprint $table) {
            $table->enum('status', ['pending', 'succeeded', 'failed', 'refunded'])
                  ->default('pending')
                  ->after('payment_method');
        });

        // Restaurer les données
        foreach ($payements as $payment) {
            if (in_array($payment->status, ['pending', 'succeeded', 'failed', 'refunded'])) {
                DB::table('payements')
                  ->where('id', $payment->id)
                  ->update(['status' => $payment->status]);
            } else {
                DB::table('payements')
                  ->where('id', $payment->id)
                  ->update(['status' => 'pending']);
            }
        }
    }
}; 