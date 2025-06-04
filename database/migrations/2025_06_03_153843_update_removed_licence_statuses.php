<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // On remplace "active" et "pending_verification" par "paid"
        DB::table('licences')
            ->whereIn('status', ['active', 'pending_verification'])
            ->update(['status' => 'paid']);
    }

    public function down(): void
    {
        // Si tu veux revenir en arrière
        DB::table('licences')
            ->where('status', 'paid')
            ->update(['status' => 'active']); // ou autre valeur par défaut
    }
};
