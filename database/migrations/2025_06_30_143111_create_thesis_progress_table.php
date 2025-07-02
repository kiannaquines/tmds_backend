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
        Schema::create('thesis_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->references('id')->on('studies')->cascadeOnDelete();
            $table->foreignId('check_by')->references('id')->on('users')->cascadeOnDelete();
            $table->string('comment');
            $table->enum('status', ['Revise', 'Approved']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thesis_progress');
    }
};
