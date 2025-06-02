<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('licence_requests', function (Blueprint $table) {
            $table->id();
            $table->string('mongo_company_id');
            $table->enum('type', ['basic', 'professional', 'enterprise']);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'validated', 'rejected', 'expired'])->default('pending');
            $table->dateTime('requested_at')->nullable();
            $table->dateTime('validated_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('validated_by')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('duration_months');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('licence_requests');
    }
}; 