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
        Schema::create('group_calls', function (Blueprint $table) {
            $table->id();
            $table->string('room_id')->unique();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['video', 'audio'])->default('video');
            $table->enum('status', ['active', 'ended'])->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_calls');
    }
};
