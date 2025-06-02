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
        Schema::table('licence_requests', function (Blueprint $table) {
            // Supprimer l'ancien champ
            $table->dropColumn('mongo_company_id');

            // Ajouter les nouveaux champs
            $table->string('company_name')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('licence_requests', function (Blueprint $table) {
            // Réinverser les changements
            $table->string('mongo_company_id')->nullable();

            $table->dropColumn([
                'company_name',
                'company_email',
                'company_phone',
                'company_address',
            ]);
        });
    }
};
