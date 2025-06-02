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
        Schema::table('licences', function (Blueprint $table) {
            $table->string('company_email')->nullable()->after('mongo_company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('licences', function (Blueprint $table) {
            $table->dropColumn('company_email');
        });
    }
}; 