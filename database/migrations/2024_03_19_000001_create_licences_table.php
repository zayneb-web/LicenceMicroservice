<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('licences', function (Blueprint $table) {
            $table->id();
            $table->string('mongo_company_id');
            $table->foreignId('licence_request_id')->nullable()->constrained('licence_requests');
            $table->enum('type', ['basic', 'professional', 'enterprise']);
            $table->enum('status', ['pending', 'validated', 'paid', 'active', 'expired', 'cancelled'])->default('pending');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->string('stripe_checkout_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('license_key')->unique();
            $table->dateTime('requested_at')->nullable();
            $table->dateTime('validated_at')->nullable();
            $table->dateTime('activated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('licences');
    }
}; 