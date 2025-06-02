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
        $licences = DB::table('licences')->get();
        
        // Supprimer la colonne status
        Schema::table('licences', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Recréer la colonne status avec les nouvelles valeurs
        Schema::table('licences', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'active', 'expired', 'cancelled', 'pending_verification'])
                  ->default('pending')
                  ->after('type');
        });

        // Restaurer les données
        foreach ($licences as $licence) {
            DB::table('licences')
              ->where('id', $licence->id)
              ->update(['status' => $licence->status]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Sauvegarder les données existantes
        $licences = DB::table('licences')->get();
        
        // Supprimer la colonne status
        Schema::table('licences', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Recréer la colonne status avec les anciennes valeurs
        Schema::table('licences', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'active', 'expired', 'cancelled'])
                  ->default('pending')
                  ->after('type');
        });

        // Restaurer les données
        foreach ($licences as $licence) {
            if (in_array($licence->status, ['pending', 'paid', 'active', 'expired', 'cancelled'])) {
                DB::table('licences')
                  ->where('id', $licence->id)
                  ->update(['status' => $licence->status]);
            } else {
                DB::table('licences')
                  ->where('id', $licence->id)
                  ->update(['status' => 'pending']);
            }
        }
    }
}; 