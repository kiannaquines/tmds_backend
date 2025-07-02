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
        Schema::create('chat', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('conversation_id')->comment('Conversation id from the conversation table')->references('id')->on('conversation')->cascadeOnDelete();
            $table->foreignId('sender')->comment('Sender of the message')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('receiver')->comment('Receiver of the message')->references('id')->on('users')->cascadeOnDelete();
            $table->string('message')->max(255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat');
    }
};
