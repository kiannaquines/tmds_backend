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
        Schema::create('study_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->references('id')->on('studies')->cascadeOnDelete();
            $table->enum('status',['Pending','In Progress','Complete']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_status');
    }
};
