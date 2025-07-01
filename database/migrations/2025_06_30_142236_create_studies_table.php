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
        Schema::create('studies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete()->comment('Study owner');
            $table->string('title')->comment('Title of the study');
            $table->foreignId(column: 'adviser')->references('id')->on('users')->cascadeOnDelete();
            $table->enum('department',['Department of Accountancy','Department of Agribusiness','Department of Agricultural Economics','Department of Business Administration','Department of Development Management'])->comment('Study department');
            $table->integer('year')->comment('Study year');
            $table->enum('type',['Outline','Manuscript'])->comment('Study status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('studies');
    }
};
