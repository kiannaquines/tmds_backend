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
        Schema::create('checking_logs_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->references('id')->on('studies')->cascadeOnDelete();
            $table->foreignId('check_by')->references('id')->on('users')->cascadeOnDelete();
            $table->dateTime('start_check_date');
            $table->dateTime('end_check_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checking_logs_progress');
    }
};
